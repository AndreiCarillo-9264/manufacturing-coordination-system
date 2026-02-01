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
            'status' => 'nullable|in:pending,approved,in_progress,completed,cancelled',
            'fulfillment_status' => 'nullable|in:full,balance,excess',
            'product_id' => 'required|exists:products,id',
            'qty_ordered' => 'required|integer|min:1',
            'qty_balance' => 'nullable|integer|min:0',
            'qty_transferred_to_ppqc' => 'nullable|integer|min:0',
            'qty_in_delivery_schedule' => 'nullable|integer|min:0',
            'withdrawal_status' => 'nullable|in:approved,with_fg_stocks',
            'withdrawal_number' => 'nullable|string|max:255',
            'week_number' => 'nullable|integer|min:1|max:53',
            'date_needed' => 'required|date|after_or_equal:today',
            'date_encoded' => 'nullable|date',
            'date_approved' => 'nullable|date',
            'encoded_by_user_id' => 'nullable|exists:users,id',
            'remarks' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Please select a product.',
            'product_id.exists' => 'The selected product does not exist.',
            'qty_ordered.required' => 'The quantity ordered field is required.',
            'qty_ordered.min' => 'Quantity ordered must be at least 1.',
            'date_needed.required' => 'The date needed field is required.',
            'date_needed.after_or_equal' => 'Date needed must be today or a future date.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Auto-calculate week_number from date_needed if not provided
        if (!$this->week_number && $this->date_needed) {
            $this->merge([
                'week_number' => (int) date('W', strtotime($this->date_needed)),
            ]);
        }

        // Set default status
        if (!$this->status) {
            $this->merge(['status' => 'pending']);
        }

        // Set date_encoded to today if not provided
        if (!$this->date_encoded) {
            $this->merge(['date_encoded' => now()->toDateString()]);
        }

        // Set encoded_by to current user if not provided
        if (!$this->encoded_by_user_id && auth()->check()) {
            $this->merge(['encoded_by_user_id' => auth()->id()]);
        }

        // Initialize balance to qty_ordered
        if (!$this->qty_balance) {
            $this->merge(['qty_balance' => $this->qty_ordered ?? 0]);
        }
    }
}