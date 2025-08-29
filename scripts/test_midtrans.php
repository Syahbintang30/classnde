<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\MidtransController;
use Illuminate\Http\Request;

$ctrl = new MidtransController();
$req = Request::create('/api/midtrans/create', 'POST', ['order_id'=>'t-1','gross_amount'=>10000]);
try {
    $res = $ctrl->createSnapToken($req);
    echo $res->getContent();
} catch (\Throwable $e) {
    echo "EXCEPTION: ", get_class($e), " - ", $e->getMessage(), "\n", $e->getTraceAsString();
}
