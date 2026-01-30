<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Public channels (all authenticated users can listen)
Broadcast::channel('job-orders', function ($user) {
    return $user !== null; // All authenticated users
});

Broadcast::channel('transfers', function ($user) {
    return $user !== null;
});

Broadcast::channel('delivery-schedules', function ($user) {
    return $user !== null;
});

Broadcast::channel('finished-goods', function ($user) {
    return $user !== null;
});

Broadcast::channel('inventory', function ($user) {
    return $user !== null;
});

// Private channels (specific to resources)
Broadcast::channel('job-order.{jobOrderId}', function ($user, $jobOrderId) {
    return true; // Allow all authenticated users
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

// Notification channels (user-specific)
Broadcast::channel('notifications.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Presence channels (show active users in department)
Broadcast::channel('dashboard.{department}', function ($user, $department) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'email' => $user->email,
        'department' => $user->department,
    ];
});
