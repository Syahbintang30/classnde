<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Twilio\Security\RequestValidator;

class TwilioWebhookController extends Controller
{
    /**
     * SECURITY: Validate Twilio webhook IP ranges  
     */
    private function validateTwilioIP(Request $request)
    {
        // Skip IP validation in local development
        if (app()->environment('local') || config('app.debug')) {
            return true;
        }
        
        $clientIP = $request->ip();
        
        // Twilio official IP ranges for webhooks (configurable)
        // Reference: https://www.twilio.com/docs/usage/webhooks/ip-addresses
        $twilioIPs = config('services.twilio.webhook_ip_ranges');
        if (!is_array($twilioIPs) || empty($twilioIPs)) {
            $twilioIPs = [
                '54.172.60.0/23',
                '54.244.51.0/24', 
                '54.171.127.192/27',
                '35.156.191.128/25',
                '54.65.63.192/27',
                '54.169.127.128/27',
                '54.252.254.64/26',
                '177.71.206.192/26'
            ];
        }
        
        foreach ($twilioIPs as $range) {
            if ($this->ipInRange($clientIP, $range)) {
                return true;
            }
        }
        
        Log::warning('Twilio webhook: unauthorized IP attempt', [
            'ip' => $clientIP,
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl()
        ]);
        
        abort(403, 'IP not authorized for webhook');
    }
    
    /**
     * Check if IP is in CIDR range
     */
    private function ipInRange($ip, $range)
    {
        if (strpos($range, '/') === false) {
            return $ip === $range;
        }
        
        list($subnet, $bits) = explode('/', $range);
        $ip = ip2long($ip);
        $subnet = ip2long($subnet);
        $mask = -1 << (32 - $bits);
        $subnet &= $mask;
        return ($ip & $mask) == $subnet;
    }
    public function video(Request $request)
    {
        // SECURITY ENHANCEMENT: Validate request IP
        $this->validateTwilioIP($request);
        
        // ENHANCED SECURITY: Strict signature validation for Twilio webhooks
        $payload = $request->all();
        $twilioToken = config('services.twilio.auth_token') ?: env('TWILIO_AUTH_TOKEN');
        $signature = $request->header('X-Twilio-Signature');

        if (! $twilioToken) {
            Log::error('Twilio webhook: auth token not configured - SECURITY RISK', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl()
            ]);
            return response()->json(['error' => 'server_configuration_error'], 500);
        }

        if (! $signature) {
            Log::warning('Twilio webhook: missing X-Twilio-Signature header - potential attack', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
            return response()->json(['error' => 'signature_required'], 403);
        }

        // SECURITY: Mandatory signature validation - no skipping allowed
        if (! class_exists(RequestValidator::class)) {
            Log::error('Twilio webhook: RequestValidator class not available - cannot verify signature', [
                'ip' => $request->ip()
            ]);
            return response()->json(['error' => 'signature_validation_unavailable'], 500);
        }

        try {
            $validator = new RequestValidator($twilioToken);
            $url = $request->fullUrl();
            $params = $payload;
            $valid = $validator->validate($signature, $url, $params);
            if (! $valid) {
                Log::warning('Twilio webhook: signature verification failed - potential attack', [
                    'url' => $url,
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);
                return response()->json(['error' => 'invalid_signature'], 403);
            }
        } catch (\Throwable $e) {
            Log::error('Twilio webhook: signature validation error: ' . $e->getMessage(), [
                'ip' => $request->ip()
            ]);
            return response()->json(['error' => 'signature_validation_error'], 500);
        }

        Log::info('Twilio webhook: signature verified successfully', [
            'ip' => $request->ip(),
            'event_type' => $payload['StatusCallbackEvent'] ?? 'unknown'
        ]);

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
