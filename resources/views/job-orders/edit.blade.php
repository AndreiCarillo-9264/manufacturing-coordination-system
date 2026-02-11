{{-- resources/views/job-orders/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Job Order')
@section('page-icon') <i class="fas fa-edit"></i> @endsection
@section('page-title', 'Edit Job Order: ' . $jobOrder->jo_number)
@section('page-description', 'Update job order information')

@section('content')
<x-resource-form 
    :action="route('job-orders.update', $jobOrder)" 
    method="PUT" 
    title="Edit Job Order" 
    description="Update the job order details below. Fields marked with * are required." 
    :cancel="route('job-orders.index')" 
    submit="Update Job Order">
    
    <x-slot name="headerRight">
        <div class="text-sm text-gray-500 font-mono bg-gray-100 px-3 py-1 rounded">
            {{ $jobOrder->jo_number }}
        </div>
    </x-slot>

    {{-- SYSTEM INFO --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-5 mb-6">
        <div>
            <label for="jo_number" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                JO Number
            </label>
            <input type="text" 
                   id="jo_number" 
                   name="jo_number" 
                   value="{{ old('jo_number', $jobOrder->jo_number) }}"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg font-mono font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('jo_number') border-red-500 ring-2 ring-red-200 @enderror">
            @error('jo_number') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>
        <div>
            <label for="po_number" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                PO Number
            </label>
            <input type="text" 
                   id="po_number" 
                   name="po_number" 
                   value="{{ old('po_number', $jobOrder->po_number) }}" 
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg font-mono font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('po_number') border-red-500 ring-2 ring-red-200 @enderror">
            @error('po_number') 
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
                {{ $jobOrder->date_encoded?->format('M d, Y H:i') ?? '—' }}
            </div>
        </div>
        @if($jobOrder->date_approved)
        <div>
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                Approved On
            </label>
            <div class="px-4 py-2.5 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800 font-medium">
                {{ $jobOrder->date_approved?->format('M d, Y H:i') ?? '—' }} by {{ $jobOrder->approvedBy?->name ?? 'System' }}
            </div>
        </div>
        @endif
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
                        data-code="{{ $product->product_code }}"
                        data-customer="{{ $product->customer_name }}"
                        data-model="{{ $product->model_name }}"
                        data-description="{{ $product->description }}"
                        data-dimension="{{ $product->dimension }}"
                        data-uom="{{ $product->uom }}"
                        {{ old('product_id', $jobOrder->product_id) == $product->id ? 'selected' : '' }}>
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

        {{-- QUANTITY --}}
        <div>
            <label for="quantity" class="block text-sm font-semibold text-gray-700 mb-2">
                Quantity <span class="text-red-500">*</span>
            </label>
            <input type="number" 
                   id="quantity" 
                   name="quantity" 
                   value="{{ old('quantity', $jobOrder->quantity) }}" 
                   min="1"
                   required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('quantity') border-red-500 ring-2 ring-red-200 @enderror">
            @error('quantity')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- DATE NEEDED --}}
        <div>
            <label for="date_needed" class="block text-sm font-semibold text-gray-700 mb-2">
                Date Needed <span class="text-red-500">*</span>
            </label>
            <input type="date" 
                   id="date_needed" 
                   name="date_needed" 
                   value="{{ old('date_needed', $jobOrder->date_needed?->format('Y-m-d')) }}" 
                   required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('date_needed') border-red-500 ring-2 ring-red-200 @enderror">
            @error('date_needed')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- STATUS --}}
        <div>
            <label for="jo_status" class="block text-sm font-semibold text-gray-700 mb-2">
                Status <span class="text-red-500">*</span>
            </label>
            <select id="jo_status" 
                    name="jo_status" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('jo_status') border-red-500 ring-2 ring-red-200 @enderror">
                <option value="Pending" {{ old('jo_status', $jobOrder->jo_status) == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Partial" {{ old('jo_status', $jobOrder->jo_status) == 'Partial' ? 'selected' : '' }}>Partial</option>
                <option value="JO Full" {{ old('jo_status', $jobOrder->jo_status) == 'JO Full' ? 'selected' : '' }}>JO Full</option>
                <option value="Cancelled" {{ old('jo_status', $jobOrder->jo_status) == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            @error('jo_status')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- CUSTOMER NAME (AUTO-FILL) --}}
        <div>
            <label for="customer_name" class="block text-sm font-semibold text-gray-700 mb-2">
                Customer Name
            </label>
            <input type="text" 
                   id="customer_name" 
                   name="customer_name" 
                   value="{{ old('customer_name', $jobOrder->customer_name) }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow bg-gray-50 @error('customer_name') border-red-500 ring-2 ring-red-200 @enderror" 
                   readonly>
            @error('customer_name')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- MODEL NAME (AUTO-FILL) --}}
        <div>
            <label for="model_name" class="block text-sm font-semibold text-gray-700 mb-2">
                Model Name
            </label>
            <input type="text" 
                   id="model_name" 
                   name="model_name" 
                   value="{{ old('model_name', $jobOrder->model_name) }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow bg-gray-50 @error('model_name') border-red-500 ring-2 ring-red-200 @enderror" 
                   readonly>
            @error('model_name')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- DESCRIPTION (AUTO-FILL) --}}
        <div class="md:col-span-2">
            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                Description
            </label>
            <textarea id="description" 
                      name="description" 
                      rows="2" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow bg-gray-50 @error('description') border-red-500 ring-2 ring-red-200 @enderror" 
                      readonly>{{ old('description', $jobOrder->description) }}</textarea>
            @error('description')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- DIMENSION (AUTO-FILL) --}}
        <div>
            <label for="dimension" class="block text-sm font-semibold text-gray-700 mb-2">
                Dimension
            </label>
            <input type="text" 
                   id="dimension" 
                   name="dimension" 
                   value="{{ old('dimension', $jobOrder->dimension) }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow bg-gray-50 @error('dimension') border-red-500 ring-2 ring-red-200 @enderror" 
                   readonly>
            @error('dimension')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- UOM (AUTO-FILL) --}}
        <div>
            <label for="uom" class="block text-sm font-semibold text-gray-700 mb-2">
                Unit of Measure (UOM)
            </label>
            <input type="text" 
                   id="uom" 
                   name="uom" 
                   value="{{ old('uom', $jobOrder->uom) }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow bg-gray-50 @error('uom') border-red-500 ring-2 ring-red-200 @enderror" 
                   readonly>
            @error('uom')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- JO BALANCE (READONLY) --}}
        <div>
            <label for="jo_balance" class="block text-sm font-semibold text-gray-700 mb-2">
                Current Balance
            </label>
            <input type="number" 
                   id="jo_balance" 
                   value="{{ $jobOrder->jo_balance }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 font-medium" 
                   readonly>
        </div>

        {{-- PPQC TRANSFER (READONLY) --}}
        <div>
            <label for="ppqc_transfer" class="block text-sm font-semibold text-gray-700 mb-2">
                PPQC Transfer
            </label>
            <input type="number" 
                   id="ppqc_transfer" 
                   value="{{ $jobOrder->ppqc_transfer ?? 0 }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 font-medium" 
                   readonly>
        </div>

        {{-- DS QUANTITY (READONLY) --}}
        <div>
            <label for="ds_quantity" class="block text-sm font-semibold text-gray-700 mb-2">
                DS Quantity
            </label>
            <input type="number" 
                   id="ds_quantity" 
                   value="{{ $jobOrder->ds_quantity ?? 0 }}" 
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
                Remarks / Special Instructions
            </label>
            <textarea id="remarks" 
                      name="remarks" 
                      rows="3" 
                      placeholder="Any special notes for production, scheduling, or quality control..."
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('remarks') border-red-500 ring-2 ring-red-200 @enderror">{{ old('remarks', $jobOrder->remarks) }}</textarea>
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
    // Initialize Select2 for searchable product dropdown
    const productSelect = $('#product_id').select2({
        placeholder: 'Search by product code, model, or customer...',
        allowClear: true,
        width: '100%',
        matcher: function(params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }
            if (typeof data.text === 'undefined') {
                return null;
            }
            const term = params.term.toLowerCase();
            const text = data.text.toLowerCase();
            if (text.indexOf(term) > -1 || 
                $(data.element).data('code').toLowerCase().indexOf(term) > -1 ||
                $(data.element).data('model').toLowerCase().indexOf(term) > -1 ||
                $(data.element).data('customer').toLowerCase().indexOf(term) > -1) {
                return data;
            }
            return null;
        }
    });

    // Auto-fill fields on product selection (for changes)
    productSelect.on('select2:select', function(e) {
        const element = $(e.currentTarget).find('option:selected');
        
        $('#product_code').val(element.data('code') || '');
        $('#customer_name').val(element.data('customer') || '');
        $('#model_name').val(element.data('model') || '');
        $('#description').val(element.data('description') || '');
        $('#dimension').val(element.data('dimension') || '');
        $('#uom').val(element.data('uom') || '');

        // Visual feedback
        productSelect.next('.select2-container').addClass('bg-green-50');
        setTimeout(() => productSelect.next('.select2-container').removeClass('bg-green-50'), 1500);
    });

    // Pre-populate fields if product is already selected
    const selectedOption = $('#product_id option:selected');
    if (selectedOption.val()) {
        $('#customer_name').val(selectedOption.data('customer') || '');
        $('#model_name').val(selectedOption.data('model') || '');
        $('#description').val(selectedOption.data('description') || '');
        $('#dimension').val(selectedOption.data('dimension') || '');
        $('#uom').val(selectedOption.data('uom') || '');
    }

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