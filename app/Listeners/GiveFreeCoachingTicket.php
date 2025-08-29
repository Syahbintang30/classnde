<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use App\Models\CoachingTicket;

class GiveFreeCoachingTicket
{
    public function handle(Registered $event)
    {
        $user = $event->user;
        if (! CoachingTicket::where('user_id', $user->id)->exists()) {
            CoachingTicket::create([
                'user_id' => $user->id,
                'is_used' => false,
                'source' => 'free_on_register',
            ]);
        }
    }
}
