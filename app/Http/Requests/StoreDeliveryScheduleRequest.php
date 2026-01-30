<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => 'required|date',
            'jo_id' => 'required|exists:job_orders,id',
            'qty' => 'required|integer|min:1',
            'uom' => 'required|string|max:50',
            'remarks' => 'nullable|string',
            'pmp_commitment' => 'nullable|string',
            'ppqc_commitment' => 'nullable|string',
            'ds_delivery_code' => 'nullable|string',
        ];
    }
}
