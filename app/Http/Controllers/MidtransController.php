<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction;
use App\Models\Voucher;
use Illuminate\Support\Str;
use App\Services\OrderIdGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MidtransController extends Controller
{
    public function createSnapToken(Request $request)
    {
    // expect gross_amount, payment_method and package_id (optional); support package_qty and package_unit_price
    $data = $request->only(['order_id', 'gross_amount', 'package_id', 'payment_method', 'package_qty', 'package_unit_price', 'referral', 'voucher_code', 'voucher_id']);
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

        // Server-side validation: if attempting to buy the special upgrade package,
        // ensure the buyer is eligible (owns or previously bought the 'beginner' package)
        try {
            $pkgCheck = \App\Models\Package::find($data['package_id']);
            if ($pkgCheck && $pkgCheck->slug === 'upgrade-intermediate') {
                $eligible = false;
                // If user is authenticated, check current package_id or historical purchases
                if (Auth::check()) {
                    $user = Auth::user();
                    // current package
                    if (! empty($user->package_id)) {
                        $cur = \App\Models\Package::find($user->package_id);
                        if ($cur && $cur->slug === 'beginner') $eligible = true;
                    }
                    // historical purchase check
                    if (! $eligible) {
                        $eligible = \App\Models\UserPackage::where('user_id', $user->id)
                            ->where('package_id', function($q){ $q->from('packages')->select('id')->where('slug','beginner')->limit(1); })
                            ->exists();
                    }
                } else {
                    // For guest flow, allow only if pre_register session indicates beginner selected (rare)
                    $pre = $request->session()->get('pre_register');
                    if (! empty($pre) && isset($pre['package_id'])) {
                        $bpkg = \App\Models\Package::find($pre['package_id']);
                        if ($bpkg && $bpkg->slug === 'beginner') $eligible = true;
                    }
                }

                if (! $eligible) {
                    return response()->json(['error' => 'upgrade_not_allowed', 'message' => 'Upgrade hanya tersedia untuk pengguna yang memiliki atau pernah membeli paket Beginner.'], 403);
                }
            }
        } catch (\Throwable $e) {
            // If any check fails unexpectedly, block upgrade-by-default to be safe
            if (! empty($pkgCheck) && isset($pkgCheck->slug) && $pkgCheck->slug === 'upgrade-intermediate') {
                return response()->json(['error' => 'upgrade_check_failed', 'message' => 'Unable to verify upgrade eligibility.'], 500);
            }
        }

    // create an external order id that Midtrans will use (use canonical generator)
    $externalOrderId = OrderIdGenerator::generate('nde');

    // Persist pending order metadata in cache only. Do NOT create DB Transaction yet.
    $qty = (int) ($data['package_qty'] ?? 1);
    $unit = (int) ($data['package_unit_price'] ?? 0);

        // If a referral code was provided, validate it and apply discount
    // prefer DB-configured setting if present
    $dbVal = \App\Models\Setting::get('referral.discount_percent', null);
    $discountPercent = $dbVal !== null ? (int) $dbVal : (int) config('referral.discount_percent', 2);
        $appliedDiscountPercent = 0;
        $ref = null;
        if (! empty($data['referral'])) {
            $ref = \App\Models\User::where('referral_code', $data['referral'])->first();
            if ($ref) {
                $appliedDiscountPercent = $discountPercent;
            }
        }

        // If a voucher was provided, validate and load it. If invalid, return 422 so client knows.
        $appliedVoucher = null;
        $appliedVoucherPercent = 0;
        if (! empty($data['voucher_id']) || ! empty($data['voucher_code'])) {
            if (! empty($data['voucher_id'])) {
                $v = Voucher::find($data['voucher_id']);
            } else {
                $v = Voucher::where('code', $data['voucher_code'])->first();
            }
            if (! $v || ! $v->isValid()) {
                return response()->json(['error' => 'invalid_voucher', 'message' => 'Voucher tidak valid atau sudah kadaluarsa.'], 422);
            }
            $appliedVoucher = $v;
            $appliedVoucherPercent = (int) $v->discount_percent;
        }

        // calculate unit price: if unit not provided, fallback to package price lookup
        if (empty($unit) && ! empty($data['package_id'])) {
            $pkg = \App\Models\Package::find($data['package_id']);
            $unit = $pkg ? (int) $pkg->price : 0;
        }

    $rawGross = $unit * max(1, $qty);
    // apply referral first
    $afterReferral = $appliedDiscountPercent > 0 ? (int) round($rawGross * (100 - $appliedDiscountPercent) / 100) : (int) $rawGross;
    // apply voucher on top of referral (sequential percent discount)
    $gross = $appliedVoucherPercent > 0 ? (int) round($afterReferral * (100 - $appliedVoucherPercent) / 100) : (int) $afterReferral;

        // cache mapping so webhook can create DB transaction only on settlement
        try {
            Cache::put('pending_txn:' . $externalOrderId, [
                'user_id' => Auth::check() ? Auth::id() : null,
                'package_id' => $data['package_id'] ?? null,
                'lesson_id' => null,
                'method' => $data['payment_method'] ?? null,
                'amount' => $gross,
                'original_amount' => $rawGross,
                'referral_code' => $data['referral'] ?? null,
                'referrer_user_id' => (!empty($data['referral']) && isset($ref) && $ref) ? $ref->id : null,
                'voucher_code' => $appliedVoucher ? $appliedVoucher->code : null,
                'voucher_id' => $appliedVoucher ? $appliedVoucher->id : null,
                'applied_voucher_percent' => $appliedVoucherPercent,
            ], now()->addHours(24));
        } catch (\Throwable $e) {
            // cache failure should not block token generation; log if necessary
        }

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
        // compute per-unit price after sequential discounts for item_details
        $unitAfterReferral = $appliedDiscountPercent > 0 ? (int) round($unit * (100 - $appliedDiscountPercent) / 100) : (int) $unit;
        $unitAfterVoucher = $appliedVoucherPercent > 0 ? (int) round($unitAfterReferral * (100 - $appliedVoucherPercent) / 100) : (int) $unitAfterReferral;
        $labelParts = [];
        if ($appliedDiscountPercent > 0) $labelParts[] = 'Referral ' . $appliedDiscountPercent . '%';
        if ($appliedVoucherPercent > 0) $labelParts[] = 'Voucher ' . $appliedVoucherPercent . '%';
        $payload['item_details'][] = [
                'id' => $itemId,
                'price' => (int) $unitAfterVoucher,
                'quantity' => max(1, $qty),
                'name' => $itemName . (count($labelParts) ? (' (' . implode(' + ', $labelParts) . ')') : ''),
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

            // we didn't create a DB transaction for pending orders; store raw midtrans error in cache for debugging
            try { Cache::put('pending_txn_error:' . $externalOrderId, json_encode($body), now()->addMinutes(60)); } catch (\Throwable $e) {}

            return response()->json(['error' => 'Midtrans request failed', 'body' => $body], $status);
        }

        // Attempt to return a token if present
        $body = $response->json();
        if (isset($body['token'])) {
            // persist raw response in cache so webhook or later processes can inspect it
            try { Cache::put('pending_txn_response:' . $externalOrderId, json_encode($body), now()->addHours(24)); } catch (\Throwable $e) {}
            return response()->json(['order_id' => $externalOrderId, 'snap_token' => $body['token'], 'raw' => $body]);
        }

    try { Cache::put('pending_txn_response:' . $externalOrderId, json_encode($body), now()->addHours(24)); } catch (\Throwable $e) {}

        return response()->json(['order_id' => $externalOrderId, 'raw' => $body]);
    }
}
