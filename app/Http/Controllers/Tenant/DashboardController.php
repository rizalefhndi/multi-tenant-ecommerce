<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the tenant admin dashboard.
     */
    public function index(Request $request): Response
    {
        $tenant = app('tenant');
        
        // For now, return demo stats. Later: query tenant-scoped data.
        return Inertia::render('Tenant/Admin/Dashboard', [
            'tenant' => [
                'id' => $tenant->id ?? 'unknown',
                'name' => $tenant->name ?? 'Store',
            ],
            'stats' => [
                'totalProducts' => 0,
                'totalOrders' => 0,
                'totalRevenue' => 0,
                'pendingOrders' => 0,
            ],
        ]);
    }
}
