<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TenantUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin user
        User::updateOrCreate(
            ['email' => 'admin@store.local'],
            [
                'name' => 'Admin Store',
                'password' => bcrypt('password'),
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ]
        );

        // Create Customer user
        User::updateOrCreate(
            ['email' => 'customer@store.local'],
            [
                'name' => 'Customer Test',
                'password' => bcrypt('password'),
                'role' => User::ROLE_CUSTOMER,
                'email_verified_at' => now(),
            ]
        );
    }
}
