<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Product::class);

        $query = Product::with('encodedBy');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('product_code', 'like', "%{$search}%")
                    ->orWhere('model_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        // Filter by customer
        if ($request->filled('customer')) {
            $query->where('customer_name', $request->customer);
        }

        $products = $query->latest()->paginate(15);

        // Get unique customers for filter dropdown
        $customers = Product::whereNotNull('customer_name')
            ->distinct()
            ->pluck('customer_name')
            ->sort()
            ->values();

        return view('products.index', compact('products', 'customers'));
    }

    public function create()
    {
        $this->authorize('create', Product::class);

        return view('products.create');
    }

    public function store(StoreProductRequest $request)
    {
        $this->authorize('create', Product::class);

        try {
            DB::beginTransaction();

            $product = Product::create($request->validated());

            DB::commit();

            return redirect()
                ->route('products.index')
                ->with('success', 'Product created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product creation failed: ' . $e->getMessage(), [
                'user_id' => auth()->id()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create product.');
        }
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        return view('products.edit', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        try {
            DB::beginTransaction();

            $oldData = $product->toArray();

            $validated = $request->validated();

            // If product_code changed, follow versioning: base-code -> base-code-01, base-code-02, ...
            if (isset($validated['product_code']) && $validated['product_code'] !== $product->product_code) {
                // Determine base code by stripping trailing -NN if present
                $currentCode = $product->product_code;
                $base = preg_replace('/-\d{2}$/', '', $currentCode);

                // Find highest existing suffix for this base
                $matching = Product::where('product_code', 'like', $base . '-%')->pluck('product_code')->toArray();
                $max = 0;
                foreach ($matching as $code) {
                    $parts = explode('-', $code);
                    $last = end($parts);
                    if (preg_match('/^\d{2}$/', $last)) {
                        $num = (int) $last;
                        if ($num > $max) $max = $num;
                    }
                }
                $newSuffix = $max + 1;
                $newCode = $base . '-' . sprintf('%02d', $newSuffix);
                $validated['product_code'] = $newCode;
            }

            $product->update($validated);

            DB::commit();

            return redirect()
                ->route('products.index')
                ->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'product_id' => $product->id
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update product. Please try again.');
        }
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        try {
            // Check if product has active job orders
            if ($product->jobOrders()->whereNotIn('status', ['completed', 'cancelled'])->exists()) {
                return back()->with('error', 'Cannot delete product with active job orders.');
            }

            DB::beginTransaction();

            $product->delete();

            DB::commit();

            return redirect()
                ->route('products.index')
                ->with('success', 'Product deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product deletion failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'product_id' => $product->id
            ]);

            return back()->with('error', 'Failed to delete product. Please try again.');
        }
    }

    /**
     * Return JSON details for a product (used by frontend selects to autofill fields).
     */
    public function json(Product $product)
    {
        $this->authorize('view', $product);

        return response()->json([
            'id' => $product->id,
            'product_code' => $product->product_code,
            'customer_name' => $product->customer_name,
            'model_name' => $product->model_name,
            'description' => $product->description,
            'dimension' => $product->dimension,
            'uom' => $product->uom,
        ]);
    }

    public function export()
    {
        $this->authorize('viewAny', Product::class);

        $data = Product::all();
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=products_' . now()->format('Y-m-d_His') . '.csv',
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            fwrite($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            fputcsv($file, ['Product Code', 'Model Name', 'Description', 'Category', 'Dimension', 'UOM', 'Reorder Level', 'Currency', 'Unit Cost', 'Selling Price', 'Remarks', 'Created At']);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->product_code ?? '',
                    $row->model_name ?? '',
                    $row->description ?? '',
                    $row->category ?? '',
                    $row->dimension ?? '',
                    $row->uom ?? '',
                    $row->reorder_level ?? 0,
                    $row->currency ?? '',
                    $row->unit_cost ?? 0,
                    $row->selling_price ?? 0,
                    $row->remarks ?? '',
                    $row->created_at?->format('Y-m-d H:i:s') ?? '',
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}