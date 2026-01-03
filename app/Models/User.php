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

    // Role constants
    const ROLE_ADMIN = 'admin';
    const ROLE_CUSTOMER = 'customer';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
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

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Get tenants owned by this user
     */
    public function ownedTenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'owner_id');
    }

    // ==========================================
    // ROLE METHODS
    // ==========================================

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is customer
     */
    public function isCustomer(): bool
    {
        return $this->role === self::ROLE_CUSTOMER;
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
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

    // ==========================================
    // ORDER & ADDRESS RELATIONSHIPS
    // ==========================================

    /**
     * Relationship: User memiliki banyak Address
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(UserAddress::class);
    }

    /**
     * Relationship: User memiliki satu default address
     */
    public function defaultAddress(): HasOne
    {
        return $this->hasOne(UserAddress::class)
            ->where('is_default', true);
    }

    /**
     * Relationship: User memiliki banyak Order
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Relationship: User memiliki banyak Transaction (through orders)
     */
    public function transactions(): HasMany
    {
        return $this->hasManyThrough(Transaction::class, Order::class);
    }

    // ==========================================
    // HELPER METHODS
    // ==========================================

    /**
     * Get default address atau address pertama
     */
    public function getDefaultAddress(): ?UserAddress
    {
        return $this->defaultAddress ?? $this->addresses()->first();
    }

    /**
     * Check apakah user memiliki address
     */
    public function hasAddress(): bool
    {
        return $this->addresses()->exists();
    }

    /**
     * Get total orders count
     */
    public function getOrdersCount(): int
    {
        return $this->orders()->count();
    }

    /**
     * Get pending orders
     */
    public function getPendingOrders()
    {
        return $this->orders()->pending()->get();
    }

    /**
     * Get completed orders
     */
    public function getCompletedOrders()
    {
        return $this->orders()->delivered()->get();
    }
}
