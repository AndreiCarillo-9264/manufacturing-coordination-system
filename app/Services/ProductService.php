<?php

namespace App\Services;

use App\Models\Product;
use App\Models\FinishedGood;
use Illuminate\Support\Collection;

class ProductService
{
    public function getProductsByCustomer(): Collection
    {
        return Product::with('finishedGood')
            ->get()
            ->groupBy('customer');
    }

    public function getActiveProducts(): Collection
    {
        return Product::whereHas('jobOrders', function ($query) {
            $query->whereIn('status', ['approved', 'in_progress']);
        })->get();
    }

    public function getProductStatistics(Product $product): array
    {
        $jobOrders = $product->jobOrders;
        $finishedGood = $product->finishedGood;

        return [
            'total_job_orders' => $jobOrders->count(),
            'active_job_orders' => $jobOrders->whereIn('status', ['approved', 'in_progress'])->count(),
            'completed_job_orders' => $jobOrders->where('status', 'completed')->count(),
            'total_ordered' => $jobOrders->sum('qty_ordered'),
            'total_produced' => $product->transfers->sum('qty_received'),
            'current_stock' => $finishedGood->qty_actual_ending ?? 0,
            'stock_value' => $finishedGood->amount_ending ?? 0,
            'is_low_stock' => $finishedGood 
                ? $finishedGood->qty_actual_ending < $finishedGood->qty_buffer_stock 
                : false,
        ];
    }

    public function calculateProfitability(Product $product): array
    {
        $mc = $product->mc ?? 0;
        $sellingPrice = $product->selling_price ?? 0;
        $grossProfit = $sellingPrice - $mc;
        $marginPercentage = $sellingPrice > 0 
            ? round(($grossProfit / $sellingPrice) * 100, 2) 
            : 0;

        return [
            'selling_price' => $sellingPrice,
            'material_cost' => $mc,
            'gross_profit' => $grossProfit,
            'margin_percentage' => $marginPercentage,
        ];
    }

    public function canDelete(Product $product): bool
    {
        // Cannot delete if has active job orders
        if ($product->jobOrders()->whereNotIn('status', ['completed', 'cancelled'])->exists()) {
            return false;
        }

        // Cannot delete if has stock
        if ($product->finishedGood && $product->finishedGood->qty_actual_ending > 0) {
            return false;
        }

        return true;
    }

    public function getProductsNeedingReorder(): Collection
    {
        return Product::whereHas('finishedGood', function ($query) {
            $query->lowStock();
        })->with('finishedGood')->get();
    }

    public function getProductsBySpecs(string $specs): Collection
    {
        return Product::where('specs', 'like', "%{$specs}%")
            ->with('finishedGood')
            ->get();
    }

    public function getSlowMovingProducts(int $minDays = 90): Collection
    {
        return Product::whereHas('finishedGood', function ($query) use ($minDays) {
            $query->where('days_aging', '>=', $minDays)
                ->where('qty_actual_ending', '>', 0);
        })->with('finishedGood')->get();
    }

    public function getFastMovingProducts(int $limit = 10): Collection
    {
        return Product::whereHas('finishedGood', function ($query) {
            $query->where('qty_out', '>', 0);
        })
            ->with('finishedGood')
            ->get()
            ->sortByDesc(function ($product) {
                $fg = $product->finishedGood;
                if (!$fg || $fg->qty_actual_ending == 0) {
                    return 0;
                }
                return $fg->qty_out / max($fg->qty_actual_ending, 1);
            })
            ->take($limit);
    }

    public function getInactiveProducts(int $months = 6): Collection
    {
        $cutoffDate = now()->subMonths($months);

        return Product::whereDoesntHave('jobOrders', function ($query) use ($cutoffDate) {
            $query->where('created_at', '>=', $cutoffDate);
        })->get();
    }

    public function search(string $query): Collection
    {
        return Product::where(function ($q) use ($query) {
            $q->where('product_code', 'like', "%{$query}%")
                ->orWhere('model_name', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->orWhere('customer', 'like', "%{$query}%");
        })->limit(20)->get();
    }
}