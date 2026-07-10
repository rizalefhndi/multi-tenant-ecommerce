<?php

$d = \Stancl\Tenancy\Database\Models\Domain::first();
if ($d) {
    $d->domain = 'onyx-app.fly.dev';
    $d->save();
    echo "Domain updated successfully!";
} else {
    echo "No domain found.";
}
