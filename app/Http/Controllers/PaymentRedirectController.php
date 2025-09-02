<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Transaction;

class PaymentRedirectController extends Controller
{
    /**
     * Handle client redirect after a payment attempt (success path)
     * Query local Transaction by order_id; if missing or pending, attempt to verify via Midtrans API once.
     */
    public function finish(Request $request)
    {
        $orderId = $request->query('order_id') ?? $request->query('orderId') ?? null;

        if (! $orderId) {
            return view('payments.error', ['message' => 'Order ID tidak ditemukan pada URL.']);
        }
        // Redirect finish requests to the centralized payments.thankyou handler which
        // will display the final thank-you page when settlement is confirmed.
        return redirect()->route('payments.thankyou', ['order_id' => $orderId]);
    }

    /**
     * New: render the thankyou page for an order_id (replaces payments.finish view)
     */
    public function thankyou(Request $request)
    {
        $orderId = $request->query('order_id') ?? $request->query('orderId') ?? null;
        if (! $orderId) {
            return view('payments.error', ['message' => 'Order ID tidak ditemukan pada URL.']);
        }

        $txn = Transaction::where('order_id', $orderId)->latest()->first();

    // ensure the user relation is loaded to avoid static analysis/runtime notices
    if ($txn) $txn->loadMissing('user');

        // If no txn found, attempt to query Midtrans (best-effort) to fetch status
        if (! $txn) {
            $remote = $this->queryMidtransStatus($orderId);
            if ($remote && (isset($remote['status_code']) || isset($remote['transaction_status']))) {
                $txn = Transaction::create([
                    'order_id' => $orderId,
                    'user_id' => null,
                    'package_id' => null,
                    'method' => null,
                    'amount' => $remote['gross_amount'] ?? null,
                    'original_amount' => $remote['gross_amount'] ?? null,
                    'referral_code' => null,
                    'referrer_user_id' => null,
                    'status' => $remote['transaction_status'] ?? ($remote['status_code'] ?? 'pending'),
                    'midtrans_response' => $remote,
                ]);
            }
        }

        if (! $txn) {
            return view('payments.error', ['message' => 'Transaksi tidak ditemukan. Jika pembayaran sukses, server webhook mungkin belum memprosesnya. Silakan cek kembali nanti atau hubungi support.']);
        }

        // Try to update remote once if not settled
        $successfulStates = ['settlement','capture','success'];
        if (! in_array(strtolower($txn->status), $successfulStates)) {
            $remote = $this->queryMidtransStatus($txn->order_id);
            if ($remote && isset($remote['transaction_status'])) {
                $txn->status = $remote['transaction_status'];
                $existing = $txn->midtrans_response;
                if (is_string($existing)) $existing = json_decode($existing, true) ?: [];
                $txn->midtrans_response = array_merge($existing ?? [], $remote ?: []);
                $txn->save();
            }
        }

        // If not settled, keep user on payment page (do not redirect to thankyou)
        if (! in_array(strtolower($txn->status), $successfulStates)) {
            // Show an informative waiting/error page — keep existing payments.waiting for now
            return view('payments.waiting', ['transaction' => $txn]);
        }

        // At this point txn is settled. Prepare data to render the existing kelas.thankyou view
        $user = null;
        if ($txn && ($txn->user instanceof \App\Models\User)) {
            $user = $txn->user;
        } else {
            $user = \Illuminate\Support\Facades\Auth::user();
        }

        $package = null;
        if ($txn && $txn->package_id) {
            $package = \App\Models\Package::find($txn->package_id);
        }

        // attempt to find a related ticket for the user if present
        $ticket = null;
        if ($user && isset($user->id)) {
            $ticket = \App\Models\CoachingTicket::where('user_id', $user->id)->orderByDesc('id')->first();
        }

        // Render the same thankyou view used by KelasController but include transaction info
        return view('kelas.thankyou', compact('user', 'package', 'ticket'));
    }

    /**
     * Show a generic error page when payment failed or was cancelled.
     */
    public function error(Request $request)
    {
        $msg = $request->query('message') ?? 'Pembayaran gagal atau dibatalkan.';
        return view('payments.error', ['message' => $msg]);
    }

    /**
     * AJAX endpoint: return current status for an order_id
     */
    public function status(Request $request)
    {
        $orderId = $request->query('order_id') ?? $request->input('order_id');
        if (! $orderId) return response()->json(['error' => 'order_id required'], 422);

        $txn = Transaction::where('order_id', $orderId)->latest()->first();
        if (! $txn) {
            // try remote once
            $remote = $this->queryMidtransStatus($orderId);
            if ($remote) return response()->json(['status' => $remote['transaction_status'] ?? ($remote['status_code'] ?? 'unknown'), 'raw' => $remote]);
            return response()->json(['error' => 'not_found'], 404);
        }

        return response()->json(['status' => $txn->status, 'transaction' => $txn]);
    }

    /**
     * Best-effort: call Midtrans API to fetch transaction status
     */
    protected function queryMidtransStatus(string $orderId)
    {
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $isProduction = filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN);
        if (! $serverKey) return null;

        $base = $isProduction ? 'https://api.midtrans.com/v2' : 'https://api.sandbox.midtrans.com/v2';
        try {
            $resp = Http::withBasicAuth($serverKey, '')->withHeaders(['Accept' => 'application/json'])->get($base . '/' . $orderId . '/status');
            if (! $resp->successful()) return null;
            return $resp->json();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
