<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        // If a redirect_to query is present (e.g., coming from buy page), set intended URL
        $redirect = $request->query('redirect_to');
        if ($redirect) {
            $request->session()->put('url.intended', $redirect);
        }
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        try {
            $request->authenticate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Attach a friendly error message and redirect back with old input
            return redirect()->back()->withInput($request->only('email', 'remember'))->withErrors($e->errors())->with('error', 'Login gagal. Periksa email dan password Anda.');
        }

        $request->session()->regenerate();

    return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

    $request->session()->regenerateToken();

    // after logout, redirect visitors to the public company profile at /ndeofficial
    // use the named route if available so URL generation follows app configuration
    if (function_exists('route')) {
        return redirect()->route('compro');
    }
    return redirect(url('/ndeofficial'));
    }
}
