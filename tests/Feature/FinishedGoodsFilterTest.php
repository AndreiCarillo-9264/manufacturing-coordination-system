<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use App\Models\FinishedGood;

class FinishedGoodsFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_date_range_filters_finished_goods()
    {
        $admin = User::factory()->create(['department' => 'admin']);

        $p1 = Product::create([
            'product_code' => 'P100',
            'model_name' => 'Model A',
            'uom' => 'pcs',
            'date_encoded' => now()->toDateString(),
        ]);

        $p2 = Product::create([
            'product_code' => 'P200',
            'model_name' => 'Model B',
            'uom' => 'pcs',
            'date_encoded' => now()->toDateString(),
        ]);

        FinishedGood::create([
            'product_id' => $p1->id,
            'qty_actual_ending' => 10,
            'date_last_in' => '2025-12-01',
        ]);

        FinishedGood::create([
            'product_id' => $p2->id,
            'qty_actual_ending' => 20,
            'date_last_in' => '2026-02-01',
        ]);

        $this->actingAs($admin)
            ->get(route('finished-goods.index', ['date_from' => '2026-01-01', 'date_to' => '2026-12-31']))
            ->assertStatus(200)
            ->assertSee('Model B')
            ->assertDontSee('Model A');
    }
}
