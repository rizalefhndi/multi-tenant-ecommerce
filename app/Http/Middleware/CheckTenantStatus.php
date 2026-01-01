<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantStatus
{
    /**
     * Handle an incoming request.
     * Blocks access to suspended tenants.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if ($tenant && $tenant->status === 'suspended') {
            // Return a view or redirect showing tenant is suspended
            return response()->view('errors.tenant-suspended', [
                'tenant' => $tenant,
                'reason' => $tenant->suspended_reason,
            ], 503);
        }

        return $next($request);
    }
}
