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

        if (!$tenant) {
            return $next($request);
        }

        // Auto-mark tenant as expired when expired_at is past
        if (
            $tenant->expired_at
            && $tenant->expired_at->isPast()
            && !in_array($tenant->status, ['expired', 'suspended'], true)
        ) {
            $tenant->update([
                'status' => 'expired',
                'subscription_status' => 'expired',
            ]);
            $tenant->refresh();
        }

        if (in_array($tenant->status, ['suspended', 'pending', 'expired'], true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'tenant_inactive',
                    'status' => $tenant->status,
                    'message' => match ($tenant->status) {
                        'pending' => 'Toko belum aktif. Pembayaran sedang menunggu konfirmasi.',
                        'expired' => 'Masa aktif toko sudah berakhir.',
                        default => 'Toko sedang dinonaktifkan sementara.',
                    },
                ], 403);
            }

            return response()->view('errors.tenant-inactive', [
                'tenant' => $tenant,
                'status' => $tenant->status,
                'reason' => $tenant->suspended_reason,
            ], $tenant->status === 'suspended' ? 503 : 403);
        }

        return $next($request);
    }
}
