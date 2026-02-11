<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;

class ProductImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_csv_import_creates_products()
    {
        Storage::fake('local');

        $admin = User::factory()->create(['role' => 'admin']);

        $csv = "product_code,model_name,uom,selling_price,customer,remarks\n";
        $csv .= "PRD-TEST-1,Test Model,PCS,12.5,ACME,Sample\n";
        $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

        $response = $this->actingAs($admin)->post(route('products.import'), [
            'file' => $file,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', ['product_code' => 'PRD-TEST-1', 'model_name' => 'Test Model']);
    }
}
