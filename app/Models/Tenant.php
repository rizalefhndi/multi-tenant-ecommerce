<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * Additional columns to be stored in data json column
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'owner_id',
            'store_name',
            'status',
            'suspended_at',
            'suspended_reason',
            'plan_id',
            'subscription_status',
            'billing_cycle',
            'trial_ends_at',
            'subscription_ends_at',
            'product_count',
            'order_count_this_month',
            'storage_used_mb',
            'usage_reset_date',
        ];
    }

    /**
     * Cast attributes
     */
    protected $casts = [
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'usage_reset_date' => 'date',
        'suspended_at' => 'datetime',
        'product_count' => 'integer',
        'order_count_this_month' => 'integer',
        'storage_used_mb' => 'integer',
    ];

    // ==========================================
    // TENANT STATUS CONSTANTS
    // ==========================================

    const TENANT_STATUS_ACTIVE = 'active';
    const TENANT_STATUS_SUSPENDED = 'suspended';

    // ==========================================
    // SUBSCRIPTION STATUS CONSTANTS
    // ==========================================

    const STATUS_TRIAL = 'trial';
    const STATUS_ACTIVE = 'active';
    const STATUS_PAST_DUE = 'past_due';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';

    /**
     * Get all status labels
     */
    public static function getSubscriptionStatuses(): array
    {
        return [
            self::STATUS_TRIAL => 'Trial',
            self::STATUS_ACTIVE => 'Aktif',
            self::STATUS_PAST_DUE => 'Terlambat Bayar',
            self::STATUS_CANCELLED => 'Dibatalkan',
            self::STATUS_EXPIRED => 'Kedaluwarsa',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Relationship: Tenant belongs to Plan
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Relationship: Tenant belongs to Owner (User who created it)
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Relationship: Tenant has many invoices
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(TenantInvoice::class);
    }

    // ==========================================
    // TENANT STATUS METHODS
    // ==========================================

    /**
     * Check if tenant is active
     */
    public function isActive(): bool
    {
        return $this->status === self::TENANT_STATUS_ACTIVE;
    }

    /**
     * Check if tenant is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === self::TENANT_STATUS_SUSPENDED;
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get subscription status label
     */
    public function getSubscriptionStatusLabelAttribute(): string
    {
        return self::getSubscriptionStatuses()[$this->subscription_status] ?? $this->subscription_status;
    }

    /**
     * Get subscription status color for UI
     */
    public function getSubscriptionStatusColorAttribute(): string
    {
        return match($this->subscription_status) {
            self::STATUS_TRIAL => 'info',
            self::STATUS_ACTIVE => 'success',
            self::STATUS_PAST_DUE => 'warning',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_EXPIRED => 'dark',
            default => 'secondary',
        };
    }

    /**
     * Get billing cycle label
     */
    public function getBillingCycleLabelAttribute(): string
    {
        return match($this->billing_cycle) {
            'monthly' => 'Bulanan',
            'yearly' => 'Tahunan',
            default => $this->billing_cycle,
        };
    }

    /**
     * Check if trial is active
     */
    public function getIsOnTrialAttribute(): bool
    {
        return $this->subscription_status === self::STATUS_TRIAL
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    /**
     * Get trial days remaining
     */
    public function getTrialDaysRemainingAttribute(): int
    {
        if (!$this->is_on_trial) {
            return 0;
        }
        return (int) now()->diffInDays($this->trial_ends_at, false);
    }

    /**
     * Get subscription days remaining
     */
    public function getSubscriptionDaysRemainingAttribute(): int
    {
        if (!$this->subscription_ends_at || $this->subscription_ends_at->isPast()) {
            return 0;
        }
        return (int) now()->diffInDays($this->subscription_ends_at, false);
    }

    // ==========================================
    // QUOTA CHECKING METHODS
    // ==========================================

    /**
     * Get current plan (fallback to free plan)
     */
    public function getCurrentPlan(): ?Plan
    {
        return $this->plan ?? Plan::getFreePlan();
    }

    /**
     * Check if can add more products
     */
    public function canAddProduct(): bool
    {
        $plan = $this->getCurrentPlan();
        if (!$plan) {
            return false;
        }
        return $plan->canAddProduct($this->product_count ?? 0);
    }

    /**
     * Check if can create more orders this month
     */
    public function canCreateOrder(): bool
    {
        $plan = $this->getCurrentPlan();
        if (!$plan) {
            return false;
        }
        return $plan->canCreateOrder($this->order_count_this_month ?? 0);
    }

    /**
     * Check if can upload file with given size
     */
    public function canUploadFile(int $fileSizeMb): bool
    {
        $plan = $this->getCurrentPlan();
        if (!$plan) {
            return false;
        }
        return $plan->canUploadFile($this->storage_used_mb ?? 0, $fileSizeMb);
    }

    /**
     * Check if subscription is active (can use features)
     */
    public function hasActiveSubscription(): bool
    {
        // Trial masih berlaku
        if ($this->is_on_trial) {
            return true;
        }

        // Status active dan belum expired
        if ($this->subscription_status === self::STATUS_ACTIVE) {
            if (!$this->subscription_ends_at) {
                return true; // No end date = lifetime/special
            }
            return $this->subscription_ends_at->isFuture();
        }

        // Status past_due masih boleh akses (grace period)
        if ($this->subscription_status === self::STATUS_PAST_DUE) {
            return true;
        }

        return false;
    }

    /**
     * Get usage percentage for products
     */
    public function getProductUsagePercentAttribute(): int
    {
        $plan = $this->getCurrentPlan();
        if (!$plan || $plan->hasUnlimitedProducts()) {
            return 0;
        }
        return min(100, (int) round(($this->product_count ?? 0) / $plan->max_products * 100));
    }

    /**
     * Get usage percentage for orders
     */
    public function getOrderUsagePercentAttribute(): int
    {
        $plan = $this->getCurrentPlan();
        if (!$plan || $plan->hasUnlimitedOrders()) {
            return 0;
        }
        return min(100, (int) round(($this->order_count_this_month ?? 0) / $plan->max_orders_per_month * 100));
    }

    /**
     * Get usage percentage for storage
     */
    public function getStorageUsagePercentAttribute(): int
    {
        $plan = $this->getCurrentPlan();
        if (!$plan || $plan->hasUnlimitedStorage()) {
            return 0;
        }
        return min(100, (int) round(($this->storage_used_mb ?? 0) / $plan->max_storage_mb * 100));
    }

    // ==========================================
    // USAGE TRACKING METHODS
    // ==========================================

    /**
     * Increment product count
     */
    public function incrementProductCount(): void
    {
        $this->increment('product_count');
    }

    /**
     * Decrement product count
     */
    public function decrementProductCount(): void
    {
        if ($this->product_count > 0) {
            $this->decrement('product_count');
        }
    }

    /**
     * Increment order count for this month
     */
    public function incrementOrderCount(): void
    {
        $this->increment('order_count_this_month');
    }

    /**
     * Add storage usage
     */
    public function addStorageUsage(int $sizeMb): void
    {
        $this->increment('storage_used_mb', $sizeMb);
    }

    /**
     * Remove storage usage
     */
    public function removeStorageUsage(int $sizeMb): void
    {
        $newValue = max(0, ($this->storage_used_mb ?? 0) - $sizeMb);
        $this->update(['storage_used_mb' => $newValue]);
    }

    /**
     * Reset monthly order count (called by scheduler)
     */
    public function resetMonthlyOrderCount(): void
    {
        $this->update([
            'order_count_this_month' => 0,
            'usage_reset_date' => now(),
        ]);
    }

    // ==========================================
    // SUBSCRIPTION MANAGEMENT METHODS
    // ==========================================

    /**
     * Start trial period
     */
    public function startTrial(int $days = 14): void
    {
        $this->update([
            'subscription_status' => self::STATUS_TRIAL,
            'trial_ends_at' => now()->addDays($days),
            'plan_id' => Plan::getFreePlan()?->id,
        ]);
    }

    /**
     * Subscribe to a plan
     */
    public function subscribeToPlan(Plan $plan, string $billingCycle = 'monthly'): void
    {
        $periodMonths = $billingCycle === 'yearly' ? 12 : 1;

        $this->update([
            'plan_id' => $plan->id,
            'subscription_status' => self::STATUS_ACTIVE,
            'billing_cycle' => $billingCycle,
            'subscription_ends_at' => now()->addMonths($periodMonths),
            'trial_ends_at' => null,
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(): void
    {
        $this->update([
            'subscription_status' => self::STATUS_CANCELLED,
        ]);
    }

    /**
     * Expire subscription
     */
    public function expireSubscription(): void
    {
        $this->update([
            'subscription_status' => self::STATUS_EXPIRED,
        ]);
    }

    /**
     * Mark as past due
     */
    public function markAsPastDue(): void
    {
        $this->update([
            'subscription_status' => self::STATUS_PAST_DUE,
        ]);
    }

    /**
     * Get usage summary
     */
    public function getUsageSummary(): array
    {
        $plan = $this->getCurrentPlan();

        return [
            'products' => [
                'used' => $this->product_count ?? 0,
                'limit' => $plan?->max_products ?? 0,
                'percentage' => $this->product_usage_percent,
                'display' => $plan?->max_products_display ?? 'N/A',
            ],
            'orders' => [
                'used' => $this->order_count_this_month ?? 0,
                'limit' => $plan?->max_orders_per_month ?? 0,
                'percentage' => $this->order_usage_percent,
                'display' => $plan?->max_orders_display ?? 'N/A',
            ],
            'storage' => [
                'used' => $this->storage_used_mb ?? 0,
                'limit' => $plan?->max_storage_mb ?? 0,
                'percentage' => $this->storage_usage_percent,
                'display' => $plan?->max_storage_display ?? 'N/A',
            ],
        ];
    }
}

