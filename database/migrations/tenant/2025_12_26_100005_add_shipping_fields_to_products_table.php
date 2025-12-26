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
        Schema::table('products', function (Blueprint $table) {
            // Add SKU for inventory tracking
            $table->string('sku')->nullable()->after('id');

            // Add weight for shipping calculation (in grams)
            $table->integer('weight')->default(0)->after('stock');

            // Add dimensions for shipping (in cm)
            $table->integer('length')->nullable()->after('weight'); // panjang
            $table->integer('width')->nullable()->after('length');  // lebar
            $table->integer('height')->nullable()->after('width');  // tinggi

            $table->index('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['sku']);
            $table->dropColumn(['sku', 'weight', 'length', 'width', 'height']);
        });
    }
};
