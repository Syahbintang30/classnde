<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use App\Models\Transaction;
use App\Models\UserPackage;
use App\Models\Package;
use App\Models\User;

class PaymentController extends Controller
{
    /**
     * SECURITY: Validate Midtrans webhook IP ranges
     * Midtrans official IP ranges for webhooks
     */
    private function validateMidtransIP(Request $request)
    {
        // Skip IP validation in local development
        if (app()->environment('local') || config('app.debug')) {
            return true;
        }
        
        $clientIP = $request->ip();
        
        // Midtrans official IP ranges (as of 2024)
        // Reference: https://docs.midtrans.com/en/other/faq/technical#what-ips-does-midtrans-use-to-send-webhooks
        $midtransIPs = [
            '103.208.23.0/24',
            '103.208.23.6',
            '103.208.23.102',
            '103.127.16.0/23',
            '103.127.17.6',
            '209.58.183.0/24',
        ];
        
        foreach ($midtransIPs as $range) {
            if ($this->ipInRange($clientIP, $range)) {
                return true;
            }
        }
        
        Log::warning('Midtrans webhook: unauthorized IP attempt', [
            'ip' => $clientIP,
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl()
        ]);
        
        abort(403, 'IP not authorized for webhook');
    }
    
    /**
     * Check if IP is in CIDR range
     */
    private function ipInRange($ip, $range)
    {
        if (strpos($range, '/') === false) {
            // Single IP
            return $ip === $range;
        }
        
        // CIDR range
        list($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;
        return ($ip & $mask) == $subnet;
    }

    /**
     * Midtrans server-to-server notification (webhook)
     * Best-effort: accept JSON payload, find or create local Transaction, update status
     * and midtrans_response, and grant UserPackage when settled.
     */
    public function midtransNotification(Request $request)
    {
        $debug = filter_var(env('PAYMENT_DEBUG_LOG', false), FILTER_VALIDATE_BOOLEAN);
        if ($debug) {
            try {
                Log::info('Midtrans webhook: incoming request (debug)', [
                    'ip' => $request->ip(),
                    'headers' => $request->headers->all(),
                ]);
            } catch (\Throwable $e) {}
        }
        // SECURITY: Soft-validate sender IP first (log if abnormal) but do not abort yet.
        // We'll still enforce signature verification strictly below which is the primary control.
        try {
            if (! (app()->environment('local') || config('app.debug'))) {
                $clientIP = $request->ip();
                $ranges = ['103.208.23.0/24','103.208.23.6','103.208.23.102','103.127.16.0/23','103.127.17.6','209.58.183.0/24'];
                $ok = false; foreach ($ranges as $r) { if ($this->ipInRange($clientIP, $r)) { $ok = true; break; } }
                if (! $ok) {
                    Log::warning('Midtrans webhook from unexpected IP (continuing due to strict signature check)', ['ip' => $clientIP]);
                }
            }
        } catch (\Throwable $e) { /* non-fatal */ }
        
        $raw = $request->getContent();
        $data = json_decode($raw, true);
        if (! is_array($data)) {
            Log::warning('Midtrans webhook: invalid JSON payload', ['ip' => $request->ip()]);
            return response()->json(['error' => 'invalid_payload'], 400);
        }

        $orderId = $data['order_id'] ?? $data['orderId'] ?? null;
        $txnStatus = $data['transaction_status'] ?? $data['status_code'] ?? null;

        Log::info('Midtrans webhook: received', ['order_id' => $orderId, 'transaction_status' => $txnStatus]);
        if ($debug) {
            try { Log::info('Midtrans webhook: payload (debug)', ['body' => $data]); } catch (\Throwable $e) {}
        }

        if (! $orderId) {
            Log::warning('Midtrans webhook: missing order_id', $data);
            return response()->json(['error' => 'order_id missing'], 400);
        }

        // SECURITY ENHANCEMENT: Verify Midtrans signature_key with enhanced validation
        // (sha512 of order_id + transaction_status + gross_amount + server_key)
        $serverKey = config('services.midtrans.server_key') ?: env('MIDTRANS_SERVER_KEY');
        
        if (! $serverKey) {
            Log::error('Midtrans webhook: server key not configured - SECURITY RISK', ['order_id' => $orderId]);
            return response()->json(['error' => 'server_configuration_error'], 500);
        }
        
        // Midtrans sends signature_key in JSON body; also accept common headers as fallback
        $providedSignature = $data['signature_key'] ?? $request->header('X-Signature') ?? $request->header('X-Callback-Signature') ?? null;
        
        if (! $providedSignature) {
            Log::warning('Midtrans webhook: signature missing - potential attack', [
                'order_id' => $orderId, 
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return response()->json(['error' => 'signature_required'], 403);
        }

        // ENHANCED SECURITY: Strict signature verification with timing attack protection
        $grossStr = (string) ($data['gross_amount'] ?? '');
        $toHash = $orderId . ($txnStatus ?? '') . $grossStr . $serverKey;
        $expected = hash('sha512', $toHash);
        
        // Use timing-safe comparison to prevent timing attacks
        if (! hash_equals($expected, $providedSignature)) {
            Log::warning('Midtrans webhook: signature verification failed - potential attack', [
                'order_id' => $orderId,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'expected_length' => strlen($expected),
                'provided_length' => strlen($providedSignature)
            ]);
            return response()->json(['error' => 'invalid_signature'], 403);
        }

        Log::info('Midtrans webhook: signature verified successfully', ['order_id' => $orderId]);

        // Only act when transaction is settled (server-to-server confirmed)
        $lower = strtolower((string) $txnStatus);
        if (! in_array($lower, ['settlement','capture','success'])) {
            // ignore non-final statuses; return 200 to acknowledge
            Log::info('Midtrans webhook: ignoring non-final status', ['order_id' => $orderId, 'status' => $txnStatus]);
            return response()->json(['ok' => true]);
        }

        // For settled payments, create DB transaction only if not present
        $txn = Transaction::where('order_id', $orderId)->latest()->first();
        if (! $txn) {
            // try to hydrate from cache (includes guest pre_register snapshot)
            $cached = Cache::get('pending_txn:' . $orderId, null);
            $payloadAmount = $data['gross_amount'] ?? ($cached['amount'] ?? null);

            $userId = $cached['user_id'] ?? null;
            $packageId = $cached['package_id'] ?? null;

            // If no user yet (guest flow) but we have pre_register cached, create user now
            if (!$userId && is_array($cached) && isset($cached['pre_register']) && isset($cached['pre_register']['email'])) {
                try {
                    $pre = $cached['pre_register'];
                    $existing = \App\Models\User::where('email', $pre['email'])->first();
                    if ($existing) {
                        $userId = $existing->id;
                    } else {
                        $plainPassword = str()->random(16);
                        $user = \App\Models\User::create([
                            'name' => $pre['name'] ?? 'User',
                            'email' => $pre['email'],
                            'password' => \Illuminate\Support\Facades\Hash::make($plainPassword),
                            'phone' => $pre['phone'] ?? null,
                            'package_id' => null, // set after settlement grant
                            'referred_by' => null,
                        ]);
                        if (! empty($pre['referral'])) {
                            $referrer = \App\Models\User::where('referral_code', $pre['referral'])->first();
                            if ($referrer) { $user->referred_by = $referrer->id; $user->save(); }
                        }
                        event(new \Illuminate\Auth\Events\Registered($user));
                        try { $user->notify(new \App\Notifications\WelcomeWithPasswordNotification($plainPassword)); } catch (\Throwable $e) {}
                        $userId = $user->id;
                        // update cache so future logic sees user_id
                        $cached['user_id'] = $userId;
                        try { Cache::put('pending_txn:' . $orderId, $cached, now()->addHours(12)); } catch (\Throwable $e) {}
                    }
                } catch (\Throwable $e) {
                    Log::error('Midtrans webhook: failed creating user from pre_register', ['order_id' => $orderId, 'err' => $e->getMessage()]);
                }
            }

            // For guest flows (presence of pre_register), ensure a one-time autologin token is available
            if (is_array($cached) && isset($cached['pre_register']) && $userId) {
                if (empty($cached['autologin_token'])) {
                    try {
                        $token = bin2hex(random_bytes(24));
                        Cache::put('autologin:' . $token, $userId, now()->addMinutes(20));
                        $cached['autologin_token'] = $token;
                        try { Cache::put('pending_txn:' . $orderId, $cached, now()->addHours(12)); } catch (\Throwable $e) {}
                    } catch (\Throwable $e) { /* ignore */ }
                }
            }

            try {
                $txn = Transaction::create([
                    'order_id' => $orderId,
                    'user_id' => $userId,
                    'package_id' => $packageId,
                    'method' => $cached['method'] ?? (isset($data['payment_type']) ? strtoupper($data['payment_type']) : null),
                    'amount' => $payloadAmount,
                    'original_amount' => $cached['original_amount'] ?? $payloadAmount,
                    'status' => $txnStatus,
                    'midtrans_response' => $data,
                ]);
                if ($debug) { Log::info('Midtrans webhook: created local transaction (debug)', ['id' => $txn->id, 'order_id' => $orderId, 'user_id' => $userId, 'package_id' => $packageId]); }
            } catch (\Throwable $e) {
                Log::error('Midtrans webhook: failed to create txn on settlement', ['err' => $e->getMessage(), 'order_id' => $orderId]);
                return response()->json(['error' => 'create_failed'], 500);
            }
        } else {
            // update existing record
            $existing = $txn->midtrans_response;
            if (is_string($existing)) $existing = json_decode($existing, true) ?: [];
            $merged = array_merge($existing ?? [], $data ?: []);
            $txn->midtrans_response = $merged;
            $txn->status = $txnStatus;
            try { $txn->save(); if ($debug) { Log::info('Midtrans webhook: updated existing transaction (debug)', ['id' => $txn->id, 'order_id' => $orderId]); } } catch (\Throwable $e) {
                Log::error('Midtrans webhook: failed to update txn on settlement', ['err' => $e->getMessage(), 'order_id' => $orderId]);
            }
        }

        // If settled, grant package if applicable
        $successful = true; // we are in settled branch
        if ($successful && $txn->user_id && $txn->package_id) {
            try {
                // If this is an upgrade-intermediate purchase, validate buyer eligibility
                $pkg = Package::find($txn->package_id);
                $allowCreate = true;
                if ($pkg && ($pkg->slug ?? '') === 'upgrade-intermediate') {
                    $allowCreate = false;
                    // eligible if user currently has beginner package or previously had it
                    $user = User::find($txn->user_id);
                    $beginnerId = Package::where('slug', 'beginner')->value('id');
                    if ($user) {
                        // current package
                        if (! empty($user->package_id) && $user->package_id == $beginnerId) {
                            $allowCreate = true;
                        }
                    }
                    // historical purchases
                    if (! $allowCreate) {
                        $had = UserPackage::where('user_id', $txn->user_id)
                            ->where('package_id', $beginnerId)->exists();
                        if ($had) $allowCreate = true;
                    }
                }

                if ($allowCreate) {
                    $exists = UserPackage::where('user_id', $txn->user_id)->where('package_id', $txn->package_id)->exists();
                    if (! $exists) {
                        UserPackage::create([
                            'user_id' => $txn->user_id,
                            'package_id' => $txn->package_id,
                            'purchased_at' => now(),
                            'source' => 'midtrans',
                        ]);
                    }
                    // ensure user's package_id set if empty
                    try { $user = User::find($txn->user_id); if ($user && empty($user->package_id)) { $user->package_id = $txn->package_id; $user->save(); }} catch (\Throwable $e) {}
                } else {
                    Log::warning('Midtrans webhook: upgrade-intermediate purchase not eligible, skipping grant', ['order_id' => $orderId, 'user_id' => $txn->user_id]);
                }
            } catch (\Throwable $e) {
                Log::error('Midtrans webhook: failed to create user package', ['err' => $e->getMessage(), 'order_id' => $orderId]);
            }
        } elseif ($successful && $txn->user_id && ! $txn->package_id) {
            // package id missing on initial transaction creation; attempt to hydrate from cache
            $cached = Cache::get('pending_txn:' . $orderId, null);
            if (is_array($cached) && isset($cached['package_id']) && $cached['package_id']) {
                try {
                    $txn->package_id = $cached['package_id'];
                    $txn->save();
                    try { $user = User::find($txn->user_id); if ($user && empty($user->package_id)) { $user->package_id = $txn->package_id; $user->save(); }} catch (\Throwable $e) {}
                } catch (\Throwable $e) {}
            }
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Return lightweight JSON status for a transaction by order_id.
     * This is used by client-side polling to wait until settlement webhook is processed.
     */
    public function transactionStatus(Request $request)
    {
        $orderId = $request->query('order_id');
        if (! $orderId) return response()->json(['error' => 'order_id required'], 400);

        $txn = Transaction::where('order_id', $orderId)->latest()->first();
        if (! $txn) return response()->json(['status' => 'not_found']);

        $lower = strtolower((string) $txn->status);
        if (in_array($lower, ['settlement','capture','success'])) {
            return response()->json(['status' => 'settlement']);
        }
        return response()->json(['status' => 'pending']);
    }
}
