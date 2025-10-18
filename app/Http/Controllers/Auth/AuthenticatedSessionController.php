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

        // If the authenticated user's email looks like an admin account (ends with @admin),
        // redirect them to the admin dashboard immediately.
        $user = Auth::user();
        if ($user && is_string($user->email) && str_ends_with(strtolower($user->email), '@admin')) {
            return redirect()->intended(url('/admin'));
        }

        // If the user already owns a package, send them directly to the lesson viewer
        // so they can continue learning immediately instead of landing on the register page.
        try {
            if ($user && ! empty($user->package_id)) {
                // Find the first available course lesson to use as landing
                $first = \App\Models\Lesson::where('type', 'course')->orderBy('position')->first();
                if ($first) {
                    return redirect()->intended(route('kelas.show', ['lesson' => $first->id]));
                }
            }
        } catch (\Throwable $e) {
            // If anything goes wrong, fall back to the default intended route
            \Illuminate\Support\Facades\Log::warning('Login redirect: failed to resolve first lesson', ['err' => $e->getMessage(), 'user_id' => $user ? $user->id : null]);
        }

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
