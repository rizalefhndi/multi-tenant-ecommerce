<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\CartItem;
use App\Models\OrderItem;

class Product extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi mass assignment
     */
    protected $fillable = [
        'sku',
        'name',
        'description',
        'price',
        'stock',
        'weight',
        'length',
        'width',
        'height',
        'image',
        'is_active',
    ];

    /**
     * Cast attributes ke tipe data tertentu
     */
    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
        'weight' => 'integer',
        'length' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'is_active' => 'boolean',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Relationship: Product memiliki banyak CartItem
     *
     * Satu product bisa ada di banyak cart (melalui cart_items)
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Relationship: Product memiliki banyak OrderItem
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Query hanya produk yang aktif
     *
     * Usage: Product::active()->get();
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Query produk yang stock-nya masih ada
     *
     * Usage: Product::inStock()->get();
     */
    public function scopeInStock($query)
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Scope: Query produk yang tersedia (aktif & ada stock)
     */
    public function scopeAvailable($query)
    {
        return $query->active()->inStock();
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Accessor: Format harga dengan Rp
     *
     * Usage: $product->formatted_price
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Accessor: Format weight
     *
     * Usage: $product->formatted_weight
     */
    public function getFormattedWeightAttribute(): string
    {
        if ($this->weight >= 1000) {
            return number_format($this->weight / 1000, 2) . ' kg';
        }
        return ($this->weight ?? 0) . ' g';
    }

    /**
     * Accessor: Get dimensions string
     *
     * Usage: $product->dimensions
     */
    public function getDimensionsAttribute(): ?string
    {
        if ($this->length && $this->width && $this->height) {
            return "{$this->length} x {$this->width} x {$this->height} cm";
        }
        return null;
    }

    /**
     * Accessor: Get image URL
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            if (str_starts_with($this->image, 'http')) {
                return $this->image;
            }
            return asset('storage/' . $this->image);
        }
        return null;
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Check apakah produk masih tersedia
     */
    public function isAvailable(): bool
    {
        return $this->is_active && $this->stock > 0;
    }

    /**
     * Check apakah stok mencukupi
     */
    public function hasStock(int $quantity = 1): bool
    {
        return $this->stock >= $quantity;
    }

    /**
     * Kurangi stock ketika dibeli
     */
    public function decreaseStock(int $quantity): bool
    {
        if ($this->stock >= $quantity) {
            $this->decrement('stock', $quantity);
            return true;
        }
        return false;
    }

    /**
     * Tambah stock (untuk restocking atau cancel order)
     */
    public function increaseStock(int $quantity): void
    {
        $this->increment('stock', $quantity);
    }

    /**
     * Generate SKU jika kosong
     */
    public function generateSku(): string
    {
        if ($this->sku) {
            return $this->sku;
        }

        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $this->name), 0, 3));
        $suffix = strtoupper(substr(uniqid(), -5));

        return "{$prefix}-{$suffix}";
    }

    /**
     * Get volumetric weight (untuk shipping)
     * Formula: (P x L x T) / 6000
     */
    public function getVolumetricWeight(): int
    {
        if (!$this->length || !$this->width || !$this->height) {
            return $this->weight ?? 0;
        }

        $volumetric = ($this->length * $this->width * $this->height) / 6000;

        // Return yang lebih besar antara actual weight dan volumetric
        return max($this->weight ?? 0, (int) ceil($volumetric * 1000));
    }

    /**
     * Convert to snapshot array (untuk OrderItem)
     */
    public function toSnapshot(): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'image' => $this->image,
            'weight' => $this->weight,
            'price' => $this->price,
        ];
    }
}

