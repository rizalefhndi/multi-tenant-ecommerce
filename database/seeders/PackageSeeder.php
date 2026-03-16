<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packages = [
            [
                'name' => 'Basic',
                'description' => 'Paket dasar untuk memulai toko online.',
                'price' => 99000,
                'duration_in_days' => 30,
            ],
            [
                'name' => 'Pro',
                'description' => 'Paket populer untuk bisnis yang sedang tumbuh.',
                'price' => 199000,
                'duration_in_days' => 30,
            ],
            [
                'name' => 'Enterprise',
                'description' => 'Paket lanjutan untuk kebutuhan skala besar.',
                'price' => 499000,
                'duration_in_days' => 30,
            ],
        ];

        foreach ($packages as $package) {
            Package::updateOrCreate(
                ['name' => $package['name']],
                $package
            );
        }
    }
}
