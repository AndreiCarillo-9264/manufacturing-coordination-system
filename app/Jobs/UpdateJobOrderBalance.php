<?php

namespace App\Jobs;

use App\Models\JobOrder;
use App\Services\JobOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateJobOrderBalance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $jobOrder;
    public $tries = 3;
    public $timeout = 60;

    public function __construct(JobOrder $jobOrder)
    {
        $this->jobOrder = $jobOrder;
    }

    public function handle(JobOrderService $service)
    {
        try {
            $service->updateBalance($this->jobOrder);
            $service->checkAndUpdateStatus($this->jobOrder);
            
            Log::info("Job Order balance updated: {$this->jobOrder->jo_number}");
        } catch (\Exception $e) {
            Log::error("Failed to update job order balance: {$e->getMessage()}");
            throw $e;
        }
    }
}
