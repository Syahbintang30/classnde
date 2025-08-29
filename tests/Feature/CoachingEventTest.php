<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\CoachingTicket;

class CoachingEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_logging_creates_record()
    {
        $user = User::factory()->create();
        CoachingTicket::create(['user_id' => $user->id, 'is_used' => false, 'source' => 'test']);

        $this->actingAs($user)
            ->post('/coaching/book', ['booking_time' => now()->addDay()->format('Y-m-d H:i:s')]);

        $booking = \App\Models\CoachingBooking::first();

        $this->actingAs($user)
            ->post('/coaching/' . $booking->id . '/event', ['event' => 'connected'])
            ->assertJson(['ok' => true]);

        // consolidated events are appended into coaching_bookings.notes
        $booking = $booking->fresh();
        $this->assertNotNull($booking->notes);
        $this->assertStringContainsString('connected', $booking->notes);
    }
}
