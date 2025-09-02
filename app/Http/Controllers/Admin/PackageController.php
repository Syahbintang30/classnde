<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use Illuminate\Support\Facades\Storage;

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
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('packages', 'public');
            $data['image'] = $path;
        }

        Package::create($data);

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
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // delete old image if exists
            try {
                if ($package->image && \Storage::disk('public')->exists($package->image)) {
                    \Storage::disk('public')->delete($package->image);
                }
            } catch (\Throwable $e) {
                // ignore deletion errors
            }

            $path = $request->file('image')->store('packages', 'public');
            $data['image'] = $path;
        }

        $package->update($data);

        return redirect()->route('admin.packages.index')->with('status', 'Package updated');
    }
}
