<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class TenantLoginToken extends Model
{
    // Use central database connection (not tenant)
    protected $connection = 'pgsql';

    protected $fillable = [
        'token',
        'user_id',
        'tenant_id',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Relationship to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new login token for a user
     */
    public static function generateForUser(User $user, string $tenantId, int $expiryMinutes = 5): self
    {
        // Clean up expired tokens
        self::where('expires_at', '<', now())->delete();

        return self::create([
            'token' => Str::random(64),
            'user_id' => $user->id,
            'tenant_id' => $tenantId,
            'expires_at' => now()->addMinutes($expiryMinutes),
        ]);
    }

    /**
     * Find a valid token
     */
    public static function findValid(string $token): ?self
    {
        return self::where('token', $token)
            ->where('expires_at', '>', now())
            ->first();
    }

    /**
     * Check if token is valid
     */
    public function isValid(): bool
    {
        return $this->expires_at->isFuture();
    }

    /**
     * Consume the token (delete after use)
     */
    public function consume(): bool
    {
        return $this->delete();
    }
}
