<?php

namespace App\Http\Controllers;

use App\Models\Transfer;
use App\Models\JobOrder;
use App\Models\Product;
use App\Models\User;
use App\Http\Requests\StoreTransferRequest;
use App\Http\Requests\UpdateTransferRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransferController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Transfer::class);

        $query = Transfer::with(['jobOrder.product', 'product', 'receivedBy']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('ptt_number', 'like', "%{$search}%")
                    ->orWhereHas('jobOrder', function ($q2) use ($search) {
                        $q2->where('jo_number', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by section
        if ($request->filled('section')) {
            $query->where('section', $request->section);
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

        $products = Product::select('id', 'product_code', 'model_name')
            ->orderByRaw("COALESCE(model_name, product_code) ASC")
            ->get();

        return view('transfers.index', compact('transfers', 'products'));
    }

    public function create()
    {
        $this->authorize('create', Transfer::class);

        $jobOrders = JobOrder::with('product')
            ->whereIn('status', ['approved', 'in_progress'])
            ->orderBy('jo_number', 'desc')
            ->get();

        $users = User::where('department', 'inventory')
            ->orderBy('name')
            ->get();

        return view('transfers.create', compact('jobOrders', 'users'));
    }

    public function store(StoreTransferRequest $request)
    {
        $this->authorize('create', Transfer::class);

        try {
            DB::beginTransaction();

            $validated = $request->validated();
            $jobOrder = JobOrder::findOrFail($validated['job_order_id']);

            // Auto-fill related data
            $validated['product_id'] = $jobOrder->product_id;
            $validated['unit_selling_price'] = $jobOrder->product->selling_price;
            $validated['total_amount'] = $validated['qty_received'] * $validated['unit_selling_price'];
            $validated['week_number'] = (int) date('W', strtotime($validated['date_transferred']));

            // Calculate JIT days
            if (isset($validated['date_delivery_scheduled'])) {
                $validated['jit_days'] = \Carbon\Carbon::parse($validated['date_delivery_scheduled'])
                    ->diffInDays($validated['date_transferred']);
            }

            // Determine status based on quantities
            $validated['status'] = $validated['qty_received'] >= $validated['qty_transferred'] ? 'complete' : 'balance';

            // Calculate job order balance
            $validated['qty_jo_balance'] = $jobOrder->qty_ordered - $jobOrder->transfers()->sum('qty_received') - $validated['qty_received'];

            $transfer = Transfer::create($validated);

            // Update job order status to in_progress if not already
            if ($jobOrder->status === 'approved') {
                $jobOrder->markInProgress();
            }

            // Update finished goods inventory
            $finishedGood = $jobOrder->product->finishedGood;
            if ($finishedGood) {
                $finishedGood->increment('qty_in', $validated['qty_received']);
                $finishedGood->increment('amount_in', $validated['total_amount']);
                $finishedGood->date_last_in = $validated['date_received'];
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
                ->with('success', 'Transfer created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer creation failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'data' => $request->validated()
            ]);

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

        $users = User::where('department', 'inventory')
            ->orderBy('name')
            ->get();

        return view('transfers.edit', compact('transfer', 'jobOrders', 'users'));
    }

    public function update(UpdateTransferRequest $request, Transfer $transfer)
    {
        $this->authorize('update', $transfer);

        try {
            DB::beginTransaction();

            $oldQtyReceived = $transfer->qty_received;
            $oldData = $transfer->toArray();

            $validated = $request->validated();

            // Recalculate fields
            $validated['status'] = $validated['qty_received'] >= $validated['qty_transferred'] ? 'complete' : 'balance';
            $validated['total_amount'] = $validated['qty_received'] * $transfer->unit_selling_price;
            
            if (isset($validated['date_delivery_scheduled'])) {
                $validated['jit_days'] = \Carbon\Carbon::parse($validated['date_delivery_scheduled'])
                    ->diffInDays($validated['date_transferred']);
            }

            $transfer->update($validated);

            // Update finished goods if qty_received changed
            if ($oldQtyReceived != $validated['qty_received']) {
                $difference = $validated['qty_received'] - $oldQtyReceived;
                $finishedGood = $transfer->product->finishedGood;

                if ($finishedGood) {
                    $finishedGood->increment('qty_in', $difference);
                    $amountDifference = $difference * $transfer->unit_selling_price;
                    $finishedGood->increment('amount_in', $amountDifference);
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
            Log::error('Transfer update failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'transfer_id' => $transfer->id
            ]);

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
                $finishedGood->decrement('qty_in', $transfer->qty_received);
                $finishedGood->decrement('amount_in', $transfer->total_amount);
                $finishedGood->save();
            }

            $oldData = $transfer->toArray();
            $transfer->delete();

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['old' => $oldData])
                ->log('Transfer deleted');

            DB::commit();

            return redirect()
                ->route('transfers.index')
                ->with('success', 'Transfer deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer deletion failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'transfer_id' => $transfer->id
            ]);

            return back()->with('error', 'Failed to delete transfer. Please try again.');
        }
    }
}