<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Broadcast::routes(['middleware' => ['api', 'auth:sanctum']]);

        // Public channels for job orders
        Broadcast::channel('job-orders', function ($user) {
            return true;
        });

        // Private channel for real-time notifications
        Broadcast::channel('notifications.{userId}', function ($user, $userId) {
            return (int) $user->id === (int) $userId;
        });

        // Presence channels for active users
        Broadcast::channel('dashboard.{department}', function ($user, $department) {
            return ['id' => $user->id, 'name' => $user->name, 'department' => $user->roles->first()?->name];
        });

        // Private channels for specific resources
        Broadcast::channel('job-order.{jobOrderId}', function ($user, $jobOrderId) {
            return true;
        });

        Broadcast::channel('transfer.{transferId}', function ($user, $transferId) {
            return true;
        });

        Broadcast::channel('delivery-schedule.{deliveryScheduleId}', function ($user, $deliveryScheduleId) {
            return true;
        });

        Broadcast::channel('inventory.{inventoryId}', function ($user, $inventoryId) {
            return true;
        });
    }
}
