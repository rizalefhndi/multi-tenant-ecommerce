<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Laptop Gaming ASUS ROG',
                'description' => 'Laptop gaming dengan performa tinggi, prosesor Intel Core i7 Gen 12, RAM 16GB, SSD 512GB, GPU RTX 3060',
                'price' => 18500000,
                'stock' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'iPhone 15 Pro Max',
                'description' => 'Smartphone flagship Apple dengan chipset A17 Pro, kamera 48MP, layar 6.7 inch Super Retina XDR',
                'price' => 21999000,
                'stock' => 25,
                'is_active' => true,
            ],
            [
                'name' => 'Samsung Galaxy S24 Ultra',
                'description' => 'Smartphone Android flagship dengan S Pen, kamera 200MP, Snapdragon 8 Gen 3, RAM 12GB',
                'price' => 19999000,
                'stock' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'MacBook Air M3',
                'description' => 'Laptop tipis dan ringan dengan chip M3, RAM 8GB, SSD 256GB, layar Retina 13.6 inch',
                'price' => 16999000,
                'stock' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Sony WH-1000XM5',
                'description' => 'Headphone wireless dengan Active Noise Cancellation terbaik, kualitas audio premium',
                'price' => 5499000,
                'stock' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'iPad Pro 12.9 inch',
                'description' => 'Tablet profesional dengan chip M2, layar Liquid Retina XDR, support Apple Pencil Gen 2',
                'price' => 17999000,
                'stock' => 12,
                'is_active' => true,
            ],
            [
                'name' => 'Logitech MX Master 3S',
                'description' => 'Mouse wireless premium untuk produktivitas, sensor 8K DPI, baterai tahan hingga 70 hari',
                'price' => 1599000,
                'stock' => 50,
                'is_active' => true,
            ],
            [
                'name' => 'Keychron K8 Pro',
                'description' => 'Mechanical keyboard wireless dengan hot-swappable switch, RGB backlight, support Mac & Windows',
                'price' => 2199000,
                'stock' => 35,
                'is_active' => true,
            ],
            [
                'name' => 'LG UltraGear 27" Gaming Monitor',
                'description' => 'Monitor gaming 144Hz, response time 1ms, resolusi QHD 2560x1440, support G-Sync',
                'price' => 4999000,
                'stock' => 18,
                'is_active' => true,
            ],
            [
                'name' => 'Razer DeathAdder V3 Pro',
                'description' => 'Mouse gaming wireless dengan sensor Focus Pro 30K, baterai 90 jam, bobot 63 gram',
                'price' => 2499000,
                'stock' => 40,
                'is_active' => true,
            ],
            [
                'name' => 'Samsung 980 PRO 1TB NVMe SSD',
                'description' => 'SSD NVMe Gen 4 dengan kecepatan read 7000 MB/s, cocok untuk gaming dan content creation',
                'price' => 2299000,
                'stock' => 45,
                'is_active' => true,
            ],
            [
                'name' => 'Corsair Vengeance RGB 32GB DDR5',
                'description' => 'RAM DDR5 32GB (2x16GB) 6000MHz dengan RGB lighting, optimized untuk gaming',
                'price' => 3199000,
                'stock' => 28,
                'is_active' => true,
            ],
            [
                'name' => 'Canon EOS R6 Mark II',
                'description' => 'Kamera mirrorless full frame 24MP, IBIS 8-stop, video 4K 60fps, perfect untuk foto & video',
                'price' => 39999000,
                'stock' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'DJI Mini 3 Pro',
                'description' => 'Drone compact dengan kamera 4K, gimbal 3-axis, obstacle sensing, flight time 34 menit',
                'price' => 12999000,
                'stock' => 8,
                'is_active' => true,
            ],
            [
                'name' => 'PlayStation 5 Digital Edition',
                'description' => 'Console gaming next-gen dengan SSD ultra-cepat, ray tracing, support game 4K 120fps',
                'price' => 7499000,
                'stock' => 0, // Out of stock
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
