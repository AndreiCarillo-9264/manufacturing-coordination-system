<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Product;
use App\Models\JobOrder;
use App\Models\Transfer;

class TransferManualReceiverTest extends TestCase
{
    use RefreshDatabase;

    public function test_transfer_can_be_created_with_manual_received_by_name()
    {
        $user = User::create([
            'name' => 'Creator',
            'email' => 'creator@test.local',
            'password' => bcrypt('password'),
            'department' => 'production',
        ]);

        $product = Product::create([
            'product_code' => 'TR-MAN-001',
            'model_name' => 'Manual Receiver Product',
            'uom' => 'pcs'
        ]);

        $jobOrder = JobOrder::create([
            'jo_number' => 'JO-MAN-001',
            'product_id' => $product->id,
            'quantity' => 50,
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
                'received_by_name' => 'John Doe (Courier)',
                'date_received' => now()->toDateString(),
                'time_received' => now()->format('H:i'),
                'qty_received' => 10,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('transfers', [
            'job_order_id' => $jobOrder->id,
            'received_by_name' => 'John Doe (Courier)'
        ]);
    }
}
