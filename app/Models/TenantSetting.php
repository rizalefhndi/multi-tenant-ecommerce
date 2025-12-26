<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class TenantSetting extends Model
{
    /**
     * Kolom yang boleh diisi mass assignment
     */
    protected $fillable = [
        'group',
        'key',
        'value',
        'type',
    ];

    // ==========================================
    // TYPE CONSTANTS
    // ==========================================

    const TYPE_STRING = 'string';
    const TYPE_INTEGER = 'integer';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_JSON = 'json';
    const TYPE_ARRAY = 'array';

    // ==========================================
    // GROUP CONSTANTS
    // ==========================================

    const GROUP_GENERAL = 'general';
    const GROUP_THEME = 'theme';
    const GROUP_STORE = 'store';
    const GROUP_PAYMENT = 'payment';
    const GROUP_SHIPPING = 'shipping';

    // ==========================================
    // ACCESSORS
    // ==========================================

    /**
     * Get typed value
     */
    public function getTypedValueAttribute()
    {
        return $this->castValue($this->value, $this->type);
    }

    /**
     * Cast value based on type
     */
    protected function castValue($value, string $type)
    {
        if ($value === null) {
            return null;
        }

        return match($type) {
            self::TYPE_INTEGER => (int) $value,
            self::TYPE_BOOLEAN => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            self::TYPE_JSON, self::TYPE_ARRAY => json_decode($value, true),
            default => $value,
        };
    }

    // ==========================================
    // STATIC HELPER METHODS
    // ==========================================

    /**
     * Get setting value by key
     */
    public static function getValue(string $key, $default = null, ?string $group = null)
    {
        $cacheKey = self::getCacheKey($key, $group);

        return Cache::remember($cacheKey, 3600, function () use ($key, $default, $group) {
            $query = self::where('key', $key);

            if ($group) {
                $query->where('group', $group);
            }

            $setting = $query->first();

            if (!$setting) {
                return $default;
            }

            return $setting->typed_value ?? $default;
        });
    }

    /**
     * Set setting value
     */
    public static function setValue(string $key, $value, string $group = 'general', string $type = 'string'): self
    {
        // Convert array/json to string
        if (is_array($value)) {
            $value = json_encode($value);
            $type = self::TYPE_JSON;
        } elseif (is_bool($value)) {
            $value = $value ? '1' : '0';
            $type = self::TYPE_BOOLEAN;
        }

        $setting = self::updateOrCreate(
            ['group' => $group, 'key' => $key],
            ['value' => $value, 'type' => $type]
        );

        // Clear cache
        Cache::forget(self::getCacheKey($key, $group));
        Cache::forget(self::getCacheKey($key, null));

        return $setting;
    }

    /**
     * Get all settings by group
     */
    public static function getByGroup(string $group): array
    {
        $cacheKey = 'tenant_settings_group_' . $group;

        return Cache::remember($cacheKey, 3600, function () use ($group) {
            return self::where('group', $group)
                ->get()
                ->mapWithKeys(fn($s) => [$s->key => $s->typed_value])
                ->all();
        });
    }

    /**
     * Set multiple settings at once
     */
    public static function setMultiple(array $settings, string $group = 'general'): void
    {
        foreach ($settings as $key => $value) {
            self::setValue($key, $value, $group);
        }

        // Clear group cache
        Cache::forget('tenant_settings_group_' . $group);
    }

    /**
     * Get all settings as flat array
     */
    public static function getAllSettings(): array
    {
        return Cache::remember('tenant_settings_all', 3600, function () {
            return self::all()
                ->mapWithKeys(fn($s) => [$s->group . '.' . $s->key => $s->typed_value])
                ->all();
        });
    }

    /**
     * Delete setting
     */
    public static function deleteSetting(string $key, ?string $group = null): bool
    {
        $query = self::where('key', $key);

        if ($group) {
            $query->where('group', $group);
        }

        $deleted = $query->delete();

        // Clear caches
        Cache::forget(self::getCacheKey($key, $group));
        Cache::forget('tenant_settings_all');
        if ($group) {
            Cache::forget('tenant_settings_group_' . $group);
        }

        return $deleted > 0;
    }

    /**
     * Get cache key
     */
    protected static function getCacheKey(string $key, ?string $group): string
    {
        return 'tenant_setting_' . ($group ? $group . '_' : '') . $key;
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget('tenant_settings_all');
        
        foreach ([self::GROUP_GENERAL, self::GROUP_THEME, self::GROUP_STORE, self::GROUP_PAYMENT, self::GROUP_SHIPPING] as $group) {
            Cache::forget('tenant_settings_group_' . $group);
        }
    }

    // ==========================================
    // DEFAULT SETTINGS
    // ==========================================

    /**
     * Get default settings
     */
    public static function getDefaults(): array
    {
        return [
            self::GROUP_THEME => [
                'primary_color' => '#6366f1',
                'secondary_color' => '#8b5cf6',
                'accent_color' => '#f59e0b',
                'background_color' => '#ffffff',
                'text_color' => '#1f2937',
                'font_family' => 'Inter',
                'logo_url' => null,
                'favicon_url' => null,
                'banner_url' => null,
                'dark_mode' => false,
            ],
            self::GROUP_STORE => [
                'store_name' => null,
                'store_description' => null,
                'store_tagline' => null,
                'store_email' => null,
                'store_phone' => null,
                'store_address' => null,
                'store_city_id' => null,
                'store_province_id' => null,
                'instagram' => null,
                'facebook' => null,
                'whatsapp' => null,
                'twitter' => null,
                'tiktok' => null,
            ],
            self::GROUP_PAYMENT => [
                'enabled_payment_methods' => ['bank_transfer'],
                'bank_name' => null,
                'bank_account_number' => null,
                'bank_account_holder' => null,
                'midtrans_enabled' => false,
            ],
            self::GROUP_SHIPPING => [
                'origin_city_id' => null,
                'enabled_couriers' => ['jne', 'tiki', 'pos'],
                'free_shipping_min_order' => 0,
            ],
        ];
    }

    /**
     * Initialize default settings for new tenant
     */
    public static function initializeDefaults(): void
    {
        foreach (self::getDefaults() as $group => $settings) {
            foreach ($settings as $key => $value) {
                // Only set if not exists
                if (!self::where('group', $group)->where('key', $key)->exists()) {
                    self::setValue($key, $value, $group);
                }
            }
        }
    }
}
