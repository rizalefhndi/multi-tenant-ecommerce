<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk integrasi Midtrans Payment Gateway
    |
    */

    // Server Key - dapatkan dari dashboard Midtrans
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),

    // Client Key - untuk Snap.js di frontend
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),

    // Merchant ID
    'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),

    // Environment: sandbox atau production
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),

    // Aktifkan sanitization
    'is_sanitized' => env('MIDTRANS_IS_SANITIZED', true),

    // Aktifkan 3DS untuk kartu kredit
    'is_3ds' => env('MIDTRANS_IS_3DS', true),

    // Snap API URL
    'snap_url' => env('MIDTRANS_IS_PRODUCTION', false)
        ? 'https://app.midtrans.com/snap/snap.js'
        : 'https://app.sandbox.midtrans.com/snap/snap.js',

    // Webhook/Notification URL
    'notification_url' => env('MIDTRANS_NOTIFICATION_URL'),

    // Finish redirect URL
    'finish_url' => env('MIDTRANS_FINISH_URL'),

    // Unfinish redirect URL
    'unfinish_url' => env('MIDTRANS_UNFINISH_URL'),

    // Error redirect URL
    'error_url' => env('MIDTRANS_ERROR_URL'),

    // Default expiry duration (dalam menit)
    'expiry_duration' => env('MIDTRANS_EXPIRY_DURATION', 1440), // 24 jam

    // Enabled payment types
    'enabled_payments' => [
        'credit_card',
        'bca_va',
        'bni_va',
        'bri_va',
        'permata_va',
        'other_va',
        'gopay',
        'shopeepay',
        'qris',
    ],

    // Credit card options
    'credit_card' => [
        'secure' => true,
        'channel' => 'migs', // migs, cybersource, braintree
        'bank' => null, // acquiring bank
        'installment' => [
            'required' => false,
            'terms' => [
                'bni' => [3, 6, 12],
                'mandiri' => [3, 6, 12],
                'cimb' => [3, 6, 12],
                'bca' => [3, 6, 12],
            ],
        ],
    ],
];
