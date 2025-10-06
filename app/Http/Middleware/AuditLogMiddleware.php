<?php

namespace App\Http\Middleware;

use App\Models\AuditTrail;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogMiddleware
{
    /**
     * Log mutating admin requests to audit_trails table.
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only log for authenticated users and mutating methods
        if (Auth::check() && in_array(strtoupper($request->method()), ['POST','PUT','PATCH','DELETE'])) {
            // Determine entity from route parameters if present
            $route = $request->route();
            $entityType = null; $entityId = null;
            if ($route) {
                foreach ((array) $route->parameters() as $key => $val) {
                    if (is_object($val) && method_exists($val, 'getKey')) {
                        $entityType = get_class($val);
                        $entityId = $val->getKey();
                        break;
                    }
                }
            }

            AuditTrail::create([
                'user_id' => Auth::id(),
                'action' => sprintf('%s %s', strtoupper($request->method()), $request->path()),
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'ip_address' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'metadata' => [
                    'query' => $request->query(),
                    'payload_keys' => array_keys($request->except(['password','password_confirmation','_token'])),
                    'status' => $response->getStatusCode(),
                ],
            ]);
        }

        return $response;
    }
}
