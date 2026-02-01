@extends('layouts.app')

@section('title', 'Create Delivery Schedule')
@section('page-icon') <i class="fas fa-plus-circle"></i> @endsection
@section('page-title', 'New Delivery Schedule')
@section('page-description', 'Schedule an upcoming delivery for a job order')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden max-w-4xl mx-auto">
    <div class="p-6 border-b bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800">Create Delivery Schedule</h3>
        <p class="text-sm text-gray-600 mt-1">Add a new delivery date and details for an approved job order. Fields marked with * are required.</p>
    </div>

    <form action="{{ route('delivery-schedules.store') }}" method="POST" class="p-6 space-y-8">
        @csrf

        <!-- Info Banner -->
        <div class="bg-blue-50 border border-blue-100 p-4 rounded-lg text-sm text-blue-800 space-y-1">
            <p><strong>Delivery Code:</strong> Will be automatically generated (DS-YYYY-NNNN)</p>
            <p><strong>Job Order:</strong> Must be in "approved", "in_progress", or "completed" status</p>
        </div>

        <!-- Suggested Delivery Code (editable) -->
        <div>
            <label for="delivery_code" class="block text-sm font-medium text-gray-700 mb-1.5">Delivery Code</label>
            <input type="text" id="delivery_code" name="delivery_code" value="{{ old('delivery_code') }}" placeholder="Auto-suggested delivery code, editable"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('delivery_code') border-red-500 @enderror">
            <p class="mt-1.5 text-xs text-gray-500">Suggested code will be generated, you may edit to match company convention.</p>
            @error('delivery_code') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Job Order -->
            <div class="md:col-span-2">
                <label for="job_order_id" class="block text-sm font-medium text-gray-700 mb-1.5">Job Order *</label>
                <select id="job_order_id" name="job_order_id" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('job_order_id') border-red-500 @enderror">
                    <option value="">— Select Job Order —</option>
                    @foreach($jobOrders as $jo)
                    <option value="{{ $jo->id }}" 
                            data-po-number="{{ $jo->po_number }}"
                            data-product-code="{{ $jo->product->product_code }}"
                            data-product-name="{{ $jo->product->model_name ?? $jo->product->product_code }}"
                            data-uom="{{ $jo->product->uom }}"
                            data-qty="{{ $jo->qty_ordered }}"
                            {{ old('job_order_id') == $jo->id ? 'selected' : '' }}>
                        {{ $jo->jo_number }} — {{ $jo->product->model_name ?? $jo->product->product_code ?? '—' }} (Customer: {{ $jo->product->customer ?? 'N/A' }} | PO: {{ $jo->po_number ?? 'N/A' }})
                    </option>
                    @endforeach
                </select>
                @error('job_order_id') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Product Code (Auto-populated) -->
            <div class="md:col-span-2">
                <label for="product_info" class="block text-sm font-medium text-gray-700 mb-1.5">Product</label>
                <input type="text" id="product_info" readonly
                       placeholder="Auto-populated from Job Order"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
            </div>

            <!-- Delivery Date -->
            <div>
                <label for="delivery_date" class="block text-sm font-medium text-gray-700 mb-1.5">Delivery Date *</label>
                <input type="date" id="delivery_date" name="delivery_date" value="{{ old('delivery_date', now()->format('Y-m-d')) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('delivery_date') border-red-500 @enderror">
                @error('delivery_date') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Quantity -->
            <div>
                <label for="qty_scheduled" class="block text-sm font-medium text-gray-700 mb-1.5">Quantity *</label>
                <input type="number" id="qty_scheduled" name="qty_scheduled" value="{{ old('qty_scheduled', 1) }}" min="1" step="1" required
                       placeholder="Number of units"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('qty_scheduled') border-red-500 @enderror">
                @error('qty_scheduled') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- UOM -->
            <div>
                <label for="uom" class="block text-sm font-medium text-gray-700 mb-1.5">Unit of Measure</label>
                <input type="text" id="uom" name="uom" value="{{ old('uom', '') }}" readonly
                       placeholder="Auto-populated from product"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
            </div>

            <!-- Remarks -->
            <div class="md:col-span-2">
                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1.5">Remarks</label>
                <textarea id="remarks" name="remarks" rows="3" placeholder="Additional delivery notes..."
                         class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('remarks') border-red-500 @enderror">{{ old('remarks') }}</textarea>
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
                <i class="fas fa-plus"></i> Create Schedule
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const joSelect = document.getElementById('job_order_id');
    const productInfo = document.getElementById('product_info');
    const poNumberInput = document.querySelector('input[name="po_number"]') || createHiddenInput('po_number');
    const uomInput = document.getElementById('uom');
    const qtyInput = document.getElementById('qty_scheduled');
    
    function autoPopulateFields() {
        const selectedOption = joSelect.options[joSelect.selectedIndex];
        
        if (selectedOption.value) {
            const poNumber = selectedOption.getAttribute('data-po-number');
            const productCode = selectedOption.getAttribute('data-product-code');
            const productName = selectedOption.getAttribute('data-product-name');
            const uom = selectedOption.getAttribute('data-uom');
            const qty = selectedOption.getAttribute('data-qty');
            
            // Auto-populate product info
            const productDisplay = productCode ? productCode + ' — ' + productName : productName;
            productInfo.value = productDisplay || '';
            
            // Auto-populate UOM if not already set by user
            if (uom && !qtyInput.dataset.userModified) {
                uomInput.value = uom;
            }
            
            // Auto-populate quantity with Job Order quantity as suggestion
            if (qty && !qtyInput.dataset.userModified) {
                qtyInput.value = qty;
            }
            
            // Store PO number (even if not visible, it will be submitted if needed)
            if (poNumberInput && poNumber) {
                poNumberInput.value = poNumber;
            }
        } else {
            productInfo.value = '';
            uomInput.value = '';
            qtyInput.value = 1;
            if (poNumberInput) poNumberInput.value = '';
        }
    }
    
    // Helper function to create hidden input if needed
    function createHiddenInput(name) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        document.querySelector('form').appendChild(input);
        return input;
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

    // Fetch suggested delivery code if not provided
    (async function() {
        const input = document.getElementById('delivery_code');
        if (!input || input.value) return;
        try {
            const resp = await fetch('/api/sequences/next?type=ds');
            if (!resp.ok) return;
            const data = await resp.json();
            if (data.delivery_code) input.value = data.delivery_code;
        } catch (e) {
            console.error('Sequence fetch failed', e);
        }
    })();
});
</script>
@endsection