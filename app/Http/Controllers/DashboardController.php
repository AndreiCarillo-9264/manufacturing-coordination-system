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
        $totalJobOrders = JobOrder::count();
        $totalProduced = Transfer::sum('qty_received');
        $totalDelivered = DeliverySchedule::where('status', 'complete')->sum('qty_delivered');
        $currentInventory = FinishedGood::sum('qty_actual_ending');

        // Recent activities
        $recentJobOrders = JobOrder::with('product')
            ->latest()
            ->limit(5)
            ->get();

        $recentTransfers = Transfer::with('product')
            ->latest('date_transferred')
            ->limit(5)
            ->get();

        $recentDeliveries = DeliverySchedule::with('product')
            ->latest('delivery_date')
            ->limit(5)
            ->get();

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
                $produced = $jo->transfers()->sum('qty_received');
                $delivered = $jo->deliverySchedules()
                    ->where('status', 'complete')
                    ->sum('qty_delivered');

                return [
                    'jo_number' => $jo->jo_number,
                    'product' => $jo->product->model_name ?? $jo->product->product_code ?? 'N/A',
                    'ordered' => (int) $jo->qty_ordered,
                    'produced' => (int) $produced,
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
            ->sum(DB::raw('job_orders.qty_ordered * products.selling_price'));

        $recentJobOrders = JobOrder::with(['product', 'encodedBy'])
            ->latest()
            ->limit(10)
            ->get();

        $jobOrdersByWeek = JobOrder::select('week_number', DB::raw('count(*) as count'), DB::raw('sum(qty_ordered) as total_qty'))
            ->groupBy('week_number')
            ->orderBy('week_number')
            ->get();

        // Get distinct customer names from products for filter
        $customers = \App\Models\Product::whereNotNull('customer')
            ->distinct()
            ->pluck('customer')
            ->sort()
            ->values();

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
        $pendingProduction = JobOrder::approved()->sum('qty_ordered');

        $producedToday = Transfer::whereDate('date_received', today())
            ->sum('qty_received');

        $totalOrdered = JobOrder::whereIn('status', ['approved', 'in_progress', 'completed'])
            ->sum('qty_ordered');
        $totalProduced = Transfer::sum('qty_received');
        $completionRate = $totalOrdered > 0 ? round(($totalProduced / $totalOrdered) * 100, 1) : 0;

        $backlogQuantity = JobOrder::whereIn('status', ['approved', 'in_progress'])
            ->sum('qty_ordered') - Transfer::sum('qty_received');

        $awaitingJobs = JobOrder::with(['product'])
            ->whereIn('status', ['approved', 'in_progress'])
            ->latest('date_needed')
            ->take(10)
            ->get();

        $recentTransfers = Transfer::with(['jobOrder', 'product', 'receivedBy'])
            ->latest('date_transferred')
            ->limit(10)
            ->get();

        $transfersByStatus = Transfer::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

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
        $stocksOnHand = FinishedGood::sum('qty_actual_ending');
        $lowStockItems = FinishedGood::lowStock()->count();
        $stockInToday = Transfer::whereDate('date_received', today())->sum('qty_received');
        $stockOutToday = DeliverySchedule::whereDate('updated_at', today())
            ->where('status', 'complete')
            ->sum('qty_delivered');

        // Low stock products
        $lowStockProducts = FinishedGood::with('product')
            ->lowStock()
            ->limit(10)
            ->get();

        // Inventory value
        $totalInventoryValue = FinishedGood::sum('amount_ending');

        // Aging analysis
        $agingData = [
            '1-30 days' => FinishedGood::sum('aging_1_30_days'),
            '31-60 days' => FinishedGood::sum('aging_31_60_days'),
            '61-90 days' => FinishedGood::sum('aging_61_90_days'),
            '91-120 days' => FinishedGood::sum('aging_91_120_days'),
            'Over 120 days' => FinishedGood::sum('aging_over_120_days'),
        ];

        // Recent inventory-related activities
        $recentActivities = ActivityLog::query()
            ->whereIn('model_type', [
                'App\\Models\\FinishedGood',
                'App\\Models\\ActualInventory',
                'App\\Models\\Transfer',
                'App\\Models\\DeliverySchedule',
            ])
            ->with('user')
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
            'recentActivities'
        ));
    }

    public function logistics()
    {
        $deliveriesToday = DeliverySchedule::whereDate('delivery_date', today())->count();
        $pendingDeliveries = DeliverySchedule::pending()->count();
        $delayedShipments = DeliverySchedule::delayed()->count();
        $completedDeliveries = DeliverySchedule::complete()->count();

        $recentDeliveries = DeliverySchedule::with(['product', 'jobOrder'])
            ->latest('delivery_date')
            ->limit(10)
            ->get();

        $deliveriesByStatus = DeliverySchedule::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

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