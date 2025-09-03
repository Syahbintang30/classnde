<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Transaction;
use App\Models\User;
use App\Models\Package;
use Illuminate\Http\Request;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Str;
use App\Services\OrderIdGenerator;

$user = User::first();
$package = Package::first();
if (! $user || ! $package) { echo "Need at least one user and one package in DB\n"; exit(1); }

$externalOrderId = OrderIdGenerator::generate('nde');
$txn = Transaction::create([
    'order_id' => $externalOrderId,
    'user_id' => $user->id,
    'package_id' => $package->id,
    'method' => 'gopay',
    'amount' => 10000,
    'status' => 'pending',
    'midtrans_response' => null,
]);

echo "Created txn: " . $txn->order_id . "\n";

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

echo "Webhook response: " . $response->getStatusCode() . "\n";
$txn->refresh();
echo json_encode($txn->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
$up = \App\Models\UserPackage::where('user_id', $txn->user_id)->where('package_id', $txn->package_id)->first();
if ($up) echo "UserPackage created: " . json_encode($up->toArray()) . "\n";
else echo "UserPackage not created\n";
