<?php
$t = \App\Models\Transfer::latest()->first();
if (! $t) {
    echo "No transfer found\n"; return;
}

$old = $t->toArray();
$t->update([
    'qty_received' => max(1, $t->qty_received - 2),
    'qty_transferred' => $t->qty_transferred,
    'received_by_user_id' => $t->received_by_user_id,
    'date_transferred' => $t->date_transferred->format('Y-m-d'),
    'time_transferred' => $t->time_transferred,
    'date_delivery_scheduled' => $t->date_delivery_scheduled->format('Y-m-d'),
    'date_received' => $t->date_received->format('Y-m-d'),
    'time_received' => $t->time_received,
]);

echo "Transfer updated: id={$t->id}, qty_received={$t->qty_received}\n";
$args = json_encode(['old' => $old, 'new' => $t->toArray()]);
echo "Changes: $args\n";