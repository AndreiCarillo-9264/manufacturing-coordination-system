<?php

namespace Tests\Feature\Notifications;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Notifications\Events\BroadcastNotificationCreated;
use App\Models\User;
use App\Models\Product;

class NotificationsBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_order_creation_creates_database_notification_and_broadcasts()
    {
        Event::fake([BroadcastNotificationCreated::class]);

        // Create an inventory user who should receive the notification
        $inventoryUser = User::create([
            'name' => 'Inventory User',
            'username' => 'inventoryuser',
            'email' => 'inventory@test.local',
            'password' => bcrypt('password'),
            'department' => 'inventory'
        ]);

        // Create a creator user who will create the job order
        $creator = User::create([
            'name' => 'Creator User',
            'username' => 'creatoruser',
            'email' => 'creator@test.local',
            'password' => bcrypt('password'),
            'department' => 'sales'
        ]);

        // Create a product
        $product = Product::create([
            'product_code' => 'TEST-01',
            'model_name' => 'Test Product',
            'uom' => 'pcs',
            'selling_price' => 10
        ]);

        // Acting as the creator, create a job order that is 'pending'
        $this->actingAs($creator)
            ->post(route('job-orders.store'), [
                'product_id' => $product->id,
                'quantity' => 5,
                'date_needed' => now()->addDays(3)->toDateString(),
                'status' => 'pending'
            ])
            ->assertRedirect();

        // Assert inventory user has a database notification
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $inventoryUser->id,
            'type' => 'App\\Notifications\\PendingJobOrderNotification'
        ]);

        // Assert BroadcastNotificationCreated event was dispatched for the inventory user
        Event::assertDispatched(BroadcastNotificationCreated::class, function ($event) use ($inventoryUser) {
            return $event->notifiable->id === $inventoryUser->id;
        });
    }
}
