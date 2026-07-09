<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$orderId = 4;

// Loop through tenants
$tenants = \App\Models\Tenant::all();
$found = false;

foreach ($tenants as $tenant) {
    tenancy()->initialize($tenant);
    
    $order = \App\Models\Order::find($orderId);
    
    if ($order) {
        echo "Order ditemukan di tenant: " . $tenant->id . "\n";
        echo "  ID: " . $order->id . "\n";
        echo "  Status Awal: " . $order->status . "\n";
        
        $order->status = 'processing';
        $order->paid_at = now();
        $order->save();
        
        echo "Status berhasil diupdate!\n";
        echo "  Status Baru: " . $order->fresh()->status . "\n";
        
        $found = true;
        break;
    }
}

if (!$found) {
    echo "Order ID $orderId tidak ditemukan di semua tenant.\n";
}
