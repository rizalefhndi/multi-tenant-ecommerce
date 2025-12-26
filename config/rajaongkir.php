<?php

return [
    /*
    |--------------------------------------------------------------------------
    | RajaOngkir Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk integrasi RajaOngkir API
    | Dapatkan API key dari https://rajaongkir.com
    |
    */

    // API Key dari RajaOngkir
    'api_key' => env('RAJAONGKIR_API_KEY', ''),

    // Account type: starter, basic, pro
    'account_type' => env('RAJAONGKIR_ACCOUNT_TYPE', 'starter'),

    // Base URL berdasarkan account type
    'base_url' => [
        'starter' => 'https://api.rajaongkir.com/starter',
        'basic' => 'https://api.rajaongkir.com/basic',
        'pro' => 'https://pro.rajaongkir.com/api',
    ],

    // Default origin (city_id toko)
    'origin' => env('RAJAONGKIR_ORIGIN', ''),

    // Origin type: city atau subdistrict (pro only)
    'origin_type' => env('RAJAONGKIR_ORIGIN_TYPE', 'city'),

    // Enabled couriers
    'couriers' => [
        'jne' => [
            'code' => 'jne',
            'name' => 'JNE',
            'enabled' => true,
            'logo' => '/images/couriers/jne.png',
        ],
        'tiki' => [
            'code' => 'tiki',
            'name' => 'TIKI',
            'enabled' => true,
            'logo' => '/images/couriers/tiki.png',
        ],
        'pos' => [
            'code' => 'pos',
            'name' => 'POS Indonesia',
            'enabled' => true,
            'logo' => '/images/couriers/pos.png',
        ],
        // Basic & Pro only
        'jnt' => [
            'code' => 'jnt',
            'name' => 'J&T Express',
            'enabled' => false, // Enable if using basic/pro
            'logo' => '/images/couriers/jnt.png',
        ],
        'sicepat' => [
            'code' => 'sicepat',
            'name' => 'SiCepat',
            'enabled' => false,
            'logo' => '/images/couriers/sicepat.png',
        ],
        'anteraja' => [
            'code' => 'anteraja',
            'name' => 'AnterAja',
            'enabled' => false,
            'logo' => '/images/couriers/anteraja.png',
        ],
    ],

    // Cache settings
    'cache' => [
        'enabled' => true,
        'prefix' => 'rajaongkir_',
        'ttl' => [
            'provinces' => 86400 * 7, // 7 days
            'cities' => 86400 * 7,    // 7 days
            'subdistricts' => 86400 * 7,
            'cost' => 3600,           // 1 hour
        ],
    ],

    // Fallback jika API error
    'fallback' => [
        'enabled' => true,
        'flat_rate' => 15000, // Flat rate jika tidak bisa get ongkir
    ],
];
