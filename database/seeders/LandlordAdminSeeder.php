<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class LandlordAdminSeeder extends Seeder
{
    /**
     * Seed the landlord admin user.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@landlord.local',
            'password' => bcrypt('password'),
            'email_verified_at' => now(),
            'role' => 'superadmin',
        ]);

    }
}
