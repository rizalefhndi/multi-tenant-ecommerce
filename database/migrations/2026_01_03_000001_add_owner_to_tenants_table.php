<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Add owner_id to link a tenant to its creator (central user)
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Owner relationship (user who created this tenant)
            $table->foreignId('owner_id')
                ->nullable()
                ->after('plan_id')
                ->constrained('users')
                ->nullOnDelete();
            
            // Store metadata
            $table->string('store_name')->nullable()->after('owner_id');
            
            // Index for quick lookup
            $table->index('owner_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
            $table->dropColumn(['owner_id', 'store_name']);
        });
    }
};
