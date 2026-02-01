<?php

namespace App\Services;

use App\Models\DeliverySchedule;
use App\Models\FinishedGood;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DeliveryService
{
    public function updateUrgentStatus(): void
    {
        // Mark as urgent if within 3 days
        DeliverySchedule::where('status', 'pending')
            ->whereBetween('delivery_date', [Carbon::now(), Carbon::now()->addDays(3)])
            ->update(['status' => 'urgent']);

        // Mark as backlog if past due
        DeliverySchedule::where('status', '!=', 'complete')
            ->where('delivery_date', '<', Carbon::now())
            ->update(['status' => 'backlog']);
    }

    public function getDelayedShipments(): Collection
    {
        return DeliverySchedule::with(['product', 'jobOrder'])
            ->delayed()
            ->orderBy('delivery_date')
            ->get();
    }

    public function getUpcomingDeliveries(int $days = 7): Collection
    {
        return DeliverySchedule::with(['product', 'jobOrder'])
            ->where('status', 'pending')
            ->whereBetween('delivery_date', [Carbon::now(), Carbon::now()->addDays($days)])
            ->orderBy('delivery_date')
            ->get();
    }

    public function calculateFulfillmentRate(): array
    {
        $total = DeliverySchedule::count();
        $completed = DeliverySchedule::where('status', 'complete')->count();
        $delayed = DeliverySchedule::delayed()->count();

        return [
            'total' => $total,
            'completed' => $completed,
            'delayed' => $delayed,
            'on_time' => $completed - $delayed,
            'fulfillment_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            'on_time_rate' => $completed > 0 ? round((($completed - $delayed) / $completed) * 100, 2) : 0,
        ];
    }

    public function canFulfillFromStock(DeliverySchedule $deliverySchedule): bool
    {
        $finishedGood = $deliverySchedule->product->finishedGood;

        if (!$finishedGood) {
            return false;
        }

        return $finishedGood->qty_actual_ending >= $deliverySchedule->qty_scheduled;
    }

    public function calculateBacklog(DeliverySchedule $deliverySchedule): int
    {
        $finishedGood = $deliverySchedule->product->finishedGood;

        if (!$finishedGood) {
            return $deliverySchedule->qty_scheduled;
        }

        $available = ($deliverySchedule->qty_fg_stocks ?? 0) + ($deliverySchedule->qty_transferred ?? 0);
        
        return max(0, $deliverySchedule->qty_scheduled - $available);
    }
}