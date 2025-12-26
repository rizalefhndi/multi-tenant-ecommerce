<?php

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected UserAddress $address;
    protected Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['id' => 'transaction_test']);
        $this->tenant->domains()->create(['domain' => 'transactiontest.localhost']);

        $this->tenant->run(function () {
            Artisan::call('migrate', ['--path' => 'database/migrations/tenant']);

            $this->user = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
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

            $this->order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $this->user->id,
                'address_id' => $this->address->id,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'subtotal' => 100000,
                'shipping_cost' => 10000,
                'total' => 110000,
                'payment_method' => 'bank_transfer',
                'shipping_address_snapshot' => $this->address->toSnapshot(),
            ]);
        });
    }

    /** @test */
    public function test_transaction_can_be_created_via_factory_method()
    {
        $this->tenant->run(function () {
            $transaction = Transaction::createPayment($this->order, 'bank_transfer', [
                'payment_channel' => 'manual',
            ]);

            $this->assertNotNull($transaction->id);
            $this->assertNotNull($transaction->transaction_id);
            $this->assertEquals(Transaction::TYPE_PAYMENT, $transaction->type);
            $this->assertEquals(Transaction::STATUS_PENDING, $transaction->status);
            $this->assertEquals(Transaction::METHOD_BANK_TRANSFER, $transaction->payment_method);
            $this->assertEquals($this->order->total, $transaction->amount);
            $this->assertNotNull($transaction->expires_at);
        });
    }

    /** @test */
    public function test_transaction_id_is_unique()
    {
        $this->tenant->run(function () {
            $ids = [];
            for ($i = 0; $i < 10; $i++) {
                $id = Transaction::generateTransactionId();
                $this->assertNotContains($id, $ids);
                $ids[] = $id;
            }
        });
    }

    /** @test */
    public function test_transaction_verification_updates_order()
    {
        $this->tenant->run(function () {
            $transaction = Transaction::createPayment($this->order, 'bank_transfer');

            $this->assertEquals(Transaction::STATUS_PENDING, $transaction->status);
            $this->assertEquals(Order::STATUS_PENDING_PAYMENT, $this->order->status);

            // Verify transaction
            $transaction->verify();

            $transaction->refresh();
            $this->order->refresh();

            $this->assertEquals(Transaction::STATUS_SUCCESS, $transaction->status);
            $this->assertNotNull($transaction->verified_at);
            $this->assertEquals(Order::STATUS_PAYMENT_RECEIVED, $this->order->status);
            $this->assertNotNull($this->order->paid_at);
        });
    }

    /** @test */
    public function test_transaction_can_be_verified_only_if_pending()
    {
        $this->tenant->run(function () {
            $transaction = Transaction::createPayment($this->order, 'bank_transfer');

            $this->assertTrue($transaction->canBeVerified());

            // After verification
            $transaction->verify();
            $this->assertFalse($transaction->canBeVerified());

            // Create failed transaction
            $failedTrx = Transaction::create([
                'order_id' => $this->order->id,
                'transaction_id' => Transaction::generateTransactionId(),
                'type' => Transaction::TYPE_PAYMENT,
                'status' => Transaction::STATUS_FAILED,
                'amount' => 100000,
                'payment_method' => 'bank_transfer',
            ]);

            $this->assertFalse($failedTrx->canBeVerified());
        });
    }

    /** @test */
    public function test_transaction_expiry_check()
    {
        $this->tenant->run(function () {
            // Not expired
            $transaction = Transaction::createPayment($this->order, 'bank_transfer');
            $this->assertFalse($transaction->is_expired);

            // Expired
            $transaction->update(['expires_at' => now()->subHour()]);
            $this->assertTrue($transaction->fresh()->is_expired);
        });
    }

    /** @test */
    public function test_transaction_remaining_time()
    {
        $this->tenant->run(function () {
            $transaction = Transaction::createPayment($this->order, 'bank_transfer');

            // Has remaining time
            $this->assertNotNull($transaction->remaining_time);
            $this->assertStringContainsString('jam', $transaction->remaining_time);

            // Expired
            $transaction->update(['expires_at' => now()->subHour()]);
            $this->assertNull($transaction->fresh()->remaining_time);
        });
    }

    /** @test */
    public function test_transaction_payment_method_labels()
    {
        $this->tenant->run(function () {
            $transaction = Transaction::createPayment($this->order, 'bank_transfer');
            $this->assertEquals('Transfer Bank', $transaction->payment_method_label);
        });
    }

    /** @test */
    public function test_transaction_status_labels()
    {
        $this->tenant->run(function () {
            $transaction = Transaction::createPayment($this->order, 'bank_transfer');

            $this->assertEquals('Menunggu', $transaction->status_label);

            $transaction->update(['status' => Transaction::STATUS_SUCCESS]);
            $this->assertEquals('Berhasil', $transaction->fresh()->status_label);

            $transaction->update(['status' => Transaction::STATUS_FAILED]);
            $this->assertEquals('Gagal', $transaction->fresh()->status_label);
        });
    }

    /** @test */
    public function test_transaction_bank_transfer_info()
    {
        $this->tenant->run(function () {
            $transaction = Transaction::create([
                'order_id' => $this->order->id,
                'transaction_id' => Transaction::generateTransactionId(),
                'type' => Transaction::TYPE_PAYMENT,
                'status' => Transaction::STATUS_PENDING,
                'amount' => 100000,
                'payment_method' => 'bank_transfer',
                'bank_name' => 'BCA',
                'account_number' => '1234567890',
                'account_holder' => 'Toko Test',
            ]);

            $info = $transaction->getBankTransferInfo();

            $this->assertArrayHasKey('bank_name', $info);
            $this->assertArrayHasKey('account_number', $info);
            $this->assertArrayHasKey('account_holder', $info);
            $this->assertEquals('BCA', $info['bank_name']);
            $this->assertEquals('1234567890', $info['account_number']);
        });
    }

    /** @test */
    public function test_transaction_refund_creation()
    {
        $this->tenant->run(function () {
            // First, create and verify payment
            $payment = Transaction::createPayment($this->order, 'bank_transfer');
            $payment->verify();

            // Create refund
            $refund = Transaction::createRefund($this->order, 50000, 'Partial refund');

            $this->assertEquals(Transaction::TYPE_REFUND, $refund->type);
            $this->assertEquals(50000, $refund->amount);
            $this->assertEquals(Transaction::STATUS_PENDING, $refund->status);
        });
    }

    /** @test */
    public function test_transaction_gateway_callback_handling()
    {
        $this->tenant->run(function () {
            $transaction = Transaction::createPayment($this->order, 'virtual_account', [
                'payment_channel' => 'midtrans_va',
            ]);

            // Simulate callback
            $transaction->handleGatewayCallback([
                'transaction_status' => 'settlement',
                'transaction_id' => 'MIDTRANS-123',
            ]);

            $transaction->refresh();
            $this->order->refresh();

            $this->assertEquals(Transaction::STATUS_SUCCESS, $transaction->status);
            $this->assertEquals('MIDTRANS-123', $transaction->gateway_transaction_id);
            $this->assertEquals(Order::STATUS_PAYMENT_RECEIVED, $this->order->status);
        });
    }
}
