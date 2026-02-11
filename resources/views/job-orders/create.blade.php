@extends('layouts.app')

@section('title', 'Create Job Order')
@section('page-icon') <i class="fas fa-plus-circle"></i> @endsection
@section('page-title', 'Create New Job Order')
@section('page-description', 'Add a new production job order')

@section('content')
<x-resource-form 
    :action="route('job-orders.store')" 
    method="POST" 
    title="New Job Order" 
    description="Enter the job order details below. Fields marked with * are required." 
    :cancel="route('job-orders.index')" 
    submit="Create Job Order">

    {{-- INFO BANNER --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-2 mb-6">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
            <div class="text-sm text-blue-800 space-y-1">
                <p><strong>JO Number</strong> will be auto-generated (JO-YYYY-NNNN) — you can override it</p>
                <p><strong>PO Number</strong> will be auto-generated if not provided</p>
                <p>Select a product to auto-fill customer, model, description, etc.</p>
            </div>
        </div>
    </div>

    {{-- SYSTEM INFO --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-5 mb-6">
        <div>
            <label for="jo_number" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                JO Number
            </label>
            <input type="text" 
                   id="jo_number" 
                   name="jo_number" 
                   value="{{ old('jo_number') }}" 
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
                   value="{{ old('po_number') }}" 
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
                {{ now()->format('M d, Y H:i') }}
            </div>
        </div>
    </div>

    {{-- MAIN FORM FIELDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- PRODUCT SEARCHABLE DROPDOWN --}}
        <div class="md:col-span-2">
            <label for="product_id" class="block text-sm font-semibold text-gray-700 mb-2">
                Product <span class="text-red-500">*</span>
            </label>
            <select id="product_id" 
                    name="product_id" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('product_id') border-red-500 ring-2 ring-red-200 @enderror">
                <option value="">Search or select product...</option>
                @foreach($products as $product)
                <option value="{{ $product->id }}" 
                        data-code="{{ $product->product_code }}"
                        data-customer="{{ $product->customer_name }}"
                        data-model="{{ $product->model_name }}"
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

        {{-- DATE NEEDED --}}
        <div>
            <label for="date_needed" class="block text-sm font-semibold text-gray-700 mb-2">
                Date Needed <span class="text-red-500">*</span>
            </label>
            <input type="date" 
                   id="date_needed" 
                   name="date_needed" 
                   value="{{ old('date_needed') }}" 
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
                Status
            </label>
            <select id="jo_status" 
                    name="jo_status" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow">
                <option value="Pending" {{ old('jo_status', 'Pending') == 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Approved" {{ old('jo_status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                <option value="JO Full" {{ old('jo_status') == 'JO Full' ? 'selected' : '' }}>JO Full</option>
                <option value="Cancelled" {{ old('jo_status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </div>

        {{-- AUTO-FILLED FIELDS --}}
        <div>
            <label for="customer_name" class="block text-sm font-semibold text-gray-700 mb-2">
                Customer Name
            </label>
            <input type="text" 
                   id="customer_name" 
                   name="customer_name" 
                   value="{{ old('customer_name') }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg @error('customer_name') border-red-500 @enderror" 
                   placeholder="(auto-filled)">
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
                   placeholder="(auto-filled)">
        </div>

        <div class="md:col-span-2">
            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                Description
            </label>
            <textarea id="description" 
                      name="description" 
                      rows="2" 
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg @error('description') border-red-500 @enderror" 
                      placeholder="(auto-filled)">{{ old('description') }}</textarea>
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
                   placeholder="(auto-filled)">
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
                   placeholder="(auto-filled)">
        </div>

        {{-- REMARKS --}}
        <div class="md:col-span-2">
            <label for="remarks" class="block text-sm font-semibold text-gray-700 mb-2">
                Remarks / Special Instructions
            </label>
            <textarea id="remarks" 
                      name="remarks" 
                      rows="3" 
                      placeholder="Any special notes for production, scheduling, or quality control..."
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

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const $productSelect = $('#product_id');
    
    // Initialize Select2 with search enabled
    $productSelect.select2({
        placeholder: "Search product by code, model or customer...",
        allowClear: true,
        width: '100%',
        search: true,
        matcher: function(params, data) {
            // Allow searching by product code, model name, and customer
            if ($.trim(params.term) === '') {
                return data;
            }
            
            const term = params.term.toLowerCase();
            const text = (data.text || '').toLowerCase();
            
            if (text.indexOf(term) > -1) {
                return data;
            }
            
            return null;
        }
    });

    // When product is selected → auto-fill fields (use select2:select event)
    $productSelect.on('select2:select', function(e) {
        const selectedOption = e.params.data;
        const $selectedElement = $(this).find('option[value="' + selectedOption.id + '"]');
        
        if (selectedOption.id) {
            $('#customer_name').val($selectedElement.data('customer') || '');
            $('#model_name').val($selectedElement.data('model') || '');
            $('#description').val($selectedElement.data('description') || '');
            $('#dimension').val($selectedElement.data('dimension') || '');
            $('#uom').val($selectedElement.data('uom') || '');

            // Highlight autofilled fields with yellow background for 2.5 seconds
            ['customer_name', 'model_name', 'description', 'dimension', 'uom'].forEach(fieldId => {
                const $field = $(`#${fieldId}`);
                if ($field.val()) {
                    $field.addClass('bg-yellow-100 border-yellow-400 ring-2 ring-yellow-300');
                    setTimeout(() => {
                        $field.removeClass('bg-yellow-100 border-yellow-400 ring-2 ring-yellow-300');
                    }, 2500);
                }
            });

            // Visual feedback on dropdown
            $productSelect.next('.select2-container').addClass('ring-2 ring-green-500');
            setTimeout(() => $productSelect.next('.select2-container').removeClass('ring-2 ring-green-500'), 1500);
        }
    });

    // Clear fields when product is cleared
    $productSelect.on('select2:clear', function() {
        $('#customer_name, #model_name, #description, #dimension, #uom').val('');
    });

    // Pre-fill if old input exists (after validation fail)
    @if(old('product_id'))
        $productSelect.val('{{ old('product_id') }}').trigger('change');
        // Manually trigger autofill
        const $oldOption = $productSelect.find('option[value="{{ old('product_id') }}"]');
        if ($oldOption.length) {
            $('#customer_name').val($oldOption.data('customer') || '');
            $('#model_name').val($oldOption.data('model') || '');
            $('#description').val($oldOption.data('description') || '');
            $('#dimension').val($oldOption.data('dimension') || '');
            $('#uom').val($oldOption.data('uom') || '');
        }
    @endif

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
@endsection