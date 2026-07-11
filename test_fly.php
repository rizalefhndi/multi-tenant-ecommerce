<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Schemas: " . PHP_EOL;
$schemas = DB::select("SELECT schema_name FROM information_schema.schemata");
foreach ($schemas as $s) {
    echo $s->schema_name . PHP_EOL;
}
