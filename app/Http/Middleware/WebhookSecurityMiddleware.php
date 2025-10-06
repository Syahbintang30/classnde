<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class WebhookSecurityMiddleware
{
    /**
     * Handle an incoming webhook request with enhanced security measures
     */
    public function handle(Request $request, Closure $next, string $provider = null)
    {
        // Rate limiting per IP to prevent abuse
        $this->applyRateLimiting($request);
        
        // Log all webhook attempts for security monitoring
        $this->logWebhookAttempt($request, $provider);
        
        // Basic security headers validation
        $this->validateSecurityHeaders($request, $provider);
        
        return $next($request);
    }
    
    /**
     * Apply rate limiting to prevent webhook abuse
     */
    private function applyRateLimiting(Request $request)
    {
        $key = 'webhook_rate_limit:' . $request->ip();
        $attempts = Cache::get($key, 0);
        
        // Allow max 100 webhook requests per minute per IP
        if ($attempts >= 100) {
            Log::warning('Webhook rate limit exceeded', [
                'ip' => $request->ip(),
                'attempts' => $attempts,
                'url' => $request->fullUrl()
            ]);
            abort(429, 'Too Many Requests');
        }
        
        Cache::put($key, $attempts + 1, 60); // 1 minute TTL
    }
    
    /**
     * Log webhook attempts for security monitoring
     */
    private function logWebhookAttempt(Request $request, ?string $provider)
    {
        Log::info('Webhook attempt', [
            'provider' => $provider,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'content_type' => $request->header('Content-Type'),
            'content_length' => $request->header('Content-Length')
        ]);
    }
    
    /**
     * Validate basic security headers
     */
    private function validateSecurityHeaders(Request $request, ?string $provider)
    {
        // Ensure proper content type for webhooks
        $contentType = $request->header('Content-Type');
        $validTypes = [
            'application/json',
            'application/x-www-form-urlencoded'
        ];
        
        $isValidContentType = false;
        foreach ($validTypes as $type) {
            if (strpos($contentType, $type) !== false) {
                $isValidContentType = true;
                break;
            }
        }
        
        if (! $isValidContentType) {
            Log::warning('Webhook invalid content type', [
                'provider' => $provider,
                'ip' => $request->ip(),
                'content_type' => $contentType
            ]);
            abort(400, 'Invalid Content-Type');
        }
        
        // Check for required provider-specific headers
        if ($provider === 'midtrans') {
            // Midtrans webhooks should have signature in body or header
            if (! $request->has('signature_key') && 
                ! $request->header('X-Signature') && 
                ! $request->header('X-Callback-Signature')) {
                Log::warning('Midtrans webhook missing signature headers', [
                    'ip' => $request->ip()
                ]);
                abort(403, 'Missing signature headers');
            }
        }
        
        if ($provider === 'twilio') {
            // Twilio webhooks must have signature header
            if (! $request->header('X-Twilio-Signature')) {
                Log::warning('Twilio webhook missing signature header', [
                    'ip' => $request->ip()
                ]);
                abort(403, 'Missing X-Twilio-Signature header');
            }
        }
    }
}