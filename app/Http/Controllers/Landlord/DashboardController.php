<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display landlord dashboard
     */
    public function index(): Response
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::count(),
            'total_domains' => \DB::table('domains')->count(),
        ];

        $recentTenants = Tenant::with('domains')
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Landlord/Dashboard', [
            'stats' => $stats,
            'recentTenants' => $recentTenants,
        ]);
    }
}
