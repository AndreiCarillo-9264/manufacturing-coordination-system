<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Events\JobOrderCreated;
use App\Models\User;
use App\Models\Product;

class RealTimeBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_order_creation_dispatches_broadcast_event()
    {
        Event::fake([JobOrderCreated::class]);

        // Create a user directly
        $user = User::create([
            'name' => 'Broadcast Tester',
            'email' => 'broadcast@test.local',
            'password' => bcrypt('password'),
            'department' => 'production',
        ]);

        // Create a product
        $product = Product::create([
            'product_code' => 'BRD-001',
            'model_name' => 'Broadcast Test Product',
            'uom' => 'pcs'
        ]);

        $this->actingAs($user)
            ->post(route('job-orders.store'), [
                'product_id' => $product->id,
                'quantity' => 10,
                'date_needed' => now()->addDays(1)->toDateString(),
            ])
            ->assertRedirect();

        Event::assertDispatched(JobOrderCreated::class);
    }
}
