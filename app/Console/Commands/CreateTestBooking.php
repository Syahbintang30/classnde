<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\CoachingTicket;
use App\Models\CoachingBooking;
use Illuminate\Support\Str;

class CreateTestBooking extends Command
{
    protected $signature = 'test:make-booking {--minutes=6} {--email=test+%rand%@example.com}';
    protected $description = 'Create test user, ticket and booking and print session URL';

    public function handle()
    {
        $rand = substr(md5(uniqid('', true)), 0, 6);
        $emailOption = $this->option('email');
        $email = str_replace('%rand%', $rand, $emailOption);

        $user = User::create([
            'name' => 'Test User ' . $rand,
            'email' => $email,
            'password' => bcrypt('password'),
        ]);

        $this->info('Created user: ' . $user->email . ' (id=' . $user->id . ')');

        $ticket = CoachingTicket::create(['user_id' => $user->id, 'is_used' => false, 'source' => 'test:command']);
        $this->info('Created ticket id=' . $ticket->id);

        $minutes = (int) $this->option('minutes');
        $bookingTime = now()->addMinutes($minutes)->startOfMinute()->format('Y-m-d H:i:s');

        $booking = CoachingBooking::create([
            'user_id' => $user->id,
            'ticket_id' => $ticket->id,
            'booking_time' => $bookingTime,
            'status' => 'pending',
        ]);

        $this->info('Created booking id=' . $booking->id . ' at ' . $bookingTime);

        $url = url('/coaching/session/' . $booking->id);
        $this->info('Open this URL in browser (login as test user):');
        $this->line($url);
        $this->info('Test credentials: email=' . $user->email . ' password=password');

        return 0;
    }
}
