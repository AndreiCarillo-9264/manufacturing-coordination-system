<?php

// Simple smoke script to validate UpdateJobOrderRequest rules programmatically

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Validator;

$job = App\Models\JobOrder::first();
if (! $job) {
    echo "No JobOrder found, running create smoke to create one...\n";
    require __DIR__ . '/smoke_joborder_create.php';
    $job = App\Models\JobOrder::first();
}

$jobOrderId = $job->id;

$rules = [
    'jo_number' => ['nullable','string', Illuminate\Validation\Rule::unique('job_orders','jo_number')->ignore($jobOrderId),'max:255'],
    'po_number' => ['nullable','string', Illuminate\Validation\Rule::unique('job_orders','po_number')->ignore($jobOrderId),'max:255'],
    'jo_status' => 'nullable|in:Pending,JO Full,Approved,In Progress,Cancelled',
    'product_id' => 'required|exists:products,id',
    'quantity' => 'required|integer|min:1',
    'jo_balance' => 'nullable|integer|min:0',
    'ppqc_transfer' => 'nullable|integer|min:0',
    'ds_quantity' => 'nullable|integer|min:0',
    'withdrawal_status' => 'nullable|in:approved,with_fg_stocks',
    'withdrawal_number' => 'nullable|string|max:255',
    'week_number' => 'nullable|string|max:10',
    'date_needed' => 'required|date',
    'date_approved' => 'nullable|date',
    'remarks' => 'nullable|string',
];

// Test case: clearing PO number (simulate user clearing it)
$data = [
    'jo_number' => $job->jo_number,
    'po_number' => null,
    'product_id' => $job->product_id,
    'quantity' => $job->quantity,
    'date_needed' => $job->date_needed->format('Y-m-d'),
];

$v = Validator::make($data, $rules);

if ($v->fails()) {
    echo "Validation failed:\n";
    print_r($v->errors()->all());
} else {
    echo "Validation passed: po_number can be null on update.\n";
}

// Test uniqueness: try to set another JobOrder's PO to the same value (should fail unless ignored)
$other = App\Models\JobOrder::where('id','!=',$job->id)->first();
if ($other) {
    $data2 = [
        'jo_number' => $job->jo_number,
        'po_number' => $other->po_number,
        'product_id' => $job->product_id,
        'quantity' => $job->quantity,
        'date_needed' => $job->date_needed->format('Y-m-d'),
    ];
    $v2 = Validator::make($data2, $rules);
    if ($v2->fails()) {
        echo "Uniqueness test passed: duplicate PO rejected when not ignoring current id.\n";
    } else {
        echo "Uniqueness test failed: duplicate PO allowed (unexpected).\n";
    }
} else {
    echo "No other JobOrder to test uniqueness against. Create one manually to complete test.\n";
}
