<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateActualInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('actual_inventory'));
    }

    public function rules(): array
    {
        $actualInventoryId = $this->route('actual_inventory')->id;

        return [
            'tag_number' => [
                'required',
                'string',
                Rule::unique('actual_inventory', 'tag_number')->ignore($actualInventoryId),
                'max:255'
            ],
            'product_id' => 'required|exists:products,id',
            'fg_quantity' => 'required|integer|min:0',
            'location' => 'nullable|string|max:255',
            'counted_by' => 'nullable|string|max:255',
            'verified_by' => 'nullable|string|max:255',
            'counted_at' => 'nullable|date',
            'verified_at' => 'nullable|date',
            'status' => 'nullable|in:Pending,Counted,Verified,Discrepancy',
            'remarks' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'tag_number.required' => 'The tag number field is required.',
            'tag_number.unique' => 'This tag number has already been used.',
            'product_id.required' => 'Please select a product.',
            'product_id.exists' => 'The selected product does not exist.',
            'fg_quantity.required' => 'The quantity counted field is required.',
            'fg_quantity.min' => 'Quantity counted cannot be negative.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Auto-set counted_at if counted_by is provided but counted_at is not
        if ($this->counted_by_user_id && !$this->counted_at) {
            $this->merge(['counted_at' => now()]);
        }

        // Auto-set verified_at if verified_by is provided but verified_at is not
        if ($this->verified_by_user_id && !$this->verified_at) {
            $this->merge(['verified_at' => now()]);
        }
    }
}