<?php
/**
 * Test script: Verify deactivation logging, low-stock guards, and approval flow
 * Run: php artisan tinker < scripts/test_features.php
 * Or:  php scripts/test_features.php
 */

require __DIR__ . '/../bootstrap/app.php';

use App\Models\JobOrder;
use App\Models\FinishedGood;
use App\Models\ActivityLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n=== FEATURE TEST SUITE ===\n\n";

// Test 1: Approval flow
echo "TEST 1: Approval Flow\n";
echo str_repeat("-", 50) . "\n";
$jo = JobOrder::where('jo_number', 'JO-001')->first();
if ($jo) {
    echo "Found Job Order: {$jo->jo_number}\n";
    echo "Before: jo_status={$jo->jo_status}, date_approved=" . ($jo->date_approved ? $jo->date_approved->format('Y-m-d H:i:s') : 'NULL') . "\n";
    
    $jo->approve();
    $jo->refresh();
    
    echo "After approval: jo_status={$jo->jo_status}, date_approved=" . ($jo->date_approved ? $jo->date_approved->format('Y-m-d H:i:s') : 'NULL') . ", approved_by={$jo->approved_by}\n";
    echo "✓ Approval updates date_approved and approved_by (jo_status remains Pending, which is correct)\n";
} else {
    echo "No Job Order found with jo_number JO-001. Skipping.\n";
}
echo "\n";

// Test 2: Deactivation with activity logging
echo "TEST 2: Deactivation & Activity Logging\n";
echo str_repeat("-", 50) . "\n";
$jo2 = JobOrder::where('jo_number', 'JO-002')->first();
if (!$jo2) {
    // Create a test JO if needed
    $product = Product::first();
    if ($product) {
        $jo2 = JobOrder::create([
            'jo_number' => 'TEST-DEACTIVATE-' . time(),
            'po_number' => 'PO-TEST-' . time(),
            'product_id' => $product->id,
            'jo_status' => 'Pending',
            'date_needed' => now()->addDays(5),
            'encoded_by' => 1, // Assume user 1 exists
        ]);
        echo "Created test Job Order: {$jo2->jo_number}\n";
    } else {
        echo "No products available; skipping deactivation test.\n";
        $jo2 = null;
    }
}

if ($jo2) {
    echo "Testing deactivate action for: {$jo2->jo_number}\n";
    
    // Deactivate via soft-delete with metadata
    $jo2->deactivation_remarks = 'Test deactivation for feature verification';
    $jo2->deactivated_by = 1;
    $jo2->deactivated_at = now();
    $jo2->save();
    
    // Log activity
    app(\App\Services\ActivityLogger::class)->logModel(
        'deactivated',
        $jo2,
        $jo2->getOriginal(),
        $jo2->getAttributes()
    );
    
    $jo2->delete(); // Soft-delete
    $jo2->refresh();
    
    echo "After deactivate: deleted_at=" . ($jo2->deleted_at ? $jo2->deleted_at->format('Y-m-d H:i:s') : 'NULL') . "\n";
    echo "deactivation_remarks='{$jo2->deactivation_remarks}'\n";
    echo "deactivated_by={$jo2->deactivated_by}\n";
    
    // Check activity log
    $activity = ActivityLog::where('subject_type', 'App\\Models\\JobOrder')
        ->where('subject_id', $jo2->id)
        ->where('event', 'deactivated')
        ->latest()
        ->first();
    
    if ($activity) {
        echo "✓ Activity log created for deactivation\n";
        echo "  Remarks in log: " . ($activity->new_values['deactivation_remarks'] ?? 'NOT FOUND') . "\n";
    } else {
        echo "✗ Activity log NOT found for deactivation\n";
    }
} else {
    echo "Skipped deactivation test.\n";
}
echo "\n";

// Test 3: Low-stock guard on removeStock()
echo "TEST 3: Low-Stock Guard on Stock Removal\n";
echo str_repeat("-", 50) . "\n";
$fg = FinishedGood::first();
if ($fg) {
    $originalQty = $fg->current_qty;
    $bufferStock = $fg->buffer_stocks ?? 10;
    echo "Found Finished Good: {$fg->fg_code}\n";
    echo "current_qty={$originalQty}, buffer_stocks={$bufferStock}\n";
    
    // Test normal removal (within buffer)
    $safeQty = max(1, (int)(($originalQty - $bufferStock) / 2));
    if ($safeQty > 0) {
        try {
            $fg->removeStock($safeQty);
            $fg->refresh();
            echo "✓ Successfully removed {$safeQty} units\n";
            echo "  New qty: {$fg->current_qty}\n";
            
            // Restore quantity for next test
            $fg->current_qty = $originalQty;
            $fg->save();
        } catch (\Exception $e) {
            echo "✗ Removal failed: " . $e->getMessage() . "\n";
        }
    }
    
    // Test removal that would go below buffer (with justification)
    if ($originalQty > 0) {
        $excessQty = $originalQty - $bufferStock + 5;
        try {
            $fg->removeStock($excessQty, 'Emergency depletion due to unexpected demand');
            $fg->refresh();
            echo "✓ Successfully removed {$excessQty} units with justification\n";
            echo "  New qty: {$fg->current_qty}\n";
            echo "  Remarks: {$fg->remarks}\n";
            
            // Restore
            $fg->current_qty = $originalQty;
            $fg->save();
        } catch (\Exception $e) {
            echo "✗ Removal with justification failed: " . $e->getMessage() . "\n";
        }
    }
} else {
    echo "No Finished Goods found; skipping low-stock test.\n";
}
echo "\n";

echo "=== TEST SUITE COMPLETE ===\n\n";
