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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            // Product Snapshot (untuk historical record, karena product bisa berubah/dihapus)
            $table->string('product_name');
            $table->string('product_sku')->nullable();
            $table->string('product_image')->nullable();
            $table->integer('product_weight')->default(0); // Weight in grams

            // Pricing
            $table->decimal('price', 15, 2); // Harga satuan saat order
            $table->integer('quantity');
            $table->decimal('subtotal', 15, 2); // price * quantity

            $table->timestamps();

            $table->index('order_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
