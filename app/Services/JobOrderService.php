<?php

namespace App\Services;

use App\Models\JobOrder;
use App\Models\Transfer;
use App\Models\DeliverySchedule;
use Illuminate\Support\Collection;

class JobOrderService
{
    public function updateBalance(JobOrder $jobOrder): void
    {
        // Calculate total from transfers
        $totalTransferred = Transfer::where('job_order_id', $jobOrder->id)
            ->sum('qty_received');

        // Calculate total from delivery schedules
        $totalInDeliverySchedule = DeliverySchedule::where('job_order_id', $jobOrder->id)
            ->sum('qty_scheduled');

        // Update job order quantities
        $jobOrder->qty_transferred_to_ppqc = $totalTransferred;
        $jobOrder->qty_in_delivery_schedule = $totalInDeliverySchedule;
        
        // Recalculate balance
        $jobOrder->calculateBalance();
    }

    public function checkAndUpdateStatus(JobOrder $jobOrder): void
    {
        $this->updateBalance($jobOrder);

        if ($jobOrder->qty_balance == 0 && $jobOrder->status == 'in_progress') {
            $jobOrder->status = 'completed';
            $jobOrder->save();
        }
    }

    public function getFulfillmentDetails(JobOrder $jobOrder): array
    {
        $this->updateBalance($jobOrder);

        $totalTransferred = $jobOrder->qty_transferred_to_ppqc ?? 0;
        $totalScheduled = $jobOrder->qty_in_delivery_schedule ?? 0;
        $totalOrdered = $jobOrder->qty_ordered;

        return [
            'ordered' => $totalOrdered,
            'transferred' => $totalTransferred,
            'scheduled' => $totalScheduled,
            'balance' => $jobOrder->qty_balance,
            'fulfillment_percentage' => $totalOrdered > 0 
                ? round((($totalTransferred + $totalScheduled) / $totalOrdered) * 100, 2) 
                : 0,
            'transfer_percentage' => $totalOrdered > 0 
                ? round(($totalTransferred / $totalOrdered) * 100, 2) 
                : 0,
            'schedule_percentage' => $totalOrdered > 0 
                ? round(($totalScheduled / $totalOrdered) * 100, 2) 
                : 0,
        ];
    }

    public function getOverdueJobOrders(): Collection
    {
        return JobOrder::with(['product'])
            ->whereIn('status', ['approved', 'in_progress'])
            ->where('date_needed', '<', now())
            ->orderBy('date_needed')
            ->get();
    }

    public function getUpcomingJobOrders(int $days = 7): Collection
    {
        return JobOrder::with(['product'])
            ->whereIn('status', ['approved', 'in_progress'])
            ->whereBetween('date_needed', [now(), now()->addDays($days)])
            ->orderBy('date_needed')
            ->get();
    }

    public function getStatistics(): array
    {
        $total = JobOrder::count();
        $pending = JobOrder::pending()->count();
        $approved = JobOrder::approved()->count();
        $inProgress = JobOrder::inProgress()->count();
        $completed = JobOrder::completed()->count();
        $cancelled = JobOrder::cancelled()->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'approved' => $approved,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'active' => $pending + $approved + $inProgress,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
        ];
    }

    public function determineFulfillmentStatus(JobOrder $jobOrder): ?string
    {
        $this->updateBalance($jobOrder);

        if ($jobOrder->qty_balance == 0) {
            return 'full';
        }

        $totalFulfilled = ($jobOrder->qty_transferred_to_ppqc ?? 0) + ($jobOrder->qty_in_delivery_schedule ?? 0);

        if ($totalFulfilled > $jobOrder->qty_ordered) {
            return 'excess';
        }

        if ($totalFulfilled > 0) {
            return 'balance';
        }

        return null;
    }

    public function updateFulfillmentStatus(JobOrder $jobOrder): void
    {
        $status = $this->determineFulfillmentStatus($jobOrder);
        
        if ($status !== null) {
            $jobOrder->fulfillment_status = $status;
            $jobOrder->save();
        }
    }

    public function canDelete(JobOrder $jobOrder): bool
    {
        // Only pending or cancelled orders can be deleted
        if (!in_array($jobOrder->status, ['pending', 'cancelled'])) {
            return false;
        }

        // Check if there are any transfers
        if ($jobOrder->transfers()->exists()) {
            return false;
        }

        // Check if there are any delivery schedules
        if ($jobOrder->deliverySchedules()->exists()) {
            return false;
        }

        return true;
    }
}