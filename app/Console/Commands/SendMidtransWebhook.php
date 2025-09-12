<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SendMidtransWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Usage:
     * php artisan midtrans:test-webhook --url="https://xxxx.ngrok.io/payments/midtrans-notify" --order="order-123" --status=settlement --amount=1000
     *
     * @var string
     */
    protected $signature = 'midtrans:test-webhook {--url=http://127.0.0.1:8000/payments/midtrans-notify} {--order=phpunit-smoke} {--status=settlement} {--amount=1000} {--dry-run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a signed Midtrans-style webhook payload (sha512 signature_key) to a target URL for testing.';

    public function handle()
    {
        $url = $this->option('url');
        $orderId = $this->option('order') ?: 'phpunit-smoke-' . uniqid();
        $status = $this->option('status') ?: 'settlement';
        $amount = (int) $this->option('amount');
        $dry = (bool) $this->option('dry-run');

        // Use configured server key if available
        $serverKey = config('services.midtrans.server_key') ?: env('MIDTRANS_SERVER_KEY');
        if (empty($serverKey)) {
            $this->error('MIDTRANS server key not configured. Set MIDTRANS_SERVER_KEY in your environment or config/services.php.');
            return 2;
        }

        $payload = [
            'order_id' => $orderId,
            'transaction_status' => $status,
            'gross_amount' => $amount,
            'payment_type' => 'bank_transfer',
            'status_code' => ($status === 'settlement' ? '200' : '201'),
        ];

        // Midtrans signature_key: sha512(order_id + transaction_status + gross_amount + server_key)
        $toHash = $orderId . $status . (string) $amount . $serverKey;
        $signature = hash('sha512', $toHash);
        $payload['signature_key'] = $signature;

        $this->info('Prepared Midtrans webhook payload:');
        $this->line(json_encode($payload));

        if ($dry) {
            $this->info('Dry-run enabled; not sending.');
            return 0;
        }

        try {
            $this->info('Sending POST to ' . $url);
            $resp = Http::withHeaders(['Accept' => 'application/json'])->post($url, $payload);
            $this->info('Response HTTP ' . $resp->status());
            $body = null;
            try { $body = $resp->json(); } catch (\Throwable $e) { $body = $resp->body(); }
            $this->line('Response body: ' . json_encode($body));
            return $resp->successful() ? 0 : 1;
        } catch (\Throwable $e) {
            $this->error('Request failed: ' . $e->getMessage());
            return 3;
        }
    }
}
