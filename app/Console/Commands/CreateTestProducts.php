<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class CreateTestProducts extends Command
{
    protected $signature = 'product:create-test {--count=5}';
    protected $description = 'Create test products for job order creation';

    public function handle()
    {
        $this->info('Creating test products...');

        try {
            $count = (int) $this->option('count');
            $products = [
                [
                    'code' => 'PROD-002',
                    'name' => 'Electronic Component B',
                    'description' => 'Standard electronic component',
                    'price' => 450,
                    'customer' => 'ABC Corporation',
                ],
                [
                    'code' => 'PROD-003',
                    'name' => 'Mechanical Part X',
                    'description' => 'Precision mechanical part',
                    'price' => 1200,
                    'customer' => 'XYZ Industries',
                ],
                [
                    'code' => 'PROD-004',
                    'name' => 'Plastic Assembly',
                    'description' => 'Injection molded assembly',
                    'price' => 350,
                    'customer' => 'XYZ Industries',
                ],
                [
                    'code' => 'PROD-005',
                    'name' => 'Metal Frame',
                    'description' => 'Welded steel frame',
                    'price' => 2500,
                    'customer' => 'TechFlow Solutions',
                ],
                [
                    'code' => 'PROD-006',
                    'name' => 'PCB Board',
                    'description' => 'Printed circuit board assembly',
                    'price' => 800,
                    'customer' => 'TechFlow Solutions',
                ],
            ];

            foreach (array_slice($products, 0, $count) as $productData) {
                $exists = Product::where('product_code', $productData['code'])->exists();
                
                if (!$exists) {
                    Product::create([
                        'customer_name' => $productData['customer'],
                        'product_code' => $productData['code'],
                        'model_name' => $productData['name'],
                        'description' => $productData['description'],
                        'date_encoded' => now(),
                        'uom' => 'pcs',
                        'selling_price' => $productData['price'],
                        'currency' => 'PHP',
                    ]);

                    $this->line("✓ Created: {$productData['code']} - {$productData['name']}");
                } else {
                    $this->line("⊘ Already exists: {$productData['code']}");
                }
            }

            $totalProducts = Product::count();
            $this->info("Total products in system: {$totalProducts}");
            return 0;

        } catch (\Exception $e) {
            $this->error("Error creating products: {$e->getMessage()}");
            return 1;
        }
    }
}
