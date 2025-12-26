<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Key-value store untuk tenant settings
     */
    public function up(): void
    {
        Schema::create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->string('group')->default('general'); // general, theme, store, payment, shipping
            $table->string('key');
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json, array
            $table->timestamps();

            $table->unique(['group', 'key']);
            $table->index('group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_settings');
    }
};
