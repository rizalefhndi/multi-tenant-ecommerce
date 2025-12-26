<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected UserAddress $address;
    protected Product $product;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['id' => 'order_ctrl_test']);
        $this->tenant->domains()->create(['domain' => 'orderctrltest.localhost']);
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
                'price' => 100000,
                'stock' => 50,
                'is_active' => true,
            ]);

            $this->order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $this->user->id,
                'address_id' => $this->address->id,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'subtotal' => 200000,
                'shipping_cost' => 10000,
                'total' => 210000,
                'payment_method' => 'bank_transfer',
                'shipping_address_snapshot' => $this->address->toSnapshot(),
            ]);

            OrderItem::create([
                'order_id' => $this->order->id,
                'product_id' => $this->product->id,
                'product_name' => $this->product->name,
                'price' => $this->product->price,
                'quantity' => 2,
                'subtotal' => 200000,
            ]);

            Transaction::createPayment($this->order, 'bank_transfer', [
                'payment_channel' => 'manual',
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_holder' => 'Toko Test',
            ]);
        });
    }

    /** @test */
    public function test_orders_index_requires_authentication()
    {
        $this->tenant->run(function () {
            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);
        });

        $response = $this->get('http://orderctrltest.localhost/orders');
        $response->assertRedirect();
    }

    /** @test */
    public function test_orders_index_shows_user_orders()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'orderctrltest.localhost')
                ->get('/orders');

            $response->assertStatus(200);
            $response->assertInertia(fn ($page) =>
                $page->component('Orders/Index')
                    ->has('orders')
                    ->has('statuses')
            );
        });
    }

    /** @test */
    public function test_orders_can_be_filtered_by_status()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            // Create another order with different status
            Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $this->user->id,
                'address_id' => $this->address->id,
                'status' => Order::STATUS_DELIVERED,
                'subtotal' => 100000,
                'total' => 100000,
                'payment_method' => 'bank_transfer',
                'shipping_address_snapshot' => $this->address->toSnapshot(),
            ]);

            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'orderctrltest.localhost')
                ->get('/orders?status=pending_payment');

            $response->assertStatus(200);
            $response->assertInertia(fn ($page) =>
                $page->where('currentStatus', 'pending_payment')
            );
        });
    }

    /** @test */
    public function test_order_show_displays_order_details()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'orderctrltest.localhost')
                ->get('/orders/' . $this->order->id);

            $response->assertStatus(200);
            $response->assertInertia(fn ($page) =>
                $page->component('Orders/Show')
                    ->has('order')
                    ->has('items')
                    ->has('transaction')
                    ->has('statusTimeline')
            );
        });
    }

    /** @test */
    public function test_user_cannot_view_other_user_orders()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            $anotherUser = User::create([
                'name' => 'Another User',
                'email' => 'another@test.com',
                'password' => bcrypt('password'),
            ]);

            $response = $this->actingAs($anotherUser)
                ->withHeader('Host', 'orderctrltest.localhost')
                ->get('/orders/' . $this->order->id);

            $response->assertStatus(403);
        });
    }

    /** @test */
    public function test_upload_proof_stores_file()
    {
        $this->initializeTenant();

        Storage::fake('public');

        $this->tenant->run(function () {
            $file = UploadedFile::fake()->image('proof.jpg');

            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'orderctrltest.localhost')
                ->post('/orders/' . $this->order->id . '/upload-proof', [
                    'transfer_proof' => $file,
                ]);

            $response->assertRedirect();
            $response->assertSessionHas('success');

            // Verify transaction has proof
            $transaction = $this->order->fresh()->getLatestTransaction();
            $this->assertNotNull($transaction->transfer_proof);
        });
    }

    /** @test */
    public function test_cancel_order_works_for_pending_orders()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            $initialStock = $this->product->stock;

            // Decrease stock first (simulating checkout)
            $this->product->decreaseStock(2);

            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'orderctrltest.localhost')
                ->post('/orders/' . $this->order->id . '/cancel');

            $response->assertRedirect();

            // Verify order cancelled
            $this->order->refresh();
            $this->assertEquals(Order::STATUS_CANCELLED, $this->order->status);

            // Verify stock restored
            $this->product->refresh();
            $this->assertEquals($initialStock, $this->product->stock);
        });
    }

    /** @test */
    public function test_cannot_cancel_shipped_order()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            $this->order->update(['status' => Order::STATUS_SHIPPED]);

            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'orderctrltest.localhost')
                ->post('/orders/' . $this->order->id . '/cancel');

            $response->assertSessionHas('error');

            $this->order->refresh();
            $this->assertEquals(Order::STATUS_SHIPPED, $this->order->status);
        });
    }

    /** @test */
    public function test_reorder_adds_items_to_cart()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'orderctrltest.localhost')
                ->post('/orders/' . $this->order->id . '/reorder');

            $response->assertRedirect(route('cart.index'));
            $response->assertSessionHas('success');
        });
    }

    /** @test */
    public function test_confirm_received_marks_order_delivered()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            $this->order->update(['status' => Order::STATUS_SHIPPED]);

            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'orderctrltest.localhost')
                ->post('/orders/' . $this->order->id . '/confirm-received');

            $response->assertRedirect();

            $this->order->refresh();
            $this->assertEquals(Order::STATUS_DELIVERED, $this->order->status);
        });
    }

    /** @test */
    public function test_track_page_displays_tracking_info()
    {
        $this->initializeTenant();

        $this->tenant->run(function () {
            $this->order->update([
                'status' => Order::STATUS_SHIPPED,
                'shipping_tracking_number' => 'JNE123456789',
                'shipping_courier' => 'JNE',
                'shipping_service' => 'REG',
            ]);

            $response = $this->actingAs($this->user)
                ->withHeader('Host', 'orderctrltest.localhost')
                ->get('/orders/' . $this->order->id . '/track');

            $response->assertStatus(200);
            $response->assertInertia(fn ($page) =>
                $page->component('Orders/Track')
                    ->has('order')
            );
        });
    }
}
