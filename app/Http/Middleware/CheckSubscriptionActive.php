<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscriptionActive
{
    /**
     * Handle an incoming request.
     * 
     * Middleware untuk memastikan tenant memiliki subscription aktif
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        // Skip if no tenant context
        if (!$tenant) {
            return $next($request);
        }

        // Check subscription status
        if (!$tenant->hasActiveSubscription()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'subscription_inactive',
                    'message' => 'Akses ditolak. Langganan Anda tidak aktif.',
                    'status' => $tenant->subscription_status,
                ], 403);
            }

            // Redirect to subscription page
            return redirect()->route('subscription.index')
                ->with('warning', 'Langganan Anda tidak aktif. Silakan perpanjang atau upgrade paket Anda.');
        }

        return $next($request);
    }
}
