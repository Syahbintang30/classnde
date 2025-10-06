<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Package;

class SettingsController extends Controller
{
    public function index()
    {
        // Get all current settings
        $settings = Setting::all()->keyBy('key');
        
        // Get available packages for reference
        $packages = Package::orderBy('name')->get();
        
        return view('admin.settings.index', compact('settings', 'packages'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'intermediate_package_id' => 'nullable|integer|exists:packages,id',
            'intermediate_package_slugs' => 'nullable|string|max:500',
            'coaching.max_booking_days_ahead' => 'nullable|integer|min:1|max:365',
            'coaching.session_duration_minutes' => 'nullable|integer|min:15|max:240',
            'coaching.buffer_minutes_before' => 'nullable|integer|min:0|max:60',
            'coaching.buffer_minutes_after' => 'nullable|integer|min:0|max:120',
            'notifications.admin_booking_enabled' => 'nullable|boolean',
            'notifications.user_booking_status_enabled' => 'nullable|boolean',
        ]);

        // Convert boolean values to string for database storage
        foreach ($validated as $key => $value) {
            if (is_bool($value)) {
                $validated[$key] = $value ? 'true' : 'false';
            }
        }

        // Update settings
        foreach ($validated as $key => $value) {
            if ($value !== null) {
                Setting::set($key, $value);
            }
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    public function reset()
    {
        // Reset to default values
        Setting::set('intermediate_package_id', '2');
        Setting::set('intermediate_package_slugs', 'intermediate,upgrade-intermediate');
        Setting::set('coaching.max_booking_days_ahead', '30');
        Setting::set('coaching.session_duration_minutes', '60');
        Setting::set('coaching.buffer_minutes_before', '10');
        Setting::set('coaching.buffer_minutes_after', '60');
        Setting::set('notifications.admin_booking_enabled', 'true');
        Setting::set('notifications.user_booking_status_enabled', 'true');

        return redirect()->back()->with('success', 'Settings reset to default values.');
    }
}