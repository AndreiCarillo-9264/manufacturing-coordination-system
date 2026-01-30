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

        $query = Product::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('product_code', 'like', "%{$search}%")
                  ->orWhere('model_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('customer', 'like', "%{$search}%");
            });
        }

        // Filter by customer
        if ($request->filled('customer')) {
            $query->where('customer', $request->customer);
        }

        $products = $query->latest()->paginate(15);

        return view('products.index', compact('products'));
    }

    public function create()
    {
        $this->authorize('create', Product::class);
        
        return view('products.create');
    }

    public function store(StoreProductRequest $request)
    {
        try {
            DB::beginTransaction();

            $product = Product::create($request->validated());

            // Log activity
            activity()
                ->performedOn($product)
                ->causedBy(auth()->user())
                ->withProperties(['new' => $product->toArray()])
                ->log('Product created');

            DB::commit();

            return redirect()
                ->route('products.show', $product)
                ->with('success', 'Product created successfully. Please review the details below and click Continue.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product creation failed: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->with('error', 'Failed to create product. Please try again.');
        }
    }

    public function show(Product $product)
    {
        $this->authorize('view', $product);
        
        $product->load(['finishedGood', 'jobOrders' => function($query) {
            $query->latest()->limit(10);
        }]);

        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);
        
        return view('products.edit', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            DB::beginTransaction();

            $oldData = $product->toArray();
            $product->update($request->validated());

            // Update finished good selling price if changed
            if ($product->wasChanged('selling_price') && $product->finishedGood) {
                $product->finishedGood->update([
                    'cur_sell_price' => $product->selling_price
                ]);
            }

            // Log activity
            activity()
                ->performedOn($product)
                ->causedBy(auth()->user())
                ->withProperties(['old' => $oldData, 'new' => $product->toArray()])
                ->log('Product updated');

            DB::commit();

            return redirect()
                ->route('products.index')
                ->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product update failed: ' . $e->getMessage());
            
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

            $product->delete();

            // Log activity
            activity()
                ->performedOn($product)
                ->causedBy(auth()->user())
                ->withProperties(['old' => $product->toArray()])
                ->log('Product deleted');

            return redirect()
                ->route('products.index')
                ->with('success', 'Product deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Product deletion failed: ' . $e->getMessage());
            
            return back()->with('error', 'Failed to delete product. Please try again.');
        }
    }
}