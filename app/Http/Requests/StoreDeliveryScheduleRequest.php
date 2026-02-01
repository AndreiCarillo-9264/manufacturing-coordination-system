<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\DeliverySchedule::class);
    }

    public function rules(): array
    {
        return [
            'delivery_code' => 'nullable|string|unique:delivery_schedules,delivery_code|max:255',
            'job_order_id' => 'required|exists:job_orders,id',
            'product_id' => 'nullable|exists:products,id',
            'status' => 'nullable|in:pending,urgent,backlog,complete',
            'ppqc_status' => 'nullable|string|max:255',
            'delivery_date' => 'required|date',
            'week_number' => 'nullable|integer|min:1|max:53',
            'date_encoded' => 'nullable|date',
            'qty_scheduled' => 'required|integer|min:1',
            'qty_delivered' => 'nullable|integer|min:0',
            'qty_transferred' => 'nullable|integer|min:0',
            'qty_max' => 'nullable|integer|min:0',
            'qty_fg_stocks' => 'nullable|integer|min:0',
            'qty_buffer_stock' => 'nullable|integer|min:0',
            'qty_backlog' => 'nullable|integer|min:0',
            'qty_jo_balance' => 'nullable|integer|min:0',
            'pmp_commitment' => 'nullable|string',
            'ppqc_commitment' => 'nullable|string',
            'remarks' => 'nullable|string',
            'delivery_remarks' => 'nullable|string',
            'jo_remarks' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'job_order_id.required' => 'Please select a job order.',
            'job_order_id.exists' => 'The selected job order does not exist.',
            'delivery_date.required' => 'The delivery date field is required.',
            'qty_scheduled.required' => 'The quantity scheduled field is required.',
            'qty_scheduled.min' => 'Quantity scheduled must be at least 1.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Auto-calculate week_number from delivery_date if not provided
        if (!$this->week_number && $this->delivery_date) {
            $this->merge([
                'week_number' => (int) date('W', strtotime($this->delivery_date)),
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

        // Initialize default quantities
        $defaults = [
            'qty_fg_stocks' => 0,
            'qty_buffer_stock' => 0,
            'qty_backlog' => 0,
            'qty_jo_balance' => 0,
        ];

        foreach ($defaults as $key => $value) {
            if (!$this->has($key)) {
                $this->merge([$key => $value]);
            }
        }
    }
}