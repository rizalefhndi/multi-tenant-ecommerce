<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Tabel invoices untuk billing history tenants
     */
    public function up(): void
    {
        Schema::create('tenant_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // INV-2024-XXXXX
            
            $table->string('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            
            // Billing details
            $table->string('billing_cycle'); // monthly, yearly
            $table->date('period_start');
            $table->date('period_end');
            
            // Amounts
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('tax', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->string('currency', 3)->default('IDR');
            
            // Status
            $table->enum('status', [
                'draft',
                'pending',
                'paid',
                'failed',
                'cancelled',
                'refunded'
            ])->default('draft');
            
            // Payment info
            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable(); // External payment ID
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('due_at')->nullable();
            
            // Additional data
            $table->json('line_items')->nullable(); // Breakdown of charges
            $table->json('metadata')->nullable(); // Extra data
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'status']);
            $table->index('due_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_invoices');
    }
};
