<?php
// Quick local script to detect obvious secrets in .env (for developer use only)
require __DIR__ . '/../vendor/autoload.php';
$path = __DIR__ . '/../.env';
if (! file_exists($path)) {
    echo ".env file not found\n";
    exit(1);
}
$contents = file_get_contents($path);
$patterns = [
    '/MIDTRANS_SERVER_KEY=.+/i',
    '/MIDTRANS_CLIENT_KEY=.+/i',
    '/TWILIO_AUTH_TOKEN=.+/i',
    '/TWILIO_API_KEY_SECRET=.+/i',
    '/BUNNY_API_KEY=.+/i',
    '/DB_PASSWORD=.+/i',
    '/AWS_SECRET_ACCESS_KEY=.+/i',
    '/MAIL_PASSWORD=.+/i',
];
$found = false;
foreach ($patterns as $p) {
    if (preg_match($p, $contents, $m)) {
        echo "Found: " . trim($m[0]) . "\n";
        $found = true;
    }
}
if (! $found) echo "No known secret keys detected by basic scanner. (This is not a proof you are safe.)\n";
