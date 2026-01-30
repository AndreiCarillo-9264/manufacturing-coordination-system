<?php

namespace App\Services;

use App\Models\FinishedGood;
use App\Models\Transfer;
use App\Models\DeliverySchedule;
use Carbon\Carbon;

class InventoryService
{
    public function updateStockLevels(int $productId): void
    {
        $fg = FinishedGood::where('product_id', $productId)->first();
        
        if (!$fg) {
            return;
        }

        // Calculate in_qty from transfers
        $fg->in_qty = Transfer::where('product_id', $productId)
            ->sum('qty_received');

        // Calculate out_qty from delivered schedules
        $fg->out_qty = DeliverySchedule::where('product_id', $productId)
            ->where('ds_status', 'delivered')
            ->sum('delivered_dsd');

        $fg->save();
        $fg->recalculateAll();
    }

    public function getAgingReport()
    {
        return FinishedGood::with('product')
            ->where('ending_count', '>', 0)
            ->get()
            ->map(function ($fg) {
                return [
                    'product_code' => $fg->product->product_code,
                    'model_name' => $fg->product->model_name,
                    'ending_count' => $fg->ending_count,
                    'days' => $fg->days,
                    'range_1_30' => $fg->range_1_30,
                    'range_31_60' => $fg->range_31_60,
                    'range_61_90' => $fg->range_61_90,
                    'range_91_120' => $fg->range_91_120,
                    'range_over_120' => $fg->range_over_120,
                ];
            });
    }

    public function getLowStockAlerts()
    {
        return FinishedGood::with('product')
            ->lowStock()
            ->get()
            ->map(function ($fg) {
                return [
                    'product_code' => $fg->product->product_code,
                    'model_name' => $fg->product->model_name,
                    'ending_count' => $fg->ending_count,
                    'buffer_stocks' => $fg->buffer_stocks,
                    'shortage' => $fg->buffer_stocks - $fg->ending_count,
                ];
            });
    }
}