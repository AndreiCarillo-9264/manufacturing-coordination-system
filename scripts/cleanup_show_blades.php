<?php
$base = __DIR__ . '/../';
$map = [
    $base . 'resources/views/products/show.blade.php' => 'products',
    $base . 'resources/views/job-orders/show.blade.php' => 'job-orders',
    $base . 'resources/views/transfers/show.blade.php' => 'transfers',
    $base . 'resources/views/actual-inventories/show.blade.php' => 'actual-inventories',
    $base . 'resources/views/endorse-to-logistics/show.blade.php' => 'endorse-to-logistics',
    $base . 'resources/views/delivery-schedules/show.blade.php' => 'delivery-schedules',
    $base . 'resources/views/activity-logs/show.blade.php' => 'activity-logs',
];

foreach ($map as $file => $route) {
    $content = "@extends('layouts.app')\n\n@section('content')\n<script>window.location.href = \"{{ route('$route.index') }}\";</script>\n<div class=\"p-6\"><div class=\"text-gray-800\">This detailed view has been removed. Redirecting to index…</div></div>\n@endsection\n";
    if (file_put_contents($file, $content) !== false) {
        echo "Wrote: $file\n";
    } else {
        echo "Failed: $file\n";
    }
}
