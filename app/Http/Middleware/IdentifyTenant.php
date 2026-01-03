<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Context;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $baseDomain = config('app.url_base', 'localhost'); // Perlu config tambahan atau hardcode
        
        // Simple extraction for local dev: demo.localhost -> demo
        // Assuming base is localhost
        $parts = explode('.', $host);
        
        // If localhost or central domain
        if ($host === 'localhost' || count($parts) === 1) {
            // Central Domain
            // Ensure no tenant context is set
            return $next($request);
        }

        // Subdomain found
        $subdomain = $parts[0];
        
        // Skip for 'www' or reserved subdomains
        if (in_array($subdomain, ['www', 'admin', 'mail'])) {
             return $next($request);
        }

        // Find tenant by ID (subdomain is the tenant ID)
        $tenant = Tenant::find($subdomain);

        if (!$tenant) {
            abort(404, 'Store not found.');
        }

        // Share tenant globally
        Context::add('tenant', $tenant);
        
        // Also bind to container if needed fallback
        app()->instance('tenant', $tenant);

        return $next($request);
    }
}
