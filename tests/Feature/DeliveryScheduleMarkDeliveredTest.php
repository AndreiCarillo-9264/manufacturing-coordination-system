<?php

namespace Tests\Feature;

use App\Models\DeliverySchedule;
use App\Models\FinishedGood;
use App\Models\JobOrder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryScheduleMarkDeliveredTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_delivered_updates_delivery_and_finished_good()
    {
        // Create a logistics user and authenticate (create directly to match users table schema)
        $user = User::create([
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'department' => 'logistics',
        ]);
        $this->actingAs($user);

        // Create product (automatically creates FinishedGood via Product::created hook)
        $product = Product::create([
            'model_name' => 'TEST MODEL',
            'selling_price' => 100.00,
            'uom' => 'pcs',
        ]);

        // Ensure finished good exists and initial values
        $fg = FinishedGood::where('product_id', $product->id)->first();
        $this->assertNotNull($fg);
        $this->assertEquals(0, $fg->qty_out);
        $this->assertEquals(0, (float) $fg->amount_out);

        // Create job order
        $jobOrder = JobOrder::create([
            'product_id' => $product->id,
            'status' => 'approved',
            'qty_ordered' => 10,
            'week_number' => (int) now()->format('W'),
            'date_needed' => now()->addDays(3)->toDateString(),
        ]);

        // Create delivery schedule
        $delivery = DeliverySchedule::create([
            'job_order_id' => $jobOrder->id,
            'product_id' => $product->id,
            'delivery_date' => now()->addDays(1)->toDateString(),
            'week_number' => (int) now()->addDays(1)->format('W'),
            'date_encoded' => now()->toDateString(),
            'qty_scheduled' => 5,
            // qty_delivered intentionally left null to assert defaulting behavior
        ]);

        // Post to mark-delivered endpoint
        // Call controller method directly to avoid middleware/session complexity in feature test
        $controller = new \App\Http\Controllers\DeliveryScheduleController();
        $response = $controller->markDelivered($delivery);

        // Should return a redirect response on success
        $this->assertTrue($response->isRedirection());

        // Refresh instances
        $delivery->refresh();
        $fg->refresh();

        $this->assertEquals('complete', $delivery->status);
        $this->assertEquals(5, $delivery->qty_delivered);

        $this->assertEquals(5, $fg->qty_out);
        $this->assertEquals(5 * 100.00, (float) $fg->amount_out);
    }
}
