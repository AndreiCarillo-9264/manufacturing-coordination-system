<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Product::class);
    }

    public function rules(): array
    {
        return [
            'product_code' => ['nullable', 'string', Rule::unique('products', 'product_code'), 'max:255'],
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

        // Map legacy / form field names to model column names
        if ($this->has('customer') && ! $this->has('customer_name')) {
            $this->merge(['customer_name' => $this->input('customer')]);
        }

        if ($this->has('encoded_by_user_id') && ! $this->has('encoded_by')) {
            $this->merge(['encoded_by' => $this->input('encoded_by_user_id')]);
        }

        // Ensure product_code is present; generate server-side if missing
        if (! $this->filled('product_code')) {
            $year = now()->format('Y');
            $prefix = "PRD-{$year}-";
            
            // Find the highest number for this year's products
            $lastProduct = \App\Models\Product::where('product_code', 'like', $prefix . '%')
                ->orderByRaw('CAST(SUBSTRING(product_code, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
                ->first(['product_code']);

            $next = 1;
            if ($lastProduct && $lastProduct->product_code) {
                // Extract number from code like "PRD-2026-0001"
                $parts = explode('-', $lastProduct->product_code);
                $lastNum = intval(end($parts));
                if ($lastNum > 0) {
                    $next = $lastNum + 1;
                }
            }

            // Generate code and ensure it doesn't exist (retry if needed)
            $maxAttempts = 10;
            $attempt = 0;
            $code = null;
            
            while ($attempt < $maxAttempts) {
                $code = $prefix . str_pad($next + $attempt, 4, '0', STR_PAD_LEFT);
                
                // Check if code already exists (case-sensitive)
                $exists = \App\Models\Product::where('product_code', $code)->exists();
                
                if (!$exists) {
                    break;
                }
                
                $attempt++;
            }

            $this->merge(['product_code' => $code ?? ($prefix . str_pad($next, 4, '0', STR_PAD_LEFT))]);
        }
    }
}