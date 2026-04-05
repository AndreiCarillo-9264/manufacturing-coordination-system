<?php

namespace App\Http\Controllers;

use App\Models\InventoryTransfer;
use App\Models\JobOrder;
use App\Models\Product;
use App\Models\User;
use App\Http\Requests\StoreTransferRequest;
use App\Http\Requests\UpdateTransferRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryTransferController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', InventoryTransfer::class);

        $query = InventoryTransfer::with(['jobOrder.product', 'product', 'receivedBy', 'encodedByUser']);

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

        return view('inventory-transfers.index', compact('transfers', 'products'));
    }

    public function create()
    {
        $this->authorize('create', InventoryTransfer::class);

        $jobOrders = JobOrder::with('product')
            ->whereIn('jo_status', ['JO Full', 'Approved', 'In Progress'])
            ->orderBy('jo_number', 'desc')
            ->get();

        return view('inventory-transfers.create', compact('jobOrders'));
    }

    public function store(StoreTransferRequest $request)
    {
        $this->authorize('create', InventoryTransfer::class);

        try {
            DB::beginTransaction();

            $validated = $request->validated();

            // Get job order with product to auto-fill product details
            $jobOrder = JobOrder::with('product')->findOrFail($validated['job_order_id']);

            // Auto-fill product details from job order
            $validated['product_id'] = $validated['product_id'] ?? $jobOrder->product_id;
            $validated['product_code'] = $jobOrder->product_code;
            $validated['customer_name'] = $jobOrder->customer_name;
            $validated['model_name'] = $jobOrder->model_name;
            $validated['description'] = $jobOrder->description;
            $validated['dimension'] = $jobOrder->dimension;
            $validated['uom'] = $jobOrder->uom;
            $validated['grade'] = $jobOrder->grade ?? null;
            $validated['jo_number'] = $jobOrder->jo_number;
            $validated['jo_balance'] = $jobOrder->jo_balance ?? 0;

            $transfer = InventoryTransfer::create($validated);

            DB::commit();

            return redirect()
                ->route('inventory-transfers.index')
                ->with('success', 'Transfer created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer creation failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to create transfer: ' . $e->getMessage());
        }
    }

    public function edit(InventoryTransfer $transfer)
    {
        $this->authorize('update', $transfer);

        $jobOrders = JobOrder::with('product')
            ->whereIn('jo_status', ['JO Full', 'Approved', 'In Progress'])
            ->orderBy('jo_number', 'desc')
            ->get();

        return view('inventory-transfers.edit', compact('transfer', 'jobOrders'));
    }

    public function update(UpdateTransferRequest $request, InventoryTransfer $transfer)
    {
        $this->authorize('update', $transfer);

        try {
            DB::beginTransaction();

            $oldData = $transfer->toArray();
            $transfer->update($request->validated());

            DB::commit();

            return redirect()
                ->route('inventory-transfers.index')
                ->with('success', 'Transfer updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer update failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'transfer_id' => $transfer->id
            ]);

            return back()
                ->withInput()
                ->with('error', 'Failed to update transfer.');
        }
    }

    public function destroy(InventoryTransfer $transfer)
    {
        $this->authorize('delete', $transfer);

        try {
            DB::beginTransaction();

            $transfer->delete();

            DB::commit();

            return redirect()
                ->route('inventory-transfers.index')
                ->with('success', 'Transfer deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transfer deletion failed: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'transfer_id' => $transfer->id
            ]);

            return back()->with('error', 'Failed to delete transfer.');
        }
    }

    public function export()
    {
        $this->authorize('viewAny', InventoryTransfer::class);

        $data = InventoryTransfer::with('jobOrder', 'product', 'encodedByUser', 'receivedBy')->get();
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=inventory_transfers_' . now()->format('Y-m-d_His') . '.csv',
        ];
        
        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            fwrite($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            fputcsv($file, ['Transfer Code', 'Job Order', 'Product Code', 'Model Name', 'Quantity Transferred', 'From Location', 'To Location', 'Transfer Date', 'Transferred By', 'Notes', 'Created At']);
            
            foreach ($data as $row) {
                fputcsv($file, [
                    $row->transfer_code ?? '',
                    $row->jobOrder?->jo_number ?? '',
                    $row->product_code ?? '',
                    $row->model_name ?? '',
                    $row->quantity_transferred ?? 0,
                    $row->from_location ?? '',
                    $row->to_location ?? '',
                    $row->transfer_date?->format('Y-m-d') ?? '',
                    $row->transfer_by ?? ($row->encodedByUser?->name) ?? '',
                    $row->notes ?? '',
                    $row->created_at?->format('Y-m-d H:i:s') ?? '',
                ]);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}