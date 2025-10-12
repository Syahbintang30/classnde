<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ReferralCaptureMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Capture referral code from query (?ref= or ?referral=) on any page and store in session
        $code = $request->query('ref') ?: $request->query('referral');
        if (! empty($code)) {
            $request->session()->put('referral', trim($code));
        }
        return $next($request);
    }
}
