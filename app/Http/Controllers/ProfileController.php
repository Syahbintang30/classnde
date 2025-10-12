<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Log;
use App\Services\SecureFileUploadService;
use App\Services\InputSanitizationService;

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

        // Referral metrics
        $referredCount = \App\Models\User::where('referred_by', $user->id)->count();
        $availableUnits = \App\Services\ReferralService::availableCoachingUnits($user);
        $referralDiscountPercent = \App\Services\ReferralService::referrerCoachingDiscountPercent($user);
        $redeemedUnits = \App\Models\ReferralRedemption::where('user_id', $user->id)->sum('units');

        return view('profile', compact('user', 'package', 'tickets', 'unusedTicketCount', 'bookings', 'referredCount', 'availableUnits', 'referralDiscountPercent', 'redeemedUnits'));
    }

    public function edit(Request $request)
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        $photoMaxSizeKb = config('constants.validation.photo_max_size_kb');
        $nameMaxLength = config('constants.validation.name_max_length');
        $emailMaxLength = config('constants.validation.email_max_length');
        $passwordMinLength = config('constants.security.password_min_length');
        
        $data = $request->validate([
            'name' => "required|string|max:{$nameMaxLength}",
            'email' => "required|email|max:{$emailMaxLength}",
            'password' => "nullable|string|min:{$passwordMinLength}|confirmed",
            'photo' => "nullable|file|max:{$photoMaxSizeKb}",
        ]);

        // Sanitize input data
        $data['name'] = InputSanitizationService::sanitizeString($data['name'], ['max_length' => $nameMaxLength]);
        $data['email'] = InputSanitizationService::sanitizeEmail($data['email']);
        
        // Detect potential XSS/SQL injection attempts
        InputSanitizationService::detectXSS($data['name'], 'name');
        InputSanitizationService::detectXSS($data['email'], 'email');
        InputSanitizationService::detectSQLInjection($data['name'], 'name');

        $user->name = $data['name'];
        if ($user->email !== $data['email']) {
            $user->email = $data['email'];
            $user->email_verified_at = null;
        }
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        // Handle profile photo upload with enhanced security
        // If user requested to remove photo
        if ($request->has('remove_photo')) {
            if ($user->photo && ! preg_match('#^https?://#i', $user->photo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->photo);
            }
            $user->photo = null;
            $user->save();
            
            Log::info('Profile photo removed', [
                'user_id' => $user->id,
                'ip' => $request->ip()
            ]);
            
            return Redirect::route('profile.edit')->with('status', 'profile-updated')->with('success', 'Profile photo removed.');
        }

        if ($request->hasFile('photo')) {
            try {
                $file = $request->file('photo');
                $secureUploadService = new SecureFileUploadService();
                
                // Validate file with comprehensive security checks
                $validation = $secureUploadService->validateUploadedFile($file, 'image');
                
                if (!$validation['valid']) {
                    Log::warning('Profile photo upload failed validation', [
                        'user_id' => $user->id,
                        'filename' => $file->getClientOriginalName(),
                        'errors' => $validation['errors'],
                        'ip' => $request->ip()
                    ]);
                    
                    return Redirect::route('profile.edit')
                        ->withErrors(['photo' => 'Invalid file: ' . implode(', ', $validation['errors'])]);
                }
                
                // Use secure storage with the service
                $uploadResult = $secureUploadService->storeSecurely($file, 'user_photos', 'image', 'public');
                
                if (!$uploadResult['success']) {
                    Log::error('Profile photo secure storage failed', [
                        'user_id' => $user->id,
                        'filename' => $file->getClientOriginalName(),
                        'errors' => $uploadResult['errors'],
                        'ip' => $request->ip()
                    ]);
                    
                    return Redirect::route('profile.edit')
                        ->withErrors(['photo' => 'File storage failed: ' . implode(', ', $uploadResult['errors'])]);
                }
                
                // Delete old photo if present and not an external URL
                if ($user->photo && ! preg_match('#^https?://#i', $user->photo)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($user->photo);
                }
                
                $user->photo = $uploadResult['path'];
                
                Log::info('Profile photo uploaded successfully', [
                    'user_id' => $user->id,
                    'original_filename' => $file->getClientOriginalName(),
                    'stored_path' => $uploadResult['path'],
                    'file_size' => $uploadResult['size'],
                    'ip' => $request->ip()
                ]);
                
            } catch (\Throwable $e) {
                Log::error('Profile photo upload exception: ' . $e->getMessage(), [
                    'user_id' => $user->id,
                    'filename' => $request->hasFile('photo') ? $request->file('photo')->getClientOriginalName() : 'unknown',
                    'ip' => $request->ip(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                return Redirect::route('profile.edit')
                    ->withErrors(['photo' => 'Photo upload failed. Please try again.']);
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
        $availableUnits = \App\Services\ReferralService::availableCoachingUnits($user);
        $referralDiscountPercent = \App\Services\ReferralService::referrerCoachingDiscountPercent($user);
        $redeemedUnits = \App\Models\ReferralRedemption::where('user_id', $user->id)->sum('units');
        return view('profile.referrals', compact('user', 'referred', 'availableUnits', 'referralDiscountPercent', 'redeemedUnits'));
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
