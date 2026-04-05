<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeliveryScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('delivery_schedule'));
    }

    public function rules(): array
    {
        $deliveryScheduleId = $this->route('delivery_schedule')->id;

        return [
            'ds_code' => [
                'nullable',
                'string',
                Rule::unique('delivery_schedules', 'ds_code')->ignore($deliveryScheduleId),
                'max:255'
            ],
            'job_order_id' => 'required|exists:job_orders,id',
            'product_id' => 'nullable|exists:products,id',
            'ds_status' => 'nullable|in:BACKLOG,ON SCHEDULE,DELIVERED,CANCELLED',
            'ppqc_status' => 'nullable|string|max:255',
            'delivery_date' => 'required|date|after_or_equal:today',
            'delivery_time' => 'nullable|date_format:H:i',
            'week_number' => 'nullable|integer|min:1|max:53',
            'quantity' => 'required|integer|min:1',
            'max_quantity' => 'nullable|integer|min:0',
            'delivered_quantity' => 'nullable|integer|min:0',
            'transfer_quantity' => 'nullable|integer|min:0',
            'fg_stocks' => 'nullable|integer|min:0',
            'buffer_stocks' => 'nullable|integer|min:0',
            'jo_balance' => 'nullable|integer|min:0',
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
            'quantity.required' => 'The quantity field is required.',
            'quantity.min' => 'Quantity must be at least 1.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Re-calculate week_number if delivery_date changed
        if ($this->delivery_date) {
            $this->merge([
                'week_number' => (int) date('W', strtotime($this->delivery_date)),
            ]);
        }

        // Enforce delivery time window (08:00 - 17:00) if provided
        if ($this->delivery_time) {
            $time = \DateTime::createFromFormat('H:i', $this->delivery_time);
            if ($time) {
                $hourMinute = $time->format('H:i');
                if ($hourMinute < '08:00' || $hourMinute > '17:00') {
                    $this->merge(['_delivery_time_error' => true]);
                }
            }
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('_delivery_time_error')) {
                $validator->errors()->add('delivery_time', 'Delivery time must be between 08:00 and 17:00.');
            }
        });
    }
}