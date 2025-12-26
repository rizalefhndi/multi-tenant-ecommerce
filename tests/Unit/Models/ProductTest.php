<?php

namespace Tests\Unit\Models;

use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['id' => 'product_test']);
        $this->tenant->domains()->create(['domain' => 'producttest.localhost']);

        $this->tenant->run(function () {
            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);
        });
    }

    /** @test */
    public function test_product_can_be_created_with_all_fields()
    {
        $this->tenant->run(function () {
            $product = Product::create([
                'sku' => 'TEST-001',
                'name' => 'Test Product',
                'description' => 'Test description',
                'price' => 150000,
                'stock' => 100,
                'weight' => 500,
                'length' => 20,
                'width' => 15,
                'height' => 10,
                'is_active' => true,
            ]);

            $this->assertNotNull($product->id);
            $this->assertEquals('TEST-001', $product->sku);
            $this->assertEquals(500, $product->weight);
        });
    }

    /** @test */
    public function test_product_formatted_price_accessor()
    {
        $this->tenant->run(function () {
            $product = Product::create([
                'name' => 'Test Product',
                'price' => 1500000,
                'stock' => 10,
                'is_active' => true,
            ]);

            $this->assertStringContainsString('Rp', $product->formatted_price);
            $this->assertStringContainsString('1.500.000', $product->formatted_price);
        });
    }

    /** @test */
    public function test_product_formatted_weight_accessor()
    {
        $this->tenant->run(function () {
            // Gram
            $product1 = Product::create([
                'name' => 'Light Product',
                'price' => 100000,
                'stock' => 10,
                'weight' => 500,
                'is_active' => true,
            ]);

            $this->assertEquals('500 g', $product1->formatted_weight);

            // Kilogram
            $product2 = Product::create([
                'name' => 'Heavy Product',
                'price' => 200000,
                'stock' => 5,
                'weight' => 2500,
                'is_active' => true,
            ]);

            $this->assertStringContainsString('kg', $product2->formatted_weight);
        });
    }

    /** @test */
    public function test_product_dimensions_accessor()
    {
        $this->tenant->run(function () {
            $product = Product::create([
                'name' => 'Box Product',
                'price' => 100000,
                'stock' => 10,
                'length' => 30,
                'width' => 20,
                'height' => 15,
                'is_active' => true,
            ]);

            $this->assertEquals('30 x 20 x 15 cm', $product->dimensions);

            // No dimensions
            $product2 = Product::create([
                'name' => 'No Dims Product',
                'price' => 100000,
                'stock' => 10,
                'is_active' => true,
            ]);

            $this->assertNull($product2->dimensions);
        });
    }

    /** @test */
    public function test_product_is_available_method()
    {
        $this->tenant->run(function () {
            // Available product
            $product1 = Product::create([
                'name' => 'Available',
                'price' => 100000,
                'stock' => 10,
                'is_active' => true,
            ]);

            $this->assertTrue($product1->isAvailable());

            // Out of stock
            $product2 = Product::create([
                'name' => 'Out of Stock',
                'price' => 100000,
                'stock' => 0,
                'is_active' => true,
            ]);

            $this->assertFalse($product2->isAvailable());

            // Inactive
            $product3 = Product::create([
                'name' => 'Inactive',
                'price' => 100000,
                'stock' => 10,
                'is_active' => false,
            ]);

            $this->assertFalse($product3->isAvailable());
        });
    }

    /** @test */
    public function test_product_has_stock_method()
    {
        $this->tenant->run(function () {
            $product = Product::create([
                'name' => 'Test',
                'price' => 100000,
                'stock' => 5,
                'is_active' => true,
            ]);

            $this->assertTrue($product->hasStock(1));
            $this->assertTrue($product->hasStock(5));
            $this->assertFalse($product->hasStock(6));
        });
    }

    /** @test */
    public function test_product_decrease_stock()
    {
        $this->tenant->run(function () {
            $product = Product::create([
                'name' => 'Test',
                'price' => 100000,
                'stock' => 10,
                'is_active' => true,
            ]);

            // Successful decrease
            $result = $product->decreaseStock(3);
            $this->assertTrue($result);
            $this->assertEquals(7, $product->fresh()->stock);

            // Failed decrease (not enough stock)
            $result = $product->decreaseStock(10);
            $this->assertFalse($result);
            $this->assertEquals(7, $product->fresh()->stock);
        });
    }

    /** @test */
    public function test_product_increase_stock()
    {
        $this->tenant->run(function () {
            $product = Product::create([
                'name' => 'Test',
                'price' => 100000,
                'stock' => 10,
                'is_active' => true,
            ]);

            $product->increaseStock(5);
            $this->assertEquals(15, $product->fresh()->stock);
        });
    }

    /** @test */
    public function test_product_generate_sku()
    {
        $this->tenant->run(function () {
            $product = Product::create([
                'name' => 'Test Product Name',
                'price' => 100000,
                'stock' => 10,
                'is_active' => true,
            ]);

            $sku = $product->generateSku();

            $this->assertNotEmpty($sku);
            $this->assertStringStartsWith('TES-', $sku);

            // If SKU already exists, return it
            $product->sku = 'EXISTING-SKU';
            $product->save();

            $this->assertEquals('EXISTING-SKU', $product->generateSku());
        });
    }

    /** @test */
    public function test_product_volumetric_weight()
    {
        $this->tenant->run(function () {
            $product = Product::create([
                'name' => 'Test',
                'price' => 100000,
                'stock' => 10,
                'weight' => 500, // 500g
                'length' => 30,
                'width' => 20,
                'height' => 15,
                'is_active' => true,
            ]);

            // Volumetric = (30 * 20 * 15) / 6000 = 1.5 kg = 1500g
            $volumetric = $product->getVolumetricWeight();

            // Should return higher of actual (500g) vs volumetric (1500g)
            $this->assertEquals(1500, $volumetric);

            // Product without dimensions
            $product2 = Product::create([
                'name' => 'No Dims',
                'price' => 100000,
                'stock' => 10,
                'weight' => 800,
                'is_active' => true,
            ]);

            $this->assertEquals(800, $product2->getVolumetricWeight());
        });
    }

    /** @test */
    public function test_product_to_snapshot()
    {
        $this->tenant->run(function () {
            $product = Product::create([
                'sku' => 'SNAP-001',
                'name' => 'Snapshot Test',
                'price' => 150000,
                'stock' => 10,
                'weight' => 300,
                'image' => 'products/test.jpg',
                'is_active' => true,
            ]);

            $snapshot = $product->toSnapshot();

            $this->assertArrayHasKey('id', $snapshot);
            $this->assertArrayHasKey('sku', $snapshot);
            $this->assertArrayHasKey('name', $snapshot);
            $this->assertArrayHasKey('price', $snapshot);
            $this->assertArrayHasKey('weight', $snapshot);
            $this->assertArrayHasKey('image', $snapshot);

            $this->assertEquals('SNAP-001', $snapshot['sku']);
            $this->assertEquals('Snapshot Test', $snapshot['name']);
            $this->assertEquals(150000, $snapshot['price']);
        });
    }

    /** @test */
    public function test_product_scopes()
    {
        $this->tenant->run(function () {
            Product::create(['name' => 'Active In Stock', 'price' => 100000, 'stock' => 10, 'is_active' => true]);
            Product::create(['name' => 'Active No Stock', 'price' => 100000, 'stock' => 0, 'is_active' => true]);
            Product::create(['name' => 'Inactive In Stock', 'price' => 100000, 'stock' => 10, 'is_active' => false]);

            $this->assertCount(2, Product::active()->get());
            $this->assertCount(2, Product::inStock()->get());
            $this->assertCount(1, Product::available()->get());
        });
    }
}
