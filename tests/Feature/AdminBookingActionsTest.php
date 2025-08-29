<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\CoachingTicket;
use App\Models\CoachingBooking;
use Illuminate\Support\Facades\Notification;
use App\Notifications\BookingStatusChanged;

class AdminBookingActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_accept_booking_and_user_gets_notified()
    {
        Notification::fake();
    $user = User::factory()->create();
    $ticket = \App\Models\CoachingTicket::create(['user_id' => $user->id, 'is_used' => true, 'source' => 'test']);
    $booking = \App\Models\CoachingBooking::create(['user_id' => $user->id, 'ticket_id' => $ticket->id, 'booking_time' => now()->addDay()->format('Y-m-d H:i:s'), 'status' => 'pending']);

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->post('/admin/coaching/bookings/'.$booking->id.'/accept')->assertRedirect();

        $this->assertDatabaseHas('coaching_bookings', ['id' => $booking->id, 'status' => 'accepted']);
        Notification::assertSentTo($user, BookingStatusChanged::class);
    }

    public function test_admin_can_reject_booking_and_ticket_released_and_user_notified()
    {
        Notification::fake();
    $user = User::factory()->create();
    $ticket = \App\Models\CoachingTicket::create(['user_id' => $user->id, 'is_used' => true, 'source' => 'test']);
    $booking = \App\Models\CoachingBooking::create(['user_id' => $user->id, 'ticket_id' => $ticket->id, 'booking_time' => now()->addDay()->format('Y-m-d H:i:s'), 'status' => 'pending']);

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)->post('/admin/coaching/bookings/'.$booking->id.'/reject')->assertRedirect();

        $this->assertDatabaseHas('coaching_bookings', ['id' => $booking->id, 'status' => 'rejected']);
        $this->assertDatabaseHas('coaching_tickets', ['id' => $ticket->id, 'is_used' => false]);
        Notification::assertSentTo($user, BookingStatusChanged::class);
    }
}
