<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\CoachingTicket;

class CoachingTicketSeeder extends Seeder
{
    public function run()
    {
        // Give each existing user one free ticket if they don't have any
        User::all()->each(function (User $user) {
            $exists = CoachingTicket::where('user_id', $user->id)->exists();
            if (! $exists) {
                CoachingTicket::create([
                    'user_id' => $user->id,
                    'is_used' => false,
                    'source' => 'free_on_seed',
                ]);
            }
        });
    }
}
