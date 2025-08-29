<?php

// Quick script to verify TwilioService configuration and token generation
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    /** @var \App\Services\TwilioService $tw */
    $tw = app(\App\Services\TwilioService::class);
    echo "isConfigured: " . ($tw->isConfigured() ? 'true' : 'false') . PHP_EOL;

    if ($tw->isConfigured()) {
        $identity = 'test-' . bin2hex(random_bytes(3));
        $roomName = 'coaching-check-room';
        try {
            $token = $tw->createAccessToken($identity, $roomName, 60);
            echo "createAccessToken: OK\n";
            echo "token-preview: " . substr($token, 0, 32) . "..." . PHP_EOL;
        } catch (\Throwable $e) {
            echo "createAccessToken: ERROR -> " . $e->getMessage() . PHP_EOL;
        }

        try {
            $client = $tw->getClient();
            echo "getClient: " . ($client ? 'available' : 'null') . PHP_EOL;
        } catch (\Throwable $e) {
            echo "getClient: ERROR -> " . $e->getMessage() . PHP_EOL;
        }
    } else {
        echo "Twilio not fully configured. Check TWILIO_ACCOUNT_SID, TWILIO_API_KEY_SID, TWILIO_API_KEY_SECRET\n";
    }
} catch (\Throwable $e) {
    echo "Fatal error: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}


