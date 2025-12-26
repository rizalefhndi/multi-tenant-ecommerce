<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantQuota
{
    /**
     * Quota types that can be checked
     */
    const QUOTA_PRODUCTS = 'products';
    const QUOTA_ORDERS = 'orders';
    const QUOTA_STORAGE = 'storage';

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $quotaType): Response
    {
        $tenant = tenant();

        // Skip if no tenant context
        if (!$tenant) {
            return $next($request);
        }

        // Check subscription status first
        if (!$tenant->hasActiveSubscription()) {
            return $this->subscriptionExpiredResponse($request);
        }

        // Check specific quota based on type
        switch ($quotaType) {
            case self::QUOTA_PRODUCTS:
                if (!$tenant->canAddProduct()) {
                    return $this->quotaExceededResponse(
                        $request, 
                        'products',
                        'Anda telah mencapai batas maksimum produk untuk paket Anda.',
                        $tenant->getCurrentPlan()?->max_products ?? 0
                    );
                }
                break;

            case self::QUOTA_ORDERS:
                if (!$tenant->canCreateOrder()) {
                    return $this->quotaExceededResponse(
                        $request, 
                        'orders',
                        'Anda telah mencapai batas maksimum pesanan bulan ini.',
                        $tenant->getCurrentPlan()?->max_orders_per_month ?? 0
                    );
                }
                break;

            case self::QUOTA_STORAGE:
                // For storage, we need file size from request
                // This will be checked in controller with specific file size
                break;
        }

        return $next($request);
    }

    /**
     * Response when subscription is expired
     */
    protected function subscriptionExpiredResponse(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'subscription_expired',
                'message' => 'Langganan Anda telah berakhir. Silakan perpanjang untuk melanjutkan.',
                'action' => 'upgrade',
            ], 403);
        }

        // For Inertia requests
        if ($request->header('X-Inertia')) {
            return redirect()->route('subscription.expired')
                ->with('error', 'Langganan Anda telah berakhir.');
        }

        return redirect()->route('subscription.expired');
    }

    /**
     * Response when quota is exceeded
     */
    protected function quotaExceededResponse(
        Request $request, 
        string $quotaType, 
        string $message, 
        int $limit
    ): Response {
        $data = [
            'error' => 'quota_exceeded',
            'quota_type' => $quotaType,
            'message' => $message,
            'limit' => $limit,
            'action' => 'upgrade',
        ];

        if ($request->expectsJson()) {
            return response()->json($data, 403);
        }

        // For Inertia requests
        if ($request->header('X-Inertia')) {
            return redirect()->back()
                ->with('error', $message)
                ->with('quota_exceeded', $quotaType);
        }

        return redirect()->back()->with('error', $message);
    }
}
