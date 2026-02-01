<?php

namespace App\Http\Controllers;

use App\Models\ActualInventory;
use App\Models\Product;
use App\Models\User;
use App\Http\Requests\StoreActualInventoryRequest;
use App\Http\Requests\UpdateActualInventoryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActualInventoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', ActualInventory::class);

        $query = ActualInventory::with(['product', 'countedBy', 'verifiedBy']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tag_number', 'like', "%{$search}%")
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('product_code', 'like', "%{$search}%")
                            ->orWhere('model_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('location')) {
            $query->where('location', $request->location);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->has('verified')) {
            $query->verified();
        }

        if ($request->has('unverified')) {
            $query->unverified();
        }

        $actualInventories = $query->latest()->paginate(15);
        
        $products = Product::select('id', 'product_code', 'model_name')
            ->orderByRaw("COALESCE(model_name, product_code) ASC")
            ->get();

        $locations = ActualInventory::whereNotNull('location')
            ->distinct()
            ->pluck('location')
            ->sort()
            ->values();

        return view('actual-inventories.index', compact('actualInventories', 'products', 'locations'));
    }

    public function create()
    {
        $this->authorize('create', ActualInventory::class);

        $products = Product::select('id', 'product_code', 'model_name', 'uom')
            ->orderByRaw("COALESCE(model_name, product_code) ASC")
            ->get();

        $users = User::where('department', 'inventory')
            ->orderBy('name')
            ->get();

        return view('actual-inventories.create', compact('products', 'users'));
    }

    public function store(StoreActualInventoryRequest $request)
    {
        $this->authorize('create', ActualInventory::class);

        try {
            DB::beginTransaction();

            $validated = $request->validated();

            // Auto-set counted_at if counted_by is provided
            if (!empty($validated['counted_by_user_id']) && empty($validated['counted_at'])) {
                $validated['counted_at'] = now();
            }

            $actualInventory = ActualInventory::create($validated);

            // Log activity
            activity()
                ->performedOn($actualInventory)
                ->causedBy(auth()->user())
                ->withProperties(['new' => $actualInventory->toArray()])
                ->log('Actual Inventory created');

            DB::commit();

            return redirect()
                ->route('actual-inventories.show', $actualInventory)
                ->with('success', 'Actual Inventory created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Actual Inventory creation failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'data' => $request->validated()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create inventory record.');
        }
    }

    public function show(ActualInventory $actualInventory)
    {
        $this->authorize('view', $actualInventory);

        $actualInventory->load(['product', 'countedBy', 'verifiedBy']);

        return view('actual-inventories.show', compact('actualInventory'));
    }

    public function edit(ActualInventory $actualInventory)
    {
        $this->authorize('update', $actualInventory);

        $products = Product::select('id', 'product_code', 'model_name', 'uom')
            ->orderByRaw("COALESCE(model_name, product_code) ASC")
            ->get();

        $users = User::where('department', 'inventory')
            ->orderBy('name')
            ->get();

        return view('actual-inventories.edit', compact('actualInventory', 'products', 'users'));
    }

    public function update(UpdateActualInventoryRequest $request, ActualInventory $actualInventory)
    {
        $this->authorize('update', $actualInventory);

        try {
            DB::beginTransaction();

            $oldData = $actualInventory->toArray();
            $actualInventory->update($request->validated());

            // Log activity
            activity()
                ->performedOn($actualInventory)
                ->causedBy(auth()->user())
                ->withProperties(['old' => $oldData, 'new' => $actualInventory->toArray()])
                ->log('Actual Inventory updated');

            DB::commit();

            return redirect()
                ->route('actual-inventories.index')
                ->with('success', 'Actual Inventory updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Actual Inventory update failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'actual_inventory_id' => $actualInventory->id
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update inventory record.');
        }
    }

    public function destroy(ActualInventory $actualInventory)
    {
        $this->authorize('delete', $actualInventory);

        try {
            DB::beginTransaction();

            $oldData = $actualInventory->toArray();
            $actualInventory->delete();

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->withProperties(['old' => $oldData])
                ->log('Actual Inventory deleted');

            DB::commit();

            return redirect()
                ->route('actual-inventories.index')
                ->with('success', 'Actual Inventory deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Actual Inventory deletion failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'actual_inventory_id' => $actualInventory->id
            ]);

            return redirect()
                ->route('actual-inventories.index')
                ->with('error', 'Failed to delete inventory record.');
        }
    }

    /**
     * Mark inventory as verified
     */
    public function verify(ActualInventory $actualInventory)
    {
        $this->authorize('update', $actualInventory);

        try {
            $actualInventory->markVerified(auth()->id());

            activity()
                ->performedOn($actualInventory)
                ->causedBy(auth()->user())
                ->log('Actual Inventory verified');

            return back()->with('success', 'Inventory count verified successfully.');

        } catch (\Exception $e) {
            Log::error('Verification failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'actual_inventory_id' => $actualInventory->id
            ]);

            return back()->with('error', 'Failed to verify inventory count.');
        }
    }
}