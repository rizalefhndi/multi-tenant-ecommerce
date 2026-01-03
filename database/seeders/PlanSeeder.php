<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Plan;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfect for side hustles and new drops.',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'features' => [
                    '1 Storefront',
                    'Up to 10 Products',
                    'Basic Analytics',
                    'Community Support'
                ],
                'max_products' => 10,
                'max_orders_per_month' => 50,
                'is_featured' => false,
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'description' => 'For brands starting to make noise.',
                'price_monthly' => 29,
                'price_yearly' => 290,
                'features' => [
                    '3 Storefronts',
                    'Up to 100 Products',
                    'Advanced Analytics',
                    'Priority Email Support',
                    'Custom Domain'
                ],
                'max_products' => 100,
                'max_orders_per_month' => 500,
                'is_featured' => true,
            ],
            [
                'name' => 'Empire',
                'slug' => 'empire',
                'description' => 'Dominate the market without limits.',
                'price_monthly' => 79,
                'price_yearly' => 790,
                'features' => [
                    'Unlimited Storefronts',
                    'Unlimited Products',
                    'Real-time Analytics',
                    '24/7 Dedicated Support',
                    'Custom Domain + SSL',
                    'White Labeling'
                ],
                'max_products' => 999999,
                'max_orders_per_month' => 999999,
                'is_featured' => false,
            ]
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
