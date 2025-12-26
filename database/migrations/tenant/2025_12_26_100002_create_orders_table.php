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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // ORD-20251226-XXXXX
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('address_id')->nullable()->constrained('user_addresses')->nullOnDelete();

            // Order Status
            $table->enum('status', [
                'pending_payment',    // Menunggu pembayaran
                'payment_received',   // Pembayaran diterima
                'processing',         // Sedang diproses
                'shipped',            // Dikirim
                'delivered',          // Selesai
                'cancelled',          // Dibatalkan
                'refunded'            // Dikembalikan
            ])->default('pending_payment');

            // Pricing
            $table->decimal('subtotal', 15, 2);
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2);

            // Shipping Info
            $table->string('shipping_courier')->nullable(); // JNE, J&T, SiCepat, etc
            $table->string('shipping_service')->nullable(); // REG, YES, OKE, etc
            $table->string('shipping_tracking_number')->nullable();
            $table->integer('shipping_weight')->default(0); // Total weight in grams
            $table->json('shipping_address_snapshot')->nullable(); // Snapshot alamat saat order

            // Payment Info
            $table->string('payment_method')->nullable(); // bank_transfer, midtrans, cod
            $table->timestamp('paid_at')->nullable();

            // Notes
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('order_number');
            $table->index('created_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
