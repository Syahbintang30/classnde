<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\CoachingTicket;

class CoachingCoachAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_configured_coach_can_access_session_and_token()
    {
        // set fake twilio config so controller proceeds
        config([
            'services.twilio.account_sid' => 'ACFAKE',
            'services.twilio.api_key_sid' => 'SKFAKE',
            'services.twilio.api_key_secret' => 'SECFake',
        ]);

        $user = User::factory()->create();
        CoachingTicket::create(['user_id' => $user->id, 'is_used' => false, 'source' => 'test']);

        $this->actingAs($user)
            ->post('/coaching/book', ['booking_time' => now()->addDay()->format('Y-m-d H:i:s')]);

        // create coach user and configure as coach via config
        $coach = User::factory()->create(['email' => 'coach@example.com']);
        config(['coaching.coaches' => ['coach@example.com']]);

        // create a booking for another user (no explicit coach assignment)
        $this->actingAs($user)
            ->post('/coaching/book', ['booking_time' => now()->addDays(2)->format('Y-m-d H:i:s')]);

        $booking = \App\Models\CoachingBooking::latest()->first();

        // configure coach email in config so they are allowed to access sessions
        config(['coaching.coaches' => ['coach@example.com']]);

        // coach can access session and token despite not being explicitly assigned
        $this->actingAs($coach)
            ->get('/coaching/session/' . $booking->id)
            ->assertStatus(200);

        $resp = $this->actingAs($coach)->getJson('/coaching/token/' . $booking->id);
        $resp->assertStatus(200)->assertJsonStructure(['token', 'room']);
    }
}
