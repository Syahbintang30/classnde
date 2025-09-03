<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use App\Services\OrderIdGenerator;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $methods = PaymentMethod::orderBy('name')->get();
        return view('admin.payment_methods.index', compact('methods'));
    }

    public function update(Request $request)
    {
        $data = $request->input('methods', []);

        // validate per-method rules (midtrans_bank required when midtrans_code == bank_transfer)
        $allErrors = new MessageBag();
        foreach ($data as $id => $attrs) {
            $v = Validator::make($attrs, [
                'midtrans_code' => 'nullable|string|max:50',
                'midtrans_bank' => 'required_if:midtrans_code,bank_transfer|string|max:50',
            ]);
            if ($v->fails()) {
                $allErrors->merge($v->errors());
            }
        }
        if ($allErrors->any()) {
            return redirect()->back()->withErrors($allErrors)->withInput();
        }

        foreach ($data as $id => $attrs) {
            $method = PaymentMethod::find($id);
            if (! $method) continue;
            $method->account_details = $attrs['account_details'] ?? $method->account_details;
            $method->is_active = isset($attrs['is_active']) ? boolval($attrs['is_active']) : false;
            $method->midtrans_code = $attrs['midtrans_code'] ?? $method->midtrans_code;
            $method->midtrans_bank = $attrs['midtrans_bank'] ?? $method->midtrans_bank;
            // handle per-method logo upload
            if ($request->hasFile("methods.{$id}.logo")) {
                $file = $request->file("methods.{$id}.logo");
                if ($file->isValid()) {
                    $path = $file->store('payment_logos', 'public');
                    $method->logo_url = 'storage/' . $path;
                }
            }
            $method->save();
        }

        return redirect()->back()->with('status', 'Payment methods updated.');
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|string|max:100|unique:payment_methods,name',
            'display_name' => 'required|string|max:150',
            'account_details' => 'nullable|string|max:255',
            'midtrans_code' => 'nullable|string|max:50',
            'midtrans_bank' => 'nullable|string|max:50',
            'logo' => 'nullable|file|image|max:2048',
            'is_active' => 'nullable|boolean',
        ];

        // conditional rule
        if ($request->input('midtrans_code') === 'bank_transfer') {
            $rules['midtrans_bank'] = 'required|string|max:50';
        }

        $attrs = $request->validate($rules);


        $attrs['is_active'] = isset($attrs['is_active']) ? boolval($attrs['is_active']) : false;

        // handle logo upload
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            if ($file->isValid()) {
                $path = $file->store('payment_logos', 'public');
                $attrs['logo_url'] = 'storage/' . $path;
            }
        }

    // include midtrans fields explicitly
    $attrs['midtrans_code'] = $request->input('midtrans_code');
    $attrs['midtrans_bank'] = $request->input('midtrans_bank');

    PaymentMethod::create($attrs);

        return redirect()->back()->with('status', 'Payment method created.');
    }

    public function destroy($id)
    {
        $method = PaymentMethod::find($id);
        if (! $method) {
            return redirect()->back()->with('status', 'Payment method not found.');
        }

        // attempt to unlink logo file if stored in storage
        try {
            if ($method->logo_url && strpos($method->logo_url, 'storage/') === 0) {
                $path = str_replace('storage/', 'public/', $method->logo_url);
                // use storage helper to delete
                \Illuminate\Support\Facades\Storage::delete(str_replace('public/', '', $path));
            }
        } catch (\Exception $e) {
            // ignore deletion errors
        }

        $method->delete();

        return redirect()->back()->with('status', 'Payment method deleted.');
    }

    /**
     * Test creating a Midtrans snap token for a given payment method (admin-only helper).
     */
    public function test($id)
    {
        $method = PaymentMethod::find($id);
        if (! $method) return response()->json(['error' => 'Method not found'], 404);

        // require server key
        $serverKey = config('services.midtrans.server_key');
        if (! $serverKey) return response()->json(['error' => 'Midtrans server key not configured'], 500);

    $orderId = OrderIdGenerator::generate('nde');
        $gross = 1000; // small test amount

        $payload = [
            'transaction_details' => [ 'order_id' => $orderId, 'gross_amount' => $gross ],
            'item_details' => [[ 'id' => 'test', 'price' => $gross, 'quantity' => 1, 'name' => 'Test' ]]
        ];

        // map method midtrans_code
        if ($method->midtrans_code) {
            $code = strtolower($method->midtrans_code);
            if ($code === 'qris') $payload['enabled_payments'] = ['qris'];
            elseif ($code === 'gopay') $payload['enabled_payments'] = ['gopay'];
            elseif ($code === 'shopeepay') $payload['enabled_payments'] = ['shopeepay'];
            elseif ($code === 'credit_card') $payload['enabled_payments'] = ['credit_card'];
            elseif ($code === 'bank_transfer') $payload['enabled_payments'] = ['bank_transfer'];
        }

        $midtransUrl = config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/v1/transactions' : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
        $auth = base64_encode($serverKey . ':');

    $res = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . $auth,
        ])->post($midtransUrl, $payload);

        if (! $res->successful()) {
            return response()->json(['error' => 'Midtrans request failed', 'body' => $res->body()], 500);
        }

        return response()->json($res->json());
    }
}
