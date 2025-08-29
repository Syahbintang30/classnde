<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Http\Controllers\PaymentController;

$txn = Transaction::latest()->first();
if (! $txn) { echo "no transaction to test\n"; exit(1); }

$payload = [
    'order_id' => $txn->order_id,
    'transaction_status' => 'settlement',
    'payment_type' => 'gopay',
    'gross_amount' => (float) $txn->amount,
    'transaction_id' => 'midtrans-test-' . time(),
];

$request = Request::create('/payments/midtrans-notify','POST',[],[],[],[], json_encode($payload));
$request->headers->set('Content-Type','application/json');

$controller = new PaymentController();
$response = $controller->midtransNotification($request);

echo "Response: " . $response->getStatusCode() . "\n";
// print txn and user_packages
$txn->refresh();
echo json_encode($txn->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";

$up = \App\Models\UserPackage::where('user_id', $txn->user_id)->where('package_id', $txn->package_id)->first();
if ($up) echo "UserPackage created: " . json_encode($up->toArray()) . "\n";
else echo "UserPackage not created (maybe txn had null user_id/package_id)\n";
