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

        $query = ActualInventory::with(['product']);

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

        // Filter by stock level thresholds (low/medium/high)
        if ($request->filled('stock_level')) {
            switch ($request->stock_level) {
                case 'low':
                    $query->where('fg_quantity', '<', 100);
                    break;
                case 'medium':
                    $query->whereBetween('fg_quantity', [100, 500]);
                    break;
                case 'high':
                    $query->where('fg_quantity', '>', 500);
                    break;
            }
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
            ->pluck('location');

        return view('actual-inventories.index', compact('actualInventories', 'products', 'locations'));
    }

    public function create()
    {
        $this->authorize('create', ActualInventory::class);

        $products = Product::orderByRaw("COALESCE(model_name, product_code) ASC")->get();

        return view('actual-inventories.create', compact('products'));
    }

    public function store(StoreActualInventoryRequest $request)
    {
        $this->authorize('create', ActualInventory::class);

        $validated = $request->validated();

        try {
            DB::beginTransaction();

            $inventory = ActualInventory::create(array_merge($validated, [
                'tag_number' => ActualInventory::generateTagNumber(),
                'counted_by' => $validated['counted_by'] ?? auth()->user()->name,
                'counted_at' => now(),
                'status' => 'Counted',
            ]));

            DB::commit();

            return redirect()
                ->route('actual-inventories.index')
                ->with('success', 'Inventory record created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Actual Inventory creation failed: ' . $e->getMessage(), [
                'user_id' => auth()->id()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create inventory record.');
        }
    }

    public function edit(ActualInventory $actualInventory)
    {
        $this->authorize('update', $actualInventory);

        return view('actual-inventories.edit', compact('actualInventory'));
    }

    public function update(UpdateActualInventoryRequest $request, ActualInventory $actualInventory)
    {
        $this->authorize('update', $actualInventory);

        $validated = $request->validated();

        try {
            DB::beginTransaction();

            $oldData = $actualInventory->toArray();
            $actualInventory->update($validated);

            DB::commit();

            return redirect()
                ->route('actual-inventories.index')
                ->with('success', 'Inventory record updated successfully.');

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

            $actualInventory->delete();

            // Log activity is handled by LogsActivity trait

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

    public function verify(ActualInventory $actualInventory)
    {
        $this->authorize('update', $actualInventory);

        try {
            $actualInventory->markVerified(auth()->id());

            return redirect()
                ->route('dashboard.inventory')
                ->with('success', 'Inventory count verified successfully!');

        } catch (\Exception $e) {
            Log::error('Verification failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'actual_inventory_id' => $actualInventory->id,
                'exception' => $e
            ]);

            return redirect()
                ->route('dashboard.inventory')
                ->with('error', 'Failed to verify inventory count: ' . $e->getMessage());
        }
    }

    public function export()
    {
        $this->authorize('viewAny', ActualInventory::class);

        $data = ActualInventory::with('product')->get();
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=actual_inventories_' . now()->format('Y-m-d_His') . '.csv',
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Write BOM for Excel UTF-8 compatibility
            fwrite($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            // Write headers
            fputcsv($file, ['Tag Number', 'Product Code', 'Model Name', 'Counted Qty', 'System Qty', 'Variance', 'Status', 'Location', 'Counted By', 'Counted At', 'Verified By', 'Verified At']);
            
            // Write data rows
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->tag_number,
                    $row->product_code,
                    $row->product?->model_name ?? '',
                    $row->fg_quantity,
                    $row->system_quantity ?? 0,
                    $row->variance ?? 0,
                    $row->status,
                    $row->location ?? '',
                    $row->counted_by ?? '',
                    $row->counted_at?->format('Y-m-d H:i:s') ?? '',
                    $row->verified_by ?? '',
                    $row->verified_at?->format('Y-m-d H:i:s') ?? '',
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}