<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantAdmin
{
    /**
     * Handle an incoming request.
     * Verifies the user is an admin for the current tenant.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $tenant = app('tenant');

        if (!$user) {
            // Redirect to tenant login
            return redirect()->route('tenant.login');
        }

        if (!$tenant) {
            abort(404, 'Tenant not found.');
        }

        // Check if user belongs to this tenant and has admin role
        // This assumes user has tenant_id and role fields
        if ($user->tenant_id !== $tenant->id || $user->role !== 'admin') {
            abort(403, 'You do not have permission to access this store admin panel.');
        }

        return $next($request);
    }
}
