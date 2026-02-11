<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Requests\StoreProductRequest;
use Illuminate\Support\Facades\Validator;

$input = [
    'product_code' => 'SMK-VAL-001',
    'customer' => 'ValidationCorp',
    'model_name' => 'ValWidget',
    'description' => 'Validation test',
    'specs' => 'N/A',
    'dimension' => '10x10',
    'moq' => 1,
    'uom' => 'pcs',
    'currency' => 'PHP',
    'selling_price' => 10.00,
];

$req = new StoreProductRequest();
$rules = $req->rules();

// Simulate prepareForValidation mapping
if (isset($input['customer']) && !isset($input['customer_name'])) {
    $input['customer_name'] = $input['customer'];
}
if (!isset($input['date_encoded'])) {
    $input['date_encoded'] = date('Y-m-d');
}
if (!isset($input['encoded_by_user_id'])) {
    $input['encoded_by_user_id'] = 1;
}
if (isset($input['encoded_by_user_id']) && !isset($input['encoded_by'])) {
    $input['encoded_by'] = $input['encoded_by_user_id'];
}

$v = Validator::make($input, $rules);

if ($v->fails()) {
    echo "Validation failed:\n";
    print_r($v->errors()->toArray());
    exit(1);
}

$validated = $v->validated();

echo "Validation passed. Validated payload:\n";
print_r($validated);
return 0;
