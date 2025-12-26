<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantInvoice extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi mass assignment
     */
    protected $fillable = [
        'invoice_number',
        'tenant_id',
        'plan_id',
        'billing_cycle',
        'period_start',
        'period_end',
        'subtotal',
        'discount',
        'tax',
        'total',
        'currency',
        'status',
        'payment_method',
        'payment_reference',
        'paid_at',
        'due_at',
        'line_items',
        'metadata',
        'notes',
    ];

    /**
     * Cast attributes ke tipe data tertentu
     */
    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_at' => 'datetime',
        'due_at' => 'datetime',
        'line_items' => 'array',
        'metadata' => 'array',
    ];

    // ==========================================
    // STATUS CONSTANTS
    // ==========================================

    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    /**
     * Get all statuses with labels
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING => 'Menunggu Pembayaran',
            self::STATUS_PAID => 'Lunas',
            self::STATUS_FAILED => 'Gagal',
            self::STATUS_CANCELLED => 'Dibatalkan',
            self::STATUS_REFUNDED => 'Dikembalikan',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Relationship: Invoice belongs to Tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Relationship: Invoice belongs to Plan
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Query invoices pending
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Query invoices paid
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope: Query overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('due_at', '<', now());
    }

    /**
     * Scope: Query invoices for specific tenant
     */
    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Format total dengan Rp
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    /**
     * Format subtotal dengan Rp
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    /**
     * Get status label Indonesia
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_PENDING => 'warning',
            self::STATUS_PAID => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_CANCELLED => 'dark',
            self::STATUS_REFUNDED => 'info',
            default => 'secondary',
        };
    }

    /**
     * Check if overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING 
            && $this->due_at 
            && $this->due_at->isPast();
    }

    /**
     * Get period display text
     */
    public function getPeriodDisplayAttribute(): string
    {
        return $this->period_start->format('d M Y') . ' - ' . $this->period_end->format('d M Y');
    }

    // ==========================================
    // STATIC METHODS
    // ==========================================

    /**
     * Generate unique invoice number
     * Format: INV-YYYY-XXXXX
     */
    public static function generateInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $random = strtoupper(substr(uniqid(), -5));

        $invoiceNumber = "INV-{$year}-{$random}";

        // Ensure unique
        while (self::where('invoice_number', $invoiceNumber)->exists()) {
            $random = strtoupper(substr(uniqid(), -5));
            $invoiceNumber = "INV-{$year}-{$random}";
        }

        return $invoiceNumber;
    }

    /**
     * Create invoice for tenant subscription
     */
    public static function createForSubscription(
        Tenant $tenant, 
        Plan $plan, 
        string $billingCycle = 'monthly'
    ): self {
        $price = $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        $periodMonths = $billingCycle === 'yearly' ? 12 : 1;

        return self::create([
            'invoice_number' => self::generateInvoiceNumber(),
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'billing_cycle' => $billingCycle,
            'period_start' => now(),
            'period_end' => now()->addMonths($periodMonths),
            'subtotal' => $price,
            'discount' => 0,
            'tax' => 0,
            'total' => $price,
            'currency' => $plan->currency ?? 'IDR',
            'status' => $plan->isFree() ? self::STATUS_PAID : self::STATUS_PENDING,
            'due_at' => now()->addDays(7),
            'paid_at' => $plan->isFree() ? now() : null,
            'line_items' => [
                [
                    'description' => "{$plan->name} - " . ucfirst($billingCycle),
                    'quantity' => 1,
                    'unit_price' => $price,
                    'total' => $price,
                ]
            ],
        ]);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(?string $paymentMethod = null, ?string $paymentReference = null): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
        ]);
    }

    /**
     * Mark invoice as failed
     */
    public function markAsFailed(): void
    {
        $this->update(['status' => self::STATUS_FAILED]);
    }

    /**
     * Mark invoice as cancelled
     */
    public function markAsCancelled(): void
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }

    /**
     * Check if invoice is paid
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if invoice is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
