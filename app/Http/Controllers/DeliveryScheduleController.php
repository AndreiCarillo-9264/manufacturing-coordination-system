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

        $query = DeliverySchedule::with(['jobOrder', 'product', 'createdBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ds_delivery_code', 'like', "%{$search}%")
                  ->orWhere('po_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('ds_status')) {
            $query->where('ds_status', $request->ds_status);
        }

        if ($request->has('delayed')) {
            $query->delayed();
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $deliverySchedules = $query->latest('date')->paginate(15);
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
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            $validated['ds_delivery_code'] = $this->generateDeliveryCode();
            $jobOrder = JobOrder::findOrFail($validated['jo_id']);

            $validated['product_id'] = $jobOrder->product_id;
            $validated['po_number'] = $jobOrder->po_number;
            $validated['ds_qty'] = $validated['qty'];
            $validated['week_num'] = (int) date('W', strtotime($validated['date']));
            $validated['date_encoded'] = now();

            $deliverySchedule = DeliverySchedule::create($validated);

            activity()
                ->performedOn($deliverySchedule)
                ->causedBy(auth()->user())
                ->withProperties(['new' => $deliverySchedule->toArray()])
                ->log('Delivery Schedule created');

            DB::commit();

            return redirect()
                ->route('delivery-schedules.show', $deliverySchedule)
                ->with('success', 'Delivery Schedule created successfully. Please review the details below and click Continue.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Delivery Schedule creation: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Failed to create delivery schedule.');
        }
    }

    private function generateDeliveryCode(): string
    {
        $year = date('Y');
        $codes = DB::table('delivery_schedules')
            ->where('ds_delivery_code', 'like', "DS-{$year}-%")
            ->pluck('ds_delivery_code');

        $maxSeq = 0;
        foreach ($codes as $code) {
            if (preg_match('/DS-\d{4}-(\d+)/', $code, $matches)) {
                $maxSeq = max($maxSeq, intval($matches[1]));
            }
        }

        return 'DS-' . $year . '-' . str_pad($maxSeq + 1, 4, '0', STR_PAD_LEFT);
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
            ->whereIn('status', ['approved', 'in_progress'])
            ->orderBy('jo_number')
            ->get();
        
        return view('delivery-schedules.edit', compact('deliverySchedule', 'jobOrders'));
    }

    public function update(UpdateDeliveryScheduleRequest $request, DeliverySchedule $deliverySchedule)
    {
        $this->authorize('update', $deliverySchedule);
        $oldData = $deliverySchedule->toArray();

        try {
            $deliverySchedule->update($request->validated());

            activity()
                ->performedOn($deliverySchedule)
                ->causedBy(auth()->user())
                ->withProperties(['old' => $oldData, 'new' => $deliverySchedule->toArray()])
                ->log('Delivery Schedule updated');

            return redirect()
                ->route('delivery-schedules.index')
                ->with('success', 'Delivery Schedule updated successfully.');
        } catch (\Exception $e) {
            Log::error('Delivery Schedule update: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Failed to update delivery schedule.');
        }
    }

    public function destroy(DeliverySchedule $deliverySchedule)
    {
        $this->authorize('delete', $deliverySchedule);

        try {
            $deliverySchedule->delete();

            // Log activity
            activity()
                ->performedOn($deliverySchedule)
                ->causedBy(auth()->user())
                ->withProperties(['old' => $deliverySchedule->toArray()])
                ->log('Delivery Schedule deleted');

            return redirect()
                ->route('delivery-schedules.index')
                ->with('success', 'Delivery Schedule deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Delivery Schedule deletion failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to delete delivery schedule. Please try again.');
        }
    }

    /**
     * Mark delivery schedule as delivered
     */
    public function markDelivered(DeliverySchedule $deliverySchedule)
    {
        $this->authorize('markDelivered', $deliverySchedule);

        try {
            DB::beginTransaction();

            $deliverySchedule->markDelivered();

            // Update finished goods out_qty
            $finishedGood = $deliverySchedule->product->finishedGood;
            if ($finishedGood) {
                $finishedGood->increment('out_qty', $deliverySchedule->qty);
                $finishedGood->out_amt += ($deliverySchedule->qty * $finishedGood->cur_sell_price);
                $finishedGood->save();
            }

            // Log activity
            activity()
                ->performedOn($deliverySchedule)
                ->causedBy(auth()->user())
                ->withProperties(['status' => 'delivered'])
                ->log('Delivery Schedule marked as delivered');

            DB::commit();

            return back()->with('success', 'Delivery marked as delivered successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Mark delivered failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to mark as delivered. Please try again.');
        }
    }
}