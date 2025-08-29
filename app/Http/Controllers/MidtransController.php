<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class MidtransController extends Controller
{
    public function createSnapToken(Request $request)
    {
    // expect gross_amount, payment_method and package_id (optional); support package_qty and package_unit_price
    $data = $request->only(['order_id', 'gross_amount', 'package_id', 'payment_method', 'package_qty', 'package_unit_price', 'referral']);
        // basic validation: gross_amount required
        if (empty($data['gross_amount'])) {
            return response()->json(['error' => 'gross_amount is required'], 422);
        }
        $serverKey = config('services.midtrans.server_key');
        if (! $serverKey) {
            return response()->json(['error' => 'Midtrans server key not configured'], 500);
        }

    // allow guest orders: don't require authentication here.
    // Transactions created without an authenticated user will have user_id=null
    // and should be associated later during paymentComplete using session('pre_register') or other flows.

        // package_id is required for purchases so the webhook can grant access to the correct package
        if (empty($data['package_id'])) {
            return response()->json(['error' => 'package_id is required'], 422);
        }

        // create an external order id that Midtrans will use (use timestamp+random to ensure unique)
        $externalOrderId = 'nde-' . time() . '-' . Str::random(6);

        // Create a local Transaction record (pending) to reference during callbacks
        $qty = (int) ($data['package_qty'] ?? 1);
        $unit = (int) ($data['package_unit_price'] ?? 0);

        // If a referral code was provided, validate it and apply discount
    // prefer DB-configured setting if present
    $dbVal = \App\Models\Setting::get('referral.discount_percent', null);
    $discountPercent = $dbVal !== null ? (int) $dbVal : (int) config('referral.discount_percent', 2);
        $appliedDiscountPercent = 0;
        if (! empty($data['referral'])) {
            $ref = \App\Models\User::where('referral_code', $data['referral'])->first();
            if ($ref) {
                $appliedDiscountPercent = $discountPercent;
            }
        }

        // calculate unit price: if unit not provided, fallback to package price lookup
        if (empty($unit) && ! empty($data['package_id'])) {
            $pkg = \App\Models\Package::find($data['package_id']);
            $unit = $pkg ? (int) $pkg->price : 0;
        }

        $rawGross = $unit * max(1, $qty);
        if ($appliedDiscountPercent > 0) {
            $gross = (int) round($rawGross * (100 - $appliedDiscountPercent) / 100);
        } else {
            $gross = (int) $rawGross;
        }

        $transaction = Transaction::create([
            'order_id' => $externalOrderId,
            'user_id' => Auth::check() ? Auth::id() : null,
            'lesson_id' => null,
            'package_id' => $data['package_id'] ?? null,
            'method' => $data['payment_method'] ?? null,
            'amount' => $gross,
            'original_amount' => $rawGross,
            'referral_code' => $data['referral'] ?? null,
            'referrer_user_id' => (!empty($data['referral']) && isset($ref) && $ref) ? $ref->id : null,
            'status' => 'pending',
            'midtrans_response' => null,
        ]);

        // Create payload required by Midtrans Snap API
        $payload = [
            'transaction_details' => [
                'order_id' => $externalOrderId,
                'gross_amount' => (int) $gross,
            ],
            'item_details' => [],
        ];

    $itemId = !empty($data['package_id']) ? ('package-'.$data['package_id']) : 'item';
    $itemName = !empty($data['package_id']) ? ('Package ' . $data['package_id']) : 'Item';
        $payload['item_details'][] = [
            'id' => $itemId,
            'price' => (int) ($unit * (100 - $appliedDiscountPercent) / 100),
            'quantity' => max(1, $qty),
            'name' => $itemName . ($appliedDiscountPercent ? (' (Referral ' . $appliedDiscountPercent . '%)') : ''),
        ];

        // Map internal payment_method to Midtrans enabled_payments if provided
        if (! empty($data['payment_method'])) {
            $pm = strtolower($data['payment_method']);
            $enabled = [];
            if ($pm === 'qris' || $pm === 'qr') {
                $enabled = ['qris'];
            } elseif (in_array($pm, ['gopay','go-pay','go_pay'])) {
                $enabled = ['gopay'];
            } elseif (in_array($pm, ['shopeepay','shopee'])) {
                $enabled = ['shopeepay'];
            } elseif (in_array($pm, ['credit_card','card'])) {
                $enabled = ['credit_card'];
            } elseif (in_array($pm, ['bca','bni','bri','mandiri','permata'])) {
                // request bank transfer; Midtrans will show available banks—this narrows to bank_transfer
                $enabled = ['bank_transfer'];
            }

            if (! empty($enabled)) {
                $payload['enabled_payments'] = $enabled;
            }
        }

        // call midtrans snap api to get token
        $midtransUrl = config('services.midtrans.is_production') ? 'https://app.midtrans.com/snap/v1/transactions' : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
        $auth = base64_encode($serverKey . ':');

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Authorization' => 'Basic ' . $auth,
        ])->post($midtransUrl, $payload);

        if (! $response->successful()) {
            // return the Midtrans error body and status so client can see the real cause
            $status = $response->status() ?: 500;
            $body = null;
            try {
                $body = $response->json();
            } catch (\Throwable $e) {
                $body = $response->body();
            }

            // mark transaction failed
            try { $transaction->update(['status' => 'failed', 'midtrans_response' => json_encode($body)]); } catch (\Throwable $e) {}

            return response()->json(['error' => 'Midtrans request failed', 'body' => $body], $status);
        }

        // Attempt to return a token if present
        $body = $response->json();
        if (isset($body['token'])) {
            // persist raw response and keep status pending
            try { $transaction->update(['midtrans_response' => json_encode($body), 'status' => 'pending']); } catch (\Throwable $e) {}
            return response()->json(['order_id' => $externalOrderId, 'snap_token' => $body['token'], 'raw' => $body]);
        }

        try { $transaction->update(['midtrans_response' => json_encode($body), 'status' => 'pending']); } catch (\Throwable $e) {}

        return response()->json(['order_id' => $externalOrderId, 'raw' => $body]);
    }
}
