<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ActualInventory;
use App\Models\Product;

class StoreActualInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', ActualInventory::class);
    }

    public function rules(): array
    {
        return [
            'tag_number' => 'required|string|unique:actual_inventories,tag_number|max:50',
            'product_id' => 'required|exists:products,id',
            'fg_qty' => 'required|integer|min:0',
            'uom' => 'required|string|max:50',
            'location' => 'required|string|max:100',
            'counted_by_user_id' => 'required|exists:users,id',
            'verified_by_user_id' => 'nullable|exists:users,id',
            'remarks' => 'nullable|string',
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->tag_number) {
            $this->merge(['tag_number' => $this->generateTagNumber()]);
        }

        if ($this->product_id && !$this->uom) {
            $product = Product::find($this->product_id);
            if ($product) {
                $this->merge(['uom' => $product->uom]);
            }
        }
    }

    private function generateTagNumber(): string
    {
        $year = date('Y');
        $lastInventory = ActualInventory::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = ($lastInventory ? intval(substr($lastInventory->tag_number, -4)) : 0) + 1;
        return 'TAG-' . $year . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
