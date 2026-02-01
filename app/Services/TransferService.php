<?php

namespace App\Services;

use App\Models\Transfer;
use App\Models\JobOrder;
use App\Models\FinishedGood;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class TransferService
{
    public function calculateJitDays(Carbon $transferDate, Carbon $deliveryDate): int
    {
        return $deliveryDate->diffInDays($transferDate);
    }

    public function determineStatus(int $qtyTransferred, int $qtyReceived): string
    {
        return $qtyReceived >= $qtyTransferred ? 'complete' : 'balance';
    }

    public function updateFinishedGoodsInventory(Transfer $transfer): void
    {
        $finishedGood = $transfer->product->finishedGood;

        if (!$finishedGood) {
            return;
        }

        $finishedGood->qty_in += $transfer->qty_received;
        $finishedGood->amount_in += $transfer->total_amount;
        $finishedGood->date_last_in = $transfer->date_received;
        $finishedGood->save();

        // Recalculate theoretical ending
        $finishedGood->calculateTheoreticalEnding();
    }

    public function reverseFinishedGoodsInventory(Transfer $transfer): void
    {
        $finishedGood = $transfer->product->finishedGood;

        if (!$finishedGood) {
            return;
        }

        $finishedGood->qty_in -= $transfer->qty_received;
        $finishedGood->amount_in -= $transfer->total_amount;
        $finishedGood->save();

        // Recalculate theoretical ending
        $finishedGood->calculateTheoreticalEnding();
    }

    public function getJobOrderTransfersSummary(JobOrder $jobOrder): array
    {
        $transfers = $jobOrder->transfers;

        return [
            'total_transfers' => $transfers->count(),
            'total_qty_transferred' => $transfers->sum('qty_transferred'),
            'total_qty_received' => $transfers->sum('qty_received'),
            'total_amount' => $transfers->sum('total_amount'),
            'complete_transfers' => $transfers->where('status', 'complete')->count(),
            'balance_transfers' => $transfers->where('status', 'balance')->count(),
            'average_jit_days' => $transfers->avg('jit_days') ?? 0,
        ];
    }

    public function getTransfersBySection(
        string $section,
        ?Carbon $dateFrom = null,
        ?Carbon $dateTo = null
    ): Collection {
        $query = Transfer::with(['product', 'jobOrder'])
            ->where('section', $section);

        if ($dateFrom) {
            $query->where('date_transferred', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('date_transferred', '<=', $dateTo);
        }

        return $query->orderBy('date_transferred', 'desc')->get();
    }

    public function getPerformanceMetrics(?Carbon $dateFrom = null, ?Carbon $dateTo = null): array
    {
        $query = Transfer::query();

        if ($dateFrom) {
            $query->where('date_transferred', '>=', $dateFrom);
        }

        if ($dateTo) {
            $query->where('date_transferred', '<=', $dateTo);
        }

        $transfers = $query->get();

        return [
            'total_transfers' => $transfers->count(),
            'total_quantity' => $transfers->sum('qty_received'),
            'total_value' => $transfers->sum('total_amount'),
            'average_jit_days' => round($transfers->avg('jit_days') ?? 0, 2),
            'on_time_transfers' => $transfers->where('jit_days', '<=', 0)->count(),
            'late_transfers' => $transfers->where('jit_days', '>', 0)->count(),
            'on_time_percentage' => $transfers->count() > 0 
                ? round(($transfers->where('jit_days', '<=', 0)->count() / $transfers->count()) * 100, 2)
                : 0,
        ];
    }

    public function canDelete(Transfer $transfer): bool
    {
        // Transfers can typically be deleted if they haven't been fully integrated
        // Add your business logic here
        return true;
    }

    public function getDailySummary(Carbon $date): array
    {
        $transfers = Transfer::whereDate('date_transferred', $date)->get();

        return [
            'date' => $date->toDateString(),
            'total_transfers' => $transfers->count(),
            'total_quantity' => $transfers->sum('qty_received'),
            'total_value' => $transfers->sum('total_amount'),
            'sections' => $transfers->groupBy('section')->map(function ($sectionTransfers) {
                return [
                    'count' => $sectionTransfers->count(),
                    'quantity' => $sectionTransfers->sum('qty_received'),
                    'value' => $sectionTransfers->sum('total_amount'),
                ];
            }),
        ];
    }
}