<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreActualInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\ActualInventory::class);
    }

    public function rules(): array
    {
        return [
            'tag_number' => 'required|string|unique:actual_inventory,tag_number|max:255',
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
        // Auto-generate tag_number if not provided
        if (!$this->tag_number) {
            $this->merge(['tag_number' => $this->generateTagNumber()]);
        }

        // Auto-set counted_at if counted_by is provided but counted_at is not
        if ($this->counted_by_user_id && !$this->counted_at) {
            $this->merge(['counted_at' => now()]);
        }

        // Auto-set verified_at if verified_by is provided but verified_at is not
        if ($this->verified_by_user_id && !$this->verified_at) {
            $this->merge(['verified_at' => now()]);
        }
    }

    private function generateTagNumber(): string
    {
        $year = date('Y');
        $lastInventory = \App\Models\ActualInventory::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = $lastInventory 
            ? intval(substr($lastInventory->tag_number, -4)) + 1 
            : 1;

        return 'TAG-' . $year . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
    }
}