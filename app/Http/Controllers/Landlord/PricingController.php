<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Inertia\Inertia;
use Inertia\Response;

class PricingController extends Controller
{
    /**
     * Display the pricing page (public)
     */
    public function index(): Response
    {
        $plans = Plan::active()
            ->ordered()
            ->get()
            ->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'slug' => $plan->slug,
                    'name' => $plan->name,
                    'description' => $plan->description,
                    'price_monthly' => $plan->price_monthly,
                    'price_yearly' => $plan->price_yearly,
                    'formatted_price_monthly' => $plan->formatted_price_monthly,
                    'formatted_price_yearly' => $plan->formatted_price_yearly,
                    'yearly_savings_percent' => $plan->yearly_savings_percent,
                    'currency' => $plan->currency,
                    'max_products' => $plan->max_products,
                    'max_orders_per_month' => $plan->max_orders_per_month,
                    'max_storage_mb' => $plan->max_storage_mb,
                    'max_users' => $plan->max_users,
                    'max_products_display' => $plan->max_products_display,
                    'max_orders_display' => $plan->max_orders_display,
                    'max_storage_display' => $plan->max_storage_display,
                    'features' => $plan->features,
                    'is_featured' => $plan->is_featured,
                    'is_custom' => $plan->is_custom,
                    'is_free' => $plan->isFree(),
                ];
            });

        return Inertia::render('Landlord/Pricing', [
            'plans' => $plans,
            'auth' => [
                'user' => auth()->user() ? [
                    'name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                ] : null,
            ],
        ]);
    }
}
