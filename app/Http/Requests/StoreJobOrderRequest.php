<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\JobOrder::class);
    }

    public function rules(): array
    {
        return [
            'jo_number' => 'nullable|string|unique:job_orders,jo_number|max:255',
            'po_number' => 'nullable|string|max:255',
            'jo_status' => 'nullable|in:Pending,JO Full,Partial,Cancelled',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'jo_balance' => 'nullable|integer|min:0',
            'ppqc_transfer' => 'nullable|integer|min:0',
            'ds_quantity' => 'nullable|integer|min:0',
            'withdrawal_status' => 'nullable|in:approved,with_fg_stocks',
            'withdrawal_number' => 'nullable|string|max:255',
            'week_number' => 'nullable|string|max:10',
            'date_needed' => 'required|date|after_or_equal:today',
            'date_encoded' => 'nullable|date',
            'date_approved' => 'nullable|date',
            'encoded_by' => 'nullable|exists:users,id',
            'remarks' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Please select a product.',
            'product_id.exists' => 'The selected product does not exist.',
            'quantity.required' => 'The quantity field is required.',
            'quantity.min' => 'Quantity must be at least 1.',
            'date_needed.required' => 'The date needed field is required.',
            'date_needed.after_or_equal' => 'Date needed must be today or a future date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Auto-calculate week_number from date_needed if not provided
        if (!$this->week_number && $this->date_needed) {
            $this->merge([
                'week_number' => (string) date('W', strtotime($this->date_needed)),
            ]);
        }

        // Set default status
        if (!$this->jo_status) {
            $this->merge(['jo_status' => 'Pending']);
        }

        // Set date_encoded to today if not provided
        if (!$this->date_encoded) {
            $this->merge(['date_encoded' => now()->toDateString()]);
        }

        // Set encoded_by to current user if not provided
        if (!$this->encoded_by && auth()->check()) {
            $this->merge(['encoded_by' => auth()->id()]);
        }

        // Initialize balance to quantity
        if (!$this->jo_balance) {
            $this->merge(['jo_balance' => $this->quantity ?? 0]);
        }
    }
}