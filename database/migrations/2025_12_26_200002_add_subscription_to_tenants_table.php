<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tambah subscription fields ke tenants table
     */
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Plan relationship
            $table->foreignId('plan_id')->nullable()->after('id')->constrained()->nullOnDelete();
            
            // Subscription status
            $table->enum('subscription_status', [
                'trial',
                'active', 
                'past_due',
                'cancelled',
                'expired'
            ])->default('trial')->after('plan_id');
            
            // Billing cycle
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly')->after('subscription_status');
            
            // Trial & Subscription dates
            $table->timestamp('trial_ends_at')->nullable()->after('billing_cycle');
            $table->timestamp('subscription_ends_at')->nullable()->after('trial_ends_at');
            
            // Usage counters (updated via events/observers)
            $table->integer('product_count')->default(0)->after('subscription_ends_at');
            $table->integer('order_count_this_month')->default(0)->after('product_count');
            $table->integer('storage_used_mb')->default(0)->after('order_count_this_month');
            
            // Last billing reset date  
            $table->date('usage_reset_date')->nullable()->after('storage_used_mb');
            
            // Indexes
            $table->index('subscription_status');
            $table->index('subscription_ends_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn([
                'plan_id',
                'subscription_status',
                'billing_cycle',
                'trial_ends_at',
                'subscription_ends_at',
                'product_count',
                'order_count_this_month',
                'storage_used_mb',
                'usage_reset_date',
            ]);
        });
    }
};
