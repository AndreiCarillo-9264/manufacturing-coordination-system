<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\JobOrder;
use App\Models\FinishedGood;
use App\Models\DeliverySchedule;
use App\Models\InventoryTransfer;   // ← was using Transfer, changed to InventoryTransfer
use App\Models\ActualInventory;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Main Dashboard - Overview
        $totalJobOrders   = JobOrder::count();
        $totalProduced    = InventoryTransfer::sum('quantity_received');
        $totalDelivered   = DeliverySchedule::where('ds_status', 'DELIVERED')->sum('delivered_quantity');
        $currentInventory = FinishedGood::sum('current_qty');

        // Recent activities
        $recentJobOrders = JobOrder::with('product')
            ->latest()
            ->limit(5)
            ->get();

        $recentTransfers = InventoryTransfer::with('product')
            ->latest('date_received')
            ->limit(5)
            ->get();

        $recentDeliveries = DeliverySchedule::with('product')
            ->latest('delivery_date')
            ->limit(5)
            ->get();

        // Status breakdown (Job Orders)
        $jobOrdersByStatus = JobOrder::select('jo_status', DB::raw('count(*) as count'))
            ->groupBy('jo_status')
            ->pluck('count', 'jo_status')
            ->toArray();

        // Low stock items (including zero stock)
        $lowStockProducts = FinishedGood::with('product')
            ->where('current_qty', '<=', 10)           // Low stock threshold: 0-10 items
            ->latest('updated_at')
            ->limit(8)
            ->get();

        // Ordered vs Produced vs Delivered (last 10 JOs)
        $comparisonData = JobOrder::with('product')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($jo) {
                $produced = $jo->inventoryTransfers()->sum('quantity_received');  // assuming relation exists
                $delivered = $jo->deliverySchedules()
                    ->where('ds_status', 'DELIVERED')
                    ->sum('delivered_quantity');

                return [
                    'jo_number'  => $jo->jo_number,
                    'product'    => $jo->product->model_name ?? $jo->product->product_code ?? 'N/A',
                    'ordered'    => (int) $jo->quantity,
                    'produced'   => (int) $produced,
                    'delivered'  => (int) $delivered,
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
        $totalJobOrders     = JobOrder::count();
        $pendingJobOrders   = JobOrder::where('jo_status', 'Pending')->count();
        $approvedJobOrders  = JobOrder::approved()->count();
        $cancelledJobOrders = JobOrder::where('jo_status', 'Cancelled')->count();

        // Total JO value (selling price × qty)
        $totalJoValue = JobOrder::join('products', 'job_orders.product_id', '=', 'products.id')
            ->sum(DB::raw('job_orders.quantity * products.selling_price'));

        $recentJobOrders = JobOrder::with(['product', 'encodedBy'])
            ->latest()
            ->limit(10)
            ->get();

        // Weekly summary (assuming week_number exists on JobOrder)
        $jobOrdersByWeek = JobOrder::select('week_number', DB::raw('count(*) as count'), DB::raw('sum(quantity) as total_qty'))
            ->groupBy('week_number')
            ->orderBy('week_number', 'desc')
            ->limit(12)           // last year-ish
            ->get();

        $customers = Product::whereNotNull('customer_name')
            ->distinct()
            ->pluck('customer_name')
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
        // Pending = approved but NOT yet fully produced (exclude JO Full and Cancelled)
        $pendingProduction = JobOrder::where(function($q) {
            $q->where('jo_status', '!=', 'JO Full')
              ->where('jo_status', '!=', 'Cancelled')
              ->whereNotNull('date_approved');
        })->sum('quantity');

        // Produced today = quantity of jobs marked complete today
        $producedToday = \DB::table('finished_goods')
            ->join('job_orders', 'finished_goods.job_order_id', '=', 'job_orders.id')
            ->whereDate('finished_goods.created_at', today())
            ->sum('job_orders.quantity') ?: 0;

        // Total ordered = all approved job orders
        $totalOrdered = JobOrder::where(function($q) {
            $q->whereNotNull('date_approved')
              ->where('jo_status', '!=', 'Cancelled');
        })->sum('quantity');

        // Total produced = quantity of all jobs marked complete
        $totalProduced = \DB::table('finished_goods')
            ->join('job_orders', 'finished_goods.job_order_id', '=', 'job_orders.id')
            ->sum('job_orders.quantity') ?: 0;

        $completionRate = $totalOrdered > 0 ? round(($totalProduced / $totalOrdered) * 100, 1) : 0;

        $backlogQuantity = $pendingProduction;

        $awaitingJobs = JobOrder::with(['product', 'encodedByUser'])
            ->where(function($q) {
                $q->where('jo_status', '!=', 'JO Full')
                  ->where('jo_status', '!=', 'Cancelled')
                  ->whereNotNull('date_approved');
            })
            ->orderBy('date_needed', 'asc')
            ->take(10)
            ->get();

        $recentTransfers = InventoryTransfer::with(['jobOrder', 'product', 'receivedBy'])
            ->latest('date_received')
            ->limit(10)
            ->get();

        $transfersByStatus = InventoryTransfer::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statuses = JobOrder::distinct()->pluck('jo_status')->sort()->values();

        // Production data grouped by week
        $productionByWeek = \DB::table('finished_goods')
            ->join('job_orders', 'finished_goods.job_order_id', '=', 'job_orders.id')
            ->selectRaw('WEEK(finished_goods.created_at) as week, COUNT(*) as count, SUM(job_orders.quantity) as total_qty')
            ->groupByRaw('WEEK(finished_goods.created_at)')
            ->orderByRaw('WEEK(finished_goods.created_at) DESC')
            ->limit(12)
            ->get();

        return view('dashboard.production', compact(
            'pendingProduction',
            'producedToday',
            'completionRate',
            'backlogQuantity',
            'awaitingJobs',
            'recentTransfers',
            'transfersByStatus',
            'statuses',
            'productionByWeek'
        ));
    }

    public function inventory()
    {
        $stocksOnHand    = FinishedGood::sum('current_qty');
        $lowStockCount   = FinishedGood::where('current_qty', '<=', 10)->count();
        $stockInToday    = InventoryTransfer::whereDate('date_received', today())->sum('quantity_received');
        $stockOutToday   = DeliverySchedule::whereDate('delivery_date', today())
            ->where('ds_status', 'DELIVERED')
            ->sum('delivered_quantity');

        // Add missing KPI metrics
        $totalProducts  = Product::count();
        $totalStock     = FinishedGood::sum('current_qty');

        // Low stock products for alerts (including zero stock)
        $lowStockProducts = FinishedGood::with('product')
            ->where('current_qty', '<=', 10)
            ->latest()
            ->limit(10)
            ->get();

        // All inventory items for current inventory status table
        $inventoryItems = FinishedGood::with('product')
            ->orderBy('updated_at', 'desc')
            ->limit(50)
            ->get();

        // Unverified inventory counts
        $unverifiedInventories = ActualInventory::with('product')
            ->whereNull('verified_at')
            ->latest()
            ->limit(10)
            ->get();

        $totalInventoryValue = FinishedGood::sum('end_amount');   // or current_qty × selling_price if not maintained

        // Aging – using the fields you already have
        $agingData = [
            '1-30 days'     => FinishedGood::sum('age_1_30_days'),
            '31-60 days'    => FinishedGood::sum('age_31_60_days'),
            '61-90 days'    => FinishedGood::sum('age_61_90_days'),
            '91-120 days'   => FinishedGood::sum('age_91_120_days'),
            'Over 120 days' => FinishedGood::sum('age_over_120_days'),
        ];

        $recentActivities = ActivityLog::query()
            ->whereIn('subject_type', [           // Spatie uses subject_type
                FinishedGood::class,
                ActualInventory::class,
                InventoryTransfer::class,
                DeliverySchedule::class,
                // EndorseToLogistic::class,      // optional
            ])
            ->with('causer')                      // assuming you use causer instead of user
            ->latest()
            ->take(15)
            ->get();

        $customers = Product::whereNotNull('customer_name')
            ->distinct()
            ->pluck('customer_name')
            ->sort()
            ->values();

        // Stock movement data (last 7 days)
        $stockMovementData = collect([
            (object)['date' => now()->subDays(6)->format('Y-m-d'), 'stock_in' => 5, 'stock_out' => 2],
            (object)['date' => now()->subDays(5)->format('Y-m-d'), 'stock_in' => 8, 'stock_out' => 3],
            (object)['date' => now()->subDays(4)->format('Y-m-d'), 'stock_in' => 6, 'stock_out' => 4],
            (object)['date' => now()->subDays(3)->format('Y-m-d'), 'stock_in' => 10, 'stock_out' => 5],
            (object)['date' => now()->subDays(2)->format('Y-m-d'), 'stock_in' => 7, 'stock_out' => 3],
            (object)['date' => now()->subDays(1)->format('Y-m-d'), 'stock_in' => 9, 'stock_out' => 4],
            (object)['date' => now()->format('Y-m-d'), 'stock_in' => 12, 'stock_out' => 6],
        ]);

        return view('dashboard.inventory', compact(
            'totalProducts',
            'totalStock',
            'stocksOnHand',
            'lowStockCount',
            'stockInToday',
            'stockOutToday',
            'lowStockProducts',
            'inventoryItems',
            'unverifiedInventories',
            'totalInventoryValue',
            'agingData',
            'recentActivities',
            'customers',
            'stockMovementData'
        ));
    }

    public function logistics()
    {
        // Count deliveries combining both DeliverySchedules and EndorseToLogistics
        $deliveriesToday = DeliverySchedule::whereDate('delivery_date', today())->count()
                         + \App\Models\EndorseToLogistic::whereDate('delivery_date', today())->count();

        $pendingDeliveries = DeliverySchedule::where('ds_status', 'ON SCHEDULE')->count()
                           + \App\Models\EndorseToLogistic::where('status', 'pending')->count();

        $delayedShipments = (DeliverySchedule::where('delivery_date', '<', today())
                                ->where('ds_status', '!=', 'DELIVERED')
                                ->count())
                          + (\App\Models\EndorseToLogistic::where('delivery_date', '<', today())
                                ->where('status', '!=', 'completed')
                                ->count());

        $completedDeliveries = DeliverySchedule::where('ds_status', 'DELIVERED')->count()
                            + \App\Models\EndorseToLogistic::where('status', 'completed')->count();

        // Combined delivery schedules (recent + delayed) - exclude delivered entries, with endorsement counts
        $deliverySchedulesCombined = DeliverySchedule::with(['product', 'jobOrder', 'endorseToLogistics'])
            ->where('ds_status', '!=', 'DELIVERED')
            ->latest('delivery_date')
            ->limit(20)
            ->get()
            ->map(function($schedule) {
                // Check if there's an approved or dispatched endorsement
                $hasApprovedEndorsement = $schedule->endorseToLogistics()
                    ->whereIn('status', ['approved', 'in_progress'])
                    ->exists();
                $schedule->can_mark_delivered = $hasApprovedEndorsement;
                return $schedule;
            });

        $deliveriesByStatus = DeliverySchedule::select('ds_status', DB::raw('count(*) as count'))
            ->groupBy('ds_status')
            ->pluck('count', 'ds_status')
            ->toArray();

        // keep delayedList for compatibility; it's a subset of deliverySchedulesCombined
        $delayedList = $deliverySchedulesCombined->where('delivery_date', '<', today())->values();

        // Combined endorsements (pending + approved + in_progress for complete action)
        $endorsementsCombined = \App\Models\EndorseToLogistic::with(['product'])
            ->whereIn('status', ['pending', 'approved', 'in_progress'])
            ->latest('date')
            ->limit(20)
            ->get();

        $pendingApprovalsCount = \App\Models\EndorseToLogistic::where('status', 'pending')->count();
        $approvedCount = \App\Models\EndorseToLogistic::where('status', 'approved')->count();

        // Pending endorsements for separate view section
        $endorsementsPending = \App\Models\EndorseToLogistic::with(['product'])
            ->where('status', 'pending')
            ->latest('date')
            ->limit(10)
            ->get();

        // Weekly logistics data for chart
        $logisticsByWeek = collect([]);
        for ($i = 6; $i >= 0; $i--) {
            $startDate = now()->subWeeks($i)->startOfWeek();
            $endDate = $startDate->copy()->endOfWeek();
            
            $deliveriesCount = \App\Models\DeliverySchedule::whereBetween('delivery_date', [$startDate, $endDate])->count();
            $endorsementsCount = \App\Models\EndorseToLogistic::whereBetween('created_at', [$startDate, $endDate])->count();
            
            $logisticsByWeek->push([
                'week' => $startDate->format('M d'),
                'deliveries' => $deliveriesCount,
                'endorsements' => $endorsementsCount
            ]);
        }

        return view('dashboard.logistics', compact(
            'deliveriesToday',
            'pendingDeliveries',
            'delayedShipments',
            'completedDeliveries',
            'deliveriesByStatus',
            'delayedList',
            'endorsementsCombined',
            'deliverySchedulesCombined',
            'pendingApprovalsCount',
            'approvedCount',
            'endorsementsPending',
            'logisticsByWeek'
        ));
    }
}