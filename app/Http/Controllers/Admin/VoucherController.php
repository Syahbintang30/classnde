<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function index()
    {
        $vouchers = Voucher::orderBy('created_at','desc')->get();
        return view('admin.vouchers.index', compact('vouchers'));
    }

    public function create()
    {
        return view('admin.vouchers.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'code' => 'required|string|unique:vouchers,code',
            'discount_percent' => 'required|integer|min:0|max:100',
            'active' => 'sometimes',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
        ]);

        $data['active'] = $r->has('active');
        Voucher::create($data);
        return redirect()->route('admin.vouchers.index')->with('success','Voucher created');
    }

    public function edit(Voucher $voucher)
    {
        return view('admin.vouchers.edit', compact('voucher'));
    }

    public function update(Request $r, Voucher $voucher)
    {
        $data = $r->validate([
            'code' => 'required|string|unique:vouchers,code,'.$voucher->id,
            'discount_percent' => 'required|integer|min:0|max:100',
            'active' => 'sometimes',
            'usage_limit' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
        ]);
        $data['active'] = $r->has('active');
        $voucher->update($data);
        return redirect()->route('admin.vouchers.index')->with('success','Voucher updated');
    }

    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return redirect()->route('admin.vouchers.index')->with('success','Voucher deleted');
    }
}
