<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi mass assignment
     */
    protected $fillable = [
        'transaction_id',
        'order_id',
        'type',
        'status',
        'amount',
        'payment_method',
        'payment_channel',
        'gateway_response',
        'gateway_transaction_id',
        'gateway_redirect_url',
        'bank_name',
        'account_number',
        'account_holder',
        'transfer_proof',
        'paid_at',
        'expires_at',
        'verified_at',
    ];

    /**
     * Cast attributes ke tipe data tertentu
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    // ==========================================
    // TYPE CONSTANTS
    // ==========================================

    const TYPE_PAYMENT = 'payment';
    const TYPE_REFUND = 'refund';

    // ==========================================
    // STATUS CONSTANTS
    // ==========================================

    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get semua status dengan label Indonesia
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Menunggu Pembayaran',
            self::STATUS_SUCCESS => 'Berhasil',
            self::STATUS_FAILED => 'Gagal',
            self::STATUS_EXPIRED => 'Kedaluwarsa',
            self::STATUS_CANCELLED => 'Dibatalkan',
        ];
    }

    /**
     * Get status colors untuk UI
     */
    public static function getStatusColors(): array
    {
        return [
            self::STATUS_PENDING => 'warning',
            self::STATUS_SUCCESS => 'success',
            self::STATUS_FAILED => 'danger',
            self::STATUS_EXPIRED => 'secondary',
            self::STATUS_CANCELLED => 'dark',
        ];
    }

    // ==========================================
    // PAYMENT METHOD CONSTANTS
    // ==========================================

    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_VIRTUAL_ACCOUNT = 'virtual_account';
    const METHOD_CREDIT_CARD = 'credit_card';
    const METHOD_GOPAY = 'gopay';
    const METHOD_SHOPEEPAY = 'shopeepay';
    const METHOD_QRIS = 'qris';
    const METHOD_COD = 'cod';

    /**
     * Get available payment methods
     */
    public static function getPaymentMethods(): array
    {
        return [
            self::METHOD_BANK_TRANSFER => 'Transfer Bank Manual',
            self::METHOD_VIRTUAL_ACCOUNT => 'Virtual Account',
            self::METHOD_CREDIT_CARD => 'Kartu Kredit',
            self::METHOD_GOPAY => 'GoPay',
            self::METHOD_SHOPEEPAY => 'ShopeePay',
            self::METHOD_QRIS => 'QRIS',
            self::METHOD_COD => 'Bayar di Tempat (COD)',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Relationship: Transaction dimiliki oleh satu Order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Format amount dengan Rp
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
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
     * Get payment method label
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return self::getPaymentMethods()[$this->payment_method] ?? $this->payment_method;
    }

    /**
     * Get transfer proof URL
     */
    public function getTransferProofUrlAttribute(): ?string
    {
        if ($this->transfer_proof) {
            if (str_starts_with($this->transfer_proof, 'http')) {
                return $this->transfer_proof;
            }
            return asset('storage/' . $this->transfer_proof);
        }
        return null;
    }

    /**
     * Check apakah sudah expired
     */
    public function getIsExpiredAttribute(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        return $this->expires_at->isPast();
    }

    /**
     * Get remaining time before expiry
     */
    public function getRemainingTimeAttribute(): ?string
    {
        if (!$this->expires_at || $this->is_expired) {
            return null;
        }
        return $this->expires_at->diffForHumans();
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Query transaksi pending
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Query transaksi success
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', self::STATUS_SUCCESS);
    }

    /**
     * Scope: Query transaksi untuk order tertentu
     */
    public function scopeForOrder($query, int $orderId)
    {
        return $query->where('order_id', $orderId);
    }

    /**
     * Scope: Query transaksi tipe payment
     */
    public function scopePayments($query)
    {
        return $query->where('type', self::TYPE_PAYMENT);
    }

    /**
     * Scope: Query transaksi tipe refund
     */
    public function scopeRefunds($query)
    {
        return $query->where('type', self::TYPE_REFUND);
    }

    /**
     * Scope: Query transaksi yang expired
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('expires_at', '<', now());
    }

    // ==========================================
    // STATIC METHODS
    // ==========================================

    /**
     * Generate unique transaction ID
     * Format: TRX-TIMESTAMP-RANDOM
     */
    public static function generateTransactionId(): string
    {
        $timestamp = now()->format('YmdHis');
        $random = strtoupper(substr(uniqid(), -4));

        $transactionId = "TRX-{$timestamp}-{$random}";

        // Pastikan unique
        while (self::where('transaction_id', $transactionId)->exists()) {
            $random = strtoupper(substr(uniqid(), -4));
            $transactionId = "TRX-{$timestamp}-{$random}";
        }

        return $transactionId;
    }

    /**
     * Create payment transaction for order
     */
    public static function createPayment(Order $order, string $paymentMethod, array $extra = []): self
    {
        return self::create(array_merge([
            'transaction_id' => self::generateTransactionId(),
            'order_id' => $order->id,
            'type' => self::TYPE_PAYMENT,
            'status' => self::STATUS_PENDING,
            'amount' => $order->total,
            'payment_method' => $paymentMethod,
            'expires_at' => now()->addHours(24),
        ], $extra));
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check apakah transaction pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check apakah transaction success
     */
    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * Check apakah transaction failed
     */
    public function isFailed(): bool
    {
        return in_array($this->status, [
            self::STATUS_FAILED,
            self::STATUS_EXPIRED,
            self::STATUS_CANCELLED,
        ]);
    }

    /**
     * Check apakah bisa diverifikasi (untuk manual transfer)
     */
    public function canBeVerified(): bool
    {
        return $this->isPending();
    }

    /**
     * Mark sebagai success
     */
    public function markAsSuccess(): void
    {
        $this->update([
            'status' => self::STATUS_SUCCESS,
            'paid_at' => now(),
        ]);

        // Update order status juga
        $this->order->markAsPaid();
    }

    /**
     * Mark sebagai failed
     */
    public function markAsFailed(): void
    {
        $this->update(['status' => self::STATUS_FAILED]);
    }

    /**
     * Mark sebagai expired
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => self::STATUS_EXPIRED]);
    }

    /**
     * Verify manual transfer (by admin)
     */
    public function verify(): bool
    {
        if (!$this->canBeVerified()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_SUCCESS,
            'paid_at' => now(),
            'verified_at' => now(),
        ]);

        // Update order status
        $this->order->markAsPaid();

        return true;
    }

    /**
     * Handle gateway callback/notification
     */
    public function handleGatewayCallback(array $response): void
    {
        $this->update([
            'gateway_response' => $response,
            'gateway_transaction_id' => $response['transaction_id'] ?? null,
        ]);

        // Handle based on status from gateway
        $status = $response['transaction_status'] ?? null;
        
        if (in_array($status, ['settlement', 'capture'])) {
            $this->update([
                'status' => self::STATUS_SUCCESS,
                'paid_at' => now(),
            ]);
            $this->order->markAsPaid();
        } elseif (in_array($status, ['deny', 'cancel', 'expire'])) {
            $this->update(['status' => self::STATUS_FAILED]);
        }
    }

    /**
     * Create refund transaction for order
     */
    public static function createRefund(Order $order, float $amount, ?string $reason = null): self
    {
        return self::create([
            'transaction_id' => self::generateTransactionId(),
            'order_id' => $order->id,
            'type' => self::TYPE_REFUND,
            'status' => self::STATUS_PENDING,
            'amount' => $amount,
            'payment_method' => $order->payment_method,
            'gateway_response' => $reason ? ['reason' => $reason] : null,
        ]);
    }

    /**
     * Get bank transfer info for display
     */
    public function getBankTransferInfo(): ?array
    {
        if ($this->payment_method !== self::METHOD_BANK_TRANSFER) {
            return null;
        }

        return [
            'bank_name' => $this->bank_name,
            'account_number' => $this->account_number,
            'account_holder' => $this->account_holder,
            'amount' => $this->formatted_amount,
            'expires_at' => $this->expires_at?->format('d M Y H:i'),
            'has_proof' => $this->transfer_proof !== null,
            'proof_url' => $this->transfer_proof_url,
        ];
    }
}
