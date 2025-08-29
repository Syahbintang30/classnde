<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\CoachingTicket;
use Illuminate\Support\Facades\Hash;

// Create or find referrer
$refEmail = 'referrer+test@example.test';
$referrer = User::where('email', $refEmail)->first();
if (! $referrer) {
    $referrer = User::create([
        'name' => 'Referrer Test',
        'email' => $refEmail,
        'password' => Hash::make('secret123'),
    ]);
    echo "Created referrer id={$referrer->id}\n";
} else {
    echo "Found referrer id={$referrer->id}\n";
}
$referrer->refresh();
$refCode = $referrer->referral_code;
if (! $refCode) {
    echo "Referrer has no referral_code.\n";
} else {
    echo "Referrer code: {$refCode}\n";
}

// Create a referred user
$childEmail = 'referred+test@example.test';
// remove existing test child
$existing = User::where('email', $childEmail)->first();
if ($existing) {
    // delete associated tickets created earlier to keep test idempotent
    CoachingTicket::where('user_id', $existing->id)->delete();
    $existing->delete();
    echo "Removed previous referred user\n";
}

$child = User::create([
    'name' => 'Referred Test',
    'email' => $childEmail,
    'password' => Hash::make('secret123'),
    'referred_by' => $referrer->id,
]);
$child->refresh();
echo "Created child id={$child->id} referred_by={$child->referred_by}\n";

// Check referrer received referral ticket for this child
$ticket = CoachingTicket::where('user_id', $referrer->id)
    ->where('source', 'referral')
    ->where('referrer_user_id', $child->id)
    ->first();

if ($ticket) {
    echo "Referral ticket granted to referrer (ticket id={$ticket->id}).\n";
} else {
    echo "Referral ticket NOT found for referrer.\n";
}

// Cleanup: keep data for inspection

echo "Test completed.\n";
