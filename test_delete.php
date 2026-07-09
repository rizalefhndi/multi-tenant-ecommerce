<?php
// test_delete.php
$t = App\Models\Tenant::find('renjana2');
if ($t) {
    echo "Deleting tenant 'renjana2'...\n";
    try {
        $t->delete();
        echo "Deleted.\n";
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
} else {
    echo "Tenant 'renjana2' not found.\n";
}
