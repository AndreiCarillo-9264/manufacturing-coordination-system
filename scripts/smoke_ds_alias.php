<?php
$ds = \App\Models\DeliverySchedule::first();
echo "DS id={$ds->id}, qty={$ds->qty}, qty_scheduled={$ds->qty_scheduled}, uom={$ds->uom}, product_uom={$ds->product->uom}\n";
