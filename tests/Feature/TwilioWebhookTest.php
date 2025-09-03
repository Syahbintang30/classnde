<?php

namespace Tests\Feature;

use Tests\TestCase;
use Twilio\Security\RequestValidator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class TwilioWebhookTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // create minimal tables needed by TwilioWebhookController
        Schema::create('coaching_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('twilio_room_sid')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('coaching_recordings', function (Blueprint $table) {
            $table->id();
            $table->string('room_sid')->nullable();
            $table->string('recording_sid')->nullable();
            $table->string('status')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }
    /** @test */
    public function valid_twilio_signature_is_accepted()
    {
        // arrange
        $token = 'test-token-123';
        config(['services.twilio.auth_token' => $token]);

        $payload = ['StatusCallbackEvent' => 'room-ended', 'RoomSid' => 'RM123456'];
        $url = url('/webhooks/twilio/video');

        $validator = new RequestValidator($token);
        $signature = $validator->computeSignature($url, $payload);

        // act
        $response = $this->withHeaders(['X-Twilio-Signature' => $signature])
                         ->post('/webhooks/twilio/video', $payload);

        // assert
        $response->assertStatus(200);
        $response->assertJson(['ok' => true]);
    }

    /** @test */
    public function invalid_twilio_signature_is_rejected()
    {
        $token = 'test-token-123';
        config(['services.twilio.auth_token' => $token]);

        $payload = ['StatusCallbackEvent' => 'room-ended', 'RoomSid' => 'RM123456'];
        // wrong signature
        $response = $this->withHeaders(['X-Twilio-Signature' => 'bad-signature'])
                         ->post('/webhooks/twilio/video', $payload);

        $response->assertStatus(403);
    }
}
