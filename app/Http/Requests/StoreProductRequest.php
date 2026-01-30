<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Product::class);
    }

    public function rules(): array
    {
        return [
            'customer' => 'required|string|max:255',
            'model_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_encoded' => 'nullable|date',
            'specs' => 'nullable|string|max:2000',
            'dimension' => 'nullable|string|max:120',
            'moq' => 'required|integer|min:1',
            'uom' => 'required|string|max:50',
            'currency' => 'required|string|max:10',
            'selling_price' => 'required|numeric|min:0',
            'rsqf_number' => 'nullable|string|max:255',
            'remarks_po' => 'nullable|string',
            'mc' => 'nullable|numeric|min:0',
            'diff' => 'nullable|numeric',
            'mu' => 'nullable|numeric',
            'location' => 'nullable|string|max:255',
            'pc' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'dimension.regex' => 'Dimension must follow a format like: 120 x 80 x 45 mm (decimals allowed, spaces optional, x/X/× separator, optional unit like mm/cm)',
            'moq.min' => 'Minimum Order Quantity (MOQ) must be at least 1.',
            'selling_price.min' => 'Selling Price cannot be negative.',
            'mc.min' => 'Material Cost (MC) cannot be negative.',
        ];
    }
}