<?php

namespace App\Console\Commands;

use App\Models\JobOrder;
use App\Services\JobOrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UpdateJobOrderBalances extends Command
{
    protected $signature = 'joborder:update-balances {--force}';
    protected $description = 'Update all job order balances based on transfers and deliveries';

    public function handle(JobOrderService $service)
    {
        $this->info('Starting job order balance updates...');

        try {
            $jobOrders = JobOrder::whereIn('status', ['approved', 'in_progress'])->get();

            if ($jobOrders->isEmpty()) {
                $this->info('No job orders to update.');
                return;
            }

            foreach ($jobOrders as $jobOrder) {
                $service->updateBalance($jobOrder);
                $service->checkAndUpdateStatus($jobOrder);
                
                $this->line("Updated: {$jobOrder->jo_number} (Balance: {$jobOrder->jo_balance})");
            }

            $this->info("✓ Updated {$jobOrders->count()} job orders successfully.");
            Log::info("Job order balances updated: {$jobOrders->count()} records");

        } catch (\Exception $e) {
            $this->error("Error updating job order balances: {$e->getMessage()}");
            Log::error("Job order balance update failed: {$e->getMessage()}");
            return 1;
        }

        return 0;
    }
}
