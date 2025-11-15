<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class TenantUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Store',
            'email' => 'admin@store.local',
            'password' => bcrypt('password'),
        ]);

        User::create([
            'name' => 'Customer Test',
            'email' => 'customer@store.local',
            'password' => bcrypt('password'),
        ]);
    }
}
