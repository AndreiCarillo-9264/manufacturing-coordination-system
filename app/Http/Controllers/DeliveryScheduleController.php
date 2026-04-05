<?php

namespace App\Http\Controllers;

use App\Models\DeliverySchedule;
use App\Models\JobOrder;
use App\Models\Product;
use App\Http\Requests\StoreDeliveryScheduleRequest;
use App\Http\Requests\UpdateDeliveryScheduleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryScheduleController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', DeliverySchedule::class);

        $query = DeliverySchedule::with(['jobOrder.product', 'product']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ds_code', 'like', "%{$search}%")
                    ->orWhereHas('jobOrder', function ($q2) use ($search) {
                        $q2->where('po_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('status')) {
            $query->where('ds_status', $request->status);
        }

        if ($request->has('delayed')) {
            $query->where('ds_status', '!=', 'DELIVERED')
                ->where('delivery_date', '<', today());
        }

        if ($request->filled('date_from')) {
            $query->where('delivery_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('delivery_date', '<=', $request->date_to);
        }

        $deliverySchedules = $query->latest('delivery_date')->paginate(15);
        
        $products = Product::select('id', 'product_code', 'model_name')
            ->orderByRaw("COALESCE(model_name, product_code) ASC")
            ->get();

        return view('delivery-schedules.index', compact('deliverySchedules', 'products'));
    }

    public function create()
    {
        $this->authorize('create', DeliverySchedule::class);

        $jobOrders = JobOrder::with('product')
            ->whereIn('jo_status', ['Approved', 'In Progress', 'JO Full'])
            ->orderBy('jo_number', 'desc')
            ->get();

        return view('delivery-schedules.create', compact('jobOrders'));
    }

    public function store(StoreDeliveryScheduleRequest $request)
    {
        $this->authorize('create', DeliverySchedule::class);

        try {
            $validated = $request->validated();

            // Get job order with product details
            $jobOrder = \App\Models\JobOrder::with('product')->find($validated['job_order_id']);
            if (!$jobOrder) {
                return back()->withInput()->with('error', 'Job order not found.');
            }

            // Check if finished goods has stock available
            $finishedGood = \App\Models\FinishedGood::where('product_id', $jobOrder->product_id)->first();
            if (!$finishedGood || $finishedGood->current_qty <= 0) {
                return back()->withInput()->with('error', 'Cannot create delivery: No verified inventory available for this product. Please verify the actual inventory count first.');
            }

            // Prevent creating delivery if requested quantity exceeds available finished goods
            if (isset($validated['quantity']) && $validated['quantity'] > ($finishedGood->current_qty ?? 0)) {
                return back()->withInput()->with('error', 'Cannot create delivery: Insufficient verified inventory. Available: ' . ($finishedGood->current_qty ?? 0) . ', Requested: ' . $validated['quantity']);
            }

            // Check for low stock based on buffer level - reject if stock would fall below buffer after delivery
            $bufferLevel = $finishedGood->buffer_stocks ?? 0;
            $currentQty = $finishedGood->current_qty ?? 0;
            $requestedQty = $validated['quantity'] ?? 0;
            
            if ($currentQty - $requestedQty < $bufferLevel) {
                $remainingAfterDelivery = max(0, $currentQty - $requestedQty);
                return back()->withInput()->with('error', 
                    "Cannot create delivery: Stock level would fall below buffer threshold. "
                    . "Current stock: {$currentQty} pcs, Buffer required: {$bufferLevel} pcs, "
                    . "Requested quantity: {$requestedQty} pcs, Would remain: {$remainingAfterDelivery} pcs. "
                    . "Please reduce delivery quantity or check buffer settings."
                );
            }

            // Auto-fill product details from job order
            $validated['product_id'] = $validated['product_id'] ?? $jobOrder->product_id;
            $validated['product_code'] = $jobOrder->product_code;
            $validated['customer_name'] = $jobOrder->customer_name;
            $validated['model_name'] = $jobOrder->model_name;
            $validated['description'] = $jobOrder->description;
            $validated['uom'] = $jobOrder->uom ?? 'PC/S';
            $validated['dimension'] = $jobOrder->product?->dimension;
            
            // Fill additional fields from job order
            $validated['jo_number'] = $validated['jo_number'] ?? $jobOrder->jo_number;
            $validated['po_number'] = $validated['po_number'] ?? $jobOrder->po_number;
            $validated['jo_balance'] = $jobOrder->jo_balance ?? 0;
            $validated['fg_stocks'] = $finishedGood->current_qty ?? 0;
            $validated['buffer_stocks'] = $finishedGood->buffer_stocks ?? 0;
            $validated['max_quantity'] = $validated['max_quantity'] ?? $validated['quantity'];
            $validated['ds_status'] = $validated['ds_status'] ?? 'ON SCHEDULE';
            $validated['encoded_by'] = auth()->id();

            DB::beginTransaction();

            $deliverySchedule = DeliverySchedule::create($validated);

            DB::commit();

            return redirect()
                ->route('delivery-schedules.index')
                ->with('success', 'Delivery Schedule created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery Schedule creation failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create delivery schedule: ' . $e->getMessage());
        }
    }

    public function edit(DeliverySchedule $deliverySchedule)
    {
        $this->authorize('update', $deliverySchedule);

        $jobOrders = JobOrder::with('product')
            ->whereIn('jo_status', ['Approved', 'In Progress', 'JO Full'])
            ->orderBy('jo_number', 'desc')
            ->get();

        return view('delivery-schedules.edit', compact('deliverySchedule', 'jobOrders'));
    }

    public function update(UpdateDeliveryScheduleRequest $request, DeliverySchedule $deliverySchedule)
    {
        $this->authorize('update', $deliverySchedule);

        try {
            DB::beginTransaction();

            $oldData = $deliverySchedule->toArray();
            $deliverySchedule->update($request->validated());

            DB::commit();

            return redirect()
                ->route('delivery-schedules.index')
                ->with('success', 'Delivery Schedule updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery Schedule update failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'delivery_schedule_id' => $deliverySchedule->id
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update delivery schedule.');
        }
    }

    public function destroy(DeliverySchedule $deliverySchedule)
    {
        $this->authorize('delete', $deliverySchedule);

        try {
            DB::beginTransaction();

            $deliverySchedule->delete();

            DB::commit();

            return redirect()
                ->route('delivery-schedules.index')
                ->with('success', 'Delivery Schedule deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery Schedule deletion failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'delivery_schedule_id' => $deliverySchedule->id
            ]);

            return back()->with('error', 'Failed to delete delivery schedule.');
        }
    }

    public function markComplete(DeliverySchedule $deliverySchedule)
    {
        $this->authorize('update', $deliverySchedule);

        try {
            DB::beginTransaction();

            $deliverySchedule->markComplete();

            DB::commit();

            return back()->with('success', 'Delivery marked as complete successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mark complete failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'delivery_schedule_id' => $deliverySchedule->id
            ]);

            return back()->with('error', 'Failed to mark as complete. Please try again.');
        }
    }

    /**
     * Mark delivery schedule as delivered (sets qty_delivered and marks complete)
     */
    public function markDelivered(Request $request, DeliverySchedule $deliverySchedule)
    {
        $this->authorize('update', $deliverySchedule);

        try {
            DB::beginTransaction();

            // Ensure qty_delivered is set (default to scheduled qty)
            if (empty($deliverySchedule->delivered_quantity)) {
                $deliverySchedule->update(['delivered_quantity' => $deliverySchedule->quantity]);
            }

            // Time restriction: only allow marking delivered during working hours (08:00 - 17:00)
            // Allow override for users with admin or logistics role
            $currentHour = (int) now()->format('H');
            $user = auth()->user();
            $canOverride = $user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'logistics']);
            if (!$canOverride && ($currentHour < 8 || $currentHour >= 17)) {
                DB::rollBack();
                $msg = 'Deliveries can only be marked between 08:00 and 17:00. Current time: ' . now()->format('H:i');
                if ($request->wantsJson() || $request->ajax() || stripos($request->header('accept', ''), 'application/json') !== false) {
                    return response()->json(['success' => false, 'message' => $msg], 422);
                }
                return back()->with('error', $msg);
            }

            // Check finished goods stock before marking delivered
            $finishedGood = \App\Models\FinishedGood::where('product_id', $deliverySchedule->product_id)->first();
            $toDeliver = $deliverySchedule->delivered_quantity ?? $deliverySchedule->quantity;
            if (!$finishedGood || ($finishedGood->current_qty ?? 0) < $toDeliver) {
                DB::rollBack();
                return back()->with('error', 'Cannot mark as delivered: insufficient verified inventory. Available: ' . ($finishedGood->current_qty ?? 0) . ', To deliver: ' . $toDeliver);
            }

            // Mark as complete (status => 'DELIVERED')
            $deliverySchedule->markComplete();

            // Note: We do NOT remove stock here - we just mark as delivered for audit trail
            // The finished goods record remains in the system with an updated status
            // This keeps a complete history of all deliveries without data loss

            DB::commit();

            // Return JSON for AJAX requests, redirect for regular requests
            if ($request->wantsJson() || $request->ajax() || stripos($request->header('accept', ''), 'application/json') !== false) {
                return response()->json([
                    'success' => true,
                    'status' => 'DELIVERED',
                    'delivery_schedule_id' => $deliverySchedule->id,
                    'message' => 'Delivery marked as delivered successfully.'
                ]);
            }

            return back()->with('success', 'Delivery marked as delivered successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mark delivered failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'delivery_schedule_id' => $deliverySchedule->id
            ]);

            $errorMsg = 'Failed to mark as delivered. Please try again.';

            // Return JSON error for AJAX requests
            if ($request->wantsJson() || $request->ajax() || stripos($request->header('accept', ''), 'application/json') !== false) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMsg,
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()->with('error', $errorMsg);
        }
    }

    public function export()
    {
        $this->authorize('viewAny', DeliverySchedule::class);

        $data = DeliverySchedule::with('jobOrder', 'product')->get();
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=delivery_schedules_' . now()->format('Y-m-d_His') . '.csv',
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            fwrite($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            fputcsv($file, ['Delivery Code', 'Job Order', 'Customer Name', 'Product Code', 'Model Name', 'Quantity', 'Delivered Qty', 'Delivery Date', 'Delivery Time', 'Status', 'Reason', 'Created At']);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->ds_code ?? '',
                    $row->jobOrder?->jo_number ?? '',
                    $row->customer_name ?? '',
                    $row->product_code ?? '',
                    $row->model_name ?? '',
                    $row->quantity ?? 0,
                    $row->delivered_quantity ?? 0,
                    $row->delivery_date?->format('Y-m-d') ?? '',
                    $row->delivery_time ?? '',
                    $row->ds_status ?? '',
                    $row->reason ?? '',
                    $row->created_at?->format('Y-m-d H:i:s') ?? '',
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}