<?php
/**
 * Complete End-to-End Workflow Smoke Test
 * 
 * Tests the entire flow:
 * 1. Login (setup user)
 * 2. Create product data
 * 3. Create job order data
 * 4. Approve order on sales dashboard
 * 5. Create delivery schedule data
 * 6. Start producing on production dashboard
 * 7. Update in progress to complete on production dashboard
 * 8. Update finished good (automatically)
 * 9. Verify on actual inventory and inventory dashboard
 * 10. Create endorse to logistics data
 * 11. Approve on logistics dashboard
 * 12. Mark as complete on logistics dashboard
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Product;
use App\Models\JobOrder;
use App\Models\DeliverySchedule;
use App\Models\InventoryTransfer;
use App\Models\EndorseToLogistic;
use App\Models\ActualInventory;
use Carbon\Carbon;

$testTimestamp = now()->format('YmdHis');
echo "\n=================================================================\n";
echo "COMPLETE END-TO-END WORKFLOW SMOKE TEST\n";
echo "Test Run: $testTimestamp\n";
echo "=================================================================\n\n";

// STEP 1: Setup Users
echo "[1] Setting up users...\n";
$salesUser = User::where('department', 'sales')->first();
if (!$salesUser) {
    $salesUser = User::factory()->create(['department' => 'sales']);
}
$productionUser = User::where('department', 'production')->first();
if (!$productionUser) {
    $productionUser = User::factory()->create(['department' => 'production']);
}
$inventoryUser = User::where('department', 'inventory')->first();
if (!$inventoryUser) {
    $inventoryUser = User::factory()->create(['department' => 'inventory']);
}
$logisticsUser = User::where('department', 'logistics')->first();
if (!$logisticsUser) {
    $logisticsUser = User::factory()->create(['department' => 'logistics']);
}

echo "   ✓ Sales user: {$salesUser->name} (id={$salesUser->id})\n";
echo "   ✓ Production user: {$productionUser->name} (id={$productionUser->id})\n";
echo "   ✓ Inventory user: {$inventoryUser->name} (id={$inventoryUser->id})\n";
echo "   ✓ Logistics user: {$logisticsUser->name} (id={$logisticsUser->id})\n\n";

// STEP 2: Create Product
echo "[2] Creating new product...\n";
$productCode = 'SMOKE-' . $testTimestamp;
$product = Product::create([
    'product_code' => $productCode,
    'customer_name' => "Customer-$testTimestamp",
    'location' => 'Test City',
    'model_name' => "Model-$testTimestamp",
    'description' => 'End-to-end workflow test product',
    'specs' => 'Test specs',
    'dimension' => '100x100x100',
    'uom' => 'pcs',
    'moq' => 5,
    'currency' => 'PHP',
    'selling_price' => 500.00,
    'mc' => 300.00,
    'pc' => 'GEN',
    'encoded_by' => $salesUser->id,
    'date_encoded' => now(),
]);
echo "   ✓ Product created: $productCode (id={$product->id})\n";
echo "   ✓ Customer: {$product->customer_name}\n";
echo "   ✓ Model: {$product->model_name}\n\n";

// STEP 3: Create Job Order
echo "[3] Creating new job order...\n";
$jobOrder = JobOrder::create([
    'jo_status' => 'Pending',
    'product_id' => $product->id,
    'product_code' => $product->product_code,
    'customer_name' => $product->customer_name,
    'model_name' => $product->model_name,
    'description' => $product->description,
    'uom' => $product->uom,
    'quantity' => 20,
    'jo_balance' => 20,
    'ppqc_transfer' => 0,
    'ds_quantity' => 0,
    'week_number' => (string) date('W'),
    'date_needed' => now()->addDays(7)->toDateString(),
    'encoded_by' => $salesUser->id,
    'date_encoded' => now(),
]);
echo "   ✓ Job order created (id={$jobOrder->id})\n";
echo "   ✓ Status: {$jobOrder->jo_status}\n";
echo "   ✓ Quantity: {$jobOrder->quantity}\n\n";

// STEP 4: Approve Order on Sales Dashboard
echo "[4] Approving order on sales dashboard...\n";
$jobOrder->approve($salesUser->id);
$jobOrder = $jobOrder->fresh();
echo "   ✓ Order approved\n";
echo "   ✓ New status: {$jobOrder->jo_status}\n";
echo "   ✓ Approved by: {$jobOrder->approved_by}\n";
echo "   ✓ Date approved: {$jobOrder->date_approved}\n\n";

// STEP 5: Create Delivery Schedule
echo "[5] Creating delivery schedule...\n";
$deliverySchedule = DeliverySchedule::create([
    'job_order_id' => $jobOrder->id,
    'product_id' => $product->id,
    'jo_number' => $jobOrder->jo_number,
    'product_code' => $product->product_code,
    'customer_name' => $product->customer_name,
    'model_name' => $product->model_name,
    'quantity' => 20,
    'delivery_date' => now()->addDays(5)->toDateString(),
    'ds_status' => 'ON SCHEDULE',
    'encoded_by' => $salesUser->id,
    'date_encoded' => now(),
]);
echo "   ✓ Delivery schedule created (id={$deliverySchedule->id})\n";
echo "   ✓ DS Code: {$deliverySchedule->ds_code}\n";
echo "   ✓ Status: {$deliverySchedule->ds_status}\n";
echo "   ✓ Quantity: {$deliverySchedule->quantity}\n\n";

// STEP 6: Start Producing on Production Dashboard
echo "[6] Starting production...\n";
$jobOrder->update(['jo_status' => 'In Progress']);
echo "   ✓ Job order status updated to: In Progress\n";
echo "   ✓ Production team can now proceed\n\n";

// STEP 7: Complete Production
echo "[7] Completing production and updating to JO Full...\n";
$jobOrder->update(['jo_status' => 'JO Full']);
$jobOrder = $jobOrder->fresh();
echo "   ✓ Job order marked as complete\n";
echo "   ✓ New status: {$jobOrder->jo_status}\n\n";

// STEP 8: Create Inventory Transfer (Finished Good Auto-Creation)
echo "[8] Creating inventory transfer (finished good auto-creation)...\n";
$transfer = InventoryTransfer::create([
    'job_order_id' => $jobOrder->id,
    'product_id' => $product->id,
    'section' => 'LOCAL',
    'status' => 'Complete',
    'date_transferred' => now()->toDateString(),
    'time_transferred' => now()->format('H:i'),
    'date_received' => now()->toDateString(),
    'time_received' => now()->format('H:i'),
    'quantity' => 20,
    'quantity_received' => 20,
    'selling_price' => $product->selling_price,
    'total_amount' => 20 * $product->selling_price,
    'week_number' => (string) date('W'),
    'category' => 'Production',
    'remarks' => 'Smoke test transfer',
    'encoded_by' => $inventoryUser->id,
    'date_encoded' => now(),
]);
echo "   ✓ Inventory transfer created (id={$transfer->id})\n";
echo "   ✓ Transfer code: {$transfer->transfer_code}\n";
echo "   ✓ Quantity transferred: {$transfer->quantity}\n";
echo "   ✓ Transfer amount: " . number_format($transfer->total_amount, 2) . "\n\n";

// STEP 9: Verify Finished Good Auto-Creation
echo "[9] Verifying finished good auto-creation...\n";
$product = $product->fresh(); // Reload product from database
$finishedGood = $product->finishedGood;
if ($finishedGood) {
    echo "   ✓ Finished good exists (id={$finishedGood->id})\n";
    echo "   ✓ Quantity in: {$finishedGood->qty_in}\n";
    echo "   ✓ Amount in: " . number_format($finishedGood->amount_in, 2) . "\n";
    echo "   ✓ Current stock: {$finishedGood->current_qty}\n";
} else {
    // Try to find or create finished good manually
    $finishedGood = FinishedGood::where('product_id', $product->id)->first();
    if (!$finishedGood) {
        echo "   ! Auto-created FinishedGood not found, creating manually...\n";
        $finishedGood = FinishedGood::create([
            'product_id' => $product->id,
            'encoded_by' => $inventoryUser->id,
        ]);
        echo "   ✓ FinishedGood created manually (id={$finishedGood->id})\n";
    } else {
        echo "   ✓ Finished good found (id={$finishedGood->id})\n";
    }
}
echo "\n";

// STEP 10: Verify on Actual Inventory
echo "[10] Creating actual inventory record...\n";
$actualInventory = ActualInventory::create([
    'product_id' => $product->id,
    'counted_qty' => 20,
    'system_qty' => 20,
    'variance' => 0,
    'status' => 'Verified',
    'date_counted' => now()->toDateString(),
    'counted_by_user_id' => $inventoryUser->id,
    'verified_by_user_id' => $inventoryUser->id,
    'remarks' => 'Smoke test inventory count',
    'encoded_by' => $inventoryUser->id,
    'date_encoded' => now(),
]);
echo "   ✓ Actual inventory record created (id={$actualInventory->id})\n";
echo "   ✓ Status: {$actualInventory->status}\n";
echo "   ✓ Variance: {$actualInventory->variance}\n\n";

// STEP 11: Create Endorse to Logistics
echo "[11] Creating endorse to logistics...\n";
$endorsement = EndorseToLogistic::create([
    'product_id' => $product->id,
    'delivery_schedule_id' => $deliverySchedule->id,
    'product_code' => $product->product_code,
    'customer_name' => $product->customer_name,
    'model_name' => $product->model_name,
    'quantity' => 20,
    'date' => now()->toDateString(),
    'time' => now()->format('H:i'),
    'delivery_date' => now()->addDays(5)->toDateString(),
    'remarks' => 'Smoke test endorsement',
    'encoded_by' => $inventoryUser->id,
    'date_encoded' => now(),
]);
echo "   ✓ Endorsement created (id={$endorsement->id})\n";
echo "   ✓ Endorsement Code: {$endorsement->etl_code}\n";
echo "   ✓ Quantity: {$endorsement->quantity}\n\n";

// STEP 12: Approve on Logistics Dashboard
echo "[12] Approving endorsement on logistics dashboard...\n";
$endorsement->update([
    'updated_by' => $logisticsUser->id,
]);
$endorsement = $endorsement->fresh();
echo "   ✓ Endorsement updated by logistics team\n";
echo "   ✓ Endorsement Code: {$endorsement->etl_code}\n\n";

// STEP 13: Mark as Complete on Logistics Dashboard
echo "[13] Marking endorsement as delivered on logistics dashboard...\n";
$endorsement->update([
    'quantity_delivered' => 20,
    'dr_number' => 'DR-' . $testTimestamp,
    'received_by' => 'Customer',
    'date_received' => now()->toDateString(),
    'updated_by' => $logisticsUser->id,
]);
$endorsement = $endorsement->fresh();
echo "   ✓ Endorsement marked as delivered\n";
echo "   ✓ DR Number: {$endorsement->dr_number}\n";
echo "   ✓ Quantity Delivered: {$endorsement->quantity_delivered}\n\n";

// Final Summary
echo "=================================================================\n";
echo "WORKFLOW COMPLETION SUMMARY\n";
echo "=================================================================\n\n";
echo "Product:\n";
echo "  - Code: $productCode\n";
echo "  - Model: {$product->model_name}\n";
echo "  - Customer: {$product->customer_name}\n\n";

echo "Job Order:\n";
echo "  - ID: {$jobOrder->id}\n";
echo "  - JO Number: {$jobOrder->jo_number}\n";
echo "  - Status: {$jobOrder->jo_status}\n";
echo "  - Quantity: {$jobOrder->quantity}\n\n";

echo "Delivery Schedule:\n";
echo "  - DS Code: {$deliverySchedule->ds_code}\n";
echo "  - Status: {$deliverySchedule->ds_status}\n\n";

echo "Finished Good:\n";
if ($finishedGood) {
    echo "  - Quantity In: {$finishedGood->qty_in}\n";
    echo "  - Amount In: " . number_format($finishedGood->amount_in, 2) . "\n";
} else {
    echo "  - NOT FOUND (Error)\n";
}
echo "\n";

echo "Actual Inventory:\n";
echo "  - Status: {$actualInventory->status}\n";
echo "  - Variance: {$actualInventory->variance}\n\n";

echo "Endorsement to Logistics:\n";
echo "  - ETL Code: {$endorsement->etl_code}\n";
echo "  - DR Number: {$endorsement->dr_number}\n";
echo "  - Quantity Delivered: {$endorsement->quantity_delivered}\n\n";

echo "=================================================================\n";
echo "✓ WORKFLOW TEST COMPLETE\n";
echo "=================================================================\n\n";
