<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use App\Models\FinishedGood;
use App\Models\JobOrder;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportPdfGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_orders_pdf_generation_uses_pdf_download()
    {
        $admin = User::factory()->create(['department' => 'admin']);

        $product = Product::create([
            'product_code' => 'JX',
            'model_name' => 'JobProd',
            'uom' => 'pcs',
            'date_encoded' => now()->toDateString(),
        ]);

        JobOrder::create([
            'product_id' => $product->id,
            'quantity' => 10,
            'date_needed' => now()->toDateString(),
        ]);

        // Mock the Pdf facade so we don't rely on the PDF binary generation
        Pdf::shouldReceive('loadView')->once()->andReturnSelf();
        Pdf::shouldReceive('setPaper')->once()->andReturnSelf();
        Pdf::shouldReceive('download')->once()->andReturn(response('pdf-binary', 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename=job-orders-report.pdf'
        ]));

        $this->actingAs($admin)
            ->get(route('reports.job-orders.pdf'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition');
    }

    public function test_inventory_pdf_generation_uses_pdf_download()
    {
        $admin = User::factory()->create(['department' => 'admin']);

        $product = Product::create([
            'product_code' => 'IX',
            'model_name' => 'InvProd',
            'uom' => 'pcs',
            'date_encoded' => now()->toDateString(),
        ]);

        FinishedGood::create([
            'product_id' => $product->id,
            'qty_actual_ending' => 100,
            'date_last_in' => now()->toDateString(),
        ]);

        Pdf::shouldReceive('loadView')->once()->andReturnSelf();
        Pdf::shouldReceive('setPaper')->once()->andReturnSelf();
        Pdf::shouldReceive('download')->once()->andReturn(response('pdf-binary', 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename=inventory-report.pdf'
        ]));

        $this->actingAs($admin)
            ->get(route('reports.inventory.pdf'))
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf')
            ->assertHeader('Content-Disposition');
    }
}
