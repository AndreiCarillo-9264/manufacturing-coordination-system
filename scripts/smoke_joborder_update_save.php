<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$job = App\Models\JobOrder::first();
if (! $job) {
    echo "No JobOrder found, creating one...\n";
    require __DIR__ . '/smoke_joborder_create.php';
    $job = App\Models\JobOrder::first();
}

echo "Before update: id={$job->id} po_number={$job->po_number}\n";
$job->po_number = null;
$job->save();
$job->refresh();
echo "After update: id={$job->id} po_number=" . ($job->po_number ?? 'NULL') . "\n";
