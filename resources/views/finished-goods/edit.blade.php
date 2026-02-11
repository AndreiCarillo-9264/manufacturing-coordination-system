{{-- resources/views/finished-goods/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Finished Good')
@section('page-icon') <i class="fas fa-edit"></i> @endsection
@section('page-title', 'Edit Finished Good: ' . $finishedGood->product->product_code ?? $finishedGood->id)
@section('page-description', 'Update finished good information')

@section('content')
<x-resource-form 
    :action="route('finished-goods.update', $finishedGood)" 
    method="PUT" 
    title="Edit Finished Good" 
    description="Update the finished good details below. Fields marked with * are required." 
    :cancel="route('finished-goods.index')" 
    submit="Update Finished Good">
    
    <x-slot name="headerRight">
        <div class="text-sm text-gray-500 font-mono bg-gray-100 px-3 py-1 rounded">
            {{ $finishedGood->fg_code ?? $finishedGood->id }}
        </div>
    </x-slot>

    {{-- SYSTEM INFO --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-5 mb-6">
        <div>
            <label for="fg_code" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                FG Code
            </label>
            <input type="text" 
                   id="fg_code" 
                   name="fg_code" 
                   value="{{ old('fg_code', $finishedGood->fg_code) }}" 
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg font-mono font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('fg_code') border-red-500 ring-2 ring-red-200 @enderror" 
                   readonly>
            @error('fg_code') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                Date Encoded
            </label>
            <div class="px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-900">
                {{ $finishedGood->date_encoded?->format('M d, Y H:i') ?? '—' }}
            </div>
        </div>
    </div>

    {{-- MAIN FORM FIELDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- CURRENT QTY --}}
        <div>
            <label for="current_qty" class="block text-sm font-semibold text-gray-700 mb-2">
                Current Quantity <span class="text-red-500">*</span>
            </label>
            <input type="number" 
                   id="current_qty" 
                   name="current_qty" 
                   value="{{ old('current_qty', $finishedGood->current_qty) }}" 
                   min="0"
                   required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('current_qty') border-red-500 ring-2 ring-red-200 @enderror">
            @error('current_qty')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- END AMOUNT --}}
        <div>
            <label for="end_amount" class="block text-sm font-semibold text-gray-700 mb-2">
                End Amount <span class="text-red-500">*</span>
            </label>
            <input type="number" 
                   id="end_amount" 
                   name="end_amount" 
                   step="0.01"
                   value="{{ old('end_amount', $finishedGood->end_amount) }}" 
                   required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('end_amount') border-red-500 ring-2 ring-red-200 @enderror">
            @error('end_amount')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- LAST IN DATE --}}
        <div>
            <label for="last_in_date" class="block text-sm font-semibold text-gray-700 mb-2">
                Last In Date
            </label>
            <input type="date" 
                   id="last_in_date" 
                   name="last_in_date" 
                   value="{{ old('last_in_date', $finishedGood->last_in_date?->format('Y-m-d')) }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('last_in_date') border-red-500 ring-2 ring-red-200 @enderror">
            @error('last_in_date')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- STOCK STATUS --}}
        <div>
            <label for="stock_status" class="block text-sm font-semibold text-gray-700 mb-2">
                Stock Status <span class="text-red-500">*</span>
            </label>
            <select id="stock_status" 
                    name="stock_status" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('stock_status') border-red-500 ring-2 ring-red-200 @enderror">
                <option value="In Stock" {{ old('stock_status', $finishedGood->stock_status) == 'In Stock' ? 'selected' : '' }}>In Stock</option>
                <option value="Low Stock" {{ old('stock_status', $finishedGood->stock_status) == 'Low Stock' ? 'selected' : '' }}>Low Stock</option>
                <option value="Out of Stock" {{ old('stock_status', $finishedGood->stock_status) == 'Out of Stock' ? 'selected' : '' }}>Out of Stock</option>
                <option value="Old Stock" {{ old('stock_status', $finishedGood->stock_status) == 'Old Stock' ? 'selected' : '' }}>Old Stock</option>
            </select>
            @error('stock_status')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- PRODUCT CODE (READONLY) --}}
        <div>
            <label for="product_code" class="block text-sm font-semibold text-gray-700 mb-2">
                Product Code
            </label>
            <input type="text" 
                   id="product_code" 
                   value="{{ $finishedGood->product->product_code ?? '—' }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 font-medium" 
                   readonly>
        </div>

        {{-- CUSTOMER NAME (READONLY) --}}
        <div>
            <label for="customer_name" class="block text-sm font-semibold text-gray-700 mb-2">
                Customer Name
            </label>
            <input type="text" 
                   id="customer_name" 
                   value="{{ $finishedGood->product->customer_name ?? '—' }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 font-medium" 
                   readonly>
        </div>

        {{-- MODEL NAME (READONLY) --}}
        <div>
            <label for="model_name" class="block text-sm font-semibold text-gray-700 mb-2">
                Model Name
            </label>
            <input type="text" 
                   id="model_name" 
                   value="{{ $finishedGood->product->model_name ?? '—' }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 font-medium" 
                   readonly>
        </div>

        {{-- DESCRIPTION (READONLY) --}}
        <div class="md:col-span-2">
            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                Description
            </label>
            <textarea id="description" 
                      rows="2" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 font-medium" 
                      readonly>{{ $finishedGood->product->description ?? '' }}</textarea>
        </div>

        {{-- DIMENSION (READONLY) --}}
        <div>
            <label for="dimension" class="block text-sm font-semibold text-gray-700 mb-2">
                Dimension
            </label>
            <input type="text" 
                   id="dimension" 
                   value="{{ $finishedGood->product->dimension ?? '—' }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 font-medium" 
                   readonly>
        </div>

        {{-- UOM (READONLY) --}}
        <div>
            <label for="uom" class="block text-sm font-semibold text-gray-700 mb-2">
                Unit of Measure (UOM)
            </label>
            <input type="text" 
                   id="uom" 
                   value="{{ $finishedGood->product->uom ?? '—' }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 font-medium" 
                   readonly>
        </div>

        {{-- VARIANCE QTY (READONLY) --}}
        <div>
            <label for="variance_qty" class="block text-sm font-semibold text-gray-700 mb-2">
                Variance Qty
            </label>
            <input type="number" 
                   id="variance_qty" 
                   value="{{ $finishedGood->variance_qty }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 font-medium" 
                   readonly>
        </div>

        {{-- AGE RANGE (READONLY) --}}
        <div>
            <label for="age_range_label" class="block text-sm font-semibold text-gray-700 mb-2">
                Age Range
            </label>
            <input type="text" 
                   id="age_range_label" 
                   value="{{ $finishedGood->age_range_label }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 font-medium" 
                   readonly>
        </div>

    </div>

    {{-- ADDITIONAL INFORMATION SECTION --}}
    <div class="border-t pt-6 mt-6">
        <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-clipboard-list text-purple-600 mr-2"></i>
            Additional Information
        </h3>
        <div class="md:col-span-2">
            <label for="remarks" class="block text-sm font-semibold text-gray-700 mb-2">
                Remarks / Notes
            </label>
            <textarea id="remarks" 
                      name="remarks" 
                      rows="3" 
                      placeholder="Any special notes for this finished good record..."
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('remarks') border-red-500 ring-2 ring-red-200 @enderror">{{ old('remarks', $finishedGood->remarks) }}</textarea>
            @error('remarks')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>
    </div>

</x-resource-form>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Auto-submit on Enter in non-textarea inputs
    const formInputs = document.querySelectorAll('input:not([type="date"]):not([type="file"]), select');
    formInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.closest('form').submit();
            }
        });
    });
});
</script>
@endsection