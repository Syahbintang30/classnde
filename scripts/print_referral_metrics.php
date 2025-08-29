<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\CoachingTicket;
use App\Models\Transaction;

$totalReferrals = User::whereNotNull('referred_by')->count();
$totalReferralTickets = CoachingTicket::where('source','referral')->count();
$totalDiscount = Transaction::whereNotNull('referral_code')->whereNotNull('original_amount')->whereColumn('original_amount','>','amount')->get()->reduce(function($carry,$t){
    return $carry + (($t->original_amount ?: 0) - ($t->amount ?: 0));
},0);

echo "total_referrals={$totalReferrals}\n";
echo "total_referral_tickets={$totalReferralTickets}\n";
echo "total_discount={$totalDiscount}\n";
