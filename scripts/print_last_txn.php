<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$txn = \App\Models\Transaction::latest()->first();
if (!$txn) { echo "no txn\n"; exit(0);} 
echo json_encode($txn->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
