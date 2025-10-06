<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\MessageBag;
use App\Services\OrderIdGenerator;
use App\Services\SecureFileUploadService;

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
            // handle per-method logo upload with security validation
            if ($request->hasFile("methods.{$id}.logo")) {
                $file = $request->file("methods.{$id}.logo");
                
                if ($file->isValid()) {
                    $secureUploadService = new SecureFileUploadService();
                    $validation = $secureUploadService->validateUploadedFile($file, 'image');
                    
                    if ($validation['valid']) {
                        $uploadResult = $secureUploadService->storeSecurely($file, 'payment_logos', 'image', 'public');
                        
                        if ($uploadResult['success']) {
                            // Delete old logo if exists
                            if ($method->logo_url && strpos($method->logo_url, 'storage/') === 0) {
                                $oldPath = str_replace('storage/', '', $method->logo_url);
                                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
                            }
                            
                            $method->logo_url = 'storage/' . $uploadResult['path'];
                            
                            Log::info('Payment method logo updated', [
                                'method_id' => $method->id,
                                'original_filename' => $file->getClientOriginalName(),
                                'stored_path' => $uploadResult['path']
                            ]);
                        } else {
                            Log::error('Payment method logo upload failed', [
                                'method_id' => $method->id,
                                'errors' => $uploadResult['errors']
                            ]);
                        }
                    } else {
                        Log::warning('Payment method logo validation failed', [
                            'method_id' => $method->id,
                            'filename' => $file->getClientOriginalName(),
                            'errors' => $validation['errors']
                        ]);
                    }
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

        // handle logo upload with security validation
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            
            if ($file->isValid()) {
                $secureUploadService = new SecureFileUploadService();
                $validation = $secureUploadService->validateUploadedFile($file, 'image');
                
                if (!$validation['valid']) {
                    Log::warning('Payment method logo creation failed validation', [
                        'filename' => $file->getClientOriginalName(),
                        'errors' => $validation['errors']
                    ]);
                    
                    return back()->withErrors(['logo' => 'Invalid logo file: ' . implode(', ', $validation['errors'])]);
                }
                
                $uploadResult = $secureUploadService->storeSecurely($file, 'payment_logos', 'image', 'public');
                
                if (!$uploadResult['success']) {
                    Log::error('Payment method logo storage failed', [
                        'filename' => $file->getClientOriginalName(),
                        'errors' => $uploadResult['errors']
                    ]);
                    
                    return back()->withErrors(['logo' => 'Logo storage failed: ' . implode(', ', $uploadResult['errors'])]);
                }
                
                $attrs['logo_url'] = 'storage/' . $uploadResult['path'];
                
                Log::info('Payment method logo created successfully', [
                    'original_filename' => $file->getClientOriginalName(),
                    'stored_path' => $uploadResult['path'],
                    'file_size' => $uploadResult['size']
                ]);
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
     * Only available in non-production environments.
     */
    public function test($id)
    {
        // Block test endpoint in production
        if (app()->environment('production')) {
            return response()->json(['error' => 'Test endpoint not available in production'], 403);
        }

        $method = PaymentMethod::find($id);
        if (! $method) return response()->json(['error' => 'Method not found'], 404);

        // require server key
        $serverKey = config('services.midtrans.server_key');
        if (! $serverKey) return response()->json(['error' => 'Midtrans server key not configured'], 500);

        $orderId = OrderIdGenerator::generate('test');
        $testAmount = \App\Services\DynamicConfigService::get('test_payment_amount', 1000);

        $payload = [
            'transaction_details' => [ 'order_id' => $orderId, 'gross_amount' => $testAmount ],
            'item_details' => [[ 'id' => 'test_item', 'price' => $testAmount, 'quantity' => 1, 'name' => 'Test Payment' ]]
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

        $midtransBase = config('constants.api_endpoints.midtrans_base');
        $midtransSandbox = config('constants.api_endpoints.midtrans_sandbox');
        $midtransUrl = config('services.midtrans.is_production') 
            ? "{$midtransBase}/snap/v1/transactions" 
            : "{$midtransSandbox}/snap/v1/transactions";
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
