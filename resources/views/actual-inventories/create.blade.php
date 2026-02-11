{{-- resources/views/actual-inventories/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Create Actual Inventory')
@section('page-icon') <i class="fas fa-plus-circle"></i> @endsection
@section('page-title', 'Create New Inventory Count')
@section('page-description', 'Add a new physical stock count record')

@section('content')
<x-resource-form 
    :action="route('actual-inventories.store')" 
    method="POST" 
    title="New Inventory Count" 
    description="Enter the inventory count details below. Fields marked with * are required." 
    :cancel="route('actual-inventories.index')" 
    submit="Create Count">

    {{-- INFO BANNER --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-2 mb-6">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
            <div class="text-sm text-blue-800 space-y-1">
                <p><strong>Tag Number</strong> will be automatically generated (TAG-YYYY-NNNNNN)</p>
                <p><strong>Date Encoded</strong> will be set to today</p>
                <p>Select a product to auto-fill customer, model, description, etc.</p>
            </div>
        </div>
    </div>

    {{-- SYSTEM INFO --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-5 mb-6">
        <div>
            <label for="tag_number" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                Tag Number
            </label>
            <input type="text" 
                   id="tag_number" 
                   name="tag_number" 
                   value="{{ old('tag_number') }}" 
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg font-mono font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('tag_number') border-red-500 ring-2 ring-red-200 @enderror">
            @error('tag_number') 
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
                {{ now()->format('M d, Y H:i') }}
            </div>
        </div>
    </div>

    {{-- MAIN FORM FIELDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- PRODUCT SEARCHABLE DROPDOWN --}}
        <div>
            <label for="product_id" class="block text-sm font-semibold text-gray-700 mb-2">
                Product <span class="text-red-500">*</span>
            </label>
            <select id="product_id" 
                    name="product_id" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('product_id') border-red-500 ring-2 ring-red-200 @enderror">
                <option value="">Search for a product...</option>
                @foreach($products as $product)
                <option value="{{ $product->id }}" 
                        data-product-code="{{ $product->product_code }}"
                        data-customer-name="{{ $product->customer_name }}"
                        data-model-name="{{ $product->model_name }}"
                        data-description="{{ $product->description }}"
                        data-dimension="{{ $product->dimension }}"
                        data-uom="{{ $product->uom }}">
                    {{ $product->product_code }} - {{ $product->model_name }} ({{ $product->customer_name }})
                </option>
                @endforeach
            </select>
            @error('product_id')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- FG QUANTITY --}}
        <div>
            <label for="fg_quantity" class="block text-sm font-semibold text-gray-700 mb-2">
                Counted Quantity <span class="text-red-500">*</span>
            </label>
            <input type="number" 
                   id="fg_quantity" 
                   name="fg_quantity" 
                   value="{{ old('fg_quantity') }}" 
                   min="0"
                   required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('fg_quantity') border-red-500 ring-2 ring-red-200 @enderror">
            @error('fg_quantity')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- LOCATION --}}
        <div>
            <label for="location" class="block text-sm font-semibold text-gray-700 mb-2">
                Location <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   id="location" 
                   name="location" 
                   value="{{ old('location') }}" 
                   placeholder="e.g., Warehouse A, Shelf 5"
                   required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('location') border-red-500 ring-2 ring-red-200 @enderror">
            @error('location')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- COUNTED BY --}}
        <div>
            <label for="counted_by" class="block text-sm font-semibold text-gray-700 mb-2">
                Counted By <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   id="counted_by" 
                   name="counted_by" 
                   value="{{ old('counted_by') }}" 
                   placeholder="Enter name of person who counted"
                   required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('counted_by') border-red-500 ring-2 ring-red-200 @enderror">
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
            <input type="datetime-local" 
                   id="counted_at" 
                   name="counted_at" 
                   value="{{ old('counted_at') }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('counted_at') border-red-500 ring-2 ring-red-200 @enderror">
            @error('counted_at')
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
            <select id="status" 
                    name="status" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                <option value="Pending" {{ old('status', 'Pending') == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Counted" {{ old('status') == 'Counted' ? 'selected' : '' }}>Counted</option>
                <option value="Verified" {{ old('status') == 'Verified' ? 'selected' : '' }}>Verified</option>
                <option value="Discrepancy" {{ old('status') == 'Discrepancy' ? 'selected' : '' }}>Discrepancy</option>
            </select>
        </div>

        {{-- PRODUCT CODE (AUTO-FILL) --}}
        <div>
            <label for="product_code" class="block text-sm font-semibold text-gray-700 mb-2">
                Product Code
            </label>
            <input type="text" 
                   id="product_code" 
                   name="product_code" 
                   value="{{ old('product_code') }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('product_code') border-red-500 ring-2 ring-red-200 @enderror" 
                   placeholder="(auto-filled)">
        </div>

        {{-- CUSTOMER NAME (AUTO-FILL) --}}
        <div>
            <label for="customer_name" class="block text-sm font-semibold text-gray-700 mb-2">
                Customer Name
            </label>
            <input type="text" 
                   id="customer_name" 
                   name="customer_name" 
                   value="{{ old('customer_name') }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('customer_name') border-red-500 ring-2 ring-red-200 @enderror" 
                   placeholder="(auto-filled)">
        </div>

        {{-- MODEL NAME (AUTO-FILL) --}}
        <div>
            <label for="model_name" class="block text-sm font-semibold text-gray-700 mb-2">
                Model Name
            </label>
            <input type="text" 
                   id="model_name" 
                   name="model_name" 
                   value="{{ old('model_name') }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('model_name') border-red-500 ring-2 ring-red-200 @enderror" 
                   placeholder="(auto-filled)">
        </div>

        {{-- DESCRIPTION (AUTO-FILL) --}}
        <div class="md:col-span-2">
            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                Description
            </label>
            <textarea id="description" 
                      name="description" 
                      rows="2" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('description') border-red-500 ring-2 ring-red-200 @enderror" 
                      placeholder="(auto-filled)">{{ old('description') }}</textarea>
        </div>

        {{-- DIMENSION (AUTO-FILL) --}}
        <div>
            <label for="dimension" class="block text-sm font-semibold text-gray-700 mb-2">
                Dimension
            </label>
            <input type="text" 
                   id="dimension" 
                   name="dimension" 
                   value="{{ old('dimension') }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('dimension') border-red-500 ring-2 ring-red-200 @enderror" 
                   placeholder="(auto-filled)">
        </div>

        {{-- UOM (AUTO-FILL) --}}
        <div>
            <label for="uom" class="block text-sm font-semibold text-gray-700 mb-2">
                Unit of Measure (UOM)
            </label>
            <input type="text" 
                   id="uom" 
                   name="uom" 
                   value="{{ old('uom') }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('uom') border-red-500 ring-2 ring-red-200 @enderror" 
                   placeholder="(auto-filled)">
        </div>

    </div>

    {{-- ADDITIONAL INFORMATION SECTION --}}
    <div class="border-t pt-6 mt-6">
        <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-clipboard-list text-purple-600 mr-2"></i>
            Additional Information
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- REMARKS --}}
            <div class="md:col-span-2">
                <label for="remarks" class="block text-sm font-semibold text-gray-700 mb-2">
                    Remarks / Notes
                </label>
                <textarea id="remarks" 
                          name="remarks" 
                          rows="3" 
                          placeholder="Any notes about the count, discrepancies, or location details..."
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('remarks') border-red-500 ring-2 ring-red-200 @enderror">{{ old('remarks') }}</textarea>
                @error('remarks')
                <p class="mt-2 text-sm text-red-600 flex items-center">
                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                </p>
                @enderror
            </div>

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
    const $productSelect = $('#product_id');
    
    // Initialize Select2 for searchable product dropdown
    $productSelect.select2({
        placeholder: 'Search by product code, model, or customer...',
        allowClear: true,
        width: '100%',
        search: true,
        matcher: function(params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }
            if (typeof data.text === 'undefined') {
                return null;
            }
            const term = params.term.toLowerCase();
            const text = data.text.toLowerCase();
            const code = ($(data.element).data('product-code') || '').toLowerCase();
            const model = ($(data.element).data('model-name') || '').toLowerCase();
            const customer = ($(data.element).data('customer-name') || '').toLowerCase();
            
            if (text.indexOf(term) > -1 || code.indexOf(term) > -1 || model.indexOf(term) > -1 || customer.indexOf(term) > -1) {
                return data;
            }
            return null;
        }
    });

    // Auto-fill fields on product selection (use select2:select event)
    $productSelect.on('select2:select', function(e) {
        const selectedOption = e.params.data;
        const $selectedElement = $(this).find('option[value="' + selectedOption.id + '"]');
        
        if (selectedOption.id) {
            $('#product_code').val($selectedElement.data('product-code') || '');
            $('#customer_name').val($selectedElement.data('customer-name') || '');
            $('#model_name').val($selectedElement.data('model-name') || '');
            $('#description').val($selectedElement.data('description') || '');
            $('#dimension').val($selectedElement.data('dimension') || '');
            $('#uom').val($selectedElement.data('uom') || '');

            // Highlight autofilled fields with yellow background for 2.5 seconds
            ['product_code', 'customer_name', 'model_name', 'description', 'dimension', 'uom'].forEach(fieldId => {
                const $field = $(`#${fieldId}`);
                if ($field.val()) {
                    $field.addClass('bg-yellow-100 border-yellow-400 ring-2 ring-yellow-300');
                    setTimeout(() => {
                        $field.removeClass('bg-yellow-100 border-yellow-400 ring-2 ring-yellow-300');
                    }, 2500);
                }
            });

            // Visual feedback
            $productSelect.next('.select2-container').addClass('ring-2 ring-green-500');
            setTimeout(() => $productSelect.next('.select2-container').removeClass('ring-2 ring-green-500'), 1500);
        }
    });

    // Pre-populate fields if product is already selected
    const selectedOption = $productSelect.find('option:selected');
    if (selectedOption.val()) {
        $('#product_code').val(selectedOption.data('product-code') || '');
        $('#customer_name').val(selectedOption.data('customer-name') || '');
        $('#model_name').val(selectedOption.data('model-name') || '');
        $('#description').val(selectedOption.data('description') || '');
        $('#dimension').val(selectedOption.data('dimension') || '');
        $('#uom').val(selectedOption.data('uom') || '');
    }

    // Fetch suggested tag number
    (async function() {
        const input = document.getElementById('tag_number');
        if (input.value) return;
        
        try {
            const resp = await fetch('/api/sequences/next?type=tag');
            if (!resp.ok) return;
            const data = await resp.json();
            if (data.tag_number) {
                input.value = data.tag_number;
                input.classList.add('bg-yellow-50');
                setTimeout(() => input.classList.remove('bg-yellow-50'), 2000);
            }
        } catch (e) {
            console.error('Failed to fetch tag number:', e);
        }
    })();

    // Auto-set counted_at to now if not set
    const countedAtInput = document.getElementById('counted_at');
    if (!countedAtInput.value) {
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        countedAtInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
    }

    // Auto-submit on Enter in non-textarea inputs
    const formInputs = document.querySelectorAll('input, select');
    formInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !this.matches('textarea')) {
                e.preventDefault();
                this.closest('form').submit();
            }
        });
    });
});
</script>
@endsection