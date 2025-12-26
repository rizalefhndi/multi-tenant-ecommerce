<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserAddress extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh diisi mass assignment
     */
    protected $fillable = [
        'user_id',
        'label',
        'recipient_name',
        'phone',
        'address_line_1',
        'address_line_2',
        'city',
        'city_id',
        'province',
        'province_id',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'is_default',
    ];

    /**
     * Cast attributes ke tipe data tertentu
     */
    protected $casts = [
        'is_default' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Label presets yang tersedia
     */
    const LABEL_HOME = 'Home';
    const LABEL_OFFICE = 'Office';
    const LABEL_OTHER = 'Other';

    /**
     * Get available labels
     */
    public static function getLabels(): array
    {
        return [
            self::LABEL_HOME => 'Rumah',
            self::LABEL_OFFICE => 'Kantor',
            self::LABEL_OTHER => 'Lainnya',
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    /**
     * Relationship: Address dimiliki oleh satu User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Address digunakan di banyak Orders
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'address_id');
    }

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get alamat lengkap dalam satu string
     *
     * Usage: $address->full_address
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->province,
            $this->postal_code,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get short address (untuk display di list)
     *
     * Usage: $address->short_address
     */
    public function getShortAddressAttribute(): string
    {
        return "{$this->city}, {$this->province}";
    }

    /**
     * Get label dengan icon
     */
    public function getLabelIconAttribute(): string
    {
        return match ($this->label) {
            self::LABEL_HOME => '🏠',
            self::LABEL_OFFICE => '🏢',
            default => '📍',
        };
    }

    // ==========================================
    // SCOPES
    // ==========================================

    /**
     * Scope: Query hanya alamat default
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope: Query alamat milik user tertentu
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // ==========================================
    // METHODS
    // ==========================================

    /**
     * Set alamat ini sebagai default, unset yang lain
     */
    public function setAsDefault(): void
    {
        // Unset semua alamat default milik user ini
        self::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_default' => false]);

        // Set alamat ini sebagai default
        $this->update(['is_default' => true]);
    }

    /**
     * Convert ke array untuk snapshot (disimpan di order)
     */
    public function toSnapshot(): array
    {
        return [
            'label' => $this->label,
            'recipient_name' => $this->recipient_name,
            'phone' => $this->phone,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'city_id' => $this->city_id,
            'province' => $this->province,
            'province_id' => $this->province_id,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'full_address' => $this->full_address,
        ];
    }

    /**
     * Check apakah alamat lengkap untuk pengiriman
     */
    public function isCompleteForShipping(): bool
    {
        return !empty($this->recipient_name)
            && !empty($this->phone)
            && !empty($this->address_line_1)
            && !empty($this->city)
            && !empty($this->province)
            && !empty($this->postal_code);
    }
}
