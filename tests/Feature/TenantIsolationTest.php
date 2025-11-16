<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_tenant_databases_are_isolated_products()
    {
        // Create tenant 1
        $tenant1 = Tenant::create(['id' => 'shop1']);
        $tenant1->domains()->create(['domain' => 'shop1.localhost']);
        $tenant1->run(function () {
            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);

            Product::create([
                'name' => 'Shop 1 Laptop',
                'description' => 'Laptop from shop 1',
                'price' => 5000000,
                'stock' => 10,
                'is_active' => true,
            ]);
        });

        // Create tenant 2
        $tenant2 = Tenant::create(['id' => 'shop2']);
        $tenant2->domains()->create(['domain' => 'shop2.localhost']);
        $tenant2->run(function () {
            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);

            Product::create([
                'name' => 'Shop 2 Phone',
                'description' => 'Phone from shop 2',
                'price' => 3000000,
                'stock' => 20,
                'is_active' => true,
            ]);
        });

        // Verify tenant 1 can only see their products
        $tenant1->run(function () {
            $products = Product::all();
            $this->assertCount(1, $products);
            $this->assertEquals('Shop 1 Laptop', $products->first()->name);
            $this->assertEquals(5000000, $products->first()->price);
        });

        // Verify tenant 2 can only see their products
        $tenant2->run(function () {
            $products = Product::all();
            $this->assertCount(1, $products);
            $this->assertEquals('Shop 2 Phone', $products->first()->name);
            $this->assertEquals(3000000, $products->first()->price);
        });
    }

    /** @test */
    public function test_tenant_databases_are_isolated_users()
    {
        // Create tenant 1
        $tenant1 = Tenant::create(['id' => 'company1']);
        $tenant1->domains()->create(['domain' => 'company1.localhost']);
        $tenant1->run(function () {
            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);

            User::create([
                'name' => 'John from Company 1',
                'email' => 'john@company1.com',
                'password' => bcrypt('password'),
            ]);
        });

        // Create tenant 2
        $tenant2 = Tenant::create(['id' => 'company2']);
        $tenant2->domains()->create(['domain' => 'company2.localhost']);
        $tenant2->run(function () {
            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);

            User::create([
                'name' => 'Jane from Company 2',
                'email' => 'jane@company2.com',
                'password' => bcrypt('password'),
            ]);
        });

        // Verify tenant 1 isolation
        $tenant1->run(function () {
            $users = User::all();
            $this->assertCount(1, $users);
            $this->assertEquals('John from Company 1', $users->first()->name);
            $this->assertEquals('john@company1.com', $users->first()->email);
        });

        // Verify tenant 2 isolation
        $tenant2->run(function () {
            $users = User::all();
            $this->assertCount(1, $users);
            $this->assertEquals('Jane from Company 2', $users->first()->name);
            $this->assertEquals('jane@company2.com', $users->first()->email);
        });
    }

    /** @test */
    public function test_multiple_tenants_can_have_same_user_email_in_different_databases()
    {
        $email = 'admin@example.com';

        // Create tenant 1 with user
        $tenant1 = Tenant::create(['id' => 'store1']);
        $tenant1->domains()->create(['domain' => 'store1.localhost']);
        $tenant1->run(function () use ($email) {
            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);

            User::create([
                'name' => 'Admin Store 1',
                'email' => $email,
                'password' => bcrypt('password1'),
            ]);
        });

        // Create tenant 2 with same email (should work - different database)
        $tenant2 = Tenant::create(['id' => 'store2']);
        $tenant2->domains()->create(['domain' => 'store2.localhost']);
        $tenant2->run(function () use ($email) {
            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);

            User::create([
                'name' => 'Admin Store 2',
                'email' => $email,
                'password' => bcrypt('password2'),
            ]);
        });

        // Verify both tenants have their own user with same email
        $tenant1->run(function () use ($email) {
            $user = User::where('email', $email)->first();
            $this->assertNotNull($user);
            $this->assertEquals('Admin Store 1', $user->name);
        });

        $tenant2->run(function () use ($email) {
            $user = User::where('email', $email)->first();
            $this->assertNotNull($user);
            $this->assertEquals('Admin Store 2', $user->name);
        });
    }

    /** @test */
    public function test_tenant_product_ids_do_not_conflict()
    {
        // Create tenant 1
        $tenant1 = Tenant::create(['id' => 'merchant1']);
        $tenant1->domains()->create(['domain' => 'merchant1.localhost']);

        $product1Id = null;
        $tenant1->run(function () use (&$product1Id) {
            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);

            $product = Product::create([
                'name' => 'Product A',
                'price' => 100000,
                'stock' => 5,
                'is_active' => true,
            ]);
            $product1Id = $product->id;
        });

        // Create tenant 2
        $tenant2 = Tenant::create(['id' => 'merchant2']);
        $tenant2->domains()->create(['domain' => 'merchant2.localhost']);

        $product2Id = null;
        $tenant2->run(function () use (&$product2Id) {
            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);

            $product = Product::create([
                'name' => 'Product B',
                'price' => 200000,
                'stock' => 10,
                'is_active' => true,
            ]);
            $product2Id = $product->id;
        });

        // IDs can be the same (different databases) - verify isolation
        $tenant1->run(function () use ($product1Id) {
            $product = Product::find($product1Id);
            $this->assertNotNull($product);
            $this->assertEquals('Product A', $product->name);
        });

        $tenant2->run(function () use ($product2Id) {
            $product = Product::find($product2Id);
            $this->assertNotNull($product);
            $this->assertEquals('Product B', $product->name);
        });
    }

    /** @test */
    public function test_deleting_tenant_data_does_not_affect_other_tenants()
    {
        // Create tenant 1
        $tenant1 = Tenant::create(['id' => 'shop_a']);
        $tenant1->domains()->create(['domain' => 'shopa.localhost']);
        $tenant1->run(function () {
            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);

            Product::create(['name' => 'Product 1', 'price' => 100000, 'stock' => 10, 'is_active' => true]);
            Product::create(['name' => 'Product 2', 'price' => 200000, 'stock' => 20, 'is_active' => true]);
        });

        // Create tenant 2
        $tenant2 = Tenant::create(['id' => 'shop_b']);
        $tenant2->domains()->create(['domain' => 'shopb.localhost']);
        $tenant2->run(function () {
            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);

            Product::create(['name' => 'Product X', 'price' => 300000, 'stock' => 15, 'is_active' => true]);
            Product::create(['name' => 'Product Y', 'price' => 400000, 'stock' => 25, 'is_active' => true]);
        });

        // Delete all products in tenant 1
        $tenant1->run(function () {
            Product::query()->delete();
            $this->assertCount(0, Product::all());
        });

        // Verify tenant 2 still has products
        $tenant2->run(function () {
            $products = Product::all();
            $this->assertCount(2, $products);
            $this->assertTrue($products->pluck('name')->contains('Product X'));
            $this->assertTrue($products->pluck('name')->contains('Product Y'));
        });
    }

    /** @test */
    public function test_central_database_remains_separate_from_tenant_data()
    {
        // Create tenants in central database
        $tenant1 = Tenant::create(['id' => 'central_test_1']);
        $tenant1->domains()->create(['domain' => 'central1.localhost']);

        $tenant2 = Tenant::create(['id' => 'central_test_2']);
        $tenant2->domains()->create(['domain' => 'central2.localhost']);

        // Verify tenants exist in central database
        $this->assertDatabaseHas('tenants', ['id' => 'central_test_1']);
        $this->assertDatabaseHas('tenants', ['id' => 'central_test_2']);
        $this->assertDatabaseHas('domains', ['domain' => 'central1.localhost']);
        $this->assertDatabaseHas('domains', ['domain' => 'central2.localhost']);

        // Add products to tenant databases
        $tenant1->run(function () {
            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);
            Product::create(['name' => 'Tenant 1 Product', 'price' => 100000, 'stock' => 10, 'is_active' => true]);
        });

        // Verify central database doesn't have tenant products table data mixed
        $this->assertEquals(2, Tenant::count());

        // Tenant 1 should have 1 product in its own database
        $tenant1->run(function () {
            $this->assertCount(1, Product::all());
        });
    }

    /** @test */
    public function test_tenant_switch_maintains_data_integrity()
    {
        // Create tenant 1
        $tenant1 = Tenant::create(['id' => 'switch_test_1']);
        $tenant1->domains()->create(['domain' => 'switch1.localhost']);
        $tenant1->run(function () {
            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);
            Product::create(['name' => 'Switch Product 1', 'price' => 111000, 'stock' => 11, 'is_active' => true]);
        });

        // Create tenant 2
        $tenant2 = Tenant::create(['id' => 'switch_test_2']);
        $tenant2->domains()->create(['domain' => 'switch2.localhost']);
        $tenant2->run(function () {
            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);
            Product::create(['name' => 'Switch Product 2', 'price' => 222000, 'stock' => 22, 'is_active' => true]);
        });

        // Switch between tenants multiple times
        for ($i = 0; $i < 3; $i++) {
            $tenant1->run(function () {
                $product = Product::first();
                $this->assertEquals('Switch Product 1', $product->name);
                $this->assertEquals(111000, $product->price);
            });

            $tenant2->run(function () {
                $product = Product::first();
                $this->assertEquals('Switch Product 2', $product->name);
                $this->assertEquals(222000, $product->price);
            });
        }
    }

}
