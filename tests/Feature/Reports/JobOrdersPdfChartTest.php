<?php

namespace Tests\Feature\Reports;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\Product;
use App\Models\JobOrder;

class JobOrdersPdfChartTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_orders_pdf_includes_chart_image()
    {
        $admin = User::create([
            'name' => 'Admin PDF',
            'username' => 'adminpdf',
            'email' => 'adminpdf@example.com',
            'password' => bcrypt('password'),
            'department' => 'admin',
        ]);

        $product = Product::create([
            'product_code' => 'JO-CHART',
            'model_name' => 'Chart Product',
            'uom' => 'pcs',
            'currency' => 'PHP',
            'date_encoded' => now()->toDateString(),
        ]);

        // Create multiple job orders across weeks so chart has data
        for ($i = 0; $i < 5; $i++) {
            JobOrder::create([
                'product_id' => $product->id,
                'quantity' => 5 + $i,
                'date_needed' => now()->addDays($i)->toDateString(),
                'encoded_by_user_id' => $admin->id,
                'date_encoded' => now()->toDateString(),
            ]);
        }

        // Fake QuickChart response with a tiny valid PNG
        Http::fake([
            'quickchart.io/*' => Http::response(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII='), 200, ['Content-Type' => 'image/png'])
        ]);

        $response = $this->actingAs($admin)
            ->get(route('reports.job-orders.pdf'));

        $response->assertStatus(200);
        // Assert we called the chart provider
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'quickchart.io');
        });

        $content = $response->getContent();
        $this->assertGreaterThan(1024, strlen($content), 'PDF content length should be > 1 KB');
    }
}
