<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('job_order'));
    }

    public function rules(): array
    {
        $jobOrderId = $this->route('job_order')->id;

        return [
            'jo_number' => [
                'nullable',
                'string',
                Rule::unique('job_orders', 'jo_number')->ignore($jobOrderId),
                'max:255'
            ],
            'po_number' => [
                'nullable',
                'string',
                Rule::unique('job_orders', 'po_number')->ignore($jobOrderId),
                'max:255'
            ],
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
            'date_needed' => 'required|date',
            'date_approved' => 'nullable|date',
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
        ];
    }

    protected function prepareForValidation(): void
    {
        // Re-calculate week_number if date_needed changed
        if ($this->date_needed) {
            $this->merge([
                'week_number' => (int) date('W', strtotime($this->date_needed)),
            ]);
        }

        // Prevent setting PO to null at DB level: if request has no po_number, keep existing one
        if ($this->has('po_number') && ($this->po_number === null || $this->po_number === '')) {
            $current = $this->route('job_order')->po_number ?? null;
            if ($current !== null) {
                $this->merge(['po_number' => $current]);
            }
        }
    }
}