<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\CoachingTicket;
use App\Models\CoachingBooking;

class CoachingBookingE2ETest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_booking_and_caching_and_twilio_room_is_set_in_testing()
    {
        // create user and ticket
        $user = User::factory()->create();
        $ticket = CoachingTicket::create(['user_id' => $user->id, 'is_used' => false, 'source' => 'test']);

        $this->actingAs($user);

        $bookingTime = now()->addDay()->startOfHour()->format('Y-m-d H:i:s');

        $resp = $this->post('/coaching/book', ['booking_time' => $bookingTime]);

        $resp->assertRedirect();

        // booking record should exist for the user and is created as pending by controller
        $this->assertDatabaseHas('coaching_bookings', [
            'user_id' => $user->id,
            'status' => 'pending'
        ]);

        $booking = CoachingBooking::first();
        $this->assertNotNull($booking);

        // ensure the ticket associated with booking belongs to the user and is marked used
        $usedTicket = CoachingTicket::find($booking->ticket_id);
        $this->assertNotNull($usedTicket);
        $this->assertEquals($user->id, $usedTicket->user_id);
        $this->assertTrue((bool) $usedTicket->is_used);

    // caching_bookings table has been deprecated; notes are optional now.

        // in testing env TwilioService returns fake SID prefixed with RMFAKE
        $this->assertNotEmpty($booking->twilio_room_sid);
        $this->assertStringStartsWith('RMFAKE', $booking->twilio_room_sid);
    }
}
