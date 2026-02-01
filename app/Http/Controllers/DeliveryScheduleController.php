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
                $q->where('delivery_code', 'like', "%{$search}%")
                    ->orWhereHas('jobOrder', function ($q2) use ($search) {
                        $q2->where('po_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('delayed')) {
            $query->delayed();
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
            ->whereIn('status', ['approved', 'in_progress', 'completed'])
            ->orderBy('jo_number', 'desc')
            ->get();

        return view('delivery-schedules.create', compact('jobOrders'));
    }

    public function store(StoreDeliveryScheduleRequest $request)
    {
        $this->authorize('create', DeliverySchedule::class);

        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $jobOrder = JobOrder::findOrFail($validated['job_order_id']);

            // Auto-fill related data
            $validated['product_id'] = $jobOrder->product_id;
            $validated['week_number'] = (int) date('W', strtotime($validated['delivery_date']));
            
            if (empty($validated['date_encoded'])) {
                $validated['date_encoded'] = now()->toDateString();
            }

            $deliverySchedule = DeliverySchedule::create($validated);

            // Log activity
            activity()
                ->performedOn($deliverySchedule)
                ->causedBy(auth()->user())
                ->withProperties(['new' => $deliverySchedule->toArray()])
                ->log('Delivery Schedule created');

            DB::commit();

            return redirect()
                ->route('delivery-schedules.show', $deliverySchedule)
                ->with('success', 'Delivery Schedule created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery Schedule creation failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'data' => $request->validated()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create delivery schedule.');
        }
    }

    public function show(DeliverySchedule $deliverySchedule)
    {
        $this->authorize('view', $deliverySchedule);

        $deliverySchedule->load(['jobOrder.product', 'product']);

        return view('delivery-schedules.show', compact('deliverySchedule'));
    }

    public function edit(DeliverySchedule $deliverySchedule)
    {
        $this->authorize('update', $deliverySchedule);

        $jobOrders = JobOrder::with('product')
            ->whereIn('status', ['approved', 'in_progress', 'completed'])
            ->orderBy('jo_number')
            ->get();

        return view('delivery-schedules.edit', compact('deliverySchedule', 'jobOrders'));
    }

    public function update(UpdateDeliveryScheduleRequest $request, DeliverySchedule $deliverySchedule)
    {
        $this->authorize('update', $deliverySchedule);

        try {
            DB::beginTransaction();

            $oldData = $deliverySchedule->toArray();
            $validated = $request->validated();

            // Update week number if delivery date changed
            if (isset($validated['delivery_date'])) {
                $validated['week_number'] = (int) date('W', strtotime($validated['delivery_date']));
            }

            $deliverySchedule->update($validated);

            // Log activity
            activity()
                ->performedOn($deliverySchedule)
                ->causedBy(auth()->user())
                ->withProperties(['old' => $oldData, 'new' => $deliverySchedule->toArray()])
                ->log('Delivery Schedule updated');

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

            $oldData = $deliverySchedule->toArray();
            $deliverySchedule->delete();

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['old' => $oldData])
                ->log('Delivery Schedule deleted');

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

            return back()->with('error', 'Failed to delete delivery schedule. Please try again.');
        }
    }

    /**
     * Mark delivery schedule as complete
     */
    public function markComplete(DeliverySchedule $deliverySchedule)
    {
        $this->authorize('update', $deliverySchedule);

        try {
            DB::beginTransaction();

            $deliverySchedule->markComplete();

            // Update finished goods out quantity
            $finishedGood = $deliverySchedule->product->finishedGood;
            if ($finishedGood) {
                $finishedGood->increment('qty_out', $deliverySchedule->qty_scheduled);
                
                // Calculate amount out
                $amountOut = $deliverySchedule->qty_scheduled * $deliverySchedule->product->selling_price;
                $finishedGood->increment('amount_out', $amountOut);
            }

            // Log activity
            activity()
                ->performedOn($deliverySchedule)
                ->causedBy(auth()->user())
                ->withProperties(['status' => 'complete'])
                ->log('Delivery Schedule marked as complete');

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
    public function markDelivered(DeliverySchedule $deliverySchedule)
    {
        $this->authorize('update', $deliverySchedule);

        try {
            DB::beginTransaction();

            // Ensure qty_delivered is set (default to scheduled qty)
            if (empty($deliverySchedule->qty_delivered)) {
                $deliverySchedule->update(['qty_delivered' => $deliverySchedule->qty_scheduled]);
            }

            // Mark as complete (status => 'complete')
            $deliverySchedule->markComplete();

            // Update finished goods out quantity (defensive lookup)
            $product = Product::find($deliverySchedule->product_id);
            if ($product && $product->finishedGood) {
                $finishedGood = $product->finishedGood;
                $finishedGood->increment('qty_out', $deliverySchedule->qty_delivered);

                // Calculate amount out
                $amountOut = $deliverySchedule->qty_delivered * $product->selling_price;
                $finishedGood->increment('amount_out', $amountOut);
            }

            // Log activity
            activity()
                ->performedOn($deliverySchedule)
                ->causedBy(auth()->user())
                ->withProperties(['status' => 'delivered', 'qty_delivered' => $deliverySchedule->qty_delivered])
                ->log('Delivery Schedule marked as delivered');

            DB::commit();

            return back()->with('success', 'Delivery marked as delivered successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mark delivered failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'delivery_schedule_id' => $deliverySchedule->id
            ]);

            return back()->with('error', 'Failed to mark as delivered. Please try again.');
        }
    }
}