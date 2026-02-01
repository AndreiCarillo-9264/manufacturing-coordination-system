<?php
$jo = \App\Models\JobOrder::first();
$deliveryDate = now()->addDays(3)->format('Y-m-d');
$ds = \App\Models\DeliverySchedule::create([
    'job_order_id' => $jo->id,
    'product_id' => $jo->product_id,
    'delivery_date' => $deliveryDate,
    'week_number' => (int) date('W', strtotime($deliveryDate)),
    'qty_scheduled' => 5,
]);
echo "DS created: id={$ds->id}, delivery_date={$ds->delivery_date}, job_order_id={$ds->job_order_id}\n";
