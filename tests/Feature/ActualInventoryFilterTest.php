<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use App\Models\ActualInventory;

class ActualInventoryFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_level_filters()
    {
        $admin = User::factory()->create(['department' => 'admin']);

        $product = Product::create([
            'product_code' => 'PX',
            'model_name' => 'Prod X',
            'uom' => 'pcs',
            'date_encoded' => now()->toDateString(),
        ]);

        ActualInventory::create([ 'tag_number' => 'T-LOW', 'product_id' => $product->id, 'qty_counted' => 50 ]);
        ActualInventory::create([ 'tag_number' => 'T-MED', 'product_id' => $product->id, 'qty_counted' => 200 ]);
        ActualInventory::create([ 'tag_number' => 'T-HIGH', 'product_id' => $product->id, 'qty_counted' => 600 ]);

        // Low
        $this->actingAs($admin)
            ->get(route('actual-inventories.index', ['stock_level' => 'low']))
            ->assertStatus(200)
            ->assertSee('T-LOW')
            ->assertDontSee('T-MED')
            ->assertDontSee('T-HIGH');

        // Medium
        $this->actingAs($admin)
            ->get(route('actual-inventories.index', ['stock_level' => 'medium']))
            ->assertStatus(200)
            ->assertSee('T-MED')
            ->assertDontSee('T-LOW')
            ->assertDontSee('T-HIGH');

        // High
        $this->actingAs($admin)
            ->get(route('actual-inventories.index', ['stock_level' => 'high']))
            ->assertStatus(200)
            ->assertSee('T-HIGH')
            ->assertDontSee('T-LOW')
            ->assertDontSee('T-MED');
    }
}
