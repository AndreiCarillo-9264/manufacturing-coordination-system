<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\InventoryTransfer::class);
    }

    public function rules(): array
    {
        return [
            'ptt_number' => 'nullable|string|unique:inventory_transfers,ptt_number|max:255',
            'job_order_id' => 'required|exists:job_orders,id',
            'product_id' => 'nullable|exists:products,id',
            'section' => 'required|in:LOCAL,IMPORTED,EXPORT',
            'category' => 'required|string|max:255',
            'status' => 'nullable|in:Balance,Complete',
            'date_transferred' => 'required|date|after_or_equal:today',
            'time_transferred' => 'required',
            'quantity' => 'required|integer|min:1',
            'delivery_date' => 'nullable|date',
            'transfer_by' => 'nullable|string|max:255',
            'received_by_user_id' => 'nullable|exists:users,id|required_without:received_by_name',
            'received_by_name' => 'nullable|string|max:255|required_without:received_by_user_id',
            'date_received' => 'required|date|after_or_equal:today',
            'time_received' => 'required',
            'quantity_received' => 'required|integer|min:1',
            'remarks' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'job_order_id.required' => 'Please select a job order.',
            'section.required' => 'The section field is required.',
            'category.required' => 'The category field is required.',
            'date_transferred.required' => 'The transfer date field is required.',
            'time_transferred.required' => 'The transfer time field is required.',
            'quantity.required' => 'The quantity field is required.',
            'quantity.min' => 'Quantity must be at least 1.',
            'received_by_user_id.required' => 'Please provide who received the transfer.',
            'received_by_name.required' => 'Please provide the name of who received the transfer.',
            'date_received.required' => 'The received date field is required.',
            'time_received.required' => 'The received time field is required.',
            'quantity_received.required' => 'The quantity received field is required.',
            'quantity_received.min' => 'Quantity received must be at least 1.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Auto-calculate week_number
        if (!$this->week_number && $this->date_transferred) {
            $this->merge([
                'week_number' => (int) date('W', strtotime($this->date_transferred)),
            ]);
        }

        // Set default status based on quantities
        if (!$this->status) {
            $status = ($this->quantity_received ?? 0) >= ($this->quantity ?? 0) ? 'Complete' : 'Balance';
            $this->merge(['status' => $status]);
        }

        // Ensure received_by_user_id is set
        if (!$this->received_by_user_id && auth()->check()) {
            $this->merge(['received_by_user_id' => auth()->id()]);
        }

        // Set audit fields
        if (!$this->encoded_by && auth()->check()) {
            $this->merge(['encoded_by' => auth()->id()]);
        }

        if (!$this->date_encoded) {
            $this->merge(['date_encoded' => now()]);
        }
    }
}