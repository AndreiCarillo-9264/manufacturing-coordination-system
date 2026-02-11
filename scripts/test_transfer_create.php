<?php
/**
 * Test: Inventory Transfer Creation
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JobOrder;
use App\Models\InventoryTransfer;
use App\Models\User;
use Carbon\Carbon;

echo "\n" . str_repeat("=", 80) . "\n";
echo "TESTING: Inventory Transfer Creation\n";
echo str_repeat("=", 80) . "\n\n";

try {
    $user = User::orderBy('id')->first();
    
    // Get any job order
    $jobOrder = JobOrder::with('product')
        ->first();
    
    if (!$jobOrder) {
        echo "! No job orders found. Cannot test.\n";
        throw new Exception("No job orders available for testing");
    }

    echo "Using Job Order: {$jobOrder->jo_number}\n";
    echo "Product ID: {$jobOrder->product_id}\n";
    echo "User ID: {$user->id}\n\n";

    // Attempt to create transfer
    echo "Creating transfer...\n";
    $transfer = InventoryTransfer::create([
        'job_order_id' => $jobOrder->id,
        'product_id' => $jobOrder->product_id,
        'section' => 'LOCAL',
        'category' => 'Final',
        'status' => 'Balance',
        'date_transferred' => now()->toDateString(),
        'time_transferred' => now()->format('H:i'),
        'quantity' => 10,
        'transfer_by' => 'Test User',
        'received_by_name' => 'Test Receiver',
        'received_by_user_id' => $user->id,
        'date_received' => now()->toDateString(),
        'time_received' => now()->format('H:i'),
        'quantity_received' => 10,
        'remarks' => 'Test transfer',
        'encoded_by' => $user->id,
        'date_encoded' => now(),
    ]);

    echo "✓ Transfer created successfully!\n";
    echo "  - Transfer ID: {$transfer->id}\n";
    echo "  - Transfer Code: {$transfer->transfer_code}\n";
    echo "  - PTT Number: {$transfer->ptt_number}\n";
    echo "\n" . str_repeat("=", 80) . "\n";

} catch (\Exception $e) {
    echo "✗ ERROR: {$e->getMessage()}\n";
    echo "File: {$e->getFile()}\n";
    echo "Line: {$e->getLine()}\n";
    echo "\nStack Trace:\n";
    echo $e->getTraceAsString();
    echo "\n";
}
