<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Komerce API Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk integrasi Komerce API (pengganti RajaOngkir)
    | Dapatkan API key dari https://collaborator.komerce.id
    |
    */

    // API Key dari Komerce (Shipping Cost)
    'api_key' => env('KOMERCE_API_KEY', ''),

    // Base URL untuk Komerce API V2 (RajaOngkir endpoints)
    'base_url' => env('KOMERCE_BASE_URL', 'https://rajaongkir.komerce.id/api/v1'),

    // Default origin (city_id / district_id toko)
    'origin' => env('KOMERCE_ORIGIN', ''),

    // Origin type: city atau subdistrict
    'origin_type' => env('KOMERCE_ORIGIN_TYPE', 'city'),

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
        'jnt' => [
            'code' => 'jnt',
            'name' => 'J&T Express',
            'enabled' => true, 
            'logo' => '/images/couriers/jnt.png',
        ],
        'sicepat' => [
            'code' => 'sicepat',
            'name' => 'SiCepat',
            'enabled' => true,
            'logo' => '/images/couriers/sicepat.png',
        ],
        'anteraja' => [
            'code' => 'anteraja',
            'name' => 'AnterAja',
            'enabled' => true,
            'logo' => '/images/couriers/anteraja.png',
        ],
    ],

    // Cache settings
    'cache' => [
        'enabled' => true,
        'prefix' => 'komerce_',
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
