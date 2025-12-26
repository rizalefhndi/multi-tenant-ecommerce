<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CheckoutControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected UserAddress $address;
    protected Product $product;
    protected Cart $cart;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['id' => 'checkout_test']);
        $this->tenant->domains()->create(['domain' => 'checkouttest.localhost']);
    }

    protected function initializeTenant(): void
    {
        $this->tenant->run(function () {
            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);

            $this->user = User::create([
                'name' => 'Test Customer',
                'email' => 'customer@test.com',
                'password' => bcrypt('password'),
            ]);

            $this->address = UserAddress::create([
                'user_id' => $this->user->id,
                'label' => 'Home',
                'recipient_name' => 'John Doe',
                'phone' => '08123456789',
                'address_line_1' => 'Jl. Test No. 123',
                'city' => 'Jakarta',
                'province' => 'DKI Jakarta',
                'postal_code' => '12345',
                'is_default' => true,
            ]);

            $this->product = Product::create([
                'name' => 'Test Product',
                'description' => 'Test description',
                'price' => 100000,
                'stock' => 50,
                'weight' => 500,
                'is_active' => true,
            ]);

            $this->cart = Cart::create([
                'user_id' => $this->user->id,
            ]);

            CartItem::create([
                'cart_id' => $this->cart->id,
                'product_id' => $this->product->id,
                'quantity' => 2,
                'price' => $this->product->price,
            ]);
        });
    }

    /** @test */
    public function test_checkout_page_requires_authentication()
    {
        $this->tenant->run(function () {
            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);
        });

        $response = $this->get('http://checkouttest.localhost/checkout');
        $response->assertRedirect();
    }

    /** @test */
    public function test_checkout_page_redirects_if_cart_empty()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            // Clear cart first
            $this->cart->items()->delete();

            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'checkouttest.localhost')
                ->get('/checkout');

            $response->assertRedirect(route('cart.index'));
        });
    }

    /** @test */
    public function test_checkout_page_shows_cart_items_and_addresses()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'checkouttest.localhost')
                ->get('/checkout');

            $response->assertStatus(200);
            $response->assertInertia(fn ($page) =>
                $page->component('Checkout/Index')
                    ->has('cart')
                    ->has('cartItems')
                    ->has('addresses')
                    ->has('paymentMethods')
            );
        });
    }

    /** @test */
    public function test_checkout_process_creates_order()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            $initialStock = $this->product->stock;

            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'checkouttest.localhost')
                ->post('/checkout', [
                    'address_id' => $this->address->id,
                    'payment_method' => 'bank_transfer',
                    'shipping_cost' => 0,
                    'customer_notes' => 'Please handle with care',
                ]);

            $response->assertRedirect();

            // Verify order created
            $this->assertDatabaseHas('orders', [
                'user_id' => $this->user->id,
                'address_id' => $this->address->id,
                'payment_method' => 'bank_transfer',
            ]);

            // Verify stock decreased
            $this->product->refresh();
            $this->assertEquals($initialStock - 2, $this->product->stock);

            // Verify cart cleared
            $this->assertCount(0, $this->cart->fresh()->items);
        });
    }

    /** @test */
    public function test_checkout_fails_with_insufficient_stock()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            // Set low stock
            $this->product->update(['stock' => 1]);

            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'checkouttest.localhost')
                ->post('/checkout', [
                    'address_id' => $this->address->id,
                    'payment_method' => 'bank_transfer',
                ]);

            $response->assertSessionHas('error');
        });
    }

    /** @test */
    public function test_checkout_requires_valid_address()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'checkouttest.localhost')
                ->post('/checkout', [
                    'address_id' => 99999, // Non-existent
                    'payment_method' => 'bank_transfer',
                ]);

            $response->assertSessionHasErrors(['address_id']);
        });
    }

    /** @test */
    public function test_checkout_requires_valid_payment_method()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'checkouttest.localhost')
                ->post('/checkout', [
                    'address_id' => $this->address->id,
                    'payment_method' => 'invalid_method',
                ]);

            $response->assertSessionHasErrors(['payment_method']);
        });
    }

    /** @test */
    public function test_checkout_success_page_shows_order_details()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            // Create order first
            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'checkouttest.localhost')
                ->post('/checkout', [
                    'address_id' => $this->address->id,
                    'payment_method' => 'bank_transfer',
                ]);

            // Get the order
            $order = $this->user->orders()->latest()->first();

            $successResponse = $this->actingAs($this->user)
                ->withHeader('Host', 'checkouttest.localhost')
                ->get('/checkout/success/' . $order->id);

            $successResponse->assertStatus(200);
            $successResponse->assertInertia(fn ($page) =>
                $page->component('Checkout/Success')
                    ->has('order')
                    ->has('items')
                    ->has('transaction')
            );
        });
    }

    /** @test */
    public function test_checkout_success_page_requires_ownership()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            // Create another user
            $anotherUser = User::create([
                'name' => 'Another User',
                'email' => 'another@test.com',
                'password' => bcrypt('password'),
            ]);

            // Create order for first user
            $this->actingAs($this->user)
                ->withHeader('Host', 'checkouttest.localhost')
                ->post('/checkout', [
                    'address_id' => $this->address->id,
                    'payment_method' => 'bank_transfer',
                ]);

            $order = $this->user->orders()->latest()->first();

            // Try to access with another user
            $response = $this->actingAs($anotherUser)
                ->withHeader('Host', 'checkouttest.localhost')
                ->get('/checkout/success/' . $order->id);

            $response->assertStatus(403);
        });
    }
}
