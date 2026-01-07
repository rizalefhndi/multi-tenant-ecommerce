<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tenant = App\Models\Tenant::find('testadminstore');

$tenant->run(function() {
    $user = App\Models\User::where('email', 'testuser@example.com')->first();
    
    if ($user) {
        $user->password = bcrypt('password123');
        $user->save();
        echo "Password reset to 'password123' for testuser@example.com\n";
    } else {
        echo "User not found!\n";
    }
});
