<?php
$product = \App\Models\Product::first();
if (! $product) {
    $product = \App\Models\Product::create(["product_code" => "SMK-P-1", "model_name" => "SMOKE-MODEL", "selling_price" => 10, "uom" => "pcs", "customer" => "TestCo"]);
}
$jo = \App\Models\JobOrder::create([
    'product_id' => $product->id,
    'quantity' => 20,
    'encoded_by' => 1,
    'po_number' => 'PO-TEST-1',
    'date_needed' => now()->addDays(2)->format('Y-m-d'),
    'week_number' => (int) date('W'),
    'encoded_by_user_id' => 1,
]);
echo "JO created: id={$jo->id}, quantity={$jo->quantity}, po={$jo->po_number}\n";
