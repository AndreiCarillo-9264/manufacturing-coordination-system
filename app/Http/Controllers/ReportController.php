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
    /**
     * Job Orders Report View
     */
    public function jobOrders(Request $request)
    {
        $query = JobOrder::with(['product', 'encodedBy']);

        // Apply filters
        if ($request->filled('customer')) {
            $query->whereHas('product', function($q) use ($request) {
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
        $totalQty = $jobOrders->sum('qty');
        $totalAmount = $jobOrders->sum(function($jo) {
            return $jo->qty * $jo->product->selling_price;
        });

        return view('reports.job-orders', compact('jobOrders', 'totalQty', 'totalAmount'));
    }

    /**
     * Generate Job Orders PDF Report
     */
    public function jobOrdersPdf(Request $request)
    {
        $query = JobOrder::with(['product', 'encodedBy']);

        // Apply same filters as above
        if ($request->filled('customer')) {
            $query->whereHas('product', function($q) use ($request) {
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
        $totalQty = $jobOrders->sum('qty');
        $totalAmount = $jobOrders->sum(function($jo) {
            return $jo->qty * $jo->product->selling_price;
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

    /**
     * Inventory Report View
     */
    public function inventory(Request $request)
    {
        $query = FinishedGood::with('product');

        // Filter by low stock
        if ($request->has('low_stock')) {
            $query->lowStock();
        }

        // Filter by customer
        if ($request->filled('customer')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('customer', $request->customer);
            });
        }

        $finishedGoods = $query->orderBy('ending_count', 'desc')->get();

        // Calculate totals
        $totalStock = $finishedGoods->sum('ending_count');
        $totalValue = $finishedGoods->sum('end_amt');
        $totalVariance = $finishedGoods->sum('variance_count');
        $totalVarianceAmount = $finishedGoods->sum('variance_amount');

        return view('reports.inventory', compact(
            'finishedGoods', 
            'totalStock', 
            'totalValue', 
            'totalVariance',
            'totalVarianceAmount'
        ));
    }

    /**
     * Generate Inventory PDF Report
     */
    public function inventoryPdf(Request $request)
    {
        $query = FinishedGood::with('product');

        // Apply same filters
        if ($request->has('low_stock')) {
            $query->lowStock();
        }

        if ($request->filled('customer')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('customer', $request->customer);
            });
        }

        $finishedGoods = $query->orderBy('ending_count', 'desc')->get();

        // Calculate totals
        $totalStock = $finishedGoods->sum('ending_count');
        $totalValue = $finishedGoods->sum('end_amt');
        $totalVariance = $finishedGoods->sum('variance_count');
        $totalVarianceAmount = $finishedGoods->sum('variance_amount');

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
}