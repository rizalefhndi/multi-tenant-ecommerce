<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Seed tenant database with sample data
     */
    public function run(): void
    {
        // Create admin user (skip if exists)
        if (!User::where('email', 'admin@demo.com')->exists()) {
            User::create([
                'name' => 'Admin Demo',
                'email' => 'admin@demo.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        // Create customer user (skip if exists)
        if (!User::where('email', 'customer@demo.com')->exists()) {
            User::create([
                'name' => 'Customer Demo',
                'email' => 'customer@demo.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

    }
}
