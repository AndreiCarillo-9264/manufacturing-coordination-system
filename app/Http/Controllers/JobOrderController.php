<?php

namespace App\Http\Controllers;

use App\Models\JobOrder;
use App\Models\Product;
use App\Http\Requests\StoreJobOrderRequest;
use App\Http\Requests\UpdateJobOrderRequest;
use App\Services\ActivityLogger;
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
            $query->where(function($q) use ($search) {
                $q->where('jo_number', 'like', "%{$search}%")
                  ->orWhere('po_number', 'like', "%{$search}%")
                  ->orWhereHas('product', function($q2) use ($search) {
                      $q2->where('product_code', 'like', "%{$search}%")
                         ->orWhere('model_name', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by JO status
        if ($request->filled('jo_status')) {
            $query->where('jo_status', $request->jo_status);
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
        $products = Product::orderBy('product_code')->get();

        return view('job-orders.index', compact('jobOrders', 'products'));
    }

    public function create()
    {
        $this->authorize('create', JobOrder::class);
        
        $products = Product::orderBy('product_code')->get();
        
        return view('job-orders.create', compact('products'));
    }

    public function store(StoreJobOrderRequest $request)
    {
        try {
            DB::beginTransaction();

            $jobOrder = JobOrder::create($request->validated());

            // Log activity
            activity()
                ->performedOn($jobOrder)
                ->causedBy(auth()->user())
                ->withProperties(['new' => $jobOrder->toArray()])
                ->log('Job Order created');

            DB::commit();

            // Broadcast event for real-time update (AFTER creation)
            event(new \App\Events\JobOrderCreated($jobOrder));

            return redirect()
                ->route('job-orders.show', $jobOrder)
                ->with('success', 'Job Order created successfully. Please review the details below and click Continue.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job Order creation failed: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Failed to create job order. Please try again.');
        }
    }

    public function show(JobOrder $jobOrder)
    {
        $this->authorize('view', $jobOrder);
        
        $jobOrder->load([
            'product',
            'encodedBy',
            'transfers' => function($query) {
                $query->latest();
            },
            'deliverySchedules' => function($query) {
                $query->latest();
            }
        ]);

        return view('job-orders.show', compact('jobOrder'));
    }

    public function edit(JobOrder $jobOrder)
    {
        $this->authorize('update', $jobOrder);
        
        $products = Product::orderBy('product_code')->get();
        
        return view('job-orders.edit', compact('jobOrder', 'products'));
    }

    public function update(UpdateJobOrderRequest $request, JobOrder $jobOrder)
    {
        try {
            DB::beginTransaction();

            $oldStatus = $jobOrder->status;
            $oldData = $jobOrder->toArray();
            $jobOrder->update($request->validated());

            // Log activity
            activity()
                ->performedOn($jobOrder)
                ->causedBy(auth()->user())
                ->withProperties(['old' => $oldData, 'new' => $jobOrder->toArray()])
                ->log('Job Order updated');

            DB::commit();

            // Broadcast status change if status changed
            if ($oldStatus !== $jobOrder->status) {
                event(new \App\Events\JobOrderStatusChanged($jobOrder, $oldStatus, $jobOrder->status));
            }

            return redirect()
                ->route('job-orders.index')
                ->with('success', 'Job Order updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job Order update failed: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Failed to update job order. Please try again.');
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

            $jobOrder->delete();

            // Log activity
            activity()
                ->performedOn($jobOrder)
                ->causedBy(auth()->user())
                ->withProperties(['old' => $jobOrder->toArray()])
                ->log('Job Order deleted');

            return redirect()
                ->route('job-orders.index')
                ->with('success', 'Job Order deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Job Order deletion failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to delete job order. Please try again.');
        }
    }

    /**
     * Approve a job order
     */
    public function approve(JobOrder $jobOrder)
    {
        $this->authorize('approve', $jobOrder);

        try {
            if ($jobOrder->status !== 'pending') {
                return back()->with('error', 'Only pending job orders can be approved.');
            }

            DB::beginTransaction();

            $oldStatus = $jobOrder->status;
            $jobOrder->approve();

            // Log activity
            activity()
                ->performedOn($jobOrder)
                ->causedBy(auth()->user())
                ->withProperties(['status' => 'approved'])
                ->log('Job Order approved');

            // Notify production department
            $productionUsers = \App\Models\User::where('department', 'production')->get();
            foreach ($productionUsers as $user) {
                $user->notify(new \App\Notifications\JobOrderApprovedNotification($jobOrder));
            }

            DB::commit();

            // Broadcast status change
            event(new \App\Events\JobOrderStatusChanged($jobOrder, $oldStatus, $jobOrder->status));

            return back()->with('success', 'Job Order approved successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job Order approval failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to approve job order. Please try again.');
        }
    }

    /**
     * Cancel a job order
     */
    public function cancel(JobOrder $jobOrder)
    {
        $this->authorize('cancel', $jobOrder);

        try {
            if (!in_array($jobOrder->status, ['pending', 'approved'])) {
                return back()->with('error', 'Can only cancel pending or approved job orders.');
            }

            DB::beginTransaction();

            $oldStatus = $jobOrder->status;
            $jobOrder->cancel();

            // Log activity
            activity()
                ->performedOn($jobOrder)
                ->causedBy(auth()->user())
                ->withProperties(['status' => 'cancelled'])
                ->log('Job Order cancelled');

            DB::commit();

            // Broadcast status change
            event(new \App\Events\JobOrderStatusChanged($jobOrder, $oldStatus, $jobOrder->status));

            return back()->with('success', 'Job Order cancelled successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Job Order cancellation failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to cancel job order. Please try again.');
        }
    }

    /**
     * Update job order status (from production dashboard)
     */
    public function updateStatus(Request $request, JobOrder $jobOrder)
    {
        $this->authorize('update', $jobOrder);

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,in_progress,completed,cancelled'
        ]);

        $oldStatus = $jobOrder->status;
        $newStatus = $validated['status'];

        $jobOrder->update(['status' => $newStatus]);

        (new ActivityLogger())->logSystem(
            'Job Order Status Updated',
            [
                'model_id' => $jobOrder->id,
                'model_type' => 'JobOrder',
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]
        );

        broadcast(new \App\Events\JobOrderStatusChanged($jobOrder, $oldStatus, $newStatus))->toOthers();

        return response()->json([
            'success' => true,
            'message' => "Job order status updated to $newStatus",
            'data' => $jobOrder->fresh()
        ]);
    }

    /**
     * Get job order details via AJAX
     */
    public function getDetails(JobOrder $jobOrder)
    {
        $this->authorize('view', $jobOrder);

        return response()->json([
            'success' => true,
            'data' => $jobOrder->load('product')
        ]);
    }
}