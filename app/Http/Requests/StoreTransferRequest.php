<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Transfer::class);
    }

    public function rules(): array
    {
        return [
            'ptt_number' => 'nullable|string|unique:transfers,ptt_number|max:255',
            'job_order_id' => 'required|exists:job_orders,id',
            'product_id' => 'nullable|exists:products,id',
            'section' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'status' => 'nullable|in:balance,complete',
            'delivery_schedule_status' => 'nullable|string|max:255',
            'date_transferred' => 'required|date',
            'time_transferred' => 'required',
            'date_delivery_scheduled' => 'required|date',
            'week_number' => 'nullable|integer|min:1|max:53',
            'jit_days' => 'nullable|integer',
            'qty_transferred' => 'required|integer|min:1',
            'qty_jo_balance' => 'nullable|integer|min:0',
            'grade' => 'nullable|string|max:255',
            'dimension' => 'nullable|string|max:255',
            'unit_selling_price' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'received_by_user_id' => 'required|exists:users,id',
            'date_received' => 'required|date',
            'time_received' => 'required',
            'qty_received' => 'required|integer|min:1',
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
            'date_delivery_scheduled.required' => 'The delivery schedule date field is required.',
            'qty_transferred.required' => 'The quantity transferred field is required.',
            'qty_transferred.min' => 'Quantity transferred must be at least 1.',
            'received_by_user_id.required' => 'Please select who received the transfer.',
            'date_received.required' => 'The received date field is required.',
            'time_received.required' => 'The received time field is required.',
            'qty_received.required' => 'The quantity received field is required.',
            'qty_received.min' => 'Quantity received must be at least 1.',
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

        // Auto-calculate JIT days
        if (!$this->jit_days && $this->date_transferred && $this->date_delivery_scheduled) {
            $jitDays = \Carbon\Carbon::parse($this->date_delivery_scheduled)
                ->diffInDays($this->date_transferred, false);
            $this->merge(['jit_days' => $jitDays]);
        }

        // Set default status
        if (!$this->status) {
            $status = ($this->qty_received ?? 0) >= ($this->qty_transferred ?? 0) ? 'complete' : 'balance';
            $this->merge(['status' => $status]);
        }
    }
}