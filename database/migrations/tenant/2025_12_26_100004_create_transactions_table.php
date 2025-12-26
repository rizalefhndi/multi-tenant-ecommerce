<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique(); // TRX-xxxxx atau dari payment gateway
            $table->foreignId('order_id')->constrained()->onDelete('cascade');

            $table->enum('type', ['payment', 'refund'])->default('payment');
            $table->enum('status', [
                'pending',
                'success',
                'failed',
                'expired',
                'cancelled'
            ])->default('pending');

            $table->decimal('amount', 15, 2);
            $table->string('payment_method'); // bank_transfer, credit_card, gopay, etc
            $table->string('payment_channel')->nullable(); // BCA, Mandiri, GoPay, OVO, etc

            // Gateway Response Storage (untuk Midtrans, etc)
            $table->json('gateway_response')->nullable();
            $table->string('gateway_transaction_id')->nullable();
            $table->string('gateway_redirect_url')->nullable(); // Payment URL from gateway

            // Bank Transfer Info (untuk manual transfer)
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_holder')->nullable();
            $table->string('transfer_proof')->nullable(); // Path to uploaded proof image

            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('verified_at')->nullable(); // Admin verification time

            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index('transaction_id');
            $table->index('status');
            $table->index('gateway_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
