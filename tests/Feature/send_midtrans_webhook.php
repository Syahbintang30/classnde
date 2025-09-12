<?php
// send_midtrans_webhook.php - run with: php send_midtrans_webhook.php https://abcd.ngrok.io/payments/midtrans-notify test-order-1 settlement 1000
$url = $argv[1] ?? 'http://127.0.0.1:8000/payments/midtrans-notify';
$order = $argv[2] ?? 'test-order';
$status = $argv[3] ?? 'settlement';
$amount = $argv[4] ?? 1000;
$serverKey = getenv('MIDTRANS_SERVER_KEY') ?: 'your_sandbox_server_key_here';

$payload = [
    'order_id' => $order,
    'transaction_status' => $status,
    'gross_amount' => (int)$amount,
    'payment_type' => 'bank_transfer',
    'status_code' => ($status === 'settlement' ? '200' : '201'),
];

$signature = hash('sha512', $order . $status . (string)$amount . $serverKey);
$payload['signature_key'] = $signature;

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\nAccept: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($payload),
        'ignore_errors' => true,
    ],
];
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);
echo \"Sent payload:\\n\" . json_encode($payload) . \"\\n\\nResponse:\\n\" . $result . \"\\n\";