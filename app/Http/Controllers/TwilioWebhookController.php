<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class TwilioWebhookController extends Controller
{
    public function video(Request $request)
    {
        // Basic skeleton: Twilio sends various event types in POST body
        // If needed, validate Twilio signature here (omitted for brevity)
        $payload = $request->all();
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
