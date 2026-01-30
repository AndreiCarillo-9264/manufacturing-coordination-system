<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product;

class UpdateActualInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('actual_inventory'));
    }

    public function rules(): array
    {
        return [
            'tag_number' => 'required|string|unique:actual_inventories,tag_number,' . $this->actualInventory->id . '|max:50',
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
        if ($this->product_id && !$this->uom) {
            $product = Product::find($this->product_id);
            if ($product) {
                $this->merge(['uom' => $product->uom]);
            }
        }
    }
}
