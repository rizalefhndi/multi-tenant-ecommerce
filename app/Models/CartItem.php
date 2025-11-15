<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi mass assignment
     */
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price',
    ];

    /**
     * Cast attributes ke tipe data tertentu
     */
    protected $casts = [
        'cart_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    /**
     * Relationship: CartItem dimiliki oleh satu Cart
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /**
     * Relationship: CartItem merujuk ke satu Product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Hitung subtotal (harga x quantity)
     *
     * Usage: $cartItem->subtotal
     */
    public function getSubtotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Format subtotal dengan Rp
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    /**
     * Format harga dengan Rp
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }

    /**
     * Update quantity
     */
    public function updateQuantity(int $quantity): bool
    {
        if ($quantity <= 0) {
            return false;
        }

        return $this->update(['quantity' => $quantity]);
    }

    /**
     * Increment quantity
     */
    public function incrementQuantity(int $amount = 1): void
    {
        $this->increment('quantity', $amount);
    }

    /**
     * Decrement quantity
     */
    public function decrementQuantity(int $amount = 1): bool
    {
        if ($this->quantity > $amount) {
            $this->decrement('quantity', $amount);
            return true;
        }

        // Jika quantity akan jadi 0 atau kurang, hapus item
        $this->delete();
        return false;
    }
}
