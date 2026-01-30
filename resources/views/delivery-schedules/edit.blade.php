@extends('layouts.app')

@section('title', 'Edit Delivery Schedule')
@section('page-icon') <i class="fas fa-edit"></i> @endsection
@section('page-title', 'Edit Delivery Schedule')
@section('page-description', 'Update delivery details for ' . $deliverySchedule->ds_delivery_code)

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden max-w-4xl mx-auto">
    <div class="p-6 border-b bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800">Edit Delivery Schedule</h3>
        <p class="text-sm text-gray-600 mt-1">Update the delivery schedule details below. Fields marked with * are required.</p>
    </div>

    <form action="{{ route('delivery-schedules.update', $deliverySchedule) }}" method="POST" class="p-6 space-y-8">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Delivery Code (Read-only) -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Delivery Code</label>
                <input type="text" value="{{ $deliverySchedule->ds_delivery_code }}" readonly
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
            </div>

            <!-- Job Order -->
            <div class="md:col-span-2">
                <label for="jo_id" class="block text-sm font-medium text-gray-700 mb-1.5">Job Order *</label>
                <select id="jo_id" name="jo_id" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('jo_id') border-red-500 @enderror">
                    @foreach($jobOrders as $jo)
                    <option value="{{ $jo->id }}" 
                            data-po-number="{{ $jo->po_number }}"
                            data-product-code="{{ $jo->product->product_code }}"
                            data-product-name="{{ $jo->product->model_name ?? $jo->product->product_code }}"
                            data-uom="{{ $jo->product->uom }}"
                            data-qty="{{ $jo->qty }}"
                            {{ old('jo_id', $deliverySchedule->jo_id) == $jo->id ? 'selected' : '' }}>
                        {{ $jo->jo_number }} — {{ $jo->product->model_name ?? $jo->product->product_code ?? '—' }} (PO: {{ $jo->po_number ?? 'N/A' }})
                    </option>
                    @endforeach
                </select>
                @error('jo_id') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Product Code (Auto-populated) -->
            <div class="md:col-span-2">
                <label for="product_info" class="block text-sm font-medium text-gray-700 mb-1.5">Product</label>
                <input type="text" id="product_info" readonly
                       value="{{ $deliverySchedule->product->product_code ?? '' }} — {{ $deliverySchedule->product->model_name ?? '' }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
            </div>

            <!-- Delivery Date -->
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700 mb-1.5">Delivery Date *</label>
                <input type="date" id="date" name="date" value="{{ old('date', $deliverySchedule->date->format('Y-m-d')) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('date') border-red-500 @enderror">
                @error('date') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Quantity -->
            <div>
                <label for="qty" class="block text-sm font-medium text-gray-700 mb-1.5">Quantity *</label>
                <input type="number" id="qty" name="qty" value="{{ old('qty', $deliverySchedule->qty) }}" min="0" step="1" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('qty') border-red-500 @enderror">
                @error('qty') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- UOM -->
            <div>
                <label for="uom" class="block text-sm font-medium text-gray-700 mb-1.5">Unit of Measure</label>
                <input type="text" id="uom" name="uom" value="{{ old('uom', $deliverySchedule->uom) }}" readonly
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
            </div>

            <!-- Remarks -->
            <div class="md:col-span-2">
                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1.5">Remarks</label>
                <textarea id="remarks" name="remarks" rows="3"
                         class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('remarks') border-red-500 @enderror">{{ old('remarks', $deliverySchedule->remarks) }}</textarea>
                @error('remarks') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end gap-3 pt-4 border-t">
            <a href="{{ route('delivery-schedules.index') }}" 
               class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2">
                <i class="fas fa-save"></i> Update Schedule
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const joSelect = document.getElementById('jo_id');
    const productInfo = document.getElementById('product_info');
    const uomInput = document.getElementById('uom');
    const qtyInput = document.getElementById('qty');
    
    function autoPopulateFields() {
        const selectedOption = joSelect.options[joSelect.selectedIndex];
        
        if (selectedOption.value) {
            const productCode = selectedOption.getAttribute('data-product-code');
            const productName = selectedOption.getAttribute('data-product-name');
            const uom = selectedOption.getAttribute('data-uom');
            const qty = selectedOption.getAttribute('data-qty');
            
            // Auto-populate product info
            const productDisplay = productCode ? productCode + ' — ' + productName : productName;
            productInfo.value = productDisplay || '';
            
            // Auto-populate UOM if not already set by user
            if (uom && !uomInput.dataset.userModified) {
                uomInput.value = uom;
            }
            
            // Auto-populate quantity with Job Order quantity as suggestion
            if (qty && !qtyInput.dataset.userModified) {
                qtyInput.value = qty;
            }
        } else {
            productInfo.value = '';
        }
    }
    
    // Track if user has manually modified quantity and UOM
    qtyInput.addEventListener('input', function() {
        this.dataset.userModified = 'true';
    });
    
    uomInput.addEventListener('input', function() {
        this.dataset.userModified = 'true';
    });
    
    // Listen for Job Order change
    joSelect.addEventListener('change', autoPopulateFields);
    
    // Populate on page load if Job Order is already selected (form validation error case)
    if (joSelect.value) {
        autoPopulateFields();
    }
});
</script>
@endsection