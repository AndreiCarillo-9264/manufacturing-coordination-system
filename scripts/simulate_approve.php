<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JobOrder;
use App\Models\User;
use App\Http\Controllers\JobOrderController;
use Illuminate\Support\Facades\Auth;

// Login as user 1
$user = User::find(1);
if (! $user) {
    echo "User id=1 not found\n";
    exit(1);
}
Auth::login($user);

$job = JobOrder::where('jo_status', 'Pending')->first();
if (! $job) {
    echo "No pending job order found\n";
    exit(0);
}

echo "Before: id={$job->id} jo_status={$job->jo_status} date_approved={$job->date_approved}\n";

$controller = new JobOrderController();
try {
    $response = $controller->approve($job);
    $job = $job->fresh();
    echo "After: id={$job->id} jo_status={$job->jo_status} date_approved={$job->date_approved} approved_by={$job->approved_by}\n";
    echo "Controller response type: " . get_class($response) . "\n";
} catch (\Exception $e) {
    echo "Exception during approval: " . $e->getMessage() . "\n";
}
