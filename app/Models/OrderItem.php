<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi mass assignment
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'product_image',
        'product_weight',
        'price',
        'quantity',
        'subtotal',
    ];

    /**
     * Cast attributes ke tipe data tertentu
     */
    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantity' => 'integer',
        'product_weight' => 'integer',
    ];

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Relationship: OrderItem dimiliki oleh satu Order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship: OrderItem terkait dengan satu Product
     * Note: Product bisa null jika sudah dihapus
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Format harga satuan dengan Rp
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Format subtotal dengan Rp
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    /**
     * Get total weight (weight * quantity)
     */
    public function getTotalWeightAttribute(): int
    {
        return $this->product_weight * $this->quantity;
    }

    /**
     * Get formatted weight
     */
    public function getFormattedWeightAttribute(): string
    {
        $weight = $this->product_weight;

        if ($weight >= 1000) {
            return number_format($weight / 1000, 2) . ' kg';
        }

        return $weight . ' g';
    }

    /**
     * Get product image URL
     */
    public function getImageUrlAttribute(): ?string
    {
        if ($this->product_image) {
            // Jika path lengkap
            if (str_starts_with($this->product_image, 'http')) {
                return $this->product_image;
            }
            // Jika path relatif
            return asset('storage/' . $this->product_image);
        }

        return null;
    }

    // ==========================================
    // STATIC METHODS
    // ==========================================

    /**
     * Create dari CartItem dengan product snapshot
     */
    public static function createFromCartItem($cartItem): self
    {
        $product = $cartItem->product;

        return self::create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'product_image' => $product->image,
            'product_weight' => $product->weight ?? 0,
            'price' => $cartItem->price,
            'quantity' => $cartItem->quantity,
            'subtotal' => $cartItem->price * $cartItem->quantity,
        ]);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Calculate subtotal (price * quantity)
     */
    public function calculateSubtotal(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Check apakah product masih tersedia
     */
    public function isProductAvailable(): bool
    {
        return $this->product !== null && $this->product->is_active;
    }

    /**
     * Get product info array (untuk display)
     */
    public function getProductInfo(): array
    {
        return [
            'id' => $this->product_id,
            'name' => $this->product_name,
            'sku' => $this->product_sku,
            'image' => $this->image_url,
            'weight' => $this->product_weight,
            'formatted_weight' => $this->formatted_weight,
        ];
    }
}
