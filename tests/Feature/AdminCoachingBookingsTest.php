<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class AdminCoachingBookingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_bookings()
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/admin/coaching/bookings');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_bookings()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin/coaching/bookings');

        $response->assertStatus(200);
    }
}
