<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use App\Models\CoachingTicket;
use App\Services\CoachingTicketService;

class GiveFreeCoachingTicket
{
    public function handle(Registered $event)
    {
        $user = $event->user;
        // Grant free tickets based on package slug:
        // - beginner => 1 ticket
        // - intermediate or highest eligible course package => 2 tickets
        // This call is idempotent (tops up to desired count for this source).
        CoachingTicketService::grantFreeOnRegister($user);
    }
}
