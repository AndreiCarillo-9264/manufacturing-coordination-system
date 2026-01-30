<?php

namespace App\Services;

use App\Models\JobOrder;
use App\Models\Transfer;
use App\Models\DeliverySchedule;
use Illuminate\Support\Facades\DB;

class JobOrderService
{
    public function updateBalance(JobOrder $jobOrder): void
    {
        // Calculate total from transfers
        $totalTransferred = Transfer::where('jo_id', $jobOrder->id)
            ->sum('qty_received');

        // Calculate total from delivery schedules
        $totalDelivered = DeliverySchedule::where('jo_id', $jobOrder->id)
            ->sum('delivered_dsd');

        // Update job order
        $jobOrder->ppqc_transfer = $totalTransferred;
        $jobOrder->ds_quantity = $totalDelivered;
        $jobOrder->calculateBalance();
    }

    public function checkAndUpdateStatus(JobOrder $jobOrder): void
    {
        if ($jobOrder->jo_balance == 0 && $jobOrder->status == 'in_progress') {
            $jobOrder->status = 'completed';
            $jobOrder->save();
        }
    }
}