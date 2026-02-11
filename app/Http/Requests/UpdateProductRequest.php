<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('product'));
    }

    public function rules(): array
    {
        $productId = $this->route('product')->id;

        return [
            'product_code' => [
                'nullable',
                'string',
                Rule::unique('products', 'product_code')->ignore($productId),
                'max:255'
            ],
            'model_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'customer_name' => 'nullable|string|max:255',
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
        // Map legacy/form field names to model columns before validation
        if ($this->has('customer') && ! $this->has('customer_name')) {
            $this->merge(['customer_name' => $this->input('customer')]);
        }
    }
}