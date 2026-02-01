<?php

namespace App\Http\Controllers;

use App\Models\FinishedGood;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinishedGoodController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', FinishedGood::class);

        $query = FinishedGood::with('product');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('product_code', 'like', "%{$search}%")
                    ->orWhere('model_name', 'like', "%{$search}%");
            });
        }

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter low stock items
        if ($request->has('low_stock')) {
            $query->lowStock();
        }

        // Filter items with variance
        if ($request->has('with_variance')) {
            $query->withVariance();
        }

        $finishedGoods = $query->latest()->paginate(15);

        // Calculate totals
        $totalStock = FinishedGood::sum('qty_actual_ending');
        $totalValue = FinishedGood::sum('amount_ending');
        $lowStockCount = FinishedGood::lowStock()->count();
        $totalVariance = FinishedGood::sum('qty_variance');

        // Products for filter dropdown
        $products = Product::select('id', 'product_code', 'model_name')
            ->orderByRaw("COALESCE(model_name, product_code) ASC")
            ->get();

        return view('finished-goods.index', compact(
            'finishedGoods',
            'totalStock',
            'totalValue',
            'lowStockCount',
            'totalVariance',
            'products'
        ));
    }

    public function show(FinishedGood $finishedGood)
    {
        $this->authorize('view', $finishedGood);

        $finishedGood->load('product');

        return view('finished-goods.show', compact('finishedGood'));
    }

    public function edit(FinishedGood $finishedGood)
    {
        $this->authorize('update', $finishedGood);

        return view('finished-goods.edit', compact('finishedGood'));
    }

    public function update(Request $request, FinishedGood $finishedGood)
    {
        $this->authorize('update', $finishedGood);

        $validated = $request->validate([
            'qty_actual_ending' => 'required|integer|min:0',
            'qty_buffer_stock' => 'required|integer|min:0',
            'qty_pc_area' => 'nullable|integer|min:0',
            'remarks' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $oldData = $finishedGood->toArray();
            $finishedGood->update($validated);

            // Recalculate variance
            $finishedGood->calculateVariance();
            $finishedGood->calculateAmountVariance();

            // Log activity
            activity()
                ->performedOn($finishedGood)
                ->causedBy(auth()->user())
                ->withProperties(['old' => $oldData, 'new' => $finishedGood->toArray()])
                ->log('Finished Good updated');

            DB::commit();

            return redirect()
                ->route('finished-goods.index')
                ->with('success', 'Finished Good updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Finished Good update failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'finished_good_id' => $finishedGood->id
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update finished good. Please try again.');
        }
    }

    public function updateAging(FinishedGood $finishedGood)
    {
        $this->authorize('update', $finishedGood);

        try {
            $finishedGood->updateAgingRanges();

            return back()->with('success', 'Aging ranges updated successfully.');

        } catch (\Exception $e) {
            Log::error('Aging update failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'finished_good_id' => $finishedGood->id
            ]);

            return back()->with('error', 'Failed to update aging ranges.');
        }
    }

    public function bulkUpdateAging()
    {
        $this->authorize('update', FinishedGood::class);

        try {
            $finishedGoods = FinishedGood::all();
            
            foreach ($finishedGoods as $finishedGood) {
                $finishedGood->updateAgingRanges();
            }

            activity()
                ->causedBy(auth()->user())
                ->log('Bulk aging update performed');

            return back()->with('success', 'All aging ranges updated successfully.');

        } catch (\Exception $e) {
            Log::error('Bulk aging update failed: ' . $e->getMessage(), [
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Failed to update aging ranges.');
        }
    }
}