<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register security middlewares
        $middleware->alias([
            'webhook.security' => \App\Http\Middleware\WebhookSecurityMiddleware::class,
            'file.upload.security' => \App\Http\Middleware\FileUploadSecurityMiddleware::class,
            'rate.limit' => \App\Http\Middleware\RateLimitingMiddleware::class,
            'session.security' => \App\Http\Middleware\SessionSecurityMiddleware::class,
            'audit.log' => \App\Http\Middleware\AuditLogMiddleware::class,
            'referral.capture' => \App\Http\Middleware\ReferralCaptureMiddleware::class,
        ]);
        
        // Apply security middlewares to all web routes
        $middleware->web(append: [
            \App\Http\Middleware\ContentSecurityPolicyMiddleware::class,
            \App\Http\Middleware\SessionSecurityMiddleware::class,
            \App\Http\Middleware\ReferralCaptureMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
