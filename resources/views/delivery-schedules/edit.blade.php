@extends('layouts.app')

@section('title', 'Edit Delivery Schedule')
@section('page-icon') <i class="fas fa-edit"></i> @endsection
@section('page-title', 'Edit Delivery Schedule')
@section('page-description', 'Update delivery details for ' . $deliverySchedule->delivery_code)

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
                <label for="delivery_code" class="block text-sm font-medium text-gray-700 mb-1.5">Delivery Code</label>
                <input type="text" id="delivery_code" name="delivery_code" value="{{ old('delivery_code', $deliverySchedule->delivery_code) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('delivery_code') border-red-500 @enderror">
                @error('delivery_code') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                <div id="delivery_code_suggestion" class="mt-2 text-sm text-gray-500 hidden">
                    Suggested: <span id="delivery_code_suggestion_text" class="font-mono text-gray-700"></span>
                    <button type="button" id="delivery_code_use_suggestion" class="ml-3 px-2 py-1 bg-green-50 text-green-700 rounded text-xs">Use suggestion</button>
                    <button type="button" id="delivery_code_regenerate" class="ml-2 px-2 py-1 bg-gray-50 rounded text-xs">Regenerate</button>
                </div>
            </div>

            <!-- Job Order -->
            <div class="md:col-span-2">
                <label for="job_order_id" class="block text-sm font-medium text-gray-700 mb-1.5">Job Order *</label>
                <select id="job_order_id" name="job_order_id" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('job_order_id') border-red-500 @enderror">
                    @foreach($jobOrders as $jo)
                    <option value="{{ $jo->id }}" 
                            data-po-number="{{ $jo->po_number }}"
                            data-product-code="{{ $jo->product->product_code }}"
                            data-product-name="{{ $jo->product->model_name ?? $jo->product->product_code }}"
                            data-uom="{{ $jo->product->uom }}"
                            data-qty="{{ $jo->qty_ordered }}"
                            {{ old('job_order_id', $deliverySchedule->job_order_id) == $jo->id ? 'selected' : '' }}>
                        {{ $jo->jo_number }} — {{ $jo->product->model_name ?? $jo->product->product_code ?? '—' }} (PO: {{ $jo->po_number ?? 'N/A' }})
                    </option>
                    @endforeach
                </select>
                @error('job_order_id') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
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
                <label for="delivery_date" class="block text-sm font-medium text-gray-700 mb-1.5">Delivery Date *</label>
                <input type="date" id="delivery_date" name="delivery_date" value="{{ old('delivery_date', $deliverySchedule->delivery_date?->format('Y-m-d')) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('delivery_date') border-red-500 @enderror">
                @error('delivery_date') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Quantity -->
            <div>
                <label for="qty_scheduled" class="block text-sm font-medium text-gray-700 mb-1.5">Quantity *</label>
                <input type="number" id="qty_scheduled" name="qty_scheduled" value="{{ old('qty_scheduled', $deliverySchedule->qty_scheduled) }}" min="0" step="1" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('qty_scheduled') border-red-500 @enderror">
                @error('qty_scheduled') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
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
    const joSelect = document.getElementById('job_order_id');
    const productInfo = document.getElementById('product_info');
    const uomInput = document.getElementById('uom');
    const qtyInput = document.getElementById('qty_scheduled');
    
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

    // Suggest delivery code (show suggestion, do not overwrite)
    (async function() {
        const input = document.getElementById('delivery_code');
        const suggestionContainer = document.getElementById('delivery_code_suggestion');
        const suggestionText = document.getElementById('delivery_code_suggestion_text');
        const useBtn = document.getElementById('delivery_code_use_suggestion');
        const regenBtn = document.getElementById('delivery_code_regenerate');

        async function fetchSuggestion() {
            try {
                const resp = await fetch('/api/sequences/next?type=ds');
                if (!resp.ok) return;
                const data = await resp.json();
                if (data.delivery_code) {
                    suggestionText.textContent = data.delivery_code;
                    suggestionContainer.classList.remove('hidden');
                    useBtn.disabled = false;
                }
            } catch (e) { console.error(e); }
        }

        useBtn.addEventListener('click', function() {
            const txt = suggestionText.textContent;
            if (txt) {
                input.value = txt;
                suggestionContainer.classList.add('hidden');
            }
        });

        regenBtn.addEventListener('click', fetchSuggestion);

        fetchSuggestion();
    })();
});
</script>
@endsection