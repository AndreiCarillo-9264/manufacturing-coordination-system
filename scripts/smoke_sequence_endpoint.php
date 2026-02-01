<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/api/sequences/next', 'GET', ['type' => 'all']);
$controller = new App\Http\Controllers\SequenceController();
$response = $controller->next($request);

echo "Sequence endpoint response:\n";
print_r($response->getData(true));
