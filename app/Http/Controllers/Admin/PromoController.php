<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class PromoController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:admin');
    }

    public function edit()
    {
        $guid = Setting::get('nde.promo_bunny_guid', null);
        $title = Setting::get('nde.promo_title', null);
        return view('admin.promo_settings', compact('guid','title'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'promo_bunny_guid' => ['nullable','string'],
            'promo_title' => ['nullable','string','max:255'],
        ]);

        Setting::set('nde.promo_bunny_guid', $data['promo_bunny_guid'] ?? '');
        Setting::set('nde.promo_title', $data['promo_title'] ?? '');

        return redirect()->route('admin.settings.promo')->with('success','Promo settings updated.');
    }
}
