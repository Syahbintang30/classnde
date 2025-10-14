<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicyMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Build CSP directives based on application needs
        $cspDirectives = $this->buildCSPDirectives($request);
        
        // Set CSP header
        $response->headers->set('Content-Security-Policy', $cspDirectives);
        
        // Additional security headers
        $this->setSecurityHeaders($response);

        return $response;
    }

    /**
     * Build CSP directives based on application requirements
     */
    private function buildCSPDirectives(Request $request): string
    {
        $baseDirectives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com https://code.jquery.com https://stackpath.bootstrapcdn.com https://cdn.tailwindcss.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com https://stackpath.bootstrapcdn.com",
            "font-src 'self' https://fonts.gstatic.com https://fonts.googleapis.com https://cdnjs.cloudflare.com https://use.fontawesome.com https://cdn.jsdelivr.net data:",
            "img-src 'self' data: blob: https: http:",
            "media-src 'self' blob: https://video.bunnycdn.com https://*.bunnycdn.com https://*.b-cdn.net",
            "connect-src 'self' https://api.midtrans.com https://app.midtrans.com https://video.bunnycdn.com https://*.bunnycdn.com wss: ws:",
            "frame-src 'self' https://app.midtrans.com https://api.midtrans.com",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
            "upgrade-insecure-requests"
        ];

        // Add specific directives for admin pages
        if ($request->is('admin/*')) {
            $baseDirectives[] = "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://unpkg.com https://cdnjs.cloudflare.com";
        }

        // Add specific directives for payment pages
        if ($request->is('*/payment*') || $request->is('registerclass/*/payment*')) {
            $baseDirectives[] = "frame-src 'self' https://app.midtrans.com https://api.midtrans.com https://app.sandbox.midtrans.com";
            $baseDirectives[] = "connect-src 'self' https://api.midtrans.com https://app.midtrans.com https://api.sandbox.midtrans.com";
        }

        // Relax CSP specifically for the coaching session page to allow Twilio SDK and signaling
        if ($request->is('coaching/session/*')) {
            $baseDirectives = array_map(function($d){ return $d; }, $baseDirectives);
            // Add Twilio SDK sources for scripts
            $baseDirectives[] = "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://media.twiliocdn.com https://unpkg.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com";
            // Allow Twilio signaling and media websocket connections
            $baseDirectives[] = "connect-src 'self' https://api.twilio.com https://video.twilio.com wss: ws: https://media.twiliocdn.com https://*.twilio.com";
        }

        // Join all directives
        return implode('; ', $baseDirectives);
    }

    /**
     * Set additional security headers
     */
    private function setSecurityHeaders(Response $response): void
    {
        // X-Frame-Options for clickjacking protection
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        
        // X-Content-Type-Options to prevent MIME sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // X-XSS-Protection for legacy browsers
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Permissions Policy (formerly Feature Policy)
        // Default deny camera/microphone; allow on specific coaching routes via route-scoped headers below.
        $policy = 'camera=(), microphone=(), geolocation=(), fullscreen=(self), payment=(self)';
        try {
            $req = request();
            if ($req && $req->is('coaching/session/*')) {
                // Allow camera/mic for live coaching session page
                $policy = 'camera=(self), microphone=(self), geolocation=(), fullscreen=(self), payment=(self)';
            }
        } catch (\Throwable $e) { /* ignore */ }
        $response->headers->set('Permissions-Policy', $policy);
        
        // Strict Transport Security (HTTPS only in production)
        if (app()->environment('production') && request()->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }
        
        // Remove server information
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');
    }
}