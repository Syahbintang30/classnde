<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SessionSecurityMiddleware
{
    /**
     * Session timeout and security configuration
     */
    private int $sessionTimeout;
    private int $maxConcurrentSessions;
    private int $sessionCacheHours;

    public function __construct()
    {
        $securityConfig = \App\Services\DynamicConfigService::getSecurity();
        
        $this->sessionTimeout = $securityConfig['session_timeout_minutes'];
        $this->maxConcurrentSessions = $securityConfig['max_concurrent_sessions'];
        $this->sessionCacheHours = config('constants.security.session_cache_hours', 24);
    }

    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only apply to authenticated users
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        $currentSessionId = session()->getId();

        // Check session security
        $this->validateSessionSecurity($request, $user, $currentSessionId);
        
        // Handle concurrent sessions
        $this->manageConcurrentSessions($user, $currentSessionId);
        
        // Update session activity
        $this->updateSessionActivity($user, $currentSessionId, $request);

        $response = $next($request);

        // Set secure session headers
        $this->setSecureSessionHeaders($response);

        return $response;
    }

    /**
     * Validate session security
     */
    private function validateSessionSecurity(Request $request, $user, string $sessionId): void
    {
        $sessionKey = "session_security:{$user->id}:{$sessionId}";
        $sessionData = Cache::get($sessionKey);

        if ($sessionData) {
            // Check if IP address changed (potential session hijacking)
            if ($sessionData['ip'] !== $request->ip()) {
                Log::warning('Session IP address changed - potential hijacking', [
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                    'original_ip' => $sessionData['ip'],
                    'current_ip' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                // Force logout for security
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                abort(403, 'Session security violation detected.');
            }

            // Check user agent changes
            if ($sessionData['user_agent'] !== $request->userAgent()) {
                Log::warning('Session user agent changed', [
                    'user_id' => $user->id,
                    'session_id' => $sessionId,
                    'original_ua' => $sessionData['user_agent'],
                    'current_ua' => $request->userAgent(),
                    'ip' => $request->ip()
                ]);
            }
        } else {
            // First time seeing this session, store security data
            Cache::put($sessionKey, [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'last_activity' => now()
            ], now()->addHours(24));
        }
    }

    /**
     * Manage concurrent sessions
     */
    private function manageConcurrentSessions($user, string $currentSessionId): void
    {
        $userSessionsKey = "user_sessions:{$user->id}";
        $userSessions = Cache::get($userSessionsKey, []);

        // Add current session if not exists
        if (!isset($userSessions[$currentSessionId])) {
            $userSessions[$currentSessionId] = [
                'created_at' => now(),
                'last_activity' => now()
            ];
        } else {
            $userSessions[$currentSessionId]['last_activity'] = now();
        }

        // Remove expired sessions
        $userSessions = $this->removeExpiredSessions($userSessions);

        // Check concurrent session limit
        if (count($userSessions) > $this->maxConcurrentSessions) {
            // Remove oldest sessions
            $userSessions = $this->removeOldestSessions($userSessions, $currentSessionId);
            
            Log::info('Concurrent session limit exceeded, removed oldest sessions', [
                'user_id' => $user->id,
                'current_session_id' => $currentSessionId,
                'total_sessions' => count($userSessions)
            ]);
        }

        // Update cache
        Cache::put($userSessionsKey, $userSessions, now()->addHours($this->sessionCacheHours));
    }

    /**
     * Remove expired sessions
     */
    private function removeExpiredSessions(array $sessions): array
    {
        $cutoff = now()->subMinutes($this->sessionTimeout);
        
        return array_filter($sessions, function ($sessionData) use ($cutoff) {
            return $sessionData['last_activity'] > $cutoff;
        });
    }

    /**
     * Remove oldest sessions to maintain limit
     */
    private function removeOldestSessions(array $sessions, string $currentSessionId): array
    {
        // Sort by last activity, keep most recent
        uasort($sessions, function ($a, $b) {
            return $b['last_activity'] <=> $a['last_activity'];
        });

        // Keep current session and most recent ones
        $keepSessions = [$currentSessionId => $sessions[$currentSessionId]];
        
        $count = 1;
        foreach ($sessions as $sessionId => $sessionData) {
            if ($sessionId !== $currentSessionId && $count < $this->maxConcurrentSessions) {
                $keepSessions[$sessionId] = $sessionData;
                $count++;
            } elseif ($sessionId !== $currentSessionId) {
                // Invalidate old session
                $this->invalidateSession($sessionId);
            }
        }

        return $keepSessions;
    }

    /**
     * Invalidate a specific session
     */
    private function invalidateSession(string $sessionId): void
    {
        try {
            // Remove from database if using database sessions
            DB::table('sessions')->where('id', $sessionId)->delete();
            
            Log::info('Session invalidated due to concurrent session limit', [
                'session_id' => $sessionId
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to invalidate session: ' . $e->getMessage());
        }
    }

    /**
     * Update session activity
     */
    private function updateSessionActivity($user, string $sessionId, Request $request): void
    {
        $sessionKey = "session_security:{$user->id}:{$sessionId}";
        $sessionData = Cache::get($sessionKey, []);
        
        $sessionData['last_activity'] = now();
        $sessionData['last_ip'] = $request->ip();
        $sessionData['last_url'] = $request->fullUrl();
        
        Cache::put($sessionKey, $sessionData, now()->addHours(24));
    }

    /**
     * Set secure session headers
     */
    private function setSecureSessionHeaders(Response $response): void
    {
        // Set secure cookie attributes
        $sessionCookieName = config('session.cookie');
        
        if ($sessionCookieName) {
            $response->headers->setCookie(cookie(
                name: $sessionCookieName,
                value: session()->getId(),
                minutes: config('session.lifetime'),
                path: config('session.path'),
                domain: config('session.domain'),
                secure: config('session.secure', true),
                httpOnly: true,
                sameSite: 'strict'
            ));
        }
    }

    /**
     * Get active sessions for a user (for admin/user display)
     */
    public static function getUserActiveSessions(int $userId): array
    {
        $userSessionsKey = "user_sessions:{$userId}";
        $sessions = Cache::get($userSessionsKey, []);
        
        $activeSessions = [];
        foreach ($sessions as $sessionId => $sessionData) {
            $sessionKey = "session_security:{$userId}:{$sessionId}";
            $securityData = Cache::get($sessionKey, []);
            
            $activeSessions[] = [
                'session_id' => $sessionId,
                'created_at' => $sessionData['created_at'],
                'last_activity' => $sessionData['last_activity'],
                'ip' => $securityData['ip'] ?? 'Unknown',
                'user_agent' => $securityData['user_agent'] ?? 'Unknown',
                'is_current' => $sessionId === session()->getId()
            ];
        }
        
        return $activeSessions;
    }

    /**
     * Force logout user from all sessions
     */
    public static function logoutUserFromAllSessions(int $userId): void
    {
        $userSessionsKey = "user_sessions:{$userId}";
        $sessions = Cache::get($userSessionsKey, []);
        
        foreach ($sessions as $sessionId => $sessionData) {
            // Remove from database
            DB::table('sessions')->where('id', $sessionId)->delete();
            
            // Remove from cache
            $sessionKey = "session_security:{$userId}:{$sessionId}";
            Cache::forget($sessionKey);
        }
        
        // Clear user sessions list
        Cache::forget($userSessionsKey);
        
        Log::info('User logged out from all sessions', [
            'user_id' => $userId,
            'sessions_count' => count($sessions)
        ]);
    }
}