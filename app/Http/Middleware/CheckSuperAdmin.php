<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperAdmin
{
    /**
     * Handle an incoming request.
     * Only allows access if user has 'superadmin' role in central domain.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Check if user has superadmin role
        if ($request->user()->role !== 'superadmin') {
            // Redirect to home with error message
            return redirect()->route('home')
                ->with('error', 'Anda tidak memiliki akses ke halaman ini. Hanya Super Admin yang diizinkan.');
        }

        return $next($request);
    }
}
