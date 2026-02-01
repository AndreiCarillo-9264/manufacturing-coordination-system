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
            'product_code' => 'nullable|string|unique:products,product_code|max:255',
            'model_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer' => 'nullable|string|max:255',
            'specs' => 'nullable|string|max:2000',
            'dimension' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'pc' => 'nullable|string|max:255',
            'uom' => 'required|string|max:50',
            'moq' => 'required|integer|min:1',
            'currency' => 'required|string|max:10',
            'selling_price' => 'required|numeric|min:0',
            'mc' => 'nullable|numeric|min:0',
            'diff' => 'nullable|numeric',
            'mu' => 'nullable|numeric',
            'rsqf_number' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'encoded_by_user_id' => 'nullable|exists:users,id',
            'date_encoded' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'model_name.required' => 'The model name field is required.',
            'uom.required' => 'The unit of measure field is required.',
            'moq.min' => 'Minimum Order Quantity (MOQ) must be at least 1.',
            'selling_price.required' => 'The selling price field is required.',
            'selling_price.min' => 'Selling price cannot be negative.',
            'mc.min' => 'Material cost (MC) cannot be negative.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Auto-set date_encoded if not provided
        if (!$this->date_encoded) {
            $this->merge(['date_encoded' => now()->toDateString()]);
        }

        // Auto-set encoded_by if not provided
        if (!$this->encoded_by_user_id && auth()->check()) {
            $this->merge(['encoded_by_user_id' => auth()->id()]);
        }
    }
}