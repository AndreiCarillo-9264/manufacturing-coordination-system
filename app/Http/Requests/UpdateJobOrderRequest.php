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
        return [
            'date_needed' => 'required|date',
            'po_number' => 'required|string|max:255',
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
            'uom' => 'required|string|max:50',
            'remarks' => 'nullable|string',
            'ppqc_transfer' => 'nullable|integer|min:0',
            'ds_quantity' => 'nullable|integer|min:0',
            'withdrawal' => 'nullable|integer|min:0',
            'withdrawal_number' => 'nullable|string',
        ];
    }

    protected function prepareForValidation()
    {
        // Re-calculate week_number if date_needed changed
        if ($this->date_needed) {
            $this->merge([
                'week_number' => (int) date('W', strtotime($this->date_needed)),
            ]);
        }
    }
}