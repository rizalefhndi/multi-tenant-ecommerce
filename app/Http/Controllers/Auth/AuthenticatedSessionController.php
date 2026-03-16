<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();

        // Check if we're in a tenant context using tenancy helper or host check
        $host = $request->getHost();
        $isCentralDomain = in_array($host, config('tenancy.central_domains', ['127.0.0.1', 'localhost', 'onyx.127.0.0.1.nip.io']));

        if (!$isCentralDomain) {
            // We're on a tenant subdomain, redirect to tenant dashboard
            return redirect('/dashboard');
        }

        // Super admin goes to landlord dashboard
        if ($user->role === 'superadmin') {
            return redirect()->route('landlord.dashboard');
        }

        // Owner/staff central flow resolver
        $ownedTenants = Tenant::query()
            ->where('owner_id', $user->id)
            ->latest('created_at')
            ->get();

        if ($ownedTenants->isEmpty()) {
            return redirect()->intended(route('pricing'));
        }

        $pendingTenant = $ownedTenants->firstWhere('status', 'pending');
        if ($pendingTenant) {
            return redirect()->route('billing.checkout.page', ['tenant' => $pendingTenant->id]);
        }

        // If user has existing stores and none are pending, land on stores list.
        return redirect()->route('my-stores');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
