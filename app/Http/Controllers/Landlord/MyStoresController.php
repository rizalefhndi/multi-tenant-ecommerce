<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Inertia\Inertia;
use Inertia\Response;

class MyStoresController extends Controller
{
    /**
     * Display user's stores
     */
    public function index(): Response
    {
        $user = auth()->user();
        
        // Get all stores owned by the user
        $stores = Tenant::where('owner_id', $user->id)
            ->with(['plan', 'domains'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($tenant) {
                $domain = $tenant->domains->first();
                return [
                    'id' => $tenant->id,
                    'store_name' => $tenant->store_name,
                    'subdomain' => $tenant->id,
                    'domain' => $domain ? $domain->domain : null,
                    'full_url' => $domain ? 'http://' . $domain->domain . ':8000' : null,
                    'plan' => $tenant->plan ? [
                        'name' => $tenant->plan->name,
                        'slug' => $tenant->plan->slug,
                    ] : null,
                    'status' => $tenant->status,
                    'subscription_status' => $tenant->subscription_status,
                    'created_at' => $tenant->created_at->format('M d, Y'),
                ];
            });

        return Inertia::render('Landlord/MyStores', [
            'stores' => $stores,
            'auth' => [
                'user' => $user,
            ],
        ]);
    }
}
