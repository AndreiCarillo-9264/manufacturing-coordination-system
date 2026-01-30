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
            'date_needed' => 'required|date|after_or_equal:today',
            'po_number' => 'nullable|string|max:255',
            'product_id' => 'required|exists:products,id',
            'qty' => 'required|integer|min:1',
            'uom' => 'required|string|max:50',
            'remarks' => 'nullable|string',
            'week_number' => 'nullable|integer',
            'date_encoded' => 'required|date',
            'encoded_by_user_id' => 'required|exists:users,id',
        ];
    }

    protected function prepareForValidation()
    {
        // Auto-calculate week_number from date_needed if not provided
        if (!$this->week_number && $this->date_needed) {
            $this->merge([
                'week_number' => (int) date('W', strtotime($this->date_needed)),
            ]);
        }

        // Set date_encoded to today
        $this->merge([
            'date_encoded' => now()->toDateString(),
            'encoded_by_user_id' => auth()->id(),
        ]);
    }
}