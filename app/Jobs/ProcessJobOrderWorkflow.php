<?php

namespace App\Jobs;

use App\Events\JobOrderStatusChanged;
use App\Models\JobOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessJobOrderWorkflow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $jobOrder;
    public $oldStatus;
    public $newStatus;
    public $tries = 3;
    public $timeout = 120;

    public function __construct(JobOrder $jobOrder, $oldStatus, $newStatus)
    {
        $this->jobOrder = $jobOrder;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function handle()
    {
        try {
            // Handle workflow transitions
            switch ($this->newStatus) {
                case 'approved':
                    $this->handleApproval();
                    break;
                case 'in_progress':
                    $this->handleInProgress();
                    break;
                case 'completed':
                    $this->handleCompletion();
                    break;
                case 'cancelled':
                    $this->handleCancellation();
                    break;
            }

            // Broadcast the status change
            event(new JobOrderStatusChanged($this->jobOrder, $this->oldStatus, $this->newStatus));

            Log::info("Job Order workflow processed: {$this->jobOrder->jo_number} ({$this->oldStatus} → {$this->newStatus})");
        } catch (\Exception $e) {
            Log::error("Failed to process job order workflow: {$e->getMessage()}");
            throw $e;
        }
    }

    protected function handleApproval()
    {
        // Add approval-specific logic here
        Log::info("Processing approval for {$this->jobOrder->jo_number}");
    }

    protected function handleInProgress()
    {
        // Add in-progress specific logic here
        Log::info("Processing in-progress for {$this->jobOrder->jo_number}");
    }

    protected function handleCompletion()
    {
        // Add completion-specific logic here
        Log::info("Processing completion for {$this->jobOrder->jo_number}");
    }

    protected function handleCancellation()
    {
        // Add cancellation-specific logic here
        Log::info("Processing cancellation for {$this->jobOrder->jo_number}");
    }
}
