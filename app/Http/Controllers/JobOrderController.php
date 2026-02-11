<?php

namespace App\Http\Controllers;

use App\Models\JobOrder;
use App\Models\Product;
use App\Http\Requests\StoreJobOrderRequest;
use App\Http\Requests\UpdateJobOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobOrderController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', JobOrder::class);

        $query = JobOrder::with(['product', 'encodedBy']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('jo_number', 'like', "%{$search}%")
                    ->orWhere('po_number', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($q2) use ($search) {
                        $q2->where('product_code', 'like', "%{$search}%")
                            ->orWhere('model_name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('jo_status', $request->status);
        }

        // Filter by fulfillment status
        if ($request->filled('fulfillment_status')) {
            $query->where('fulfillment_status', $request->fulfillment_status);
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('date_needed', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date_needed', '<=', $request->date_to);
        }

        $jobOrders = $query->latest()->paginate(15);
        
        $products = Product::select('id', 'product_code', 'model_name')
            ->orderByRaw("COALESCE(model_name, product_code) ASC")
            ->get();

        return view('job-orders.index', compact('jobOrders', 'products'));
    }

    public function create()
    {
        $this->authorize('create', JobOrder::class);

        $products = Product::orderByRaw("COALESCE(model_name, product_code) ASC")->get();

        return view('job-orders.create', compact('products'));
    }

    public function store(StoreJobOrderRequest $request)
    {
        $this->authorize('create', JobOrder::class);

        try {
            DB::beginTransaction();

            $validated = $request->validated();

            $jobOrder = JobOrder::create($validated);

            DB::commit();

            // Broadcast event for real-time update
            if (class_exists('\App\Events\JobOrderCreated')) {
                event(new \App\Events\JobOrderCreated($jobOrder));
            }

            return redirect()
                ->route('job-orders.index')
                ->with('success', 'Job Order created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job Order creation failed: ' . $e->getMessage(), [
                'user_id' => auth()->id()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create job order.');
        }
    }

    public function edit(JobOrder $jobOrder)
    {
        $this->authorize('update', $jobOrder);

        return view('job-orders.edit', compact('jobOrder'));
    }

    public function update(UpdateJobOrderRequest $request, JobOrder $jobOrder)
    {
        $this->authorize('update', $jobOrder);

        try {
            DB::beginTransaction();

            $oldData = $jobOrder->toArray();
            $jobOrder->update($request->validated());

            DB::commit();

            return redirect()
                ->route('job-orders.index')
                ->with('success', 'Job Order updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job Order update failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'job_order_id' => $jobOrder->id
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update job order.');
        }
    }

    public function destroy(JobOrder $jobOrder)
    {
        $this->authorize('delete', $jobOrder);

        try {
            // Only allow deletion of pending or cancelled job orders
            if (!in_array($jobOrder->status, ['pending', 'cancelled'])) {
                return back()->with('error', 'Can only delete pending or cancelled job orders.');
            }

            DB::beginTransaction();

            $jobOrder->delete();

            DB::commit();

            return redirect()
                ->route('job-orders.index')
                ->with('success', 'Job Order deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job Order deletion failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'job_order_id' => $jobOrder->id
            ]);

            return back()->with('error', 'Failed to delete job order.');
        }
    }

    public function approve(JobOrder $jobOrder)
    {
        try {
            // Check if job order is in pending status before approval
            if ($jobOrder->jo_status !== 'Pending') {
                return back()->with('error', 'Only pending job orders can be approved. Current status: ' . $jobOrder->jo_status);
            }

            DB::beginTransaction();

            $jobOrder->approve();

            DB::commit();

            return back()->with('success', 'Job Order approved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job Order approval failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'job_order_id' => $jobOrder->id
            ]);

            return back()->with('error', 'Failed to approve job order: ' . $e->getMessage());
        }
    }

    public function cancel(JobOrder $jobOrder)
    {
        $this->authorize('update', $jobOrder);

        try {
            DB::beginTransaction();

            $jobOrder->cancel();

            DB::commit();

            return back()->with('success', 'Job Order cancelled successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job Order cancellation failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'job_order_id' => $jobOrder->id
            ]);

            return back()->with('error', 'Failed to cancel job order. Please try again.');
        }
    }

    public function updateStatus(Request $request, JobOrder $jobOrder)
    {
        $this->authorize('update', $jobOrder);

        $validated = $request->validate([
            'jo_status' => 'required|in:Pending,Approved,In Progress,JO Full,Cancelled'
        ]);

        try {
            $oldStatus = $jobOrder->jo_status;
            $jobOrder->update(['jo_status' => $validated['jo_status']]);

            // When production is completed (JO Full), create/update finished goods with qty = 0
            if ($validated['jo_status'] === 'JO Full') {
                $this->createOrUpdateFinishedGoods($jobOrder);
            }

            if (class_exists('\App\Events\JobOrderStatusChanged')) {
                broadcast(new \App\Events\JobOrderStatusChanged($jobOrder, $oldStatus, $validated['jo_status']))->toOthers();
            }

            return response()->json([
                'success' => true,
                'message' => "Job order status updated to {$validated['jo_status']}",
                'data' => $jobOrder->fresh()
            ]);
        } catch (\Exception $e) {
            Log::error('Job Order status update failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    private function createOrUpdateFinishedGoods(JobOrder $jobOrder)
    {
        // Ensure finished goods are unique per product to avoid duplicates
        \App\Models\FinishedGood::updateOrCreate(
            ['product_id' => $jobOrder->product_id],
            [
                'job_order_id' => $jobOrder->id,
                'product_code' => $jobOrder->product_code,
                'customer_name' => $jobOrder->customer_name,
                'model_name' => $jobOrder->model_name,
                'description' => $jobOrder->description,
                'uom' => $jobOrder->uom,
                // Only set current_qty to 0 if not present
                'current_qty' => \App\Models\FinishedGood::where('product_id', $jobOrder->product_id)->value('current_qty') ?? 0,
                'encoded_by' => auth()->id(),
            ]
        );
    }

    public function getDetails(JobOrder $jobOrder)
    {
        $this->authorize('view', $jobOrder);

        return response()->json([
            'success' => true,
            'data' => $jobOrder->load('product')
        ]);
    }

    public function export()
    {
        $this->authorize('viewAny', JobOrder::class);

        $data = JobOrder::with('product')->get();
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=job_orders_' . now()->format('Y-m-d_His') . '.csv',
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            fwrite($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            fputcsv($file, ['JO Number', 'PO Number', 'Customer Name', 'Product Code', 'Model Name', 'Description', 'Quantity', 'UOM', 'Status', 'Date Needed', 'Date Encoded', 'Date Approved', 'Encoded By', 'Approved By']);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->jo_number ?? '',
                    $row->po_number ?? '',
                    $row->customer_name ?? '',
                    $row->product_code ?? '',
                    $row->model_name ?? '',
                    $row->description ?? '',
                    $row->quantity ?? 0,
                    $row->uom ?? '',
                    $row->jo_status ?? '',
                    $row->date_needed?->format('Y-m-d') ?? '',
                    $row->date_encoded?->format('Y-m-d H:i:s') ?? '',
                    $row->date_approved?->format('Y-m-d H:i:s') ?? '',
                    $row->encoded_by ?? '',
                    $row->approved_by ?? '',
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}