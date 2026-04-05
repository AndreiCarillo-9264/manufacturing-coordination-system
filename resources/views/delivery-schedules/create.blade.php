@extends('layouts.app')

@section('title', 'Create Delivery Schedule')
@section('page-icon') <i class="fas fa-plus-circle"></i> @endsection
@section('page-title', 'Create New Delivery Schedule')
@section('page-description', 'Add a new delivery schedule for production orders')

@section('content')
<x-resource-form 
    :action="route('delivery-schedules.store')" 
    method="POST" 
    title="New Delivery Schedule" 
    description="Enter the delivery schedule details below. Fields marked with * are required." 
    :cancel="route('delivery-schedules.index')" 
    submit="Create Delivery Schedule">

    {{-- INFO BANNER --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-2 mb-6">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
            <div class="text-sm text-blue-800 space-y-1">
                <p><strong>DS Code</strong> will be auto-generated</p>
                <p>Select a Job Order to auto-fill product, customer, model, suggested quantity, etc.</p>
            </div>
        </div>
    </div>

    {{-- HIDDEN FIELDS --}}
    <input type="hidden" id="product_id" name="product_id" value="">
    <input type="hidden" id="date_encoded" name="date_encoded" value="{{ now()->toDateString() }}">

    {{-- SYSTEM INFO --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-5 mb-6">
        <div>
            <label for="ds_code" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                DS Code
            </label>
            <input type="text" 
                   id="ds_code" 
                   name="ds_code" 
                   value="{{ old('ds_code') }}" 
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg font-mono font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('ds_code') border-red-500 ring-2 ring-red-200 @enderror">
            @error('ds_code') 
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

        {{-- JOB ORDER SEARCHABLE DROPDOWN --}}
        <div class="md:col-span-2">
            <label for="job_order_id" class="block text-sm font-semibold text-gray-700 mb-2">
                Job Order <span class="text-red-500">*</span>
            </label>
            <select id="job_order_id" 
                    name="job_order_id" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('job_order_id') border-red-500 ring-2 ring-red-200 @enderror">
                <option value="">Search or select job order...</option>
                @foreach($jobOrders as $jo)
                <option value="{{ $jo->id }}" 
                        data-jo-number="{{ $jo->jo_number }}"
                        data-po-number="{{ $jo->po_number }}"
                        data-product-id="{{ $jo->product_id }}"
                        data-product-code="{{ $jo->product_code }}"
                        data-customer-name="{{ $jo->customer_name }}"
                        data-model-name="{{ $jo->model_name }}"
                        data-description="{{ $jo->description }}"
                        data-dimension="{{ $jo->dimension }}"
                        data-uom="{{ $jo->uom }}">
                    {{ $jo->jo_number }} - {{ $jo->product_code }} ({{ $jo->customer_name }})
                </option>
                @endforeach
            </select>
            @error('job_order_id')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- QUANTITY --}}
        <div>
            <label for="quantity" class="block text-sm font-semibold text-gray-700 mb-2">
                Quantity <span class="text-red-500">*</span>
            </label>
            <input type="number" 
                   id="quantity" 
                   name="quantity" 
                   value="{{ old('quantity') }}" 
                   min="1"
                   required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('quantity') border-red-500 ring-2 ring-red-200 @enderror">
            @error('quantity')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- DELIVERY DATE --}}
        <div>
            <label for="delivery_date" class="block text-sm font-semibold text-gray-700 mb-2">
                Delivery Date <span class="text-red-500">*</span>
            </label>
            <input type="date" 
                   id="delivery_date" 
                   name="delivery_date" 
                   value="{{ old('delivery_date') }}" 
                   required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('delivery_date') border-red-500 ring-2 ring-red-200 @enderror">
            @error('delivery_date')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- DELIVERY TIME --}}
        <div>
            <label for="delivery_time" class="block text-sm font-semibold text-gray-700 mb-2">
                Delivery Time
            </label>
            <input type="time"
                   id="delivery_time"
                   name="delivery_time"
                   value="{{ old('delivery_time') }}"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('delivery_time') border-red-500 ring-2 ring-red-200 @enderror">
            @error('delivery_time')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- STATUS --}}
        <div>
            <label for="ds_status" class="block text-sm font-semibold text-gray-700 mb-2">
                Status
            </label>
            <select id="ds_status" 
                    name="ds_status" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                <option value="ON SCHEDULE" {{ old('ds_status', 'ON SCHEDULE') == 'ON SCHEDULE' ? 'selected' : '' }}>On Schedule</option>
                <option value="BACKLOG" {{ old('ds_status') == 'BACKLOG' ? 'selected' : '' }}>Backlog</option>
                <option value="DELIVERED" {{ old('ds_status') == 'DELIVERED' ? 'selected' : '' }}>Delivered</option>
                <option value="CANCELLED" {{ old('ds_status') == 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        {{-- AUTO-FILLED FIELDS --}}
        <div>
            <label for="jo_number" class="block text-sm font-semibold text-gray-700 mb-2">
                JO Number
            </label>
                 <input type="text" 
                     id="jo_number" 
                     name="jo_number" 
                     value="{{ old('jo_number') }}" 
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg @error('jo_number') border-red-500 @enderror" 
                     readonly>
        </div>

        <div>
            <label for="po_number" class="block text-sm font-semibold text-gray-700 mb-2">
                PO Number
            </label>
                 <input type="text" 
                     id="po_number" 
                     name="po_number" 
                     value="{{ old('po_number') }}" 
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg @error('po_number') border-red-500 @enderror" 
                     readonly>
        </div>

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

        <div>
            <label for="uom" class="block text-sm font-semibold text-gray-700 mb-2">
                UOM
            </label>
                 <input type="text" 
                     id="uom" 
                     name="uom" 
                     value="{{ old('uom') }}" 
                     class="w-full px-4 py-3 border border-gray-300 rounded-lg @error('uom') border-red-500 @enderror" 
                     readonly>
        </div>

        {{-- REMARKS --}}
        <div class="md:col-span-2">
            <label for="remarks" class="block text-sm font-semibold text-gray-700 mb-2">
                Remarks / Delivery Instructions
            </label>
            <textarea id="remarks" 
                      name="remarks" 
                      rows="3" 
                      placeholder="Special delivery instructions, notes for logistics, or any other remarks..."
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('remarks') border-red-500 ring-2 ring-red-200 @enderror">{{ old('remarks') }}</textarea>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const $jobOrderSelect = $('#job_order_id');
    
    // Initialize Select2 with search always enabled and robust selection handling
    $jobOrderSelect.select2({
        placeholder: "Search job order by number, product or customer...",
        allowClear: true,
        width: '100%',
        minimumResultsForSearch: 0,
        matcher: function(params, data) {
            if ($.trim(params.term) === '') return data;
            if (typeof data.text === 'undefined') return null;
            const term = params.term.toLowerCase();
            const text = data.text.toLowerCase();
            const joNum = (data.element && $(data.element).data('jo-number') || '').toString().toLowerCase();
            const prodCode = (data.element && $(data.element).data('product-code') || '').toString().toLowerCase();
            const customer = (data.element && $(data.element).data('customer-name') || '').toString().toLowerCase();
            if (text.indexOf(term) > -1 || joNum.indexOf(term) > -1 || prodCode.indexOf(term) > -1 || customer.indexOf(term) > -1) {
                return data;
            }
            return null;
        }
    });

    // Helper to fill fields from a selected option (uses jQuery to be compatible with Select2)
    function fillJobOrderFields($select) {
        const $opt = $select.find('option:selected');
        const val = $opt.val();
        if (!val) {
            $('#product_id, #jo_number, #po_number, #customer_name, #model_name, #description, #dimension, #uom').val('');
            return;
        }

        const prodId = $opt.attr('data-product-id') || $opt.data('product-id') || $opt.data('productId') || '';
        const joNum = $opt.attr('data-jo-number') || $opt.data('jo-number') || '';
        const poNum = $opt.attr('data-po-number') || $opt.data('po-number') || '';
        const custName = $opt.attr('data-customer-name') || $opt.data('customer-name') || '';
        const modelName = $opt.attr('data-model-name') || $opt.data('model-name') || '';
        const desc = $opt.attr('data-description') || $opt.data('description') || '';
        const dim = $opt.attr('data-dimension') || $opt.data('dimension') || '';
        const uomVal = $opt.attr('data-uom') || $opt.data('uom') || '';

        $('#product_id').val(prodId);
        $('#jo_number').val(joNum);
        $('#po_number').val(poNum);
        $('#customer_name').val(custName);
        $('#model_name').val(modelName);
        $('#description').val(desc);
        $('#dimension').val(dim);
        $('#uom').val(uomVal);

        ['jo_number', 'po_number', 'customer_name', 'model_name', 'description', 'dimension', 'uom'].forEach(fieldId => {
            const $field = $(`#${fieldId}`);
            if ($field.val()) {
                $field.addClass('bg-yellow-100 border-yellow-400 ring-2 ring-yellow-300');
                setTimeout(() => $field.removeClass('bg-yellow-100 border-yellow-400 ring-2 ring-yellow-300'), 2500);
            }
        });
    }

    // Bind to Select2 and native change to ensure compatibility
    $jobOrderSelect.on('change select2:select', function() {
        fillJobOrderFields($(this));
    });

    // Clear handler when user clears selection
    $jobOrderSelect.on('select2:clear', function() {
        $('#product_id, #jo_number, #po_number, #customer_name, #model_name, #description, #dimension, #uom').val('');
    });

    // Pre-populate if job order is already selected (after validation fail)
    fillJobOrderFields($jobOrderSelect);

    // Auto-submit on Enter
    document.querySelectorAll('input, select').forEach(el => {
        el.addEventListener('keypress', e => {
            if (e.key === 'Enter' && el.tagName !== 'TEXTAREA') {
                e.preventDefault();
                el.closest('form').querySelector('button[type="submit"]').click();
            }
        });
    });
});
</script>
@endpush