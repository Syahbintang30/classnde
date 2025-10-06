<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Services\SecureFileUploadService;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::orderBy('id')->get();
        return view('admin.packages.index', compact('packages'));
    }

    public function edit(Package $package)
    {
        return view('admin.packages.edit', compact('package'));
    }

    public function create()
    {
        return view('admin.packages.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:packages,slug',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'benefits' => 'nullable|string',
            'image' => 'nullable|file|max:3072', // 3MB max
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $secureUploadService = new SecureFileUploadService();
            
            // Validate file with comprehensive security checks
            $validation = $secureUploadService->validateUploadedFile($file, 'image');
            
            if (!$validation['valid']) {
                Log::warning('Package image upload failed validation', [
                    'filename' => $file->getClientOriginalName(),
                    'errors' => $validation['errors'],
                    'ip' => $request->ip(),
                    'user_id' => Auth::id()
                ]);
                
                return back()->withErrors(['image' => 'Invalid image file: ' . implode(', ', $validation['errors'])]);
            }
            
            // Use secure storage
            $uploadResult = $secureUploadService->storeSecurely($file, 'packages', 'image', 'public');
            
            if (!$uploadResult['success']) {
                Log::error('Package image secure storage failed', [
                    'filename' => $file->getClientOriginalName(),
                    'errors' => $uploadResult['errors'],
                    'ip' => $request->ip(),
                    'user_id' => Auth::id()
                ]);
                
                return back()->withErrors(['image' => 'Image storage failed: ' . implode(', ', $uploadResult['errors'])]);
            }
            
            $data['image'] = $uploadResult['path'];
            
            Log::info('Package image uploaded successfully', [
                'original_filename' => $file->getClientOriginalName(),
                'stored_path' => $uploadResult['path'],
                'file_size' => $uploadResult['size'],
                'ip' => $request->ip(),
                'user_id' => Auth::id()
            ]);
        }

        $pkg = Package::create($data);

        // Domain audit context
        try {
            \App\Models\AuditTrail::create([
                'user_id' => Auth::id(),
                'action' => 'PACKAGE_CREATE',
                'entity_type' => Package::class,
                'entity_id' => $pkg->id,
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'metadata' => [
                    'name' => $pkg->name,
                    'slug' => $pkg->slug,
                    'price' => $pkg->price,
                ],
            ]);
        } catch (\Throwable $e) { /* swallow audit failures */ }

        return redirect()->route('admin.packages.index')->with('status', 'Package created');
    }

    public function update(Request $request, Package $package)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'benefits' => 'nullable|string',
            'image' => 'nullable|file|max:3072', // 3MB max
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $secureUploadService = new SecureFileUploadService();
            
            // Validate file with comprehensive security checks
            $validation = $secureUploadService->validateUploadedFile($file, 'image');
            
            if (!$validation['valid']) {
                Log::warning('Package image update failed validation', [
                    'package_id' => $package->id,
                    'filename' => $file->getClientOriginalName(),
                    'errors' => $validation['errors'],
                    'ip' => $request->ip(),
                    'user_id' => Auth::id()
                ]);
                
                return back()->withErrors(['image' => 'Invalid image file: ' . implode(', ', $validation['errors'])]);
            }
            
            // Delete old image if exists
            try {
                if ($package->image && Storage::disk('public')->exists($package->image)) {
                    Storage::disk('public')->delete($package->image);
                    Log::info('Old package image deleted', [
                        'package_id' => $package->id,
                        'old_image_path' => $package->image
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to delete old package image: ' . $e->getMessage(), [
                    'package_id' => $package->id,
                    'old_image_path' => $package->image
                ]);
            }
            
            // Use secure storage
            $uploadResult = $secureUploadService->storeSecurely($file, 'packages', 'image', 'public');
            
            if (!$uploadResult['success']) {
                Log::error('Package image update secure storage failed', [
                    'package_id' => $package->id,
                    'filename' => $file->getClientOriginalName(),
                    'errors' => $uploadResult['errors'],
                    'ip' => $request->ip(),
                    'user_id' => Auth::id()
                ]);
                
                return back()->withErrors(['image' => 'Image storage failed: ' . implode(', ', $uploadResult['errors'])]);
            }
            
            $data['image'] = $uploadResult['path'];
            
            Log::info('Package image updated successfully', [
                'package_id' => $package->id,
                'original_filename' => $file->getClientOriginalName(),
                'stored_path' => $uploadResult['path'],
                'file_size' => $uploadResult['size'],
                'ip' => $request->ip(),
                'user_id' => Auth::id()
            ]);
        }

        $package->update($data);

        try {
            \App\Models\AuditTrail::create([
                'user_id' => Auth::id(),
                'action' => 'PACKAGE_UPDATE',
                'entity_type' => Package::class,
                'entity_id' => $package->id,
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'metadata' => [
                    'name' => $package->name,
                    'slug' => $package->slug,
                    'price' => $package->price,
                ],
            ]);
        } catch (\Throwable $e) { /* swallow audit failures */ }

        return redirect()->route('admin.packages.index')->with('status', 'Package updated');
    }
    
    public function destroy(Package $package)
    {
        $id = $package->id;
        $name = $package->name;
        $slug = $package->slug;
        $price = $package->price;
        $package->delete();
        try {
            \App\Models\AuditTrail::create([
                'user_id' => Auth::id(),
                'action' => 'PACKAGE_DELETE',
                'entity_type' => Package::class,
                'entity_id' => $id,
                'ip_address' => request()->ip(),
                'user_agent' => (string) request()->userAgent(),
                'metadata' => compact('name','slug','price'),
            ]);
        } catch (\Throwable $e) { /* swallow audit failures */ }
        return redirect()->route('admin.packages.index')->with('success','package deleted');
    }
}
