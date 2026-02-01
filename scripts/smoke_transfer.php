<?php
use Carbon\Carbon;

$user = \App\Models\User::where('department','inventory')->first();
if (! $user) {
    $id = \DB::table('users')->insertGetId([
        'name' => 'Receiver Test',
        'department' => 'inventory',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    $user = \App\Models\User::find($id);
}

$product = \App\Models\Product::create(["model_name" => "SMOKE-PRD", "customer" => "TestCo", "uom" => "pcs", "moq" => 1, "selling_price" => 100, "pc" => "PC"]);
$jo = \App\Models\JobOrder::create(["product_id" => $product->id, "qty_ordered" => 50, "status" => "approved", "encoded_by_user_id" => $user->id, "po_number" => "PO-SMOKE-1", "week_number" => (int) date('W'), "date_needed" => now()->addDays(7)->format('Y-m-d')]);

$validated = [
    "job_order_id" => $jo->id,
    "section" => "Assembly",
    "category" => "Electronics",
    "date_transferred" => now()->format("Y-m-d"),
    "time_transferred" => now()->format("H:i"),
    "date_delivery_scheduled" => now()->addDays(2)->format("Y-m-d"),
    "received_by_user_id" => $user->id,
    "date_received" => now()->format("Y-m-d"),
    "time_received" => now()->format("H:i"),
    "qty_transferred" => 10,
    "qty_received" => 10,
    "remarks" => "Smoke test",
];

$validated["product_id"] = $jo->product_id;
$validated["unit_selling_price"] = $jo->product->selling_price;
$validated["total_amount"] = $validated["qty_received"] * $validated["unit_selling_price"];
$validated["week_number"] = (int) date("W", strtotime($validated["date_transferred"]));
$validated["jit_days"] = Carbon::parse($validated["date_delivery_scheduled"])->diffInDays($validated["date_transferred"]);
$validated["status"] = $validated["qty_received"] >= $validated["qty_transferred"] ? "complete" : "balance";
$validated["qty_jo_balance"] = $jo->qty_ordered - $jo->transfers()->sum("qty_received") - $validated["qty_received"];

$transfer = \App\Models\Transfer::create($validated);
if ($jo->status === "approved") {
    if (method_exists($jo, 'markInProgress')) {
        $jo->markInProgress();
    } else {
        $jo->status = 'in_progress'; $jo->save();
    }
}

$fg = $jo->product->finishedGood;
if ($fg) {
    $fg->increment("qty_in", $validated["qty_received"]);
    $fg->increment("amount_in", $validated["total_amount"]);
    $fg->date_last_in = $validated["date_received"];
    $fg->save();
}

$transfer->load("product", "jobOrder", "receivedBy");
echo "Transfer created: id={$transfer->id}, ptt={$transfer->ptt_number}, status={$transfer->status}, total_amount={$transfer->total_amount}\n";
echo "FinishedGood in: {$fg->qty_in}, amount_in: {$fg->amount_in}, theor_end: {$fg->qty_theoretical_ending}\n";
