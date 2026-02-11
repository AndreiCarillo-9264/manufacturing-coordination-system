<?php
/**
 * Test: Finished Good Auto-Creation
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\FinishedGood;
use App\Models\User;

echo "\n" . str_repeat("=", 80) . "\n";
echo "TESTING: Finished Good Auto-Creation\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $user = User::first();
    
    // Create a product
    echo "Creating product...\n";
    $product = Product::create([
        'product_code' => 'TEST-FG-' . time(),
        'customer_name' => "TestCustomer-" . time(),
        'location' => 'Test Location',
        'model_name' => "TestModel-" . time(),
        'description' => 'Test Product',
        'specs' => 'Test Specs',
        'dimension' => '100x100x100',
        'uom' => 'pcs',
        'moq' => 5,
        'currency' => 'PHP',
        'selling_price' => 1000.00,
        'mc' => 600.00,
        'pc' => 'GEN',
        'encoded_by' => $user->id,
        'date_encoded' => now(),
    ]);
    echo "✓ Product created: {$product->id}\n\n";
    
    // Check if finished good was auto-created
    echo "Checking for auto-created FinishedGood...\n";
    
    // Method 1: Fresh query
    $fgCount = FinishedGood::where('product_id', $product->id)->count();
    echo "  - Fresh query count: $fgCount\n";
    
    // Method 2: Relationship
    $fg = $product->finishedGood;
    if ($fg) {
        echo "  - Relationship check: ✓ FOUND\n";
        echo "    * FinishedGood ID: {$fg->id}\n";
        echo "    * Product ID: {$fg->product_id}\n";
    } else {
        echo "  - Relationship check: ✗ NOT FOUND\n";
    }
    
    // Method 3: Direct all() check
    $allFgs = FinishedGood::all();
    $productFgs = $allFgs->where('product_id', $product->id);
    echo "  - All finished goods count: " . $allFgs->count() . "\n";
    echo "  - For this product: " . $productFgs->count() . "\n";
    
    if ($fgCount === 0) {
        echo "\n✗ ISSUE: FinishedGood was NOT auto-created!\n";
        echo "\nPossible Causes:\n";
        echo "1. The created() callback in Product model isn't firing\n";
        echo "2. The check `if (!$product->finishedGood)` is failing\n";
        echo "3. The FinishedGood::create() failed silently\n";
        echo "\n";
    } else {
        echo "\n✓ SUCCESS: FinishedGood was auto-created!\n";
    }
    
    echo "\n" . str_repeat("=", 80) . "\n";
    
} catch (\Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}\n";
    echo "Line: {$e->getLine()}\n";
    echo "\nStack Trace:\n";
    echo $e->getTraceAsString();
}
