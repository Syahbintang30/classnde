<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Twilio\Security\RequestValidator;

class TwilioWebhookController extends Controller
{
    public function video(Request $request)
    {
        // Validate Twilio signature if auth token configured
        $payload = $request->all();
        $twilioToken = config('services.twilio.auth_token') ?: env('TWILIO_AUTH_TOKEN');
        $signature = $request->header('X-Twilio-Signature');

        if ($twilioToken) {
            if (! $signature) {
                Log::warning('Twilio webhook: missing X-Twilio-Signature header');
                return response()->json(['error' => 'signature_missing'], 403);
            }

            if (! class_exists(RequestValidator::class)) {
                Log::warning('Twilio webhook: RequestValidator class not available; cannot verify signature.');
            } else {
                try {
                    $validator = new RequestValidator($twilioToken);
                    $url = $request->fullUrl();
                    $params = $payload;
                    $valid = $validator->validate($signature, $url, $params);
                    if (! $valid) {
                        Log::warning('Twilio webhook: signature verification failed', ['url' => $url]);
                        return response()->json(['error' => 'invalid_signature'], 403);
                    }
                } catch (\Throwable $e) {
                    Log::error('Twilio webhook: signature validation error: ' . $e->getMessage());
                    return response()->json(['error' => 'signature_validation_error'], 500);
                }
            }
        } else {
            Log::notice('Twilio webhook: TWILIO_AUTH_TOKEN not configured; skipping signature verification.');
        }

        Log::info('Twilio webhook received', $payload);

        // Example handling: record room/participant/recording events into coaching_events or coaching_recordings
        try {
            $eventType = $payload['StatusCallbackEvent'] ?? ($payload['EventType'] ?? null);
            $roomSid = $payload['RoomSid'] ?? ($payload['RoomSid'] ?? null);

            // store generic event: try to attach to a booking by twilio_room_sid
            if (isset($payload['RoomSid'])) {
                $roomSidVal = $payload['RoomSid'];
                $booking = \App\Models\CoachingBooking::where('twilio_room_sid', $roomSidVal)->first();

                $line = "Twilio event: " . ($eventType ?? 'unknown') . " at " . now()->toDateTimeString();
                try {
                    $metaJson = json_encode($payload, JSON_UNESCAPED_UNICODE);
                    $line .= "\nmeta: " . $metaJson;
                } catch (\Throwable $_) { /* ignore meta encoding issues */ }

                if ($booking) {
                    $existing = $booking->notes ?? '';
                    $booking->notes = trim(($existing ? $existing . "\n\n" : '') . $line);
                    $booking->save();
                } else {
                    // No booking found; log for investigation.
                    Log::info('Twilio webhook: no booking matched for RoomSid ' . $roomSidVal, $payload);
                }
            }

            // store recording info if present
            if (isset($payload['RecordingSid']) || isset($payload['RecordingUrl'])) {
                \App\Models\CoachingRecording::create([
                    'room_sid' => $payload['RoomSid'] ?? null,
                    'recording_sid' => $payload['RecordingSid'] ?? ($payload['RecordingSid'] ?? null),
                    'status' => $payload['Status'] ?? ($payload['RecordingStatus'] ?? 'unknown'),
                    'details' => $payload,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Twilio webhook processing error: ' . $e->getMessage());
            return response()->json(['ok' => false], 500);
        }

        return response()->json(['ok' => true]);
    }
}
