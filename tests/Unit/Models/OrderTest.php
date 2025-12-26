<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected UserAddress $address;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant and migrate
        $this->tenant = Tenant::create(['id' => 'order_test']);
        $this->tenant->domains()->create(['domain' => 'ordertest.localhost']);

        $this->tenant->run(function () {
            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);

            // Create test user
            $this->user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password'),
            ]);

            // Create test address
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

            // Create test product
            $this->product = Product::create([
                'name' => 'Test Product',
                'description' => 'Test description',
                'price' => 100000,
                'stock' => 50,
                'weight' => 500,
                'is_active' => true,
            ]);
        });
    }

    /** @test */
    public function test_order_can_be_created_with_required_fields()
    {
        $this->tenant->run(function () {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $this->user->id,
                'address_id' => $this->address->id,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'subtotal' => 100000,
                'shipping_cost' => 10000,
                'discount' => 0,
                'tax' => 0,
                'total' => 110000,
                'payment_method' => 'bank_transfer',
                'shipping_address_snapshot' => $this->address->toSnapshot(),
            ]);

            $this->assertNotNull($order->id);
            $this->assertEquals($this->user->id, $order->user_id);
            $this->assertEquals(110000, $order->total);
        });
    }

    /** @test */
    public function test_order_number_generation_is_unique()
    {
        $this->tenant->run(function () {
            $numbers = [];
            for ($i = 0; $i < 10; $i++) {
                $number = Order::generateOrderNumber();
                $this->assertNotContains($number, $numbers);
                $numbers[] = $number;
            }
        });
    }

    /** @test */
    public function test_order_status_transitions_work_correctly()
    {
        $this->tenant->run(function () {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $this->user->id,
                'address_id' => $this->address->id,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'subtotal' => 100000,
                'shipping_cost' => 0,
                'total' => 100000,
                'payment_method' => 'bank_transfer',
                'shipping_address_snapshot' => $this->address->toSnapshot(),
            ]);

            // Can transition to payment_received
            $this->assertTrue($order->canTransitionTo(Order::STATUS_PAYMENT_RECEIVED));

            // Cannot skip to shipped directly
            $this->assertFalse($order->canTransitionTo(Order::STATUS_SHIPPED));

            // Can transition to cancelled
            $this->assertTrue($order->canTransitionTo(Order::STATUS_CANCELLED));
        });
    }

    /** @test */
    public function test_order_can_be_marked_as_paid()
    {
        $this->tenant->run(function () {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $this->user->id,
                'address_id' => $this->address->id,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'subtotal' => 100000,
                'shipping_cost' => 0,
                'total' => 100000,
                'payment_method' => 'bank_transfer',
                'shipping_address_snapshot' => $this->address->toSnapshot(),
            ]);

            $this->assertNull($order->paid_at);
            $this->assertFalse($order->isPaid());

            $order->markAsPaid();

            $this->assertEquals(Order::STATUS_PAYMENT_RECEIVED, $order->status);
            $this->assertNotNull($order->paid_at);
            $this->assertTrue($order->isPaid());
        });
    }

    /** @test */
    public function test_order_cancellation_restores_stock()
    {
        $this->tenant->run(function () {
            $initialStock = $this->product->stock;

            // Create order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $this->user->id,
                'address_id' => $this->address->id,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'subtotal' => 200000,
                'shipping_cost' => 0,
                'total' => 200000,
                'payment_method' => 'bank_transfer',
                'shipping_address_snapshot' => $this->address->toSnapshot(),
            ]);

            // Add item and decrease stock
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'price' => $this->product->price,
                'quantity' => 2,
                'subtotal' => 200000,
            ]);

            $this->product->decreaseStock(2);
            $this->product->refresh();
            $this->assertEquals($initialStock - 2, $this->product->stock);

            // Cancel order
            $order->cancel();

            // Verify stock restored
            $this->product->refresh();
            $this->assertEquals($initialStock, $this->product->stock);
            $this->assertEquals(Order::STATUS_CANCELLED, $order->status);
        });
    }

    /** @test */
    public function test_order_has_correct_relationships()
    {
        $this->tenant->run(function () {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $this->user->id,
                'address_id' => $this->address->id,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'subtotal' => 100000,
                'shipping_cost' => 0,
                'total' => 100000,
                'payment_method' => 'bank_transfer',
                'shipping_address_snapshot' => $this->address->toSnapshot(),
            ]);

            // Create order item
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'price' => $this->product->price,
                'quantity' => 1,
                'subtotal' => 100000,
            ]);

            // Create transaction
            $transaction = Transaction::createPayment($order, 'bank_transfer');

            $order->refresh();
            $order->load(['user', 'address', 'items', 'transactions']);

            // Test relationships
            $this->assertEquals($this->user->id, $order->user->id);
            $this->assertEquals($this->address->id, $order->address->id);
            $this->assertCount(1, $order->items);
            $this->assertCount(1, $order->transactions);
        });
    }

    /** @test */
    public function test_order_accessors_return_formatted_values()
    {
        $this->tenant->run(function () {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $this->user->id,
                'address_id' => $this->address->id,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'subtotal' => 1500000,
                'shipping_cost' => 25000,
                'discount' => 0,
                'tax' => 0,
                'total' => 1525000,
                'payment_method' => 'bank_transfer',
                'shipping_address_snapshot' => $this->address->toSnapshot(),
            ]);

            $this->assertStringContainsString('Rp', $order->formatted_total);
            $this->assertStringContainsString('1.525.000', $order->formatted_total);
            $this->assertEquals('Menunggu Pembayaran', $order->status_label);
        });
    }

    /** @test */
    public function test_order_scopes_filter_correctly()
    {
        $this->tenant->run(function () {
            // Create orders with different statuses
            $orderPending = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $this->user->id,
                'address_id' => $this->address->id,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'subtotal' => 100000,
                'total' => 100000,
                'payment_method' => 'bank_transfer',
                'shipping_address_snapshot' => $this->address->toSnapshot(),
            ]);

            $orderPaid = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $this->user->id,
                'address_id' => $this->address->id,
                'status' => Order::STATUS_PAYMENT_RECEIVED,
                'subtotal' => 200000,
                'total' => 200000,
                'payment_method' => 'bank_transfer',
                'paid_at' => now(),
                'shipping_address_snapshot' => $this->address->toSnapshot(),
            ]);

            $orderCancelled = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $this->user->id,
                'address_id' => $this->address->id,
                'status' => Order::STATUS_CANCELLED,
                'subtotal' => 300000,
                'total' => 300000,
                'payment_method' => 'bank_transfer',
                'shipping_address_snapshot' => $this->address->toSnapshot(),
            ]);

            // Test scopes
            $this->assertCount(1, Order::pending()->get());
            $this->assertCount(1, Order::paid()->get());
            $this->assertCount(1, Order::needsProcessing()->get());
        });
    }

    /** @test */
    public function test_order_total_items_calculated_correctly()
    {
        $this->tenant->run(function () {
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $this->user->id,
                'address_id' => $this->address->id,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'subtotal' => 500000,
                'total' => 500000,
                'payment_method' => 'bank_transfer',
                'shipping_address_snapshot' => $this->address->toSnapshot(),
            ]);

            // Add multiple items
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $this->product->id,
                'product_name' => 'Product A',
                'price' => 100000,
                'quantity' => 2,
                'subtotal' => 200000,
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $this->product->id,
                'product_name' => 'Product B',
                'price' => 100000,
                'quantity' => 3,
                'subtotal' => 300000,
            ]);

            $order->refresh();

            $this->assertEquals(5, $order->total_items);
        });
    }
}
