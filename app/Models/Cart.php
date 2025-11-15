<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi mass assignment
     */
    protected $fillable = [
        'user_id',
        'status',
    ];

    /**
     * Cast attributes ke tipe data tertentu
     */
    protected $casts = [
        'user_id' => 'integer',
    ];

    /**
     * Status cart yang valid
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ABANDONED = 'abandoned';

    /**
     * Relationship: Cart dimiliki oleh satu User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Cart memiliki banyak CartItem
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Scope: Query cart yang masih aktif
     *
     * Usage: Cart::active()->get();
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Hitung total harga semua item di cart
     *
     * Usage: $cart->total_price
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
        });
    }

    /**
     * Hitung total item di cart
     *
     * Usage: $cart->total_items
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    /**
     * Format total harga dengan Rp
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total_price, 0, ',', '.');
    }

    /**
     * Tambah product ke cart
     */
    public function addProduct(Product $product, int $quantity = 1): CartItem
    {
        // Cek apakah product sudah ada di cart
        $cartItem = $this->items()->where('product_id', $product->id)->first();

        if ($cartItem) {
            // Jika sudah ada, tambahkan quantity
            $cartItem->increment('quantity', $quantity);
            return $cartItem;
        }

        // Jika belum ada, buat cart item baru
        return $this->items()->create([
            'product_id' => $product->id,
            'quantity' => $quantity,
            'price' => $product->price,
        ]);
    }

    /**
     * Hapus semua item dari cart
     */
    public function clear(): void
    {
        $this->items()->delete();
    }

    /**
     * Tandai cart sebagai completed
     */
    public function markAsCompleted(): void
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
    }
}
