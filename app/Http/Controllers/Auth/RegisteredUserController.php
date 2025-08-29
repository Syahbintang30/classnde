<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:30'],
            'referral' => ['nullable', 'string', 'max:64'],
            'selected_package' => ['nullable', 'integer', 'exists:packages,id'],
            'package_id' => ['nullable', 'integer', 'exists:packages,id'],
        ]);

        // If the form included a selected package, don't create the user yet.
        // Store registration data in session temporarily and redirect to the purchase/payment page.
        if ($request->filled('selected_package') || $request->filled('package_id')) {
            $pkg = $request->input('selected_package') ?: $request->input('package_id');
            // Keep registration input in session until payment completes. We'll create the user after payment.
            $request->session()->put('pre_register', [
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'password' => $request->input('password'), // plain for now; will hash when creating the user after payment
                'referral' => $request->input('referral') ?: $request->session()->get('referral') ?: null,
                'package_id' => $pkg,
                'package_qty' => $request->input('package_qty') ? intval($request->input('package_qty')) : 1,
            ]);

            // redirect to the buy/payment page (use buy route which renders the payment view)
            $firstLesson = \App\Models\Lesson::orderBy('position')->first();
            return redirect(route('kelas.buy', ['lesson' => $firstLesson->id ?? null, 'package_id' => $pkg]));
        }

        // No package selected: proceed with normal immediate registration flow
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone ?? null,
            // lesson_id removed; use package_id only
            'package_id' => $request->input('selected_package') ?: $request->input('package_id') ?: null,
            'referred_by' => null,
        ]);

        // If referral code present on the form, resolve it and set referred_by
        if ($request->filled('referral')) {
            $refCode = $request->input('referral');
            $referrer = User::where('referral_code', $refCode)->first();
            if ($referrer) {
                $user->referred_by = $referrer->id;
                $user->save();
            } else {
                // invalid referral code supplied — reject the registration with message
                return redirect()->back()->withInput()->withErrors(['referral' => 'Referral code not valid.']);
            }
        }

        event(new Registered($user));

        Auth::login($user);

    return redirect(route('registerclass', absolute: false));
    }
}
