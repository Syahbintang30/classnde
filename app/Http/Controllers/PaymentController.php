<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\Transaction;
use App\Models\UserPackage;
use App\Models\Package;
use App\Models\User;

class PaymentController extends Controller
{

    /**
     * Midtrans server-to-server notification (webhook)
     * Best-effort: accept JSON payload, find or create local Transaction, update status
     * and midtrans_response, and grant UserPackage when settled.
     */
    public function midtransNotification(Request $request)
    {
        $raw = $request->getContent();
        $data = json_decode($raw, true);
        if (! is_array($data)) {
            Log::warning('Midtrans webhook: invalid JSON payload');
            return response()->json(['error' => 'invalid_payload'], 400);
        }

        $orderId = $data['order_id'] ?? $data['orderId'] ?? null;
        $txnStatus = $data['transaction_status'] ?? $data['status_code'] ?? null;

        Log::info('Midtrans webhook: received', ['order_id' => $orderId, 'transaction_status' => $txnStatus]);

        if (! $orderId) {
            Log::warning('Midtrans webhook: missing order_id', $data);
            return response()->json(['error' => 'order_id missing'], 400);
        }

        // Verify Midtrans signature_key (sha512 of order_id + transaction_status + gross_amount + server_key)
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $hasServerKey = ! empty($serverKey);
        // Midtrans sends signature_key in JSON body; also accept common headers as fallback
        $providedSignature = $data['signature_key'] ?? $request->header('X-Signature') ?? $request->header('X-Callback-Signature') ?? null;
        if (! $hasServerKey || ! $providedSignature) {
            Log::notice('Midtrans webhook: signature not provided or server key missing', ['order_id' => $orderId, 'has_server_key' => $hasServerKey, 'has_signature' => (bool) $providedSignature]);
            return response()->json(['error' => 'signature_missing_or_server_key_missing'], 403);
        }

        $grossStr = (string) ($data['gross_amount'] ?? '');
        $toHash = $orderId . ($txnStatus ?? '') . $grossStr . $serverKey;
        $expected = hash('sha512', $toHash);
        if (! hash_equals($expected, $providedSignature)) {
            Log::warning('Midtrans webhook: signature mismatch', ['order_id' => $orderId]);
            return response()->json(['error' => 'invalid_signature'], 403);
        }

        Log::info('Midtrans webhook: signature verified', ['order_id' => $orderId]);

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
            // try to hydrate from cache
            $cached = Cache::get('pending_txn:' . $orderId, null);
            $payloadAmount = $data['gross_amount'] ?? ($cached['amount'] ?? null);
            try {
                $txn = Transaction::create([
                    'order_id' => $orderId,
                    'user_id' => $cached['user_id'] ?? null,
                    'package_id' => $cached['package_id'] ?? null,
                    'method' => $cached['method'] ?? (isset($data['payment_type']) ? strtoupper($data['payment_type']) : null),
                    'amount' => $payloadAmount,
                    'original_amount' => $cached['original_amount'] ?? $payloadAmount,
                    'status' => $txnStatus,
                    'midtrans_response' => $data,
                ]);
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
            try { $txn->save(); } catch (\Throwable $e) {
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
                } else {
                    Log::warning('Midtrans webhook: upgrade-intermediate purchase not eligible, skipping grant', ['order_id' => $orderId, 'user_id' => $txn->user_id]);
                }
            } catch (\Throwable $e) {
                Log::error('Midtrans webhook: failed to create user package', ['err' => $e->getMessage(), 'order_id' => $orderId]);
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
