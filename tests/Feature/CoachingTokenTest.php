<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\CoachingTicket;

class CoachingTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_token_endpoint_returns_token_and_room()
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

        $booking = \App\Models\CoachingBooking::first();

        $resp = $this->actingAs($user)->getJson('/coaching/token/' . $booking->id);

        $resp->assertStatus(200)
            ->assertJsonStructure(['token', 'room']);
    }
}
