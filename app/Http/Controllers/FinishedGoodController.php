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
            $query->whereHas('product', function($q) use ($search) {
                $q->where('product_code', 'like', "%{$search}%")
                  ->orWhere('model_name', 'like', "%{$search}%");
            });
        }

        // NEW: Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter low stock items
        if ($request->has('low_stock')) {
            $query->lowStock();
        }

        $finishedGoods = $query->latest()->paginate(15);

        // Calculate totals
        $totalStock    = FinishedGood::sum('ending_count');
        $totalValue    = FinishedGood::sum('end_amt');
        $lowStockCount = FinishedGood::lowStock()->count();

        // Products for filter dropdown
        $products = Product::query()
            ->select('id', 'product_code', 'model_name')
            ->orderByRaw("COALESCE(model_name, product_code) ASC")
            ->get();

        return view('finished-goods.index', compact(
            'finishedGoods',
            'totalStock',
            'totalValue',
            'lowStockCount',
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
            'ending_count' => 'required|integer|min:0',
            'buffer_stocks' => 'required|integer|min:0',
            'remarks'      => 'nullable|string',
        ]);

        try {
            $oldData = $finishedGood->toArray();
            $finishedGood->update($validated);

            // Log activity
            activity()
                ->performedOn($finishedGood)
                ->causedBy(auth()->user())
                ->withProperties(['old' => $oldData, 'new' => $finishedGood->toArray()])
                ->log('Finished Good updated');

            return redirect()
                ->route('finished-goods.index')
                ->with('success', 'Finished Good updated successfully.');

        } catch (\Exception $e) {
            Log::error('Finished Good update failed: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Failed to update finished good. Please try again.');
        }
    }
}