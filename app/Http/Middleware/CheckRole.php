<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        switch ($role) {
            case 'super_admin':
                if (!$user->isSuperAdmin()) {
                    abort(403, 'Access denied. Super admin access required.');
                }
                break;

            case 'tenant_admin':
                if (!$user->isTenantAdmin() && !$user->isSuperAdmin()) {
                    abort(403, 'Access denied. Tenant admin access required.');
                }
                break;

            case 'any':
                // Any authenticated user can access
                break;
        }

        return $next($request);
    }
}
