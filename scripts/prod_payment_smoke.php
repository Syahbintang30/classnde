<?php
// scripts/prod_payment_smoke.php
// Minimal smoke test script to create a small Midtrans Snap transaction in PRODUCTION.
// Usage (PowerShell):
//   $env:MIDTRANS_SERVER_KEY = "your_production_server_key"; $env:MIDTRANS_PRODUCTION = "1"; $env:AMOUNT = "1000"; php .\scripts\prod_payment_smoke.php

$serverKey = getenv('MIDTRANS_SERVER_KEY');
$prodFlag = getenv('MIDTRANS_PRODUCTION');
$amount = getenv('AMOUNT') ?: 1000;
$orderId = getenv('ORDER_ID') ?: 'smoke-' . uniqid();
$packageId = getenv('PACKAGE_ID') ?: 'smoke-package';

if (!$serverKey) {
    fwrite(STDERR, "MIDTRANS_SERVER_KEY not set. Aborting.\n");
    exit(2);
}
if (!$prodFlag) {
    fwrite(STDERR, "MIDTRANS_PRODUCTION not set. This script will not run against sandbox. Set MIDTRANS_PRODUCTION=1 to run.\n");
    exit(2);
}

$payload = [
    'transaction_details' => [
        'order_id' => $orderId,
        'gross_amount' => (int)$amount,
    ],
    'item_details' => [
        [
            'id' => $packageId,
            'price' => (int)$amount,
            'quantity' => 1,
            'name' => 'Smoke Test Item',
        ],
    ],
    'customer_details' => [
        'first_name' => 'Smoke',
        'last_name' => 'Test',
        'email' => 'smoke@example.invalid',
    ],
];

$ch = curl_init('https://app.midtrans.com/snap/v1/transactions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$auth = base64_encode($serverKey . ':');
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Basic ' . $auth,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

fwrite(STDOUT, "HTTP code: $httpCode\n");
if ($err) {
    fwrite(STDERR, "cURL error: $err\n");
}

fwrite(STDOUT, "Response:\n$response\n");

// Basic success heuristics
if (in_array($httpCode, [200,201])) {
    fwrite(STDOUT, "Looks like the request succeeded. Inspect response above for token or redirect_url.\n");
    exit(0);
}

fwrite(STDERR, "Non-success response code. Do not treat as fully verified.\n");
exit(1);
