<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tenant;

class AddShop1DomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tambahkan domain untuk tenant shop1
        $tenant = Tenant::find('shop1');

        if ($tenant) {
            $tenant->domains()->create([
                'domain' => 'shop1.localhost'
            ]);

            $this->command->info('Domain shop1.localhost berhasil ditambahkan untuk tenant shop1');
        } else {
            $this->command->error('Tenant shop1 tidak ditemukan!');
        }
    }
}
