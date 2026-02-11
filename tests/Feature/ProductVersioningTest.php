<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;

class ProductVersioningTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_code_appends_suffix_on_update()
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.local',
            'password' => bcrypt('password'),
            'department' => 'admin',
        ]);

        $product = Product::create([
            'product_code' => 'PRD-2026-0001',
            'model_name' => 'Original Product',
            'uom' => 'pcs',
            'moq' => 1,
            'currency' => 'PHP',
            'selling_price' => 10,
        ]);

        // First update — should become PRD-2026-0001-01
        $this->actingAs($admin)
            ->put(route('products.update', $product), [
                'product_code' => 'PRD-2026-0001',
                'model_name' => 'Updated Once',
                'uom' => 'pcs',
                'moq' => 1,
                'currency' => 'PHP',
                'selling_price' => 10,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('products', ['product_code' => 'PRD-2026-0001-01']);

        $product->refresh();

        // Second update — should become PRD-2026-0001-02
        $this->actingAs($admin)
            ->put(route('products.update', $product), [
                'product_code' => 'PRD-2026-0001-01',
                'model_name' => 'Updated Twice',
                'uom' => 'pcs',
                'moq' => 1,
                'currency' => 'PHP',
                'selling_price' => 10,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('products', ['product_code' => 'PRD-2026-0001-02']);
    }
}