<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantLoginToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class StoreController extends Controller
{
    /**
     * Show the create store form
     */
    public function create(Request $request): Response
    {
        $planSlug = $request->query('plan', 'starter');
        $plan = Plan::where('slug', $planSlug)->first();
        
        // Fallback to free plan if not found
        if (!$plan) {
            $plan = Plan::where('price_monthly', 0)->first();
        }

        return Inertia::render('Landlord/CreateStore', [
            'plan' => $plan ? [
                'id' => $plan->id,
                'slug' => $plan->slug,
                'name' => $plan->name,
                'description' => $plan->description,
                'price_monthly' => $plan->price_monthly,
                'formatted_price_monthly' => $plan->formatted_price_monthly,
                'is_free' => $plan->isFree(),
            ] : null,
            'auth' => [
                'user' => auth()->user(),
            ],
        ]);
    }

    /**
     * Handle store creation
     */
    public function store(Request $request)
    {
        $request->validate([
            'store_name' => ['required', 'string', 'max:255'],
            'subdomain' => [
                'required',
                'string',
                'max:63',
                'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/',
                Rule::unique('tenants', 'id'), // Tenant ID = subdomain
            ],
            'plan_id' => ['required', 'exists:plans,id'],
        ], [
            'subdomain.regex' => 'Subdomain can only contain lowercase letters, numbers, and hyphens.',
            'subdomain.unique' => 'This subdomain is already taken.',
        ]);

        $user = auth()->user();
        $plan = Plan::findOrFail($request->plan_id);

        // Generate tenant ID (subdomain)
        $tenantId = Str::slug($request->subdomain);

        // Create the tenant
        $tenant = Tenant::create([
            'id' => $tenantId,
            'owner_id' => $user->id,
            'store_name' => $request->store_name,
            'plan_id' => $plan->id,
            'subscription_status' => $plan->isFree() ? 'active' : 'trial',
            'billing_cycle' => 'monthly',
            'trial_ends_at' => $plan->isFree() ? null : now()->addDays(14),
            'status' => 'active',
        ]);

        // Create domain for tenant
        $domain = $tenantId . '.localhost';
        $tenant->domains()->create([
            'domain' => $domain,
        ]);

        // Return success response with store data (for success modal)
        return back()->with('success', [
            'store_name' => $request->store_name,
            'subdomain' => $tenantId,
            'domain' => $domain,
            'full_url' => 'http://' . $domain . ':8000',
            'plan_name' => $plan->name,
        ]);
    }
}

