<?php

namespace App\Services;

use App\Models\FinishedGood;
use App\Models\Transfer;
use App\Models\DeliverySchedule;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class InventoryService
{
    public function updateStockLevels(int $productId): void
    {
        $fg = FinishedGood::where('product_id', $productId)->first();

        if (!$fg) {
            return;
        }

        // Calculate qty_in from transfers
        $fg->qty_in = Transfer::where('product_id', $productId)
            ->sum('qty_received');

        // Calculate qty_out from completed deliveries
        $fg->qty_out = DeliverySchedule::where('product_id', $productId)
            ->where('status', 'complete')
            ->sum('qty_delivered');

        // Calculate theoretical ending
        $fg->qty_theoretical_ending = $fg->qty_beginning + $fg->qty_in - $fg->qty_out;

        // Calculate variance
        $fg->qty_variance = $fg->qty_actual_ending - $fg->qty_theoretical_ending;

        // Update amounts
        $product = Product::find($productId);
        if ($product) {
            $fg->amount_in = $fg->qty_in * $product->selling_price;
            $fg->amount_out = $fg->qty_out * $product->selling_price;
            $fg->amount_ending = $fg->qty_actual_ending * $product->selling_price;
            $fg->amount_variance = $fg->qty_variance * $product->selling_price;
        }

        $fg->save();
    }

    public function updateAllAgingRanges(): void
    {
        FinishedGood::all()->each(function ($fg) {
            $fg->updateAgingRanges();
        });
    }

    public function getAgingReport(): Collection
    {
        return FinishedGood::with('product')
            ->where('qty_actual_ending', '>', 0)
            ->get()
            ->map(function ($fg) {
                return [
                    'product_code' => $fg->product->product_code,
                    'model_name' => $fg->product->model_name,
                    'qty_actual_ending' => $fg->qty_actual_ending,
                    'days_aging' => $fg->days_aging,
                    'aging_1_30_days' => $fg->aging_1_30_days,
                    'aging_31_60_days' => $fg->aging_31_60_days,
                    'aging_61_90_days' => $fg->aging_61_90_days,
                    'aging_91_120_days' => $fg->aging_91_120_days,
                    'aging_over_120_days' => $fg->aging_over_120_days,
                ];
            });
    }

    public function getLowStockAlerts(): Collection
    {
        return FinishedGood::with('product')
            ->lowStock()
            ->get()
            ->map(function ($fg) {
                return [
                    'product_code' => $fg->product->product_code,
                    'model_name' => $fg->product->model_name,
                    'qty_actual_ending' => $fg->qty_actual_ending,
                    'qty_buffer_stock' => $fg->qty_buffer_stock,
                    'shortage' => $fg->qty_buffer_stock - $fg->qty_actual_ending,
                ];
            });
    }

    public function getInventoryValuation(): array
    {
        $finishedGoods = FinishedGood::with('product')->get();

        $totalQty = $finishedGoods->sum('qty_actual_ending');
        $totalValue = $finishedGoods->sum('amount_ending');
        $totalVariance = $finishedGoods->sum('qty_variance');
        $totalVarianceValue = $finishedGoods->sum('amount_variance');

        return [
            'total_quantity' => $totalQty,
            'total_value' => $totalValue,
            'total_variance_qty' => $totalVariance,
            'total_variance_value' => $totalVarianceValue,
            'average_value_per_unit' => $totalQty > 0 ? round($totalValue / $totalQty, 2) : 0,
            'items_count' => $finishedGoods->count(),
            'low_stock_count' => $finishedGoods->filter(function ($fg) {
                return $fg->qty_actual_ending < $fg->qty_buffer_stock;
            })->count(),
        ];
    }

    public function hasSufficientStock(int $productId, int $requiredQty): bool
    {
        $fg = FinishedGood::where('product_id', $productId)->first();

        if (!$fg) {
            return false;
        }

        return $fg->qty_actual_ending >= $requiredQty;
    }

    public function getStockAvailability(int $productId): array
    {
        $fg = FinishedGood::where('product_id', $productId)->first();

        if (!$fg) {
            return [
                'available' => 0,
                'buffer' => 0,
                'available_above_buffer' => 0,
                'is_low_stock' => true,
            ];
        }

        return [
            'available' => $fg->qty_actual_ending,
            'buffer' => $fg->qty_buffer_stock,
            'available_above_buffer' => max(0, $fg->qty_actual_ending - $fg->qty_buffer_stock),
            'is_low_stock' => $fg->qty_actual_ending < $fg->qty_buffer_stock,
        ];
    }

    public function calculateTurnoverRate(int $productId, int $days = 30): float
    {
        $fg = FinishedGood::where('product_id', $productId)->first();

        if (!$fg || $fg->qty_actual_ending == 0) {
            return 0;
        }

        $recentOut = DeliverySchedule::where('product_id', $productId)
            ->where('status', 'complete')
            ->where('delivery_date', '>=', Carbon::now()->subDays($days))
            ->sum('qty_delivered');

        return $recentOut > 0 ? round($recentOut / $fg->qty_actual_ending, 2) : 0;
    }
}