<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImportController extends Controller
{
    public function products(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx,xls']);

        $file = $request->file('file');
        $path = $file->storeAs('imports/products', now()->format('Ymd_His_') . $file->getClientOriginalName());

        // If CSV, try a quick inline import (safe, row-by-row validation). For XLSX support recommend adding maatwebsite/excel later.
        $ext = strtolower($file->getClientOriginalExtension());
        if ($ext === 'csv' || in_array($file->getMimeType(), ['text/csv', 'text/plain'])) {
            $handle = fopen($file->getRealPath(), 'r');
            $header = null;
            $created = 0; $updated = 0; $errors = [];
            $rowNumber = 0;

            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                if (!$header) { $header = array_map('trim', $row); continue; }

                // pad row if columns missing
                $row = array_pad($row, count($header), null);
                $data = array_combine($header, $row);

                $validator = \Validator::make($data, [
                    'product_code' => 'required|string|max:50',
                    'model_name' => 'required|string|max:255',
                    'selling_price' => 'nullable|numeric',
                ]);

                if ($validator->fails()) {
                    $errors[] = ['row' => $rowNumber, 'errors' => $validator->errors()->all()];
                    continue;
                }

                $product = \App\Models\Product::firstOrNew(['product_code' => $data['product_code']]);
                $isNew = !$product->exists;
                $product->model_name = $data['model_name'] ?? $product->model_name;
                if (isset($data['uom'])) $product->uom = $data['uom'];
                if (isset($data['selling_price'])) $product->selling_price = $data['selling_price'];
                if (isset($data['customer'])) $product->customer = $data['customer'];
                if (isset($data['remarks'])) $product->remarks = $data['remarks'];
                $product->save();

                if ($isNew) $created++; else $updated++;
            }

            fclose($handle);

            $summary = "Products import completed: {$created} created, {$updated} updated. File stored: {$path}";

            if (count($errors)) {
                $errPath = 'imports/products/errors_' . now()->format('Ymd_His') . '.json';
                Storage::put($errPath, json_encode($errors, JSON_PRETTY_PRINT));
                $errUrl = route('imports.errors', ['resource' => 'products', 'filename' => basename($errPath)]);
                $summary .= " — " . count($errors) . " row errors. Download: {$errUrl}";
            }

            session()->flash('success', $summary);
            return back();
        }

        // Fallback for other types: store and defer processing
        session()->flash('success', 'Products import received. File saved to storage: ' . $path);
        return back();
    }

    /**
     * Download stored import error file
     */
    public function downloadErrors($resource, $filename)
    {
        $path = "imports/{$resource}/{$filename}";
        if (!\Illuminate\Support\Facades\Storage::exists($path)) {
            abort(404);
        }

        return response()->download(storage_path('app/' . $path));
    }

    public function jobOrders(Request $request)
    {
        return $this->handleUpload($request, 'job-orders');
    }

    public function deliverySchedules(Request $request)
    {
        return $this->handleUpload($request, 'delivery-schedules');
    }

    public function transfers(Request $request)
    {
        return $this->handleUpload($request, 'transfers');
    }

    public function finishedGoods(Request $request)
    {
        return $this->handleUpload($request, 'finished-goods');
    }

    public function actualInventories(Request $request)
    {
        return $this->handleUpload($request, 'actual-inventories');
    }

    public function endorseToLogistics(Request $request)
    {
        return $this->handleUpload($request, 'endorse-to-logistics');
    }

    protected function handleUpload(Request $request, string $resource)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt,xlsx,xls']);

        $file = $request->file('file');
        $path = $file->storeAs('imports/' . $resource, now()->format('Ymd_His_') . $file->getClientOriginalName());

        // For large files or non-CSV types, dispatch a background job to process asynchronously
        $ext = strtolower($file->getClientOriginalExtension());
        $size = $file->getSize();

        if ($ext !== 'csv' || $size > 500_000) {
            // Dispatch a generic import job (currently only supports CSV processing for products)
            \App\Jobs\ProcessImportJob::dispatch($path, $resource);
            session()->flash('success', ucfirst(str_replace('-', ' ', $resource)) . ' import received and queued for processing. File saved to storage: ' . $path);
            return back();
        }

        // Process small CSV files inline based on resource type
        $result = $this->processCSV($request->file('file'), $resource);
        
        $resource_name = ucfirst(str_replace('-', ' ', $resource));
        $summary = "{$resource_name} import completed: {$result['created']} created, {$result['updated']} updated. File saved to storage: {$path}";

        if (count($result['errors'])) {
            $errPath = 'imports/' . $resource . '/errors_' . now()->format('Ymd_His') . '.json';
            Storage::put($errPath, json_encode($result['errors'], JSON_PRETTY_PRINT));
            $summary .= " — " . count($result['errors']) . " row errors. Please review the errors and resubmit.";
            session()->flash('warning', $summary);
        } else {
            session()->flash('success', $summary);
        }

        return back();
    }

    protected function processCSV($file, $resource)
    {
        $handle = fopen($file->getRealPath(), 'r');
        $header = null;
        $created = 0;
        $updated = 0;
        $errors = [];
        $rowNumber = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if (!$header) {
                $header = array_map('trim', $row);
                continue;
            }

            $row = array_pad($row, count($header), null);
            $data = array_combine($header, $row);
            $data = array_map('trim', $data); // Trim all values

            try {
                $resourceClass = 'App\\Models\\' . $this->getModelName($resource);
                $result = $this->upsertRecord($resource, $data, $resourceClass, $rowNumber);
                
                if ($result === 'created') {
                    $created++;
                } elseif ($result === 'updated') {
                    $updated++;
                } elseif ($result === 'error') {
                    $errors[] = ['row' => $rowNumber, 'message' => 'Validation or processing error'];
                }
            } catch (\Exception $e) {
                $errors[] = ['row' => $rowNumber, 'message' => $e->getMessage()];
            }
        }

        fclose($handle);

        return compact('created', 'updated', 'errors');
    }

    protected function getModelName($resource)
    {
        $map = [
            'job-orders' => 'JobOrder',
            'delivery-schedules' => 'DeliverySchedule',
            'transfers' => 'InventoryTransfer',
            'inventory-transfers' => 'InventoryTransfer',
            'finished-goods' => 'FinishedGood',
            'actual-inventories' => 'ActualInventory',
            'endorse-to-logistics' => 'EndorseToLogistic',
        ];
        return $map[$resource] ?? ucfirst(str_replace('-', '', $resource));
    }

    protected function upsertRecord($resource, $data, $modelClass, $rowNumber)
    {
        // Basic validation
        if (empty($data) || empty(array_filter($data))) {
            return 'skipped'; // Skip empty rows
        }

        switch ($resource) {
            case 'job-orders':
                return $this->upsertJobOrder($data, $modelClass, $rowNumber);
            case 'delivery-schedules':
                return $this->upsertDeliverySchedule($data, $modelClass, $rowNumber);
            case 'transfers':
            case 'inventory-transfers':
                return $this->upsertInventoryTransfer($data, $modelClass, $rowNumber);
            case 'finished-goods':
                return $this->upsertFinishedGood($data, $modelClass, $rowNumber);
            case 'actual-inventories':
                return $this->upsertActualInventory($data, $modelClass, $rowNumber);
            case 'endorse-to-logistics':
                return $this->upsertEndorseToLogistic($data, $modelClass, $rowNumber);
            default:
                return 'skipped';
        }
    }

    protected function upsertJobOrder($data, $modelClass, $rowNumber)
    {
        $validator = \Validator::make($data, [
            'jo_number' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) return 'error';

        $record = $modelClass::firstOrNew(['jo_number' => $data['jo_number']]);
        $isNew = !$record->exists;
        
        $record->po_number = $data['po_number'] ?? $record->po_number;
        $record->customer_id = $data['customer_id'] ?? $record->customer_id;
        $record->product_id = $data['product_id'] ?? $record->product_id;
        $record->quantity = $data['quantity'] ?? $record->quantity;
        $record->uom = $data['uom'] ?? $record->uom;
        $record->jo_status = $data['jo_status'] ?? $record->jo_status ?? 'pending';
        $record->save();

        return $isNew ? 'created' : 'updated';
    }

    protected function upsertDeliverySchedule($data, $modelClass, $rowNumber)
    {
        $validator = \Validator::make($data, [
            'ds_code' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) return 'error';

        $record = $modelClass::firstOrNew(['ds_code' => $data['ds_code']]);
        $isNew = !$record->exists;
        
        $record->job_order_id = $data['job_order_id'] ?? $record->job_order_id;
        $record->quantity = $data['quantity'] ?? $record->quantity;
        $record->ds_status = $data['ds_status'] ?? $record->ds_status ?? 'scheduled';
        $record->delivery_date = $data['delivery_date'] ?? $record->delivery_date;
        $record->save();

        return $isNew ? 'created' : 'updated';
    }

    protected function upsertInventoryTransfer($data, $modelClass, $rowNumber)
    {
        $validator = \Validator::make($data, [
            'transfer_code' => 'required|string|max:50',
            'quantity_transferred' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) return 'error';

        $record = $modelClass::firstOrNew(['transfer_code' => $data['transfer_code']]);
        $isNew = !$record->exists;
        
        $record->job_order_id = $data['job_order_id'] ?? $record->job_order_id;
        $record->product_id = $data['product_id'] ?? $record->product_id;
        $record->quantity_transferred = $data['quantity_transferred'] ?? $record->quantity_transferred;
        $record->from_location = $data['from_location'] ?? $record->from_location;
        $record->to_location = $data['to_location'] ?? $record->to_location;
        $record->transfer_date = $data['transfer_date'] ?? $record->transfer_date;
        $record->save();

        return $isNew ? 'created' : 'updated';
    }

    protected function upsertFinishedGood($data, $modelClass, $rowNumber)
    {
        $validator = \Validator::make($data, [
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) return 'error';

        $record = $modelClass::firstOrNew(['product_id' => $data['product_id']]);
        $isNew = !$record->exists;
        
        $record->current_qty = $data['current_qty'] ?? $record->current_qty ?? 0;
        $record->buffer_stocks = $data['buffer_stocks'] ?? $record->buffer_stocks ?? 0;
        $record->save();

        return $isNew ? 'created' : 'updated';
    }

    protected function upsertActualInventory($data, $modelClass, $rowNumber)
    {
        $validator = \Validator::make($data, [
            'tag_number' => 'required|string|max:50',
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) return 'error';

        $record = $modelClass::firstOrNew(['tag_number' => $data['tag_number']]);
        $isNew = !$record->exists;
        
        $record->product_id = $data['product_id'] ?? $record->product_id;
        $record->fg_quantity = $data['fg_quantity'] ?? $data['counted_qty'] ?? $record->fg_quantity ?? 0;
        $record->system_quantity = $data['system_quantity'] ?? $record->system_quantity ?? 0;
        $record->location = $data['location'] ?? $record->location;
        $record->status = $data['status'] ?? $record->status ?? 'pending_verification';
        $record->save();

        return $isNew ? 'created' : 'updated';
    }

    protected function upsertEndorseToLogistic($data, $modelClass, $rowNumber)
    {
        $validator = \Validator::make($data, [
            'etl_number' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:1',
        ]);

        if ($validator->fails()) return 'error';

        $record = $modelClass::firstOrNew(['etl_number' => $data['etl_number']]);
        $isNew = !$record->exists;
        
        $record->job_order_id = $data['job_order_id'] ?? $record->job_order_id;
        $record->product_id = $data['product_id'] ?? $record->product_id;
        $record->quantity = $data['quantity'] ?? $record->quantity;
        $record->status = $data['status'] ?? $record->status ?? 'pending';
        $record->save();

        return $isNew ? 'created' : 'updated';
    }
}
