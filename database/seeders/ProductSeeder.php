<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed sample products with images
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Kaos Polos Hitam',
                'description' => 'Kaos polos berbahan cotton combed 30s, nyaman dipakai sehari-hari.',
                'price' => 75000,
                'stock' => 100,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=400&h=400&fit=crop',
            ],
            [
                'name' => 'Celana Jeans Slim Fit',
                'description' => 'Celana jeans slim fit dengan bahan stretch yang nyaman.',
                'price' => 250000,
                'stock' => 50,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1542272604-787c3835535d?w=400&h=400&fit=crop',
            ],
            [
                'name' => 'Sepatu Sneakers Putih',
                'description' => 'Sepatu sneakers kasual cocok untuk aktivitas sehari-hari.',
                'price' => 350000,
                'stock' => 30,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&h=400&fit=crop',
            ],
            [
                'name' => 'Tas Ransel Laptop',
                'description' => 'Tas ransel untuk laptop 15 inch dengan banyak kompartemen.',
                'price' => 199000,
                'stock' => 25,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=400&h=400&fit=crop',
            ],
            [
                'name' => 'Jam Tangan Digital',
                'description' => 'Jam tangan digital tahan air dengan banyak fitur.',
                'price' => 450000,
                'stock' => 15,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&h=400&fit=crop',
            ],
            [
                'name' => 'Kemeja Formal Biru',
                'description' => 'Kemeja formal bahan katun premium.',
                'price' => 185000,
                'stock' => 40,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=400&h=400&fit=crop',
            ],
            [
                'name' => 'Topi Baseball Cap',
                'description' => 'Topi baseball cap dengan desain sporty.',
                'price' => 89000,
                'stock' => 60,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1588850561407-ed78c282e89b?w=400&h=400&fit=crop',
            ],
            [
                'name' => 'Dompet Kulit Pria',
                'description' => 'Dompet kulit asli dengan banyak slot kartu.',
                'price' => 275000,
                'stock' => 20,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1627123424574-724758594e93?w=400&h=400&fit=crop',
            ],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(
                ['name' => $product['name']],
                $product
            );
        }
    }
}
