<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Services\DynamicConfigService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class AppSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Use central role-based middleware instead of hardcoded email check
        $this->middleware(\App\Http\Middleware\EnsureSuperAdmin::class);
    }

    /**
     * Display a listing of the settings.
     */
    public function index(Request $request)
    {
        $category = $request->get('category', 'all');
        
        $query = AppSetting::query();
        
        if ($category !== 'all') {
            $query->where('category', $category);
        }
        
        $settings = $query->orderBy('category')->orderBy('key')->paginate(50);
        $categories = AppSetting::distinct('category')->pluck('category');
        
        return view('admin.app_settings.index', compact('settings', 'categories', 'category'));
    }

    /**
     * Show the form for creating a new setting.
     */
    public function create()
    {
        $categories = AppSetting::distinct('category')->pluck('category');
        return view('admin.app_settings.create', compact('categories'));
    }

    /**
     * Store a newly created setting.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'key' => 'required|string|unique:app_settings,key|max:255',
            'value' => 'nullable|string',
            'type' => 'required|in:string,integer,boolean,float,array,json',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_public' => 'boolean',
            'requires_restart' => 'boolean',
        ]);

        $data['updated_by'] = Auth::id();

        AppSetting::create($data);

        // Clear cache
        DynamicConfigService::clearCache();

        return redirect()->route('admin.app-settings.index')
            ->with('success', 'Setting created successfully.');
    }

    /**
     * Show the form for editing the specified setting.
     */
    public function edit(AppSetting $appSetting)
    {
        $categories = AppSetting::distinct('category')->pluck('category');
        return view('admin.app_settings.edit', compact('appSetting', 'categories'));
    }

    /**
     * Update the specified setting.
     */
    public function update(Request $request, AppSetting $appSetting)
    {
        $data = $request->validate([
            'value' => 'nullable|string',
            'type' => 'required|in:string,integer,boolean,float,array,json',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'is_public' => 'boolean',
            'requires_restart' => 'boolean',
        ]);

        $data['updated_by'] = Auth::id();

        $appSetting->update($data);

        // Clear cache
        DynamicConfigService::clearCache();

        $message = 'Setting updated successfully.';
        if ($appSetting->requires_restart) {
            $message .= ' Note: This setting requires application restart to take full effect.';
        }

        return redirect()->route('admin.app-settings.index')
            ->with('success', $message);
    }

    /**
     * Remove the specified setting.
     */
    public function destroy(AppSetting $appSetting)
    {
        $appSetting->delete();
        
        // Clear cache
        DynamicConfigService::clearCache();

        return redirect()->route('admin.app-settings.index')
            ->with('success', 'Setting deleted successfully.');
    }

    /**
     * Clear all settings cache
     */
    public function clearCache()
    {
        DynamicConfigService::clearCache();
        
        return back()->with('success', 'Settings cache cleared successfully.');
    }

    /**
     * Export settings as JSON
     */
    public function export()
    {
        $settings = AppSetting::all();
        
        $export = $settings->map(function ($setting) {
            return [
                'key' => $setting->key,
                'value' => $setting->value,
                'type' => $setting->type,
                'category' => $setting->category,
                'description' => $setting->description,
                'is_public' => $setting->is_public,
                'requires_restart' => $setting->requires_restart,
            ];
        });

        $filename = 'app_settings_' . date('Y-m-d_H-i-s') . '.json';
        
        return response()->json($export, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Get public settings for API
     */
    public function publicSettings()
    {
        $settings = DynamicConfigService::getPublicSettings();
        
        return response()->json([
            'success' => true,
            'data' => $settings
        ]);
    }
}
