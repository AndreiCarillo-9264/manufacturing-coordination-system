{{-- resources/views/finished-goods/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Create Finished Good')
@section('page-icon') <i class="fas fa-plus-circle"></i> @endsection
@section('page-title', 'Create New Finished Good')
@section('page-description', 'Add a new finished good record (typically auto-created)')

@section('content')
<x-resource-form 
    :action="route('finished-goods.store')" 
    method="POST" 
    title="New Finished Good" 
    description="Enter the finished good details below. Fields marked with * are required." 
    :cancel="route('finished-goods.index')" 
    submit="Create Finished Good">

    {{-- INFO BANNER --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-2 mb-6">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
            <div class="text-sm text-blue-800 space-y-1">
                <p><strong>FG Code</strong> will be automatically generated (FG-YYYY-NNNNNN)</p>
                <p><strong>Date Encoded</strong> will be set to today</p>
                <p>Select a product to auto-fill customer, model, description, etc.</p>
            </div>
        </div>
    </div>

    {{-- SYSTEM INFO --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-5 mb-6">
        <div>
            <label for="fg_code" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                FG Code
            </label>
            <input type="text" 
                   id="fg_code" 
                   name="fg_code" 
                   value="{{ old('fg_code') }}" 
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg font-mono font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('fg_code') border-red-500 ring-2 ring-red-200 @enderror">
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
                        data-uom="{{ $product->uom }}"
                        data-currency="{{ $product->currency }}"
                        data-selling-price="{{ $product->selling_price }}">
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

        {{-- CURRENT QTY --}}
        <div>
            <label for="current_qty" class="block text-sm font-semibold text-gray-700 mb-2">
                Current Quantity <span class="text-red-500">*</span>
            </label>
            <input type="number" 
                   id="current_qty" 
                   name="current_qty" 
                   value="{{ old('current_qty') }}" 
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
                   value="{{ old('end_amount') }}" 
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
                   value="{{ old('last_in_date') }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('last_in_date') border-red-500 ring-2 ring-red-200 @enderror">
            @error('last_in_date')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- STATUS --}}
        <div>
            <label for="stock_status" class="block text-sm font-semibold text-gray-700 mb-2">
                Stock Status
            </label>
            <select id="stock_status" 
                    name="stock_status" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                <option value="In Stock" {{ old('stock_status', 'In Stock') == 'In Stock' ? 'selected' : '' }}>In Stock</option>
                <option value="Low Stock" {{ old('stock_status') == 'Low Stock' ? 'selected' : '' }}>Low Stock</option>
                <option value="Out of Stock" {{ old('stock_status') == 'Out of Stock' ? 'selected' : '' }}>Out of Stock</option>
                <option value="Old Stock" {{ old('stock_status') == 'Old Stock' ? 'selected' : '' }}>Old Stock</option>
            </select>
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
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg @error('customer_name') border-red-500 @enderror" 
                     readonly>
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
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg @error('model_name') border-red-500 @enderror" 
                     readonly>
        </div>

        {{-- DESCRIPTION (AUTO-FILL) --}}
        <div class="md:col-span-2">
            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                Description
            </label>
            <textarea id="description" 
                      name="description" 
                      rows="2" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg @error('description') border-red-500 @enderror" 
                      readonly>{{ old('description') }}</textarea>
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
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg @error('dimension') border-red-500 @enderror" 
                     readonly>
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
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow bg-gray-50 @error('uom') border-red-500 ring-2 ring-red-200 @enderror" 
                   readonly>
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
                          placeholder="Any special notes for this finished good record..."
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const $productSelect = $('#product_id');
    
    // Initialize Select2 for searchable product dropdown (search always enabled)
    $productSelect.select2({
        placeholder: 'Search by product code, model, or customer...',
        allowClear: true,
        width: '100%',
        minimumResultsForSearch: 0,
        matcher: function(params, data) {
            if ($.trim(params.term) === '') return data;
            if (typeof data.text === 'undefined') return null;
            const term = params.term.toLowerCase();
            const text = data.text.toLowerCase();
            const code = (data.element && $(data.element).data('product-code') || '').toString().toLowerCase();
            const model = (data.element && $(data.element).data('model-name') || '').toString().toLowerCase();
            const customer = (data.element && $(data.element).data('customer-name') || '').toString().toLowerCase();
            if (text.indexOf(term) > -1 || code.indexOf(term) > -1 || model.indexOf(term) > -1 || customer.indexOf(term) > -1) {
                return data;
            }
            return null;
        }
    });

    function fillProductFields($select) {
        const $opt = $select.find('option:selected');
        const val = $opt.val();
        if (!val) {
            $('#customer_name, #model_name, #description, #dimension, #uom, #selling_price, #currency').val('');
            return;
        }

        const custName = $opt.attr('data-customer-name') || $opt.data('customer-name') || '';
        const modelName = $opt.attr('data-model-name') || $opt.data('model-name') || '';
        const desc = $opt.attr('data-description') || $opt.data('description') || '';
        const dim = $opt.attr('data-dimension') || $opt.data('dimension') || '';
        const uomVal = $opt.attr('data-uom') || $opt.data('uom') || '';
        const price = $opt.attr('data-selling-price') || $opt.data('selling-price') || '0.00';
        const curr = $opt.attr('data-currency') || $opt.data('currency') || 'PHP';

        $('#customer_name').val(custName);
        $('#model_name').val(modelName);
        $('#description').val(desc);
        $('#dimension').val(dim);
        $('#uom').val(uomVal);
        $('#selling_price').val(price);
        $('#currency').val(curr);

        ['customer_name', 'model_name', 'description', 'dimension', 'uom', 'selling_price', 'currency'].forEach(fieldId => {
            const $field = $(`#${fieldId}`);
            if ($field.val()) {
                $field.addClass('bg-yellow-100 border-yellow-400 ring-2 ring-yellow-300');
                setTimeout(() => $field.removeClass('bg-yellow-100 border-yellow-400 ring-2 ring-yellow-300'), 2500);
            }
        });
    }

    $productSelect.on('change select2:select', function() { fillProductFields($(this)); });
    $productSelect.on('select2:clear', function() { $('#customer_name, #model_name, #description, #dimension, #uom, #selling_price, #currency').val(''); });

    // Pre-populate fields if product is already selected
    fillProductFields($productSelect);

    // Fetch suggested FG code
    (async function() {
        const input = document.getElementById('fg_code');
        if (input.value) return;
        
        try {
            const resp = await fetch('/api/sequences/next?type=fg');
            if (!resp.ok) return;
            const data = await resp.json();
            if (data.fg_code) {
                input.value = data.fg_code;
                input.classList.add('bg-yellow-50');
                setTimeout(() => input.classList.remove('bg-yellow-50'), 2000);
            }
        } catch (e) {
            console.error('Failed to fetch FG code:', e);
        }
    })();

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
@endpush