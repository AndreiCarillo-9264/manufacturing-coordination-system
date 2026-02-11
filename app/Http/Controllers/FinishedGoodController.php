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

        // Filter by date range (use last_in_date)
        if ($request->filled('date_from')) {
            $query->where('last_in_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('last_in_date', '<=', $request->date_to);
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
        $totalStock = FinishedGood::sum('current_qty');
        $totalValue = FinishedGood::sum('end_amount');
        $lowStockCount = FinishedGood::lowStock()->count();
        $totalVariance = FinishedGood::sum('variance_qty');

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

    public function edit(FinishedGood $finishedGood)
    {
        $this->authorize('update', $finishedGood);

        return view('finished-goods.edit', compact('finishedGood'));
    }

    public function update(Request $request, FinishedGood $finishedGood)
    {
        $this->authorize('update', $finishedGood);

        $validated = $request->validate([
            'current_qty' => 'required|integer|min:0',
            'end_amount' => 'required|numeric|min:0',
            'remarks' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $oldData = $finishedGood->toArray();
            $finishedGood->update($validated);

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

            return back()->with('success', 'All aging ranges updated successfully.');

        } catch (\Exception $e) {
            Log::error('Bulk aging update failed: ' . $e->getMessage(), [
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Failed to update aging ranges.');
        }
    }

    public function export()
    {
        $this->authorize('viewAny', FinishedGood::class);

        $data = FinishedGood::with('product')->get();
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=finished_goods_' . now()->format('Y-m-d_His') . '.csv',
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            fwrite($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            fputcsv($file, ['Product Code', 'Model Name', 'Current Qty', 'UOM', 'Buffer Stocks', 'Selling Price', 'Currency', 'Aging Range', 'Created At']);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->product_code ?? '',
                    $row->model_name ?? '',
                    $row->current_qty ?? 0,
                    $row->uom ?? '',
                    $row->buffer_stocks ?? 0,
                    $row->selling_price ?? 0,
                    $row->currency ?? '',
                    $row->aging_range ?? '',
                    $row->created_at?->format('Y-m-d H:i:s') ?? '',
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}