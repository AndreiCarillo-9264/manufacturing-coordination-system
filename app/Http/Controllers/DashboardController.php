<?php

namespace App\Http\Controllers;

use App\Models\JobOrder;
use App\Models\FinishedGood;
use App\Models\DeliverySchedule;
use App\Models\Transfer;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Main Dashboard - Overview of entire system
        $totalJobOrders   = JobOrder::count();
        $totalProduced    = FinishedGood::sum('in_qty');
        $totalDelivered   = DeliverySchedule::where('ds_status', 'delivered')->sum('qty');
        $currentInventory = FinishedGood::sum('ending_count');

        // Recent activities
        $recentJobOrders  = JobOrder::with('product')->latest()->limit(5)->get();
        $recentTransfers  = Transfer::with('product')->latest('date_transferred')->limit(5)->get();
        $recentDeliveries = DeliverySchedule::with('product')->latest('date')->limit(5)->get();

        // Status breakdown
        $jobOrdersByStatus = JobOrder::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Low stock items for main dashboard widget
        $lowStockProducts = FinishedGood::with('product')
            ->lowStock()
            ->limit(8)
            ->get();

        // Data for "Ordered vs Produced vs Delivered" chart
        $comparisonData = JobOrder::with('product')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($jo) {
                $produced  = $jo->transfers()->sum('qty_received');
                $delivered = $jo->deliverySchedules()
                    ->where('ds_status', 'delivered')
                    ->sum('qty');

                return [
                    'jo_number' => $jo->jo_number,
                    'product'   => $jo->product->model_name ?? $jo->product->product_code ?? 'N/A',
                    'ordered'   => (int) $jo->qty,
                    'produced'  => (int) $produced,
                    'delivered' => (int) $delivered,
                ];
            });

        return view('dashboard.index', compact(
            'totalJobOrders',
            'totalProduced',
            'totalDelivered',
            'currentInventory',
            'recentJobOrders',
            'recentTransfers',
            'recentDeliveries',
            'jobOrdersByStatus',
            'lowStockProducts',
            'comparisonData'
        ));
    }

    public function sales()
    {

        $totalJobOrders = JobOrder::count();
        $pendingJobOrders = JobOrder::pending()->count();
        $approvedJobOrders = JobOrder::approved()->count();
        $cancelledJobOrders = JobOrder::cancelled()->count();

        $totalJoValue = JobOrder::join('products', 'job_orders.product_id', '=', 'products.id')
            ->sum(DB::raw('job_orders.qty * products.selling_price'));

        $recentJobOrders = JobOrder::with(['product', 'encodedBy'])
            ->latest()
            ->limit(10)
            ->get();

        $jobOrdersByWeek = JobOrder::select('week_number', DB::raw('count(*) as count'), DB::raw('sum(qty) as total_qty'))
            ->groupBy('week_number')
            ->orderBy('week_number')
            ->get();

        // Get distinct customer names from products for filter
        $customers = \App\Models\Product::distinct()->whereNotNull('customer')->pluck('customer')->sort()->values();

        return view('dashboard.sales', compact(
            'totalJobOrders',
            'pendingJobOrders',
            'approvedJobOrders',
            'cancelledJobOrders',
            'totalJoValue',
            'recentJobOrders',
            'jobOrdersByWeek',
            'customers'
        ));
    }

    public function production()
    {

        $pendingProduction = JobOrder::approved()->sum('qty');

        $producedToday = Transfer::whereDate('date_received', today())
            ->sum('qty_received');

        $totalOrdered  = JobOrder::whereIn('status', ['approved', 'in_progress', 'completed'])
            ->sum('qty');
        $totalProduced = Transfer::sum('qty_received');
        $completionRate = $totalOrdered > 0 ? round(($totalProduced / $totalOrdered) * 100, 1) : 0;

        $backlogQuantity = JobOrder::whereIn('status', ['approved', 'in_progress'])
            ->sum('qty') - Transfer::sum('qty_received');

        $awaitingJobs = JobOrder::with(['product'])
            ->whereIn('status', ['approved', 'in_progress'])
            ->latest('date_needed')
            ->take(10)
            ->get();

        $recentTransfers = Transfer::with(['jobOrder', 'product', 'receivedBy'])
            ->latest('date_transferred')
            ->limit(10)
            ->get();

        $transfersByStatus = Transfer::select('transfer_status', DB::raw('count(*) as count'))
            ->groupBy('transfer_status')
            ->pluck('count', 'transfer_status');

        return view('dashboard.production', compact(
            'pendingProduction',
            'producedToday',
            'completionRate',
            'backlogQuantity',
            'awaitingJobs',
            'recentTransfers',
            'transfersByStatus'
        ));
    }

    public function inventory()
    {
        // Inventory Dashboard KPIs
        $stocksOnHand    = FinishedGood::sum('ending_count');
        $lowStockItems   = FinishedGood::lowStock()->count();
        $stockInToday    = Transfer::whereDate('date_received', today())->sum('qty_received');
        $stockOutToday   = DeliverySchedule::whereDate('updated_at', today())
            ->where('ds_status', 'delivered')
            ->sum('qty');

        // Low stock products
        $lowStockProducts = FinishedGood::with('product')
            ->lowStock()
            ->limit(10)
            ->get();

        // Inventory value
        $totalInventoryValue = FinishedGood::sum('end_amt');

        // Aging analysis
        $agingData = [
            '1-30 days'     => FinishedGood::sum('range_1_30'),
            '31-60 days'    => FinishedGood::sum('range_31_60'),
            '61-90 days'    => FinishedGood::sum('range_61_90'),
            '91-120 days'   => FinishedGood::sum('range_91_120'),
            'Over 120 days' => FinishedGood::sum('range_over_120'),
        ];

        // Recent inventory-related activities (for "Recent Inventory Updates" section)
        $recentActivities = ActivityLog::query()
            ->whereIn('model_type', [
                'App\\Models\\FinishedGood',
                'App\\Models\\ActualInventory',
                'App\\Models\\Transfer',
                'App\\Models\\DeliverySchedule',
            ])
            ->with('user')           // eager load user name
            ->latest()
            ->take(15)
            ->get();

        return view('dashboard.inventory', compact(
            'stocksOnHand',
            'lowStockItems',
            'stockInToday',
            'stockOutToday',
            'lowStockProducts',
            'totalInventoryValue',
            'agingData',
            'recentActivities'       // ← now passed to the view
        ));
    }

    public function logistics()
    {

        $deliveriesToday     = DeliverySchedule::whereDate('date', today())->count();
        $pendingDeliveries   = DeliverySchedule::pending()->count();
        $delayedShipments    = DeliverySchedule::delayed()->count();
        $completedDeliveries = DeliverySchedule::delivered()->count();

        $recentDeliveries = DeliverySchedule::with(['product', 'jobOrder'])
            ->latest('date')
            ->limit(10)
            ->get();

        $deliveriesByStatus = DeliverySchedule::select('ds_status', DB::raw('count(*) as count'))
            ->groupBy('ds_status')
            ->pluck('count', 'ds_status');

        $delayedList = DeliverySchedule::with(['product', 'jobOrder'])
            ->delayed()
            ->limit(10)
            ->get();

        return view('dashboard.logistics', compact(
            'deliveriesToday',
            'pendingDeliveries',
            'delayedShipments',
            'completedDeliveries',
            'recentDeliveries',
            'deliveriesByStatus',
            'delayedList'
        ));
    }
}