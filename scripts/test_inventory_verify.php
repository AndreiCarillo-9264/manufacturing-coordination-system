<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ActualInventory, App\Models\Product, App\Models\User;

echo "================================================================================\n";
echo "TESTING: Inventory Verification Flow\n";
echo "================================================================================\n\n";

// Get a test user
$user = User::first();
if (!$user) {
    echo "✗ ERROR: No users found in database\n";
    exit;
}
echo "Using User: {$user->name} (ID: {$user->id})\n\n";

// Get a product
$product = Product::first();
if (!$product) {
    echo "✗ ERROR: No products found in database\n";
    exit;
}
echo "Using Product: {$product->product_code}\n\n";

// Create test inventory
echo "Creating inventory record...\n";
try {
    $inventory = ActualInventory::create([
        'tag_number' => 'TEST-' . now()->format('YmdHis'),
        'product_id' => $product->id,
        'product_code' => $product->product_code,
        'customer_name' => $product->customer_name,
        'model_name' => $product->model_name,
        'description' => $product->description,
        'dimension' => $product->dimension,
        'uom' => 'PC/S',
        'fg_quantity' => 50,
        'location' => 'Test Location',
        'counted_by' => $user->name,
        'counted_at' => now(),
        'status' => 'Counted',
        'encoded_by' => $user->id,
    ]);
    
    echo "✓ Inventory created: {$inventory->tag_number}\n";
    echo "  - Status: {$inventory->status}\n";
    echo "  - Counted At: {$inventory->counted_at}\n";
    echo "  - Counted By: {$inventory->counted_by}\n\n";
    
    // Test verification
    echo "Verifying inventory...\n";
    try {
        $inventory->markVerified($user->id);
        
        $inventory->refresh();
        echo "✓ Inventory verified successfully!\n";
        echo "  - Status: {$inventory->status}\n";
        echo "  - Verified At: {$inventory->verified_at}\n";
        echo "  - Verified By: {$inventory->verified_by}\n\n";
        
        // Cleanup
        $inventory->forceDelete();
        echo "✓ Test record cleaned up\n";
        
    } catch (\Exception $e) {
        echo "✗ ERROR: {$e->getMessage()}\n";
        $inventory->forceDelete();
        exit;
    }
    
} catch (\Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n";
    exit;
}

echo "\n================================================================================\n";
echo "✓ All verification tests passed!\n";
echo "================================================================================\n";
