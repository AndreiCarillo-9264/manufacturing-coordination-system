<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use App\Models\JobOrder;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Transfer::class);

        $query = Transfer::with(['jobOrder.encodedBy', 'product', 'receivedBy']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ptt_number', 'like', "%{$search}%")
                  ->orWhereHas('jobOrder', function($q2) use ($search) {
                      $q2->where('jo_number', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->filled('transfer_status')) {
            $query->where('transfer_status', $request->transfer_status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('date_transferred', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date_transferred', '<=', $request->date_to);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        $transfers = $query->latest('date_transferred')->paginate(15);

        $products = Product::query()
            ->select('id', 'product_code', 'model_name')
            ->orderByRaw("COALESCE(model_name, '') ASC, product_code ASC")
            ->get();

        return view('transfers.index', compact('transfers', 'products'));
    }

    public function create()
    {
        $this->authorize('create', Transfer::class);
        
        $jobOrders = JobOrder::with('product')
            ->where('status', 'approved')
            ->orderBy('jo_number')
            ->get();
        
        return view('transfers.create', compact('jobOrders'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Transfer::class);

        $validated = $request->validate([
            'section' => 'required|string|max:255',
            'date_transferred' => 'required|date',
            'jo_id' => 'required|exists:job_orders,id',
            'qty' => 'required|integer|min:1',
            'delivery_date' => 'required|date',
            'remarks' => 'nullable|string',
            'transfer_time' => 'required',
            'grade' => 'nullable|string',
            'dimension' => 'nullable|string',
            'received_by_user_id' => 'required|exists:users,id',
            'date_received' => 'required|date',
            'time_received' => 'required',
            'qty_received' => 'required|integer|min:1',
            'category' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $jobOrder = JobOrder::findOrFail($validated['jo_id']);
            
            // Get product and selling price
            $validated['product_id'] = $jobOrder->product_id;
            $validated['selling_price'] = $jobOrder->product->selling_price;
            $validated['total_amount'] = $validated['qty_received'] * $validated['selling_price'];
            $validated['week_num'] = date('W', strtotime($validated['date_transferred']));

            // Calculate JIT days
            $validated['jit_days'] = \Carbon\Carbon::parse($validated['delivery_date'])
                ->diffInDays($validated['date_transferred']);

            // Determine transfer status
            $validated['transfer_status'] = $validated['qty_received'] >= $validated['qty'] ? 'complete' : 'balance';

            $transfer = Transfer::create($validated);

            // Update job order status to in_progress if not already
            if ($jobOrder->status === 'approved') {
                $jobOrder->markInProgress();
            }

            // Update finished goods inventory
            $finishedGood = $jobOrder->product->finishedGood;
            if ($finishedGood) {
                $finishedGood->increment('in_qty', $validated['qty_received']);
                $finishedGood->in_amt += $validated['total_amount'];
                $finishedGood->last_in_date = $validated['date_received'];
                $finishedGood->save();
            }

            // Log activity
            activity()
                ->performedOn($transfer)
                ->causedBy(auth()->user())
                ->withProperties(['new' => $transfer->toArray()])
                ->log('Transfer created');

            DB::commit();

            return redirect()
                ->route('transfers.show', $transfer)
                ->with('success', 'Transfer created successfully. Please review the details below and click Continue.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer creation failed: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Failed to create transfer. Please try again.');
        }
    }

    public function show(Transfer $transfer)
    {
        $this->authorize('view', $transfer);
        
        $transfer->load(['jobOrder.product', 'product', 'receivedBy']);

        return view('transfers.show', compact('transfer'));
    }

    public function edit(Transfer $transfer)
    {
        $this->authorize('update', $transfer);
        
        $jobOrders = JobOrder::with('product')
            ->whereIn('status', ['approved', 'in_progress'])
            ->orderBy('jo_number')
            ->get();
        
        return view('transfers.edit', compact('transfer', 'jobOrders'));
    }

    public function update(Request $request, Transfer $transfer)
    {
        $this->authorize('update', $transfer);

        // Validation rules similar to store
        $validated = $request->validate([
            'section' => 'required|string|max:255',
            'date_transferred' => 'required|date',
            'qty' => 'required|integer|min:1',
            'delivery_date' => 'required|date',
            'remarks' => 'nullable|string',
            'transfer_time' => 'required',
            'grade' => 'nullable|string',
            'dimension' => 'nullable|string',
            'date_received' => 'required|date',
            'time_received' => 'required',
            'qty_received' => 'required|integer|min:1',
            'category' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $oldQtyReceived = $transfer->qty_received;
            $oldData = $transfer->toArray();

            // Update transfer
            $validated['transfer_status'] = $validated['qty_received'] >= $validated['qty'] ? 'complete' : 'balance';
            $validated['total_amount'] = $validated['qty_received'] * $transfer->selling_price;
            $validated['jit_days'] = \Carbon\Carbon::parse($validated['delivery_date'])
                ->diffInDays($validated['date_transferred']);

            $transfer->update($validated);

            // Update finished goods if qty_received changed
            if ($oldQtyReceived != $validated['qty_received']) {
                $difference = $validated['qty_received'] - $oldQtyReceived;
                $finishedGood = $transfer->product->finishedGood;
                
                if ($finishedGood) {
                    $finishedGood->increment('in_qty', $difference);
                    $finishedGood->in_amt += ($difference * $transfer->selling_price);
                    $finishedGood->save();
                }
            }

            // Log activity
            activity()
                ->performedOn($transfer)
                ->causedBy(auth()->user())
                ->withProperties(['old' => $oldData, 'new' => $transfer->toArray()])
                ->log('Transfer updated');

            DB::commit();

            return redirect()
                ->route('transfers.index')
                ->with('success', 'Transfer updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer update failed: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Failed to update transfer. Please try again.');
        }
    }

    public function destroy(Transfer $transfer)
    {
        $this->authorize('delete', $transfer);

        try {
            DB::beginTransaction();

            // Reverse finished goods inventory
            $finishedGood = $transfer->product->finishedGood;
            if ($finishedGood) {
                $finishedGood->decrement('in_qty', $transfer->qty_received);
                $finishedGood->in_amt -= $transfer->total_amount;
                $finishedGood->save();
            }

            $transfer->delete();

            // Log activity
            activity()
                ->performedOn($transfer)
                ->causedBy(auth()->user())
                ->withProperties(['old' => $transfer->toArray()])
                ->log('Transfer deleted');

            DB::commit();

            return redirect()
                ->route('transfers.index')
                ->with('success', 'Transfer deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer deletion failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to delete transfer. Please try again.');
        }
    }
}