<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi mass assignment
     */
    protected $fillable = [
        'slug',
        'name',
        'description',
        'price_monthly',
        'price_yearly',
        'currency',
        'max_products',
        'max_orders_per_month',
        'max_storage_mb',
        'max_users',
        'features',
        'is_active',
        'is_featured',
        'is_custom',
        'sort_order',
    ];

    /**
     * Cast attributes ke tipe data tertentu
     */
    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'max_products' => 'integer',
        'max_orders_per_month' => 'integer',
        'max_storage_mb' => 'integer',
        'max_users' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_custom' => 'boolean',
        'sort_order' => 'integer',
    ];

    // ==========================================
    // PLAN SLUG CONSTANTS
    // ==========================================

    const PLAN_FREE = 'free';
    const PLAN_BASIC = 'basic';
    const PLAN_PRO = 'pro';
    const PLAN_ENTERPRISE = 'enterprise';

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Relationship: Plan memiliki banyak Tenants
     */
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Query hanya plan aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Query diurutkan
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price_monthly');
    }

    /**
     * Scope: Query plan gratis
     */
    public function scopeFree($query)
    {
        return $query->where('price_monthly', 0);
    }

    /**
     * Scope: Query plan berbayar
     */
    public function scopePaid($query)
    {
        return $query->where('price_monthly', '>', 0);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Format harga monthly dengan Rp
     */
    public function getFormattedPriceMonthlyAttribute(): string
    {
        if ($this->price_monthly == 0) {
            return 'Gratis';
        }
        return '$' . number_format($this->price_monthly, 2);
    }

    /**
     * Format harga yearly dengan Rp
     */
    public function getFormattedPriceYearlyAttribute(): string
    {
        if ($this->price_yearly == 0) {
            return 'Gratis';
        }
        return '$' . number_format($this->price_yearly, 2);
    }

    /**
     * Get savings percentage untuk yearly vs monthly
     */
    public function getYearlySavingsPercentAttribute(): int
    {
        if ($this->price_monthly == 0) {
            return 0;
        }
        $monthlyTotal = $this->price_monthly * 12;
        if ($monthlyTotal == 0) {
            return 0;
        }
        return (int) round((($monthlyTotal - $this->price_yearly) / $monthlyTotal) * 100);
    }

    /**
     * Get max products display text
     */
    public function getMaxProductsDisplayAttribute(): string
    {
        return $this->max_products == 0 ? 'Unlimited' : number_format($this->max_products);
    }

    /**
     * Get max orders display text
     */
    public function getMaxOrdersDisplayAttribute(): string
    {
        return $this->max_orders_per_month == 0 ? 'Unlimited' : number_format($this->max_orders_per_month) . '/bulan';
    }

    /**
     * Get max storage display text
     */
    public function getMaxStorageDisplayAttribute(): string
    {
        if ($this->max_storage_mb == 0) {
            return 'Unlimited';
        }
        if ($this->max_storage_mb >= 1024) {
            return number_format($this->max_storage_mb / 1024, 1) . ' GB';
        }
        return $this->max_storage_mb . ' MB';
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check apakah ini plan gratis
     */
    public function isFree(): bool
    {
        return $this->price_monthly == 0 && $this->price_yearly == 0;
    }

    /**
     * Check apakah limit products unlimited
     */
    public function hasUnlimitedProducts(): bool
    {
        return $this->max_products == 0;
    }

    /**
     * Check apakah limit orders unlimited
     */
    public function hasUnlimitedOrders(): bool
    {
        return $this->max_orders_per_month == 0;
    }

    /**
     * Check apakah limit storage unlimited
     */
    public function hasUnlimitedStorage(): bool
    {
        return $this->max_storage_mb == 0;
    }

    /**
     * Check apakah tenant dapat menambah product
     */
    public function canAddProduct(int $currentCount): bool
    {
        if ($this->hasUnlimitedProducts()) {
            return true;
        }
        return $currentCount < $this->max_products;
    }

    /**
     * Check apakah tenant dapat membuat order
     */
    public function canCreateOrder(int $currentCount): bool
    {
        if ($this->hasUnlimitedOrders()) {
            return true;
        }
        return $currentCount < $this->max_orders_per_month;
    }

    /**
     * Check apakah tenant dapat upload file
     */
    public function canUploadFile(int $currentUsageMb, int $fileSizeMb): bool
    {
        if ($this->hasUnlimitedStorage()) {
            return true;
        }
        return ($currentUsageMb + $fileSizeMb) <= $this->max_storage_mb;
    }

    /**
     * Get plan by slug
     */
    public static function findBySlug(string $slug): ?self
    {
        return self::where('slug', $slug)->first();
    }

    /**
     * Get free plan
     */
    public static function getFreePlan(): ?self
    {
        return self::findBySlug(self::PLAN_FREE);
    }

    /**
     * Get all quotas as array
     */
    public function getQuotas(): array
    {
        return [
            'max_products' => $this->max_products,
            'max_orders_per_month' => $this->max_orders_per_month,
            'max_storage_mb' => $this->max_storage_mb,
            'max_users' => $this->max_users,
        ];
    }

    /**
     * Compare with another plan
     */
    public function isHigherThan(Plan $other): bool
    {
        return $this->price_monthly > $other->price_monthly;
    }
}
