<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use App\Models\FinishedGood;
use App\Models\JobOrder;

class ReportPdfSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_pdf_generates_and_saves_file()
    {
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'department' => 'admin',
        ]);

        $product = Product::create([
            'product_code' => 'PX-SMOKE',
            'model_name' => 'Smoke Product',
            'uom' => 'pcs',
            'currency' => 'PHP',
            'date_encoded' => now()->toDateString(),
        ]);

        FinishedGood::updateOrCreate([
            'product_id' => $product->id,
        ], [
            'qty_actual_ending' => 100,
            'amount_ending' => 12345.67,
            'amount_variance' => -12.34,
            'date_last_in' => now()->toDateString(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('reports.inventory.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type');

        $content = $response->getContent();
        // Save for manual inspection
        @mkdir(storage_path('testing'), 0755, true);
        file_put_contents(storage_path('testing/inventory-sample.pdf'), $content);

        $this->assertGreaterThan(1024, strlen($content), 'PDF content length should be > 1 KB');
    }

    public function test_job_orders_pdf_generates_and_saves_file()
    {
        $admin = User::create([
            'name' => 'Admin 2',
            'username' => 'admin2',
            'email' => 'admin2@example.com',
            'password' => bcrypt('password'),
            'department' => 'admin',
        ]);

        $product = Product::create([
            'product_code' => 'JO-SMOKE',
            'model_name' => 'Job Product',
            'uom' => 'pcs',
            'currency' => 'USD',
            'date_encoded' => now()->toDateString(),
        ]);

        JobOrder::create([
            'product_id' => $product->id,
            'quantity' => 10,
            'date_needed' => now()->toDateString(),
            'encoded_by_user_id' => $admin->id,
            'date_encoded' => now()->toDateString(),
        ]);

        // Ensure external chart requests return a small valid PNG so PDF generation is deterministic in tests
        \Illuminate\Support\Facades\Http::fake([
            'quickchart.io/*' => \Illuminate\Support\Facades\Http::response(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII='), 200, ['Content-Type' => 'image/png'])
        ]);

        $response = $this->actingAs($admin)
            ->get(route('reports.job-orders.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type');

        $content = $response->getContent();
        @mkdir(storage_path('testing'), 0755, true);
        file_put_contents(storage_path('testing/job-orders-sample.pdf'), $content);

        $this->assertGreaterThan(1024, strlen($content), 'PDF content length should be > 1 KB');
    }
}
