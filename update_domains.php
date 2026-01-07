<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Update all domains from .localhost to .onyx.test
$updated = DB::table('domains')
    ->where('domain', 'LIKE', '%.localhost')
    ->update([
        'domain' => DB::raw("REPLACE(domain, '.localhost', '.onyx.test')")
    ]);

echo "Updated $updated domain(s) from .localhost to .onyx.test\n\n";

// Show current domains
echo "Current domains:\n";
$domains = DB::table('domains')->get();
foreach ($domains as $d) {
    echo "  - {$d->domain}\n";
}
