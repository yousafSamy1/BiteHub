<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Role
{
    /**
     * Handle an incoming request.
     * Accepts multiple roles: ->middleware('role:Admin') or ->middleware('role:Admin,KitchenOwner')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $parsedRoles = [];
        foreach ($roles as $role) {
            $parsedRoles = array_merge($parsedRoles, explode(',', (string) $role));
        }

        if (!$request->user() || !in_array($request->user()->Role, $parsedRoles)) {
            abort(403, 'Unauthorized. You do not have the required role.');
        }

        return $next($request);
    }
}
