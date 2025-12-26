<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Kolom yang boleh diisi mass assignment
     */
    protected $fillable = [
        'order_number',
        'user_id',
        'address_id',
        'status',
        'subtotal',
        'shipping_cost',
        'discount',
        'tax',
        'total',
        'shipping_courier',
        'shipping_service',
        'shipping_tracking_number',
        'shipping_weight',
        'shipping_address_snapshot',
        'payment_method',
        'paid_at',
        'customer_notes',
        'admin_notes',
    ];

    /**
     * Cast attributes ke tipe data tertentu
     */
    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'shipping_weight' => 'integer',
        'shipping_address_snapshot' => 'array',
        'paid_at' => 'datetime',
    ];

    // ==========================================
    // STATUS CONSTANTS
    // ==========================================

    const STATUS_PENDING_PAYMENT = 'pending_payment';
    const STATUS_PAYMENT_RECEIVED = 'payment_received';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    /**
     * Get semua status dengan label Indonesia
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING_PAYMENT => 'Menunggu Pembayaran',
            self::STATUS_PAYMENT_RECEIVED => 'Pembayaran Diterima',
            self::STATUS_PROCESSING => 'Sedang Diproses',
            self::STATUS_SHIPPED => 'Dikirim',
            self::STATUS_DELIVERED => 'Selesai',
            self::STATUS_CANCELLED => 'Dibatalkan',
            self::STATUS_REFUNDED => 'Dikembalikan',
        ];
    }

    /**
     * Get status colors untuk UI
     */
    public static function getStatusColors(): array
    {
        return [
            self::STATUS_PENDING_PAYMENT => 'warning',
            self::STATUS_PAYMENT_RECEIVED => 'info',
            self::STATUS_PROCESSING => 'primary',
            self::STATUS_SHIPPED => 'secondary',
            self::STATUS_DELIVERED => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_REFUNDED => 'dark',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Relationship: Order dimiliki oleh satu User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Order menggunakan satu Address
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(UserAddress::class, 'address_id');
    }

    /**
     * Relationship: Order memiliki banyak OrderItem
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Relationship: Order memiliki banyak Transaction
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Format total harga dengan Rp
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
     * Format shipping cost dengan Rp
     */
    public function getFormattedShippingCostAttribute(): string
    {
        return 'Rp ' . number_format($this->shipping_cost, 0, ',', '.');
    }

    /**
     * Get status label Indonesia
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatuses()[$this->status] ?? $this->status;
    }

    /**
     * Get status color untuk UI
     */
    public function getStatusColorAttribute(): string
    {
        return self::getStatusColors()[$this->status] ?? 'secondary';
    }

    /**
     * Get total items count
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Get shipping info text
     */
    public function getShippingInfoAttribute(): ?string
    {
        if ($this->shipping_courier && $this->shipping_service) {
            return strtoupper($this->shipping_courier) . ' - ' . strtoupper($this->shipping_service);
        }
        return null;
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Query order dengan status pending payment
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING_PAYMENT);
    }

    /**
     * Scope: Query order yang sudah dibayar
     */
    public function scopePaid($query)
    {
        return $query->whereNotIn('status', [
            self::STATUS_PENDING_PAYMENT,
            self::STATUS_CANCELLED,
        ]);
    }

    /**
     * Scope: Query order yang perlu diproses
     */
    public function scopeNeedsProcessing($query)
    {
        return $query->where('status', self::STATUS_PAYMENT_RECEIVED);
    }

    /**
     * Scope: Query order yang perlu dikirim
     */
    public function scopeNeedsShipping($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope: Query order yang sudah dikirim
     */
    public function scopeShipped($query)
    {
        return $query->where('status', self::STATUS_SHIPPED);
    }

    /**
     * Scope: Query order yang selesai
     */
    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    /**
     * Scope: Query order milik user tertentu
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // ==========================================
    // STATIC METHODS
    // ==========================================

    /**
     * Generate unique order number
     * Format: ORD-YYYYMMDD-XXXXX
     */
    public static function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -5));

        $orderNumber = "ORD-{$date}-{$random}";

        // Pastikan unique
        while (self::where('order_number', $orderNumber)->exists()) {
            $random = strtoupper(substr(uniqid(), -5));
            $orderNumber = "ORD-{$date}-{$random}";
        }

        return $orderNumber;
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check apakah order sudah dibayar
     */
    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    /**
     * Check apakah order bisa dibatalkan
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING_PAYMENT,
        ]);
    }

    /**
     * Check apakah order bisa diupdate statusnya ke status tertentu
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $allowedTransitions = [
            self::STATUS_PENDING_PAYMENT => [self::STATUS_PAYMENT_RECEIVED, self::STATUS_CANCELLED],
            self::STATUS_PAYMENT_RECEIVED => [self::STATUS_PROCESSING, self::STATUS_CANCELLED, self::STATUS_REFUNDED],
            self::STATUS_PROCESSING => [self::STATUS_SHIPPED, self::STATUS_CANCELLED, self::STATUS_REFUNDED],
            self::STATUS_SHIPPED => [self::STATUS_DELIVERED, self::STATUS_REFUNDED],
            self::STATUS_DELIVERED => [self::STATUS_REFUNDED],
            self::STATUS_CANCELLED => [],
            self::STATUS_REFUNDED => [],
        ];

        return in_array($newStatus, $allowedTransitions[$this->status] ?? []);
    }

    /**
     * Mark order sebagai sudah dibayar
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => self::STATUS_PAYMENT_RECEIVED,
            'paid_at' => now(),
        ]);
    }

    /**
     * Mark order sebagai sedang diproses
     */
    public function markAsProcessing(): void
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
    }

    /**
     * Mark order sebagai sudah dikirim
     */
    public function markAsShipped(?string $trackingNumber = null): void
    {
        $data = ['status' => self::STATUS_SHIPPED];

        if ($trackingNumber) {
            $data['shipping_tracking_number'] = $trackingNumber;
        }

        $this->update($data);
    }

    /**
     * Mark order sebagai selesai (delivered)
     */
    public function markAsDelivered(): void
    {
        $this->update(['status' => self::STATUS_DELIVERED]);
    }

    /**
     * Cancel order dan kembalikan stok
     */
    public function cancel(): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        // Restore stock untuk setiap item
        foreach ($this->items as $item) {
            if ($item->product) {
                $item->product->increment('stock', $item->quantity);
            }
        }

        // Update status
        $this->update(['status' => self::STATUS_CANCELLED]);

        // Cancel pending transactions
        $this->transactions()
            ->where('status', Transaction::STATUS_PENDING)
            ->update(['status' => Transaction::STATUS_CANCELLED]);

        return true;
    }

    /**
     * Get latest transaction
     */
    public function getLatestTransaction(): ?Transaction
    {
        return $this->transactions()->latest()->first();
    }

    /**
     * Get pending transaction
     */
    public function getPendingTransaction(): ?Transaction
    {
        return $this->transactions()
            ->where('status', Transaction::STATUS_PENDING)
            ->latest()
            ->first();
    }

    /**
     * Calculate and return summary
     */
    public function getSummary(): array
    {
        return [
            'order_number' => $this->order_number,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'total_items' => $this->total_items,
            'subtotal' => $this->subtotal,
            'shipping_cost' => $this->shipping_cost,
            'discount' => $this->discount,
            'tax' => $this->tax,
            'total' => $this->total,
            'formatted_total' => $this->formatted_total,
            'is_paid' => $this->isPaid(),
            'can_cancel' => $this->canBeCancelled(),
        ];
    }
}
