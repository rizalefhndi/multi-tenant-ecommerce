<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Check if user has one of the allowed roles
        foreach ($roles as $role) {
            if ($request->user()->hasRole($role)) {
                return $next($request);
            }
        }

        // If no role matches, redirect based on user's role
        if ($request->user()->isAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have access to this page.');
        }

        return redirect()->route('customer.home')
            ->with('error', 'You do not have access to this page.');
    }
}
