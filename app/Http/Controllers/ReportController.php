<?php

namespace App\Http\Controllers;

use App\Models\JobOrder;
use App\Models\FinishedGood;
use App\Models\Product;
use App\Models\DeliverySchedule;
use App\Models\EndorseToLogistic;
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
                $q->where('customer_name', $request->customer);
            });
        }

        if ($request->filled('status')) {
            $query->where('jo_status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('date_needed', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date_needed', '<=', $request->date_to);
        }

        $jobOrders = $query->orderBy('date_needed')->get();

        // Calculate totals
        $totalQty = $jobOrders->sum('quantity');
        $totalAmount = $jobOrders->sum(function ($jo) {
            return $jo->quantity * $jo->product->selling_price;
        });

        // Determine report currency if uniform across results
        $currencies = $jobOrders->map(fn($j) => strtoupper($j->product->currency ?? 'PHP'))->unique();
        $reportCurrency = $currencies->count() === 1 ? $currencies->first() : null;

        // Get customers for filter
        $customers = Product::whereNotNull('customer_name')
            ->distinct()
            ->pluck('customer_name')
            ->sort()
            ->values();

        return view('reports.job-orders', compact('jobOrders', 'totalQty', 'totalAmount', 'customers', 'reportCurrency'));
    }

    public function jobOrdersPdf(Request $request)
    {
        $query = JobOrder::with(['product', 'encodedBy']);

        // Apply same filters as above
        if ($request->filled('customer')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('customer_name', $request->customer);
            });
        }

        if ($request->filled('status')) {
            $query->where('jo_status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('date_needed', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date_needed', '<=', $request->date_to);
        }

        $jobOrders = $query->orderBy('date_needed')->get();

        // Calculate totals
        $totalQty = $jobOrders->sum('quantity');
        $totalAmount = $jobOrders->sum(function ($jo) {
            return $jo->quantity * $jo->product->selling_price;
        });

        $currencies = $jobOrders->map(fn($j) => strtoupper($j->product->currency ?? 'PHP'))->unique();
        $reportCurrency = $currencies->count() === 1 ? $currencies->first() : null;

        $filters = [
            'customer' => $request->filled('customer') ? $request->customer : 'All',
            'status' => $request->filled('status') ? ucfirst($request->status) : 'All',
            'date_from' => $request->filled('date_from') ? $request->date_from : 'Start',
            'date_to' => $request->filled('date_to') ? $request->date_to : 'End',
        ];

        $pdf = Pdf::loadView('reports.pdf.job-orders', compact(
            'jobOrders',
            'totalQty',
            'totalAmount',
            'filters',
            'reportCurrency'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('job-orders-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function inventory(Request $request)
    {
        $query = FinishedGood::with('product');

        // Apply filters
        if ($request->filled('customer')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('customer_name', $request->customer);
            });
        }

        if ($request->has('low_stock')) {
            $query->lowStock();
        }

        $finishedGoods = $query->orderBy('current_qty')->get();

        // Calculate totals
        $totalStock = $finishedGoods->sum('current_qty');
        $totalValue = $finishedGoods->sum('end_amount');
        $totalVariance = $finishedGoods->sum('variance_qty');
        $totalVarianceAmount = $finishedGoods->sum('variance_amount');

        // Determine report currency if uniform across results
        $currencies = $finishedGoods->map(fn($f) => strtoupper($f->product->currency ?? 'PHP'))->unique();
        $reportCurrency = $currencies->count() === 1 ? $currencies->first() : null;

        // Get customers for filter
        $customers = Product::whereNotNull('customer_name')
            ->distinct()
            ->pluck('customer_name')
            ->sort()
            ->values();

        return view('reports.inventory', compact('finishedGoods', 'totalStock', 'totalValue', 'totalVariance', 'totalVarianceAmount', 'customers', 'reportCurrency'));
    }

    public function inventoryPdf(Request $request)
    {
        $query = FinishedGood::with('product');

        if ($request->filled('customer')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('customer_name', $request->customer);
            });
        }

        if ($request->has('low_stock')) {
            $query->lowStock();
        }

        $finishedGoods = $query->orderBy('current_qty')->get();

        $totalStock = $finishedGoods->sum('current_qty');
        $totalValue = $finishedGoods->sum('end_amount');
        $totalVariance = $finishedGoods->sum('variance_qty');
        $totalVarianceAmount = $finishedGoods->sum('variance_amount');

        // Determine report currency if uniform across results
        $currencies = $finishedGoods->map(fn($f) => strtoupper($f->product->currency ?? 'PHP'))->unique();
        $reportCurrency = $currencies->count() === 1 ? $currencies->first() : null;

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
            'filters',
            'reportCurrency'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('inventory-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function aging(Request $request)
    {
        $query = FinishedGood::with('product')
            ->where('current_qty', '>', 0);

        // Filter by customer
        if ($request->filled('customer')) {
            $query->whereHas('product', function ($q) use ($request) {
                $q->where('customer_name', $request->customer);
            });
        }

        $finishedGoods = $query->orderBy('number_of_days', 'desc')->get();

        // Calculate aging totals
        $agingTotals = [
            '1-30' => $finishedGoods->sum('age_1_30_days'),
            '31-60' => $finishedGoods->sum('age_31_60_days'),
            '61-90' => $finishedGoods->sum('age_61_90_days'),
            '91-120' => $finishedGoods->sum('age_91_120_days'),
            'over_120' => $finishedGoods->sum('age_over_120_days'),
        ];

        // Get customers for filter
        $customers = Product::whereNotNull('customer_name')
            ->distinct()
            ->pluck('customer_name')
            ->sort()
            ->values();

        return view('reports.aging', compact('finishedGoods', 'agingTotals', 'customers'));
    }

    /**
     * Production Report
     */
    public function production(Request $request)
    {
        $query = JobOrder::with(['product', 'encodedBy']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('jo_status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('date_needed', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date_needed', '<=', $request->date_to);
        }

        $jobOrders = $query->orderBy('date_needed')->get();

        // Calculate production metrics
        $totalQty = $jobOrders->sum('quantity');
        $totalDeliverable = $jobOrders->sum(fn($jo) => $jo->deliverable_qty ?? 0);
        $completionRate = $totalQty > 0 ? round(($totalDeliverable / $totalQty) * 100, 2) : 0;

        // Get unique statuses for filter
        $statuses = JobOrder::distinct()->pluck('jo_status')->sort()->values();

        return view('reports.production', compact('jobOrders', 'totalQty', 'totalDeliverable', 'completionRate', 'statuses'));
    }

    public function productionPdf(Request $request)
    {
        $query = JobOrder::with(['product', 'encodedBy']);

        if ($request->filled('status')) {
            $query->where('jo_status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('date_needed', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date_needed', '<=', $request->date_to);
        }

        $jobOrders = $query->orderBy('date_needed')->get();

        $totalQty = $jobOrders->sum('quantity');
        $totalDeliverable = $jobOrders->sum(fn($jo) => $jo->deliverable_qty ?? 0);
        $completionRate = $totalQty > 0 ? round(($totalDeliverable / $totalQty) * 100, 2) : 0;

        $filters = [
            'status' => $request->filled('status') ? ucfirst($request->status) : 'All',
            'date_from' => $request->filled('date_from') ? $request->date_from : 'Start',
            'date_to' => $request->filled('date_to') ? $request->date_to : 'End',
        ];

        $pdf = Pdf::loadView('reports.pdf.production', compact(
            'jobOrders',
            'totalQty',
            'totalDeliverable',
            'completionRate',
            'filters'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('production-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Delivery Reports
     */
    public function deliveries(Request $request)
    {
        $query = DeliverySchedule::with(['product', 'jobOrder']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('ds_status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('delivery_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('delivery_date', '<=', $request->date_to);
        }

        $deliveries = $query->orderBy('delivery_date', 'desc')->get();

        // Calculate totals
        $totalQty = $deliveries->sum('quantity');
        $deliveredQty = $deliveries->where('ds_status', 'DELIVERED')->sum('quantity');

        $statuses = DeliverySchedule::distinct()->pluck('ds_status')->sort()->values();

        return view('reports.deliveries', compact('deliveries', 'totalQty', 'deliveredQty', 'statuses'));
    }

    public function deliveriesPdf(Request $request)
    {
        $query = DeliverySchedule::with(['product', 'jobOrder']);

        if ($request->filled('status')) {
            $query->where('ds_status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('delivery_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('delivery_date', '<=', $request->date_to);
        }

        $deliveries = $query->orderBy('delivery_date', 'desc')->get();

        $totalQty = $deliveries->sum('quantity');
        $deliveredQty = $deliveries->where('ds_status', 'DELIVERED')->sum('quantity');

        $filters = [
            'status' => $request->filled('status') ? ucfirst($request->status) : 'All',
            'date_from' => $request->filled('date_from') ? $request->date_from : 'Start',
            'date_to' => $request->filled('date_to') ? $request->date_to : 'End',
        ];

        $pdf = Pdf::loadView('reports.pdf.deliveries', compact(
            'deliveries',
            'totalQty',
            'deliveredQty',
            'filters'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('delivery-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /**
     * Logistics/Endorsement Reports
     */
    public function logistics(Request $request)
    {
        $query = EndorseToLogistic::with(['product']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $endorsements = $query->orderBy('date', 'desc')->get();

        // Calculate totals
        $totalQty = $endorsements->sum('quantity');
        $approvedQty = $endorsements->where('status', 'approved')->sum('quantity');
        $completedQty = $endorsements->where('status', 'completed')->sum('quantity');

        $statuses = EndorseToLogistic::distinct()->pluck('status')->sort()->values();

        return view('reports.logistics', compact('endorsements', 'totalQty', 'approvedQty', 'completedQty', 'statuses'));
    }

    public function logisticsPdf(Request $request)
    {
        $query = EndorseToLogistic::with(['product']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $endorsements = $query->orderBy('date', 'desc')->get();

        $totalQty = $endorsements->sum('quantity');
        $approvedQty = $endorsements->where('status', 'approved')->sum('quantity');
        $completedQty = $endorsements->where('status', 'completed')->sum('quantity');

        $filters = [
            'status' => $request->filled('status') ? ucfirst($request->status) : 'All',
            'date_from' => $request->filled('date_from') ? $request->date_from : 'Start',
            'date_to' => $request->filled('date_to') ? $request->date_to : 'End',
        ];

        $pdf = Pdf::loadView('reports.pdf.logistics', compact(
            'endorsements',
            'totalQty',
            'approvedQty',
            'completedQty',
            'filters'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('logistics-report-' . now()->format('Y-m-d') . '.pdf');
    }
}