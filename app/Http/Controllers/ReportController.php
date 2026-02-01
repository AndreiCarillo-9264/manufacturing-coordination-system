<?php

namespace App\Http\Controllers;

use App\Models\JobOrder;
use App\Models\FinishedGood;
use App\Models\Product;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function jobOrders(Request $request)
    {
        $query = JobOrder::with(['product', 'encodedBy']);

        // Apply filters
        if ($request->filled('customer')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('customer', $request->customer);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('date_needed', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date_needed', '<=', $request->date_to);
        }

        $jobOrders = $query->orderBy('date_needed')->get();

        // Calculate totals
        $totalQty = $jobOrders->sum('qty_ordered');
        $totalAmount = $jobOrders->sum(function ($jo) {
            return $jo->qty_ordered * $jo->product->selling_price;
        });

        // Get customers for filter
        $customers = Product::whereNotNull('customer')
            ->distinct()
            ->pluck('customer')
            ->sort()
            ->values();

        return view('reports.job-orders', compact('jobOrders', 'totalQty', 'totalAmount', 'customers'));
    }

    public function jobOrdersPdf(Request $request)
    {
        $query = JobOrder::with(['product', 'encodedBy']);

        // Apply same filters as above
        if ($request->filled('customer')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('customer', $request->customer);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('date_needed', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date_needed', '<=', $request->date_to);
        }

        $jobOrders = $query->orderBy('date_needed')->get();

        // Calculate totals
        $totalQty = $jobOrders->sum('qty_ordered');
        $totalAmount = $jobOrders->sum(function ($jo) {
            return $jo->qty_ordered * $jo->product->selling_price;
        });

        $filters = [
            'customer' => $request->filled('customer') ? $request->customer : 'All',
            'status' => $request->status ?? 'All',
            'date_from' => $request->date_from ?? 'Start',
            'date_to' => $request->date_to ?? 'End',
        ];

        $pdf = Pdf::loadView('reports.pdf.job-orders', compact('jobOrders', 'totalQty', 'totalAmount', 'filters'));

        return $pdf->download('job-orders-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function inventory(Request $request)
    {
        $query = FinishedGood::with('product');

        // Filter by low stock
        if ($request->has('low_stock')) {
            $query->lowStock();
        }

        // Filter by customer
        if ($request->filled('customer')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('customer', $request->customer);
            });
        }

        $finishedGoods = $query->orderBy('qty_actual_ending', 'desc')->get();

        // Calculate totals
        $totalStock = $finishedGoods->sum('qty_actual_ending');
        $totalValue = $finishedGoods->sum('amount_ending');
        $totalVariance = $finishedGoods->sum('qty_variance');
        $totalVarianceAmount = $finishedGoods->sum('amount_variance');

        // Get customers for filter
        $customers = Product::whereNotNull('customer')
            ->distinct()
            ->pluck('customer')
            ->sort()
            ->values();

        return view('reports.inventory', compact(
            'finishedGoods',
            'totalStock',
            'totalValue',
            'totalVariance',
            'totalVarianceAmount',
            'customers'
        ));
    }

    public function inventoryPdf(Request $request)
    {
        $query = FinishedGood::with('product');

        // Apply same filters
        if ($request->has('low_stock')) {
            $query->lowStock();
        }

        if ($request->filled('customer')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('customer', $request->customer);
            });
        }

        $finishedGoods = $query->orderBy('qty_actual_ending', 'desc')->get();

        // Calculate totals
        $totalStock = $finishedGoods->sum('qty_actual_ending');
        $totalValue = $finishedGoods->sum('amount_ending');
        $totalVariance = $finishedGoods->sum('qty_variance');
        $totalVarianceAmount = $finishedGoods->sum('amount_variance');

        $filters = [
            'customer' => $request->filled('customer') ? $request->customer : 'All',
            'type' => $request->has('low_stock') ? 'Low Stock Items' : 'All Items',
        ];

        $pdf = Pdf::loadView('reports.pdf.inventory', compact(
            'finishedGoods',
            'totalStock',
            'totalValue',
            'totalVariance',
            'totalVarianceAmount',
            'filters'
        ));

        return $pdf->download('inventory-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function aging(Request $request)
    {
        $query = FinishedGood::with('product')
            ->where('qty_actual_ending', '>', 0);

        // Filter by customer
        if ($request->filled('customer')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('customer', $request->customer);
            });
        }

        $finishedGoods = $query->orderBy('days_aging', 'desc')->get();

        // Calculate aging totals
        $agingTotals = [
            '1-30' => $finishedGoods->sum('aging_1_30_days'),
            '31-60' => $finishedGoods->sum('aging_31_60_days'),
            '61-90' => $finishedGoods->sum('aging_61_90_days'),
            '91-120' => $finishedGoods->sum('aging_91_120_days'),
            'over_120' => $finishedGoods->sum('aging_over_120_days'),
        ];

        // Get customers for filter
        $customers = Product::whereNotNull('customer')
            ->distinct()
            ->pluck('customer')
            ->sort()
            ->values();

        return view('reports.aging', compact('finishedGoods', 'agingTotals', 'customers'));
    }
}