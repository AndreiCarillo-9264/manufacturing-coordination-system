<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEndorseToLogisticRequest extends FormRequest
{
    public function authorize(): bool
    {
        $endorse = $this->route('endorse_to_logistic');
        return $this->user()->can('update', $endorse);
    }

    public function rules(): array
    {
        $id = $this->route('endorse_to_logistic')->id ?? null;

        return [
            'etl_delivery_code' => 'nullable|string|unique:endorse_to_logistics,etl_delivery_code,' . $id . '|max:255',
            'delivery_schedule_id' => 'nullable|exists:delivery_schedules,id',
            'job_order_id' => 'nullable|exists:job_orders,id',
            'product_id' => 'nullable|exists:products,id',
            'customer' => 'nullable|string|max:255',
            'date_endorsed' => 'nullable|date|after_or_equal:today',
            'time_endorsed' => 'nullable',
            'product_code' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'total_out' => 'nullable|integer|min:0',
            'uom' => 'nullable|string|max:50',
            'delivery_date' => 'nullable|date|after_or_equal:today',
            'dr_number' => 'nullable|string|max:255',
            'si_number' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
            'received_by' => 'nullable|string|max:255',
            'received_date' => 'nullable|date',
            'common_stretch_film_code' => 'nullable|string|max:255',
            'csf_quantity' => 'nullable|integer|min:0',
            'quantity' => 'nullable|integer|min:0',
            'qty_delivered' => 'nullable|integer|min:0',
        ];
    }
}
