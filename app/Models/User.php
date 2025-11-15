<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relationship: User memiliki banyak Cart (history)
     */
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    /**
     * Relationship: User memiliki satu active cart
     */
    public function activeCart(): HasOne
    {
        return $this->hasOne(Cart::class)
            ->where('status', Cart::STATUS_ACTIVE)
            ->latestOfMany();
    }

    /**
     * Get atau create active cart untuk user
     */
    public function getOrCreateCart(): Cart
    {
        // Cari cart yang masih aktif
        $cart = $this->activeCart;

        // Jika tidak ada, buat baru
        if (!$cart) {
            $cart = $this->carts()->create([
                'status' => Cart::STATUS_ACTIVE,
            ]);
        }

        return $cart;
    }
}
