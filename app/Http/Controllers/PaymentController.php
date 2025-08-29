<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Transaction;
use App\Models\UserPackage;

class PaymentController extends Controller
{
    public function generateQris(Request $request)
    {
        $request->validate([
            'total_amount' => 'required|numeric|min:1',
            'order_id' => 'required|string',
            'lesson_id' => 'nullable|integer|exists:lessons,id',
            'package_id' => 'nullable|integer|exists:packages,id',
        ]);

        $serverKey = env('MIDTRANS_SERVER_KEY');
        $isProduction = filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOLEAN);

        // Prefer using Midtrans SDK if available
        if (class_exists('Midtrans\Config')) {
            \Midtrans\Config::$serverKey = $serverKey;
            \Midtrans\Config::$isProduction = $isProduction;
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            $params = [
                'payment_type' => 'qris',
                'transaction_details' => [
                    'order_id' => $request->input('order_id'),
                    'gross_amount' => (float) $request->input('total_amount'),
                ],
            ];

            try {
                $response = \Midtrans\Snap::createTransaction($params);
                $json = (array) $response;
            } catch (\Exception $e) {
                return response()->json(['error' => 'Midtrans SDK error', 'message' => $e->getMessage()], 500);
            }
        } else {
            // fallback: use HTTP client
            $base = $isProduction ? 'https://api.midtrans.com/v2' : 'https://api.sandbox.midtrans.com/v2';
            $payload = [
                'payment_type' => 'qris',
                'transaction_details' => [
                    'order_id' => $request->input('order_id'),
                    'gross_amount' => (float) $request->input('total_amount'),
                ],
            ];

            $response = Http::withBasicAuth($serverKey, '')
                ->withHeaders(['Accept' => 'application/json'])
                ->post($base . '/charge', $payload);

            if (! $response->successful()) {
                return response()->json(['error' => 'Midtrans error', 'body' => $response->body()], 500);
            }

            $json = $response->json();
        }

        // try to find qr url or qr_string
        $qrUrl = null;
        if (isset($json['actions']) && is_array($json['actions'])) {
            foreach ($json['actions'] as $act) {
                if (isset($act['url'])) {
                    if (str_contains($act['url'], 'qris') || str_contains($act['url'], 'qrstring') || str_contains($act['url'], 'scan')) {
                        $qrUrl = $act['url'];
                        break;
                    }
                }
            }
        }
        if (! $qrUrl && isset($json['qr_string'])) {
            $qrUrl = $json['qr_string'];
        }

        // persist transaction record
        $txn = Transaction::create([
            'order_id' => $request->input('order_id'),
            'user_id' => $request->user()->id ?? null,
            'lesson_id' => $request->input('lesson_id'),
            'package_id' => $request->input('package_id'),
            'method' => 'QRIS',
            'amount' => (float) $request->input('total_amount'),
            'original_amount' => (float) $request->input('total_amount'),
            'referral_code' => $request->input('referral') ?? null,
            'referrer_user_id' => null,
            'status' => $json['status'] ?? null,
            'midtrans_response' => $json,
        ]);

    return response()->json(['qr_url' => $qrUrl, 'raw' => $json, 'transaction_id' => $txn->id]);
    }
}
