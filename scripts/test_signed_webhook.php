<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\PaymentController;

$txn = Transaction::latest()->first();
if (! $txn) { echo "no txn\n"; exit(1); }

 $serverKey = env('MIDTRANS_SERVER_KEY') ?: '';
 $orderId = $txn->order_id;
 $status = 'settlement';
 $gross = (float) $txn->amount;

$toHash = $orderId . $status . (string)$gross . $serverKey;
$signature = hash('sha512', $toHash);

$payload = [
    'order_id' => $orderId,
    'transaction_status' => $status,
    'payment_type' => 'gopay',
    'gross_amount' => $gross,
    'transaction_id' => 'midtrans-test-signed-'.time(),
    'signature_key' => $signature,
];

$request = Request::create('/payments/midtrans-notify','POST',[],[],[],[], json_encode($payload));
$request->headers->set('Content-Type','application/json');

$controller = new PaymentController();
$response = $controller->midtransNotification($request);

echo "Response: " . $response->getStatusCode() . "\n";
$txn->refresh();
echo json_encode($txn->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
