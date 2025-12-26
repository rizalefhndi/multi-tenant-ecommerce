<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'slug' => Plan::PLAN_FREE,
                'name' => 'Free',
                'description' => 'Sempurna untuk memulai bisnis online Anda',
                'price_monthly' => 0,
                'price_yearly' => 0,
                'currency' => 'IDR',
                'max_products' => 10,
                'max_orders_per_month' => 50,
                'max_storage_mb' => 100, // 100 MB
                'max_users' => 1,
                'features' => [
                    'Hingga 10 produk',
                    '50 pesanan per bulan',
                    '100 MB penyimpanan',
                    'Subdomain gratis',
                    'Tema dasar',
                    'Dukungan email',
                ],
                'is_active' => true,
                'is_featured' => false,
                'is_custom' => false,
                'sort_order' => 1,
            ],
            [
                'slug' => Plan::PLAN_BASIC,
                'name' => 'Basic',
                'description' => 'Ideal untuk toko yang sedang berkembang',
                'price_monthly' => 99000,
                'price_yearly' => 990000, // 2 bulan gratis
                'currency' => 'IDR',
                'max_products' => 100,
                'max_orders_per_month' => 500,
                'max_storage_mb' => 1024, // 1 GB
                'max_users' => 3,
                'features' => [
                    'Hingga 100 produk',
                    '500 pesanan per bulan',
                    '1 GB penyimpanan',
                    'Custom domain',
                    'Tema premium',
                    'Laporan penjualan',
                    'Integrasi WhatsApp',
                    'Dukungan chat',
                ],
                'is_active' => true,
                'is_featured' => false,
                'is_custom' => false,
                'sort_order' => 2,
            ],
            [
                'slug' => Plan::PLAN_PRO,
                'name' => 'Pro',
                'description' => 'Untuk bisnis yang serius dengan pertumbuhan tinggi',
                'price_monthly' => 249000,
                'price_yearly' => 2490000, // 2 bulan gratis
                'currency' => 'IDR',
                'max_products' => 1000,
                'max_orders_per_month' => 5000,
                'max_storage_mb' => 10240, // 10 GB
                'max_users' => 10,
                'features' => [
                    'Hingga 1.000 produk',
                    '5.000 pesanan per bulan',
                    '10 GB penyimpanan',
                    'Custom domain',
                    'Semua tema premium',
                    'Laporan analytics lengkap',
                    'Integrasi Midtrans',
                    'Email marketing',
                    'Priority support',
                    'Multi admin',
                    'API access',
                ],
                'is_active' => true,
                'is_featured' => true, // Highlight di pricing
                'is_custom' => false,
                'sort_order' => 3,
            ],
            [
                'slug' => Plan::PLAN_ENTERPRISE,
                'name' => 'Enterprise',
                'description' => 'Solusi lengkap untuk perusahaan besar',
                'price_monthly' => 999000,
                'price_yearly' => 9990000, // 2 bulan gratis
                'currency' => 'IDR',
                'max_products' => 0, // Unlimited
                'max_orders_per_month' => 0, // Unlimited
                'max_storage_mb' => 0, // Unlimited
                'max_users' => 0, // Unlimited
                'features' => [
                    'Produk unlimited',
                    'Pesanan unlimited',
                    'Storage unlimited',
                    'Custom domain',
                    'White-label branding',
                    'Dedicated server',
                    'Custom integration',
                    'SLA 99.9%',
                    'Dedicated account manager',
                    '24/7 phone support',
                    'Training & onboarding',
                    'Custom development',
                ],
                'is_active' => true,
                'is_featured' => false,
                'is_custom' => true, // Custom plan
                'sort_order' => 4,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }

        $this->command->info('Created ' . count($plans) . ' subscription plans.');
    }
}
