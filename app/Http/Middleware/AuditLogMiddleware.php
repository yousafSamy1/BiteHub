<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log for authenticated admin/owner and state-changing methods
        if (Auth::check() && in_array(Auth::user()->Role, ['Admin', 'Owner'])) {
            $method = $request->method();
            if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
                
                // Skip if it's a login/logout action to avoid noise or if password sensitive
                $path = $request->path();
                if (str_contains($path, 'login') || str_contains($path, 'logout')) {
                    return $response;
                }

                $action = $method . ' ' . ($request->route() ? $request->route()->getName() : $path);
                
                // Sanitize details (exclude passwords and technical fields)
                $details = $request->except(['password', 'old_password', 'new_password', 'new_password_confirmation', '_token', '_method', 'ajax']);
                
                // Add route parameters to details (e.g. {id})
                if ($request->route()) {
                    $details = array_merge($details, $request->route()->parameters());
                }

                AuditLog::create([
                    'UserID'    => Auth::id(),
                    'Action'    => $action,
                    'Details'   => json_encode($details),
                    'IPAddress' => $request->ip(),
                ]);
            }
        }

        return $response;
    }
}
