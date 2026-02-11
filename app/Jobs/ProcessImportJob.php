<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProcessImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $path;
    public string $resource;

    public function __construct(string $path, string $resource)
    {
        $this->path = $path;
        $this->resource = $resource;
    }

    public function handle()
    {
        // Currently we support CSV processing for 'products'.
        try {
            if ($this->resource === 'products') {
                $localPath = Storage::path($this->path);
                if (!file_exists($localPath)) {
                    Log::warning("Import file missing: {$this->path}");
                    return;
                }

                $handle = fopen($localPath, 'r');
                $header = null;
                $created = 0; $updated = 0; $errors = [];
                $rowNumber = 0;

                while (($row = fgetcsv($handle)) !== false) {
                    $rowNumber++;
                    if (!$header) { $header = array_map('trim', $row); continue; }
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

                // Save errors if any
                if (count($errors)) {
                    $errPath = 'imports/products/errors_' . now()->format('Ymd_His') . '.json';
                    Storage::put($errPath, json_encode($errors, JSON_PRETTY_PRINT));
                }

                Log::info("ProcessImportJob: products imported ({$created} created, {$updated} updated)");
            }

            // Other resources can be added later.
        } catch (\Throwable $e) {
            Log::error('ProcessImportJob failed: ' . $e->getMessage());
        }
    }
}
