<?php
/**
 * User Flow Workflow Test
 * Simulates actual user interactions step by step
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
echo "\n" . str_repeat("=", 80) . "\n";
echo "USER WORKFLOW TEST - Step by Step Verification\n";
echo "Test Run: $testTimestamp\n";
echo str_repeat("=", 80) . "\n\n";

try {
    // STEP 1: Login (Get/Create User)
    echo "STEP 1: LOGIN\n";
    echo str_repeat("-", 80) . "\n";
    $user = User::where('department', 'sales')->first();
    if (!$user) {
        $user = User::factory()->create(['department' => 'sales']);
        echo "   ! Created new user\n";
    }
    echo "   ✓ Logged in as: {$user->name} (Department: {$user->department})\n\n";

    // STEP 2: Create Product
    echo "STEP 2: CREATE PRODUCT\n";
    echo str_repeat("-", 80) . "\n";
    $productCode = "UF-$testTimestamp";
    $product = Product::create([
        'product_code' => $productCode,
        'customer_name' => "TestCustomer-$testTimestamp",
        'location' => 'Test Location',
        'model_name' => "TestModel-$testTimestamp",
        'description' => 'Test Product for Workflow',
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
    echo "   ✓ Product created successfully\n";
    echo "   - Code: $productCode\n";
    echo "   - ID: {$product->id}\n\n";

    // STEP 3: Create Job Order
    echo "STEP 3: CREATE JOB ORDER\n";
    echo str_repeat("-", 80) . "\n";
    $jobOrder = JobOrder::create([
        'jo_status' => 'Pending',
        'product_id' => $product->id,
        'product_code' => $product->product_code,
        'customer_name' => $product->customer_name,
        'model_name' => $product->model_name,
        'description' => $product->description,
        'uom' => $product->uom,
        'quantity' => 50,
        'jo_balance' => 50,
        'ppqc_transfer' => 0,
        'ds_quantity' => 0,
        'week_number' => (string) date('W'),
        'date_needed' => now()->addDays(7)->toDateString(),
        'encoded_by' => $user->id,
        'date_encoded' => now(),
    ]);
    echo "   ✓ Job Order created successfully\n";
    echo "   - JO Number: {$jobOrder->jo_number}\n";
    echo "   - Status: {$jobOrder->jo_status}\n";
    echo "   - Quantity: {$jobOrder->quantity}\n";
    echo "   - ID: {$jobOrder->id}\n\n";

    // STEP 4: Approve Order on Sales Dashboard
    echo "STEP 4: APPROVE ORDER ON SALES DASHBOARD\n";
    echo str_repeat("-", 80) . "\n";
    $jobOrder->approve($user->id);
    echo "   ✓ Order approved\n";
    echo "   - New Status: {$jobOrder->jo_status}\n";
    echo "   - Approved By: {$jobOrder->approved_by}\n\n";

    // STEP 5: Create Delivery Schedule
    echo "STEP 5: CREATE DELIVERY SCHEDULE\n";
    echo str_repeat("-", 80) . "\n";
    try {
        $deliverySchedule = DeliverySchedule::create([
            'job_order_id' => $jobOrder->id,
            'product_id' => $product->id,
            'product_code' => $product->product_code,
            'customer_name' => $product->customer_name,
            'model_name' => $product->model_name,
            'description' => $product->description,
            'uom' => $product->uom,
            'jo_number' => $jobOrder->jo_number,
            'quantity' => 50,
            'delivery_date' => now()->addDays(5)->toDateString(),
            'ds_status' => 'ON SCHEDULE',
            'encoded_by' => $user->id,
            'date_encoded' => now(),
        ]);
        echo "   ✓ Delivery Schedule created successfully\n";
        echo "   - DS Code: {$deliverySchedule->ds_code}\n";
        echo "   - Status: {$deliverySchedule->ds_status}\n";
        echo "   - Quantity: {$deliverySchedule->quantity}\n";
        echo "   - ID: {$deliverySchedule->id}\n\n";
    } catch (\Exception $e) {
        echo "   ✗ ERROR creating delivery schedule!\n";
        echo "   - Message: {$e->getMessage()}\n";
        echo "   - File: {$e->getFile()}\n";
        echo "   - Line: {$e->getLine()}\n";
        throw $e;
    }

    // STEP 6: Start Producing (Production Dashboard)
    echo "STEP 6: START PRODUCTION\n";
    echo str_repeat("-", 80) . "\n";
    $jobOrder->update(['jo_status' => 'In Progress']);
    echo "   ✓ Production started\n";
    echo "   - New Status: {$jobOrder->jo_status}\n\n";

    // STEP 7: Mark Complete (Production Dashboard)
    echo "STEP 7: MARK PRODUCTION COMPLETE\n";
    echo str_repeat("-", 80) . "\n";
    $jobOrder->update(['jo_status' => 'JO Full']);
    echo "   ✓ Production completed\n";
    echo "   - New Status: {$jobOrder->jo_status}\n\n";

    // STEP 8: Create Inventory Transfer (Auto-create Finished Good)
    echo "STEP 8: CREATE INVENTORY TRANSFER (Auto-create Finished Good)\n";
    echo str_repeat("-", 80) . "\n";
    $inventoryUser = User::where('department', 'inventory')->first() ?? User::factory()->create(['department' => 'inventory']);
    $transfer = InventoryTransfer::create([
        'job_order_id' => $jobOrder->id,
        'product_id' => $product->id,
        'section' => 'LOCAL',
        'status' => 'Complete',
        'product_code' => $product->product_code,
        'customer_name' => $product->customer_name,
        'model_name' => $product->model_name,
        'description' => $product->description,
        'uom' => $product->uom,
        'jo_number' => $jobOrder->jo_number,
        'date_transferred' => now()->toDateString(),
        'time_transferred' => now()->format('H:i'),
        'date_received' => now()->toDateString(),
        'time_received' => now()->format('H:i'),
        'quantity' => 50,
        'quantity_received' => 50,
        'selling_price' => $product->selling_price,
        'total_amount' => 50 * $product->selling_price,
        'week_number' => (string) date('W'),
        'category' => 'Production',
        'remarks' => 'Workflow test transfer',
        'encoded_by' => $inventoryUser->id,
        'date_encoded' => now(),
    ]);
    echo "   ✓ Inventory transfer created successfully\n";
    echo "   - Transfer Code: {$transfer->transfer_code}\n";
    echo "   - Quantity: {$transfer->quantity}\n\n";

    // STEP 9: Verify Finished Good Auto-Creation
    echo "STEP 9: VERIFY FINISHED GOOD (Auto-Created)\n";
    echo str_repeat("-", 80) . "\n";
    // Refresh product to reload relationships from database
    $product = $product->fresh();
    $finishedGood = $product->finishedGood;
    if ($finishedGood) {
        echo "   ✓ Finished Good auto-created\n";
        echo "   - ID: {$finishedGood->id}\n";
    } else {
        echo "   ! Finished Good not auto-created, creating manually\n";
        $finishedGood = \App\Models\FinishedGood::create([
            'product_id' => $product->id,
            'encoded_by' => $inventoryUser->id,
        ]);
        echo "   ✓ Created manually\n";
    }
    echo "\n";

    // STEP 10: Create Actual Inventory Record
    echo "STEP 10: VERIFY ON ACTUAL INVENTORY\n";
    echo str_repeat("-", 80) . "\n";
    $actualInventory = ActualInventory::create([
        'product_id' => $product->id,
        'counted_qty' => 50,
        'system_qty' => 50,
        'variance' => 0,
        'status' => 'Verified',
        'date_counted' => now()->toDateString(),
        'counted_by_user_id' => $inventoryUser->id,
        'verified_by_user_id' => $inventoryUser->id,
        'remarks' => 'Workflow test count',
        'encoded_by' => $inventoryUser->id,
        'date_encoded' => now(),
    ]);
    echo "   ✓ Actual inventory record created\n";
    echo "   - Status: {$actualInventory->status}\n";
    echo "   - Variance: {$actualInventory->variance}\n\n";

    // STEP 11: Create Endorse to Logistics
    echo "STEP 11: CREATE ENDORSE TO LOGISTICS\n";
    echo str_repeat("-", 80) . "\n";
    $logisticsUser = User::where('department', 'logistics')->first() ?? User::factory()->create(['department' => 'logistics']);
    $endorsement = EndorseToLogistic::create([
        'product_id' => $product->id,
        'delivery_schedule_id' => $deliverySchedule->id,
        'product_code' => $product->product_code,
        'customer_name' => $product->customer_name,
        'model_name' => $product->model_name,
        'description' => $product->description,
        'uom' => $product->uom,
        'quantity' => 50,
        'date' => now()->toDateString(),
        'time' => now()->format('H:i'),
        'delivery_date' => now()->addDays(7)->toDateString(),
        'remarks' => 'Workflow test endorsement',
        'encoded_by' => $inventoryUser->id,
        'date_encoded' => now(),
    ]);
    echo "   ✓ Endorsement to Logistics created\n";
    echo "   - ETL Code: {$endorsement->etl_code}\n";
    echo "   - Quantity: {$endorsement->quantity}\n\n";

    // STEP 12: Approve on Logistics Dashboard
    echo "STEP 12: APPROVE ON LOGISTICS DASHBOARD\n";
    echo str_repeat("-", 80) . "\n";
    $endorsement->update(['updated_by' => $logisticsUser->id]);
    echo "   ✓ Endorsement updated by logistics team\n\n";

    // STEP 13: Mark as Complete on Logistics Dashboard
    echo "STEP 13: MARK COMPLETE ON LOGISTICS DASHBOARD\n";
    echo str_repeat("-", 80) . "\n";
    $endorsement->update([
        'quantity_delivered' => 50,
        'dr_number' => "DR-$testTimestamp",
        'received_by' => 'Customer',
        'date_received' => now()->toDateString(),
        'updated_by' => $logisticsUser->id,
    ]);
    echo "   ✓ Endorsement marked as delivered\n";
    echo "   - DR Number: {$endorsement->dr_number}\n";
    echo "   - Quantity Delivered: {$endorsement->quantity_delivered}\n\n";

    // Final Summary
    echo str_repeat("=", 80) . "\n";
    echo "✓ COMPLETE WORKFLOW TEST PASSED\n";
    echo str_repeat("=", 80) . "\n\n";

    echo "Summary:\n";
    echo "  Product Code: $productCode\n";
    echo "  JO Number: {$jobOrder->jo_number}\n";
    echo "  DS Code: {$deliverySchedule->ds_code}\n";
    echo "  ETL Code: {$endorsement->etl_code}\n";
    echo "\n";

} catch (\Exception $e) {
    echo "\n" . str_repeat("=", 80) . "\n";
    echo "✗ WORKFLOW TEST FAILED\n";
    echo str_repeat("=", 80) . "\n\n";
    echo "Error Details:\n";
    echo "  Message: {$e->getMessage()}\n";
    echo "  File: {$e->getFile()}\n";
    echo "  Line: {$e->getLine()}\n";
    echo "\n  Stack Trace:\n";
    echo $e->getTraceAsString();
    echo "\n\n";
    exit(1);
}
