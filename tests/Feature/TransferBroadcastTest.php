<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Events\TransferCreated;
use App\Events\FinishedGoodUpdated;
use App\Models\User;
use App\Models\Product;
use App\Models\JobOrder;

class TransferBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_transfer_creation_dispatches_events()
    {
        Event::fake([TransferCreated::class, FinishedGoodUpdated::class]);

        $user = User::create([
            'name' => 'Transfer Tester',
            'email' => 'transfer@test.local',
            'password' => bcrypt('password'),
            'department' => 'inventory',
        ]);

        $product = Product::create([
            'product_code' => 'TR-001',
            'model_name' => 'Transfer Product',
            'uom' => 'pcs'
        ]);

        $jobOrder = JobOrder::create([
            'jo_number' => 'JO-TR-001',
            'product_id' => $product->id,
            'quantity' => 20,
            'date_needed' => now()->addDays(2)->toDateString(),
            'encoded_by_user_id' => $user->id
        ]);

        $this->actingAs($user)
            ->post(route('inventory-transfers.store'), [
                'job_order_id' => $jobOrder->id,
                'section' => 'PPQC',
                'category' => 'Final',
                'date_transferred' => now()->toDateString(),
                'time_transferred' => now()->format('H:i'),
                'date_delivery_scheduled' => now()->addDays(3)->toDateString(),
                'qty_transferred' => 10,
                'received_by_user_id' => $user->id,
                'date_received' => now()->toDateString(),
                'time_received' => now()->format('H:i'),
                'qty_received' => 10,
            ])
            ->assertRedirect();

        Event::assertDispatched(TransferCreated::class);
        Event::assertDispatched(FinishedGoodUpdated::class);
    }
}
