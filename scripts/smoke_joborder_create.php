<?php
use Carbon\Carbon;

$product = \App\Models\Product::first();
if (! $product) {
    $product = \App\Models\Product::create(["model_name"=>"SMOKE-JO-PRD","product_code"=>"SMOKE-PRD","uom"=>"pcs","selling_price"=>100]);
}

$user = \App\Models\User::first();
if (! $user) {
    $id = \DB::table('users')->insertGetId(['name' => 'Smoke Creator', 'department' => 'sales', 'created_at' => now(), 'updated_at' => now()]);
    $user = \App\Models\User::find($id);
}

$jo = \App\Models\JobOrder::create([
    'product_id' => $product->id,
    'qty_ordered' => 5,
    'date_needed' => Carbon::now()->addDays(3)->format('Y-m-d'),
    'encoded_by_user_id' => $user->id,
]);

$jo->refresh();
echo "JobOrder created: id={$jo->id}, jo_number={$jo->jo_number}, po_number={$jo->po_number}\n";