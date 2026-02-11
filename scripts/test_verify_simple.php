<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ActualInventory, App\Models\User;

echo "Getting test data...\n";
$user = User::first();
$inventory = ActualInventory::first();

if (!$inventory) {
    echo "No inventory records found\n";
    exit;
}

echo "Inventory: {$inventory->tag_number}\n";
echo "Status before: {$inventory->status}\n";
echo "Verified At before: {$inventory->verified_at}\n\n";

echo "Calling markVerified({$user->id})...\n";
try {
    $inventory->markVerified($user->id);
    echo "✓ markVerified completed\n";
    echo "Status after: {$inventory->status}\n";
    echo "Verified At after: {$inventory->verified_at}\n";
    echo "Verified By: {$inventory->verified_by}\n";
} catch(\Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n";
    echo "Trace: {$e->getTraceAsString()}\n";
}
