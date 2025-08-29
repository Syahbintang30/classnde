<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\CoachingTicket;
use Illuminate\Support\Facades\Hash;

class UserObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_referral_ticket_granted_to_referrer_on_user_create()
    {
        // create referrer
        $ref = User::factory()->create();
        // ensure referrer has referral_code
        $this->assertNotEmpty($ref->referral_code);

        // create referred user
        $child = User::create([
            'name' => 'Child Test',
            'email' => 'child-test+' . uniqid() . '@example.test',
            'password' => Hash::make('secret123'),
            'referred_by' => $ref->id,
        ]);

        $this->assertDatabaseHas('users', ['id' => $child->id, 'referred_by' => $ref->id]);

        // referrer should have a referral-sourced CoachingTicket referencing this referred user
        $ticket = CoachingTicket::where('user_id', $ref->id)->where('source','referral')->where('referrer_user_id', $child->id)->first();
        $this->assertNotNull($ticket, 'Expected referral coaching ticket for referrer');
    }
}
