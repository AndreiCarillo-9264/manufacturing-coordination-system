<?php

namespace App\Http\Controllers;

use App\Models\ActualInventory;
use App\Models\Product;
use App\Models\User;
use App\Http\Requests\StoreActualInventoryRequest;
use App\Http\Requests\UpdateActualInventoryRequest;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActualInventoryController extends Controller
{
    protected ActivityLogger $logger;

    public function __construct(ActivityLogger $logger)
    {
        $this->logger = $logger;
    }
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

        $actualInventories = $query->latest()->paginate(15);
        $products = Product::select('id', 'product_code', 'model_name')
            ->orderByRaw("COALESCE(model_name, product_code) ASC")
            ->get();

        return view('actual-inventories.index', compact('actualInventories', 'products'));
    }

    public function create()
    {
        $this->authorize('create', ActualInventory::class);

        $products = Product::orderByRaw("COALESCE(model_name, product_code) ASC")
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

            $actualInventory = ActualInventory::create($request->validated());

            $this->logger->logSystem('Actual Inventory created', [
                'model' => ActualInventory::class,
                'model_id' => $actualInventory->id,
                'tag_number' => $actualInventory->tag_number,
                'product_id' => $actualInventory->product_id,
            ]);

            DB::commit();

            return redirect()->route('actual-inventories.show', $actualInventory)
                ->with('success', 'Actual Inventory created successfully. Please review the details below and click Continue.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Actual Inventory creation: ' . $e->getMessage());

            return back()->withInput()
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

        $products = Product::orderByRaw("COALESCE(model_name, product_code) ASC")
            ->get();

        $users = User::where('department', 'inventory')
            ->orderBy('name')
            ->get();

        return view('actual-inventories.edit', compact('actualInventory', 'products', 'users'));
    }

    public function update(UpdateActualInventoryRequest $request, ActualInventory $actualInventory)
    {
        $this->authorize('update', $actualInventory);
        $oldValues = $actualInventory->toArray();

        try {
            DB::beginTransaction();

            $actualInventory->update($request->validated());

            $this->logger->logSystem('Actual Inventory updated', [
                'model' => ActualInventory::class,
                'model_id' => $actualInventory->id,
                'tag_number' => $actualInventory->tag_number,
                'old_values' => $oldValues,
                'new_values' => $request->validated(),
            ]);

            DB::commit();

            return redirect()->route('actual-inventories.index')
                ->with('success', 'Actual Inventory updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Actual Inventory update: ' . $e->getMessage());

            return back()->withInput()
                ->with('error', 'Failed to update inventory record.');
        }
    }

    public function destroy(ActualInventory $actualInventory)
    {
        $this->authorize('delete', $actualInventory);
        $oldValues = $actualInventory->toArray();

        try {
            DB::beginTransaction();

            $actualInventory->delete();

            $this->logger->logSystem('Actual Inventory deleted', [
                'model' => ActualInventory::class,
                'model_id' => $actualInventory->id,
                'tag_number' => $oldValues['tag_number'] ?? null,
                'old_values' => $oldValues,
            ]);

            DB::commit();

            return redirect()->route('actual-inventories.index')
                ->with('success', 'Actual Inventory deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Actual Inventory deletion: ' . $e->getMessage());

            return redirect()->route('actual-inventories.index')
                ->with('error', 'Failed to delete inventory record.');
        }
    }
}