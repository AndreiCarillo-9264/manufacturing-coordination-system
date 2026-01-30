<?php

namespace App\Console\Commands;

use App\Models\JobOrder;
use App\Events\JobOrderCreated;
use App\Events\JobOrderStatusChanged;
use Illuminate\Console\Command;

class TestBroadcasting extends Command
{
    protected $signature = 'broadcast:test {--count=1}';
    protected $description = 'Test broadcasting by triggering events';

    public function handle()
    {
        $this->info('Testing broadcasting...');

        try {
            // Get a sample job order or create one
            $jobOrder = JobOrder::first();

            if (!$jobOrder) {
                $this->error('No job orders found in database. Please create one first.');
                return 1;
            }

            $count = (int) $this->option('count');

            for ($i = 0; $i < $count; $i++) {
                // Broadcast created event
                event(new JobOrderCreated($jobOrder));
                $this->line("✓ Broadcasted JobOrderCreated event for {$jobOrder->jo_number}");

                // Broadcast status change
                event(new JobOrderStatusChanged($jobOrder, $jobOrder->status, 'in_progress'));
                $this->line("✓ Broadcasted JobOrderStatusChanged event");
            }

            $this->info("Broadcasting tests completed successfully ({$count} events sent)");
            return 0;

        } catch (\Exception $e) {
            $this->error("Broadcast test failed: {$e->getMessage()}");
            return 1;
        }
    }
}
