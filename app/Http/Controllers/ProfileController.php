<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $package = null;
        if ($user->package_id) $package = \App\Models\Package::find($user->package_id);

        $tickets = \App\Models\CoachingTicket::where('user_id', $user->id)->orderByDesc('id')->get();
        $unusedTicketCount = $tickets->where('is_used', false)->count();

        $bookings = \App\Models\CoachingBooking::where('user_id', $user->id)->where('status', '!=', 'cancelled')->orderBy('booking_time')->get();

        return view('profile', compact('user', 'package', 'tickets', 'unusedTicketCount', 'bookings'));
    }

    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'photo' => 'nullable|image|max:4096',
        ]);

        $user->name = $data['name'];
        if ($user->email !== $data['email']) {
            $user->email = $data['email'];
            $user->email_verified_at = null;
        }
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        // Handle profile photo upload
            // If user requested to remove photo
            if ($request->has('remove_photo')) {
                if ($user->photo && ! preg_match('#^https?://#i', $user->photo)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($user->photo);
                }
                $user->photo = null;
                $user->save();
                return Redirect::route('profile.edit')->with('status', 'profile-updated')->with('success', 'Profile photo removed.');
            }

            if ($request->hasFile('photo')) {
            try {
                $file = $request->file('photo');
                $path = $file->store('user_photos', 'public');
                // delete old photo if present and not an external URL
                if ($user->photo && ! preg_match('#^https?://#i', $user->photo)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($user->photo);
                }
                $user->photo = $path;
            } catch (\Throwable $e) {
                // log and continue (validation already checks file type/size)
                logger()->error('Failed saving profile photo: ' . $e->getMessage());
            }
        }
            $user->save();

            // The Blade partial checks session('status') === 'profile-updated'
            return Redirect::route('profile.edit')->with('status', 'profile-updated')->with('success', 'Profile updated.');
    }

    /**
     * Update only the user's password (used by the password form).
     */
    public function updatePassword(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'current_password' => ['required','current_password'],
            'password' => ['required','string','min:8','confirmed'],
        ]);

        $user->password = Hash::make($data['password']);
        $user->save();

        // The Blade partial checks session('status') === 'password-updated'
        return Redirect::route('profile.edit')->with('status', 'password-updated');
    }

    /**
     * Show users referred by the current user.
     */
    public function referrals(Request $request)
    {
        $user = $request->user();
        $referred = \App\Models\User::where('referred_by', $user->id)->orderByDesc('id')->get();
        return view('profile.referrals', compact('user', 'referred'));
    }

    /**
     * Delete the user's account after confirming password.
     */
    public function destroy(Request $request)
    {
        $user = $request->user();

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'password' => ['required','current_password'],
        ]);

        if ($validator->fails()) {
            // Use error bag name 'userDeletion' to match tests expectations
            return redirect()->route('profile')->withInput()->withErrors($validator->errors(), 'userDeletion');
        }

        // Delete and logout
        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
