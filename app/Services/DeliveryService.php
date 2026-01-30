<?php

namespace App\Services;

use App\Models\DeliverySchedule;
use Carbon\Carbon;

class DeliveryService
{
    public function updateUrgentStatus(): void
    {
        // Mark as urgent if within 3 days
        DeliverySchedule::where('ds_status', 'pending')
            ->whereBetween('date', [Carbon::now(), Carbon::now()->addDays(3)])
            ->update(['ds_status' => 'urgent']);

        // Mark as backlog if past due
        DeliverySchedule::where('ds_status', '!=', 'delivered')
            ->where('date', '<', Carbon::now())
            ->update(['ds_status' => 'backlog']);
    }

    public function getDelayedShipments()
    {
        return DeliverySchedule::with(['product', 'jobOrder'])
            ->delayed()
            ->orderBy('date')
            ->get();
    }
}