<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSuperAdmin
{
    /**
     * Allow only superadmin users
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || ! ($user->is_superadmin ?? false)) {
            abort(403, 'Unauthorized. Superadmin only.');
        }
        return $next($request);
    }
}
