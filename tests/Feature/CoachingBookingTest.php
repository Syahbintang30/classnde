<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\CoachingTicket;

class CoachingBookingTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_ticket_can_book()
    {
        $user = User::factory()->create();
        CoachingTicket::create(['user_id' => $user->id, 'is_used' => false, 'source' => 'test']);

        $this->actingAs($user)
            ->post('/coaching/book', ['booking_time' => now()->addDay()->format('Y-m-d H:i:s')])
            ->assertRedirect();

        $this->assertDatabaseHas('coaching_bookings', ['user_id' => $user->id]);
        $this->assertDatabaseHas('coaching_tickets', ['user_id' => $user->id, 'is_used' => true]);
    }

    public function test_user_without_ticket_cannot_book()
    {
        $user = User::factory()->create();

    // remove any auto-created tickets (AppServiceProvider seeds a free ticket on user created)
    \App\Models\CoachingTicket::where('user_id', $user->id)->delete();

    $response = $this->actingAs($user)
            ->followingRedirects()
            ->post('/coaching/book', ['booking_time' => now()->addDay()->format('Y-m-d H:i:s')]);

    $response->assertSee('available');
    }

    public function test_only_owner_can_access_session()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        CoachingTicket::create(['user_id' => $user->id, 'is_used' => false, 'source' => 'test']);

        $this->actingAs($user)
            ->post('/coaching/book', ['booking_time' => now()->addDay()->format('Y-m-d H:i:s')]);

        $booking = \App\Models\CoachingBooking::first();

        $this->actingAs($other)
            ->get('/coaching/session/' . $booking->id)
            ->assertStatus(403);
    }
}
