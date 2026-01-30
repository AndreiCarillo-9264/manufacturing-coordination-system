<?php

namespace App\Console\Commands;

use App\Models\JobOrder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;

class CreateTestJobOrder extends Command
{
    protected $signature = 'joborder:create-test {--count=1 : Number of test job orders to create}';
    protected $description = 'Create test job orders for demonstration';

    public function handle()
    {
        $count = (int) $this->option('count');
        $this->info("Creating {$count} test job order(s)...");

        try {
            $products = Product::limit(3)->get();
            $encodedByUser = User::where('department', 'sales')->first() ?? User::first();

            if ($products->isEmpty()) {
                $this->error('No products found. Please create products first.');
                return 1;
            }

            for ($i = 0; $i < $count; $i++) {
                $product = $products->random();
                $quantity = rand(10, 500);
                $dateNeeded = now()->addDays(rand(1, 30));

                $jobOrder = JobOrder::create([
                    'product_id' => $product->id,
                    'qty' => $quantity,
                    'uom' => 'pcs',
                    'date_needed' => $dateNeeded,
                    'remarks' => 'Test job order #' . ($i + 1),
                    'encoded_by_user_id' => $encodedByUser->id,
                    'date_encoded' => now(),
                    'week_number' => $dateNeeded->format('W'),
                ]);

                $this->line("✓ Created job order: {$jobOrder->jo_number} | PO: {$jobOrder->po_number} ({$quantity} units of {$product->model_name})");
            }

            $this->info("Successfully created {$count} test job order(s)");
            return 0;

        } catch (\Exception $e) {
            $this->error("Error creating test job orders: {$e->getMessage()}");
            return 1;
        }
    }
}
