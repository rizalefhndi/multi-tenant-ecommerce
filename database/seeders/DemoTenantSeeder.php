<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class DemoTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Delete existing tenant database file if exists
        $dbPath = database_path('tenantdemo');
        if (File::exists($dbPath)) {
            File::delete($dbPath);
            $this->command->info('Deleted existing tenant database: ' . $dbPath);
        }

        // Delete existing tenant record if exists
        Tenant::find('demo')?->delete();

        // Create demo tenant
        $tenant = Tenant::create([
            'id' => 'demo',
        ]);

        // Create domain
        $tenant->domains()->create([
            'domain' => 'demo.localhost',
        ]);

        // Run tenant migrations
        $tenant->run(function () {
            Artisan::call('migrate', [
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);
        });

        // Create demo user in tenant database
        $tenant->run(function () {
            \App\Models\User::create([
                'name' => 'Demo Admin',
                'email' => 'admin@demo.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);

            \App\Models\User::create([
                'name' => 'Demo Customer',
                'email' => 'customer@demo.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);

            // Create sample products
            \App\Models\Product::create([
                'name' => 'Kaos Polos Hitam',
                'slug' => 'kaos-polos-hitam',
                'description' => 'Kaos polos berbahan cotton combed 30s, nyaman dipakai sehari-hari.',
                'price' => 75000,
                'stock' => 100,
                'weight' => 200,
                'is_active' => true,
            ]);

            \App\Models\Product::create([
                'name' => 'Celana Jeans Slim Fit',
                'slug' => 'celana-jeans-slim-fit',
                'description' => 'Celana jeans slim fit dengan bahan stretch yang nyaman.',
                'price' => 250000,
                'stock' => 50,
                'weight' => 500,
                'is_active' => true,
            ]);

            \App\Models\Product::create([
                'name' => 'Sepatu Sneakers Putih',
                'slug' => 'sepatu-sneakers-putih',
                'description' => 'Sepatu sneakers kasual cocok untuk aktivitas sehari-hari.',
                'price' => 350000,
                'stock' => 30,
                'weight' => 800,
                'is_active' => true,
            ]);
        });

        $this->command->info('Demo tenant created: demo.localhost');
        $this->command->info('Admin: admin@demo.com / password');
        $this->command->info('Customer: customer@demo.com / password');
    }
}
