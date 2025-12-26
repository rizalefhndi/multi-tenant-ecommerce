<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel plans untuk menyimpan subscription plans (central database)
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // free, basic, pro, enterprise
            $table->string('name'); // Display name
            $table->text('description')->nullable();

            // Pricing
            $table->decimal('price_monthly', 12, 2)->default(0);
            $table->decimal('price_yearly', 12, 2)->default(0);
            $table->string('currency', 3)->default('IDR');

            // Quotas/Limits
            $table->integer('max_products')->default(0); // 0 = unlimited
            $table->integer('max_orders_per_month')->default(0); // 0 = unlimited
            $table->integer('max_storage_mb')->default(0); // 0 = unlimited
            $table->integer('max_users')->default(1); // Staff/Admin users

            // Features sebagai JSON
            $table->json('features')->nullable(); // Array of feature strings

            // Flags
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false); // Highlight di pricing page
            $table->boolean('is_custom')->default(false); // Enterprise custom plan

            // Order untuk display
            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
