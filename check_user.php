<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenant = App\Models\Tenant::find('testadminstore');

if (!$tenant) {
    echo "Tenant not found!\n";
    exit(1);
}

echo "Tenant: testadminstore\n\n";

$tenant->run(function() {
    $users = App\Models\User::all();
    
    if ($users->isEmpty()) {
        echo "No users found in tenant database!\n";
    } else {
        echo "Users in tenant:\n";
        foreach($users as $u) {
            echo "  - ID: {$u->id}\n";
            echo "    Email: {$u->email}\n";
            echo "    Role: {$u->role}\n";
            echo "    Password hash exists: " . (strlen($u->password) > 10 ? 'Yes' : 'No') . "\n\n";
        }
    }
});
