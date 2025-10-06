<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RateLimitingMiddleware
{
    /**
     * Rate limiting configuration
     */
    private array $limits = [];

    public function __construct()
    {
        $rateLimitConfig = \App\Services\DynamicConfigService::getRateLimiting();
        
        $this->limits = [
            'api' => [
                'requests' => $rateLimitConfig['api_requests'],
                'per_minutes' => $rateLimitConfig['window_minutes'],
            ],
            'auth' => [
                'requests' => $rateLimitConfig['auth_requests'],
                'per_minutes' => $rateLimitConfig['auth_window_minutes'],
            ],
            'payment' => [
                'requests' => config('constants.rate_limiting.payment_requests_per_hour', 20),
                'per_minutes' => $rateLimitConfig['window_minutes'],
            ],
            'contact' => [
                'requests' => config('constants.rate_limiting.contact_requests_per_hour', 5),
                'per_minutes' => $rateLimitConfig['window_minutes'],
            ],
            'search' => [
                'requests' => config('constants.rate_limiting.search_requests_per_hour', 50),
                'per_minutes' => $rateLimitConfig['window_minutes'],
            ],
            'default' => [
                'requests' => config('constants.rate_limiting.default_requests_per_hour', 200),
                'per_minutes' => $rateLimitConfig['window_minutes'],
            ]
        ];
    }

    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next, string $type = 'default'): Response
    {
        $identifier = $this->getIdentifier($request);
        $limit = $this->limits[$type] ?? $this->limits['default'];
        
        // Check if rate limit exceeded
        if ($this->isRateLimitExceeded($identifier, $type, $limit)) {
            $this->logRateLimitViolation($request, $type, $identifier);
            return $this->rateLimitResponse($limit);
        }

        // Increment request count
        $this->incrementRequestCount($identifier, $type, $limit);

        $response = $next($request);

        // Add rate limit headers to response
        $this->addRateLimitHeaders($response, $identifier, $type, $limit);

        return $response;
    }

    /**
     * Get unique identifier for rate limiting
     */
    private function getIdentifier(Request $request): string
    {
        // Use user ID for authenticated users, IP for guests
        if ($request->user()) {
            return 'user:' . $request->user()->id;
        }

        return 'ip:' . $request->ip();
    }

    /**
     * Check if rate limit is exceeded
     */
    private function isRateLimitExceeded(string $identifier, string $type, array $limit): bool
    {
        $key = "rate_limit:{$type}:{$identifier}";
        $requests = Cache::get($key, 0);
        
        return $requests >= $limit['requests'];
    }

    /**
     * Increment request count
     */
    private function incrementRequestCount(string $identifier, string $type, array $limit): void
    {
        $key = "rate_limit:{$type}:{$identifier}";
        $ttl = $limit['per_minutes'] * 60; // Convert to seconds
        
        $requests = Cache::get($key, 0);
        Cache::put($key, $requests + 1, $ttl);
    }

    /**
     * Get current request count and remaining requests
     */
    private function getRateLimitInfo(string $identifier, string $type, array $limit): array
    {
        $key = "rate_limit:{$type}:{$identifier}";
        $requests = Cache::get($key, 0);
        $remaining = max(0, $limit['requests'] - $requests);
        
        return [
            'requests' => $requests,
            'remaining' => $remaining,
            'limit' => $limit['requests'],
            'reset_time' => now()->addMinutes($limit['per_minutes'])->timestamp
        ];
    }

    /**
     * Add rate limit headers to response
     */
    private function addRateLimitHeaders(Response $response, string $identifier, string $type, array $limit): void
    {
        $info = $this->getRateLimitInfo($identifier, $type, $limit);
        
        $response->headers->set('X-RateLimit-Limit', $info['limit']);
        $response->headers->set('X-RateLimit-Remaining', $info['remaining']);
        $response->headers->set('X-RateLimit-Reset', $info['reset_time']);
    }

    /**
     * Log rate limit violation
     */
    private function logRateLimitViolation(Request $request, string $type, string $identifier): void
    {
        Log::warning('Rate limit exceeded', [
            'type' => $type,
            'identifier' => $identifier,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'user_id' => $request->user()?->id,
        ]);
    }

    /**
     * Return rate limit exceeded response
     */
    private function rateLimitResponse(array $limit): Response
    {
        $message = "Too many requests. You can make {$limit['requests']} requests per {$limit['per_minutes']} minutes.";
        
        if (request()->wantsJson()) {
            return response()->json([
                'error' => 'Rate limit exceeded',
                'message' => $message,
                'limit' => $limit['requests'],
                'window_minutes' => $limit['per_minutes']
            ], 429);
        }

        return response()->view('errors.429', [
            'message' => $message
        ], 429);
    }
}