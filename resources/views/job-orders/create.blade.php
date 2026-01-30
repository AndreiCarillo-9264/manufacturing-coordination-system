@extends('layouts.app')

@section('title', 'Create Job Order')
@section('page-icon') <i class="fas fa-plus-circle"></i> @endsection
@section('page-title', 'Create New Job Order')
@section('page-description', 'Add a new job order for production')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="p-6 border-b bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800">New Job Order Form</h3>
        <p class="text-sm text-gray-600 mt-1">Enter the details below. Fields marked with * are required.</p>
    </div>

    <form action="{{ route('job-orders.store') }}" method="POST" class="p-6 space-y-8">
        @csrf

        <!-- Auto-generated info banner -->
        <div class="bg-blue-50 border border-blue-100 p-4 rounded-lg text-sm text-blue-800 space-y-1">
            <p><strong>JO Number</strong> will be automatically generated (JO-YYYY-NNNN)</p>
            <p><strong>PO Number</strong> will be automatically generated (PO-YYYY-MM-NNNN)</p>
            <p><strong>Date Encoded</strong> will be set to today ({{ now()->format('M d, Y') }})</p>
            <p><strong>Week Number</strong> will be calculated from Date Needed</p>
            <p><strong>Encoded By</strong> will be set to current user</p>
            <p><strong>Status</strong> will start as Pending</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Date Needed -->
            <div>
                <label for="date_needed" class="block text-sm font-medium text-gray-700 mb-1.5">Date Needed *</label>
                <input type="date" name="date_needed" value="{{ old('date_needed', now()->format('Y-m-d')) }}"
                       min="{{ now()->format('Y-m-d') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('date_needed') border-red-500 @enderror">
                @error('date_needed') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Product -->
            <div>
                <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1.5">Product *</label>
                <select id="product_id" name="product_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('product_id') border-red-500 @enderror">
                    <option value="">— Select Product —</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-uom="{{ $product->uom }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->product_code }} - {{ $product->model_name }} ({{ $product->customer_name ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
                @error('product_id') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Quantity -->
            <div>
                <label for="qty" class="block text-sm font-medium text-gray-700 mb-1.5">Quantity *</label>
                <input type="number" name="qty" value="{{ old('qty', 1) }}" min="1"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('qty') border-red-500 @enderror">
                @error('qty') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- UOM -->
            <div>
                <label for="uom" class="block text-sm font-medium text-gray-700 mb-1.5">UOM <span class="text-gray-500 text-xs">(Unit of Measure)</span> *</label>
                <select id="uom" name="uom" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('uom') border-red-500 @enderror">
                    <option value="">— Select Unit —</option>
                    <option value="pcs" {{ old('uom', 'pcs') == 'pcs' ? 'selected' : '' }}>pcs – Pieces</option>
                    <option value="set" {{ old('uom') == 'set' ? 'selected' : '' }}>set – Set</option>
                    <option value="kg" {{ old('uom') == 'kg' ? 'selected' : '' }}>kg – Kilogram</option>
                    <option value="g" {{ old('uom') == 'g' ? 'selected' : '' }}>g – Gram</option>
                    <option value="m" {{ old('uom') == 'm' ? 'selected' : '' }}>m – Meter</option>
                    <option value="cm" {{ old('uom') == 'cm' ? 'selected' : '' }}>cm – Centimeter</option>
                    <option value="mm" {{ old('uom') == 'mm' ? 'selected' : '' }}>mm – Millimeter</option>
                    <option value="l" {{ old('uom') == 'l' ? 'selected' : '' }}>L – Liter</option>
                    <option value="ml" {{ old('uom') == 'ml' ? 'selected' : '' }}>mL – Milliliter</option>
                    <option value="box" {{ old('uom') == 'box' ? 'selected' : '' }}>box – Box</option>
                    <option value="pack" {{ old('uom') == 'pack' ? 'selected' : '' }}>pack – Pack</option>
                </select>
                @error('uom') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Remarks -->
            <div class="md:col-span-2">
                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1.5">Remarks</label>
                <textarea name="remarks" rows="3" placeholder="Additional notes or special instructions..."
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('remarks') border-red-500 @enderror">{{ old('remarks') }}</textarea>
                @error('remarks') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

        </div>

        <div class="flex justify-end gap-4 pt-6 border-t">
            <a href="{{ route('job-orders.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">Create Job Order</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const uomSelect = document.getElementById('uom');
    
    // Auto-populate UOM when product changes
    productSelect.addEventListener('change', function() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const uom = selectedOption.getAttribute('data-uom');
        
        if (uom) {
            uomSelect.value = uom;
        }
    });
    
    // Trigger on page load if product is already selected (form validation error case)
    if (productSelect.value) {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const uom = selectedOption.getAttribute('data-uom');
        
        if (uom && !uomSelect.value) {
            uomSelect.value = uom;
        }
    }
});
</script>
@endsection