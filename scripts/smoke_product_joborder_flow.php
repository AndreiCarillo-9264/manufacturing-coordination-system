<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Product;
use App\Models\JobOrder;

echo "=== Smoke: Product -> JobOrder -> Approve Flow ===\n";

// Create or get user
$user = User::first();
if (! $user) {
    $user = User::create([
        'username' => 'smoke_user',
        'name' => 'Smoke Tester',
        'department' => 'sales',
        'email' => 'smoke@example.test',
        'password' => 'secret123',
        'is_active' => true,
    ]);
    echo "Created user id={$user->id}\n";
} else {
    echo "Using existing user id={$user->id}\n";
}

// Create or get product
$productCode = 'SMK-001';
$product = Product::where('product_code', $productCode)->first();
if (! $product) {
    $product = Product::create([
        'product_code' => $productCode,
    'customer_name' => 'SmokeCorp',
    'customer_location' => 'Test City',
    'model_name' => 'SmokeWidget',
    'description' => 'Test product for smoke flow',
    'specs' => 'N/A',
    'dimension' => '10x10',
    'moq' => 1,
    'uom' => 'pcs',
    'currency' => 'PHP',
    'selling_price' => 100.00,
    'mc' => 60.00,
    'pc' => 'GEN',
    'encoded_by' => $user->id,
    'date_encoded' => now(),
    ]);

    echo "Created product id={$product->id}\n";
} else {
    echo "Using existing product id={$product->id} code={$product->product_code}\n";
}

// Create Job Order
$job = JobOrder::create([
    'jo_number' => null,
    'jo_status' => 'Pending',
    'product_id' => $product->id,
    'product_code' => $product->product_code,
    'customer_name' => $product->customer_name,
    'model_name' => $product->model_name,
    'description' => $product->description,
    'uom' => $product->uom,
    'quantity' => 10,
    'jo_balance' => 10,
    'ppqc_transfer' => 0,
    'ds_quantity' => 0,
    'week_number' => (string) date('W'),
    'date_needed' => now()->addDays(7)->toDateString(),
    'encoded_by' => $user->id,
    'date_encoded' => now(),
]);

echo "Created job order id={$job->id} quantity={$job->quantity} status={$job->jo_status}\n";

// Approve job order (simulate sales approval)
$job->approve($user->id);
$job = $job->fresh();

echo "After approve: date_approved={$job->date_approved} approved_by={$job->approved_by}\n";

echo "=== Smoke completed ===\n";
