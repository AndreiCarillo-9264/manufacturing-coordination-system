<!-- resources/views/actual-inventories/edit.blade.php -->
@extends('layouts.app')

@section('title', 'Edit Actual Inventory')
@section('page-icon') <i class="fas fa-edit"></i> @endsection
@section('page-title', 'Edit Inventory Count: ' . $actualInventory->tag_number)
@section('page-description', 'Update inventory count information')

@section('content')
<x-resource-form 
    :action="route('actual-inventories.update', $actualInventory)" 
    method="PUT" 
    title="Edit Inventory Count" 
    description="Update the inventory details below. Fields marked with * are required." 
    :cancel="route('actual-inventories.index')" 
    submit="Update Inventory">
    
    <x-slot name="headerRight">
        <div class="text-sm text-gray-500 font-mono bg-gray-100 px-3 py-1 rounded">
            {{ $actualInventory->tag_number }}
        </div>
    </x-slot>

    {{-- SYSTEM INFO --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-5">
        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                Tag Number
            </label>
            <div class="px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-900">
                {{ $actualInventory->tag_number }}
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                Date Encoded
            </label>
            <div class="px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-900">
                {{ $actualInventory->date_encoded?->format('M d, Y') ?? '—' }}
            </div>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                Encoded By
            </label>
            <div class="px-4 py-2.5 bg-white border border-gray-300 rounded-lg text-sm text-gray-900">
                {{ $actualInventory->encodedByUser?->name ?? 'System' }}
            </div>
        </div>
    </div>

    {{-- PRODUCT SELECTION --}}
    <div class="mt-6">
        <label for="product_id" class="block text-sm font-semibold text-gray-700 mb-2">
            Product <span class="text-red-500">*</span>
        </label>
        <select id="product_id" name="product_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('product_id') border-red-500 ring-2 ring-red-200 @enderror">
            <option value="">Search and select product...</option>
            @foreach($products as $product)
            <option value="{{ $product->id }}" {{ old('product_id', $actualInventory->product_id) == $product->id ? 'selected' : '' }}>{{ $product->product_code }} - {{ $product->model_name }}</option>
            @endforeach
        </select>
        @error('product_id') 
        <p class="mt-2 text-sm text-red-600 flex items-center">
            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
        </p> 
        @enderror
    </div>

    {{-- AUTO-FILLED FIELDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        {{-- PRODUCT CODE --}}
        <div>
            <label for="product_code" class="block text-sm font-semibold text-gray-700 mb-2">
                Product Code
            </label>
            <input type="text" id="product_code" name="product_code" value="{{ old('product_code', $actualInventory->product_code) }}" readonly class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 cursor-not-allowed">
        </div>

        {{-- CUSTOMER NAME --}}
        <div>
            <label for="customer_name" class="block text-sm font-semibold text-gray-700 mb-2">
                Customer Name
            </label>
            <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name', $actualInventory->customer_name) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('customer_name') border-red-500 ring-2 ring-red-200 @enderror">
            @error('customer_name') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>

        {{-- MODEL NAME --}}
        <div>
            <label for="model_name" class="block text-sm font-semibold text-gray-700 mb-2">
                Model Name
            </label>
            <input type="text" id="model_name" name="model_name" value="{{ old('model_name', $actualInventory->model_name) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('model_name') border-red-500 ring-2 ring-red-200 @enderror">
            @error('model_name') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>

        {{-- DESCRIPTION --}}
        <div>
            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                Description
            </label>
            <input type="text" id="description" name="description" value="{{ old('description', $actualInventory->description) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('description') border-red-500 ring-2 ring-red-200 @enderror">
            @error('description') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>

        {{-- DIMENSION --}}
        <div>
            <label for="dimension" class="block text-sm font-semibold text-gray-700 mb-2">
                Dimension
            </label>
            <input type="text" id="dimension" name="dimension" value="{{ old('dimension', $actualInventory->dimension) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('dimension') border-red-500 ring-2 ring-red-200 @enderror">
            @error('dimension') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>

        {{-- UOM --}}
        <div>
            <label for="uom" class="block text-sm font-semibold text-gray-700 mb-2">
                UOM
            </label>
            <input type="text" id="uom" name="uom" value="{{ old('uom', $actualInventory->uom) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('uom') border-red-500 ring-2 ring-red-200 @enderror">
            @error('uom') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>
    </div>

    {{-- MAIN FORM FIELDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        {{-- FG QUANTITY --}}
        <div>
            <label for="fg_quantity" class="block text-sm font-semibold text-gray-700 mb-2">
                FG Quantity <span class="text-red-500">*</span>
            </label>
            <input type="number" id="fg_quantity" name="fg_quantity" value="{{ old('fg_quantity', $actualInventory->fg_quantity) }}" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('fg_quantity') border-red-500 ring-2 ring-red-200 @enderror">
            @error('fg_quantity') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>

        {{-- LOCATION --}}
        <div>
            <label for="location" class="block text-sm font-semibold text-gray-700 mb-2">
                Location
            </label>
            <input type="text" id="location" name="location" value="{{ old('location', $actualInventory->location) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('location') border-red-500 ring-2 ring-red-200 @enderror">
            @error('location') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>

        {{-- COUNTED BY --}}
        <div>
            <label for="counted_by" class="block text-sm font-semibold text-gray-700 mb-2">
                Counted By
            </label>
            <input type="text" id="counted_by" name="counted_by" value="{{ old('counted_by', $actualInventory->counted_by) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('counted_by') border-red-500 ring-2 ring-red-200 @enderror">
            @error('counted_by') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>

        {{-- COUNTED AT --}}
        <div>
            <label for="counted_at" class="block text-sm font-semibold text-gray-700 mb-2">
                Counted At
            </label>
            <input type="date" id="counted_at" name="counted_at" value="{{ old('counted_at', $actualInventory->counted_at?->format('Y-m-d')) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('counted_at') border-red-500 ring-2 ring-red-200 @enderror">
            @error('counted_at') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>

        {{-- VERIFIED BY --}}
        <div>
            <label for="verified_by" class="block text-sm font-semibold text-gray-700 mb-2">
                Verified By
            </label>
            <input type="text" id="verified_by" name="verified_by" value="{{ old('verified_by', $actualInventory->verified_by) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('verified_by') border-red-500 ring-2 ring-red-200 @enderror">
            @error('verified_by') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>

        {{-- VERIFIED AT --}}
        <div>
            <label for="verified_at" class="block text-sm font-semibold text-gray-700 mb-2">
                Verified At
            </label>
            <input type="date" id="verified_at" name="verified_at" value="{{ old('verified_at', $actualInventory->verified_at?->format('Y-m-d')) }}" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('verified_at') border-red-500 ring-2 ring-red-200 @enderror">
            @error('verified_at') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>

        {{-- STATUS --}}
        <div>
            <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">
                Status
            </label>
            <select id="status" name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('status') border-red-500 ring-2 ring-red-200 @enderror">
                <option value="Pending" {{ old('status', $actualInventory->status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Counted" {{ old('status', $actualInventory->status) == 'Counted' ? 'selected' : '' }}>Counted</option>
                <option value="Verified" {{ old('status', $actualInventory->status) == 'Verified' ? 'selected' : '' }}>Verified</option>
                <option value="Discrepancy" {{ old('status', $actualInventory->status) == 'Discrepancy' ? 'selected' : '' }}>Discrepancy</option>
            </select>
            @error('status') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>

        {{-- REMARKS --}}
        <div class="md:col-span-2">
            <label for="remarks" class="block text-sm font-semibold text-gray-700 mb-2">
                Remarks
            </label>
            <textarea id="remarks" name="remarks" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('remarks') border-red-500 ring-2 ring-red-200 @enderror">{{ old('remarks', $actualInventory->remarks) }}</textarea>
            @error('remarks') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>
    </div>
</x-resource-form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Select2 for searchable dropdown
    $('#product_id').select2({
        placeholder: 'Search product code...',
        allowClear: true,
        minimumResultsForSearch: 0
    });

    // Preload products data
    const products = @json($products);

    // Auto-fill fields on product selection
    $('#product_id').on('change', function() {
        const productId = $(this).val();
        if (productId) {
            const selected = products.find(p => p.id == productId);
            if (selected) {
                $('#product_code').val(selected.product_code);
                $('#customer_name').val(selected.customer_name);
                $('#model_name').val(selected.model_name);
                $('#description').val(selected.description);
                $('#dimension').val(selected.dimension);
                $('#uom').val(selected.uom);
            }
        } else {
            // Clear fields if no product selected
            $('#product_code').val('');
            $('#customer_name').val('');
            $('#model_name').val('');
            $('#description').val('');
            $('#dimension').val('');
            $('#uom').val('');
        }
    });
});
</script>
@endpush