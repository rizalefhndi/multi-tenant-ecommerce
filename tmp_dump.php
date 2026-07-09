<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenant = \App\Models\Tenant::latest()->first();
if ($tenant) {
    file_put_contents('tenant_data.json', json_encode($tenant->toArray(), JSON_PRETTY_PRINT));
    echo "Dumped to tenant_data.json\n";
} else {
    echo "No tenants found.\n";
}
