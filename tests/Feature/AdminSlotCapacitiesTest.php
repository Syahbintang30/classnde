<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\CoachingSlotCapacity;

class AdminSlotCapacitiesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_bulk_slot_capacities_via_json()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $payload = [
            'slots_json' => [
                '2025-08-24' => ['08:00', '09:00']
            ],
            'replace' => true
        ];

        $response = $this->actingAs($admin)
            ->postJson('/admin/coaching/slot-capacities', $payload);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('coaching_slot_capacities', [
            'date' => '2025-08-24 00:00:00',
            'time' => '08:00'
        ]);

        $this->assertDatabaseHas('coaching_slot_capacities', [
            'date' => '2025-08-24 00:00:00',
            'time' => '09:00'
        ]);
    }
}
