<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureAdminOrSuper
{
    /**
     * Handle an incoming request.
     * Allow access if user is_admin OR is_superadmin.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || (! $user->is_admin && ! $user->is_superadmin)) {
            abort(403, 'Unauthorized.');
        }
        return $next($request);
    }
}
