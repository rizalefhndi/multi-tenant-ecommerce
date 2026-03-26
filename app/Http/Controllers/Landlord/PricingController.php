<?php

namespace App\Http\Controllers\Landlord;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class PricingController extends Controller
{
    /**
     * Display the pricing page (public)
     */
    public function index(): Response
    {
        $plans = Package::query()
            ->orderBy('price')
            ->get()
            ->map(function ($package, $index) {
                $price = (float) $package->price;
                return [
                    'id' => $package->id,
                    'slug' => Str::slug($package->name),
                    'name' => $package->name,
                    'description' => $package->description,
                    'price_monthly' => $price,
                    'price_yearly' => $price * 12,
                    'formatted_price_monthly' => 'Rp ' . number_format($price, 0, ',', '.'),
                    'formatted_price_yearly' => 'Rp ' . number_format($price * 12, 0, ',', '.'),
                    'yearly_savings_percent' => 0,
                    'currency' => 'IDR',
                    'max_products' => null,
                    'max_orders_per_month' => null,
                    'max_storage_mb' => null,
                    'max_users' => null,
                    'max_products_display' => 'Unlimited',
                    'max_orders_display' => 'Unlimited',
                    'max_storage_display' => 'Unlimited',
                    'features' => [
                        'Custom store subdomain',
                        'Fast onboarding',
                        'Midtrans payment integration',
                    ],
                    'is_featured' => $index === 1,
                    'is_custom' => false,
                    'is_free' => $price <= 0,
                ];
            });

        return Inertia::render('Landlord/Pricing', [
            'plans' => $plans,
            'auth' => [
                'user' => Auth::user() ? [
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                ] : null,
            ],
        ]);
    }
}
