{{-- resources/views/endorse-to-logistics/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Create Endorse To Logistics')
@section('page-icon') <i class="fas fa-plus-circle"></i> @endsection
@section('page-title', 'Create New ETL')
@section('page-description', 'Add a new endorsement to logistics record')

@section('content')
<x-resource-form 
    :action="route('endorse-to-logistics.store')" 
    method="POST" 
    title="New ETL Record" 
    description="Enter the endorsement details below. Fields marked with * are required." 
    :cancel="route('endorse-to-logistics.index')" 
    submit="Create ETL">

    {{-- INFO BANNER --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-2 mb-6">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
            <div class="text-sm text-blue-800 space-y-1">
                <p><strong>ETL Code</strong> will be automatically generated (ETL-YYYY-NNNN)</p>
                <p><strong>Date Encoded</strong> will be set to today</p>
                <p>Select a Job Order to auto-fill product details and suggested quantity</p>
            </div>
        </div>
    </div>

    {{-- SYSTEM INFO --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-5 mb-6">
        <div>
            <label for="etl_code" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                ETL Code
            </label>
            <input type="text" 
                   id="etl_code" 
                   name="etl_code" 
                   value="{{ old('etl_code') }}" 
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg font-mono font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('etl_code') border-red-500 ring-2 ring-red-200 @enderror">
            @error('etl_code') 
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
        <div>
            <label for="job_order_id" class="block text-sm font-semibold text-gray-700 mb-2">
                Job Order <span class="text-red-500">*</span>
            </label>
            <select id="job_order_id" 
                    name="job_order_id" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('job_order_id') border-red-500 ring-2 ring-red-200 @enderror">
                <option value="">Search for a job order...</option>
                @foreach($jobOrders as $jo)
                <option value="{{ $jo->id }}" 
                        data-product-code="{{ $jo->product_code }}"
                        data-customer-name="{{ $jo->customer_name }}"
                        data-model-name="{{ $jo->model_name }}"
                        data-description="{{ $jo->description }}"
                        data-uom="{{ $jo->uom }}"
                        data-jo-balance="{{ $jo->jo_balance }}">
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

        {{-- DATE --}}
        <div>
            <label for="date" class="block text-sm font-semibold text-gray-700 mb-2">
                Date <span class="text-red-500">*</span>
            </label>
            <input type="date" 
                   id="date" 
                   name="date" 
                   value="{{ old('date') }}" 
                   required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('date') border-red-500 ring-2 ring-red-200 @enderror">
            @error('date')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- TIME --}}
        <div>
            <label for="time" class="block text-sm font-semibold text-gray-700 mb-2">
                Time
            </label>
            <input type="time" 
                   id="time" 
                   name="time" 
                   value="{{ old('time') }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('time') border-red-500 ring-2 ring-red-200 @enderror">
            @error('time')
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

        {{-- DR NUMBER --}}
        <div>
            <label for="dr_number" class="block text-sm font-semibold text-gray-700 mb-2">
                DR Number
            </label>
            <input type="text" 
                   id="dr_number" 
                   name="dr_number" 
                   value="{{ old('dr_number') }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('dr_number') border-red-500 ring-2 ring-red-200 @enderror">
            @error('dr_number')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- SI NUMBER --}}
        <div>
            <label for="si_number" class="block text-sm font-semibold text-gray-700 mb-2">
                SI Number
            </label>
            <input type="text" 
                   id="si_number" 
                   name="si_number" 
                   value="{{ old('si_number') }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('si_number') border-red-500 ring-2 ring-red-200 @enderror">
            @error('si_number')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- RECEIVED BY --}}
        <div>
            <label for="received_by" class="block text-sm font-semibold text-gray-700 mb-2">
                Received By
            </label>
            <input type="text" 
                   id="received_by" 
                   name="received_by" 
                   value="{{ old('received_by') }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('received_by') border-red-500 ring-2 ring-red-200 @enderror">
            @error('received_by')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- DATE RECEIVED --}}
        <div>
            <label for="date_received" class="block text-sm font-semibold text-gray-700 mb-2">
                Date Received
            </label>
            <input type="date" 
                   id="date_received" 
                   name="date_received" 
                   value="{{ old('date_received') }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('date_received') border-red-500 ring-2 ring-red-200 @enderror">
            @error('date_received')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- STRETCH FILM CODE --}}
        <div>
            <label for="stretch_film_code" class="block text-sm font-semibold text-gray-700 mb-2">
                Stretch Film Code
            </label>
            <input type="text" 
                   id="stretch_film_code" 
                   name="stretch_film_code" 
                   value="{{ old('stretch_film_code') }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('stretch_film_code') border-red-500 ring-2 ring-red-200 @enderror">
            @error('stretch_film_code')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
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

        {{-- UOM (AUTO-FILL) --}}
        <div>
            <label for="uom" class="block text-sm font-semibold text-gray-700 mb-2">
                UOM
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
        <div class="md:col-span-2">
            <label for="remarks" class="block text-sm font-semibold text-gray-700 mb-2">
                Remarks / Notes
            </label>
            <textarea id="remarks" 
                      name="remarks" 
                      rows="3" 
                      placeholder="Any special notes for logistics or delivery..."
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
    const $jobOrderSelect = $('#job_order_id');
    
    // Initialize Select2 for searchable job order dropdown
    $jobOrderSelect.select2({
        placeholder: 'Search by JO number, product code, or customer...',
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
            const joNum = ($(data.element).data('jo-number') || '').toLowerCase();
            const prodCode = ($(data.element).data('product-code') || '').toLowerCase();
            const customer = ($(data.element).data('customer-name') || '').toLowerCase();
            
            if (text.indexOf(term) > -1 || joNum.indexOf(term) > -1 || prodCode.indexOf(term) > -1 || customer.indexOf(term) > -1) {
                return data;
            }
            return null;
        }
    });

    // Auto-fill fields on job order selection (use select2:select event)
    $jobOrderSelect.on('select2:select', function(e) {
        const selectedOption = e.params.data;
        const $selectedElement = $(this).find('option[value="' + selectedOption.id + '"]');
        
        if (selectedOption.id) {
            $('#product_code').val($selectedElement.data('product-code') || '');
            $('#customer_name').val($selectedElement.data('customer-name') || '');
            $('#model_name').val($selectedElement.data('model-name') || '');
            $('#description').val($selectedElement.data('description') || '');
            $('#uom').val($selectedElement.data('uom') || '');
            
            // Suggest quantity from JO balance
            const suggestedQty = $selectedElement.data('jo-balance') || 0;
            if (suggestedQty > 0) {
                $('#quantity').val(suggestedQty);
            }

            // Highlight autofilled fields with yellow background for 2.5 seconds
            ['product_code', 'customer_name', 'model_name', 'description', 'uom', 'quantity'].forEach(fieldId => {
                const $field = $(`#${fieldId}`);
                if ($field.val()) {
                    $field.addClass('bg-yellow-100 border-yellow-400 ring-2 ring-yellow-300');
                    setTimeout(() => {
                        $field.removeClass('bg-yellow-100 border-yellow-400 ring-2 ring-yellow-300');
                    }, 2500);
                }
            });

            // Visual feedback
            $jobOrderSelect.next('.select2-container').addClass('ring-2 ring-green-500');
            setTimeout(() => $jobOrderSelect.next('.select2-container').removeClass('ring-2 ring-green-500'), 1500);
        }
    });

    // Pre-populate fields if job order is already selected
    const selectedOption = $jobOrderSelect.find('option:selected');
    if (selectedOption.val()) {
        $('#product_code').val(selectedOption.data('product-code') || '');
        $('#customer_name').val(selectedOption.data('customer-name') || '');
        $('#model_name').val(selectedOption.data('model-name') || '');
        $('#description').val(selectedOption.data('description') || '');
        $('#uom').val(selectedOption.data('uom') || '');
        
        const suggestedQty = selectedOption.data('jo-balance') || 0;
        if (suggestedQty > 0) {
            $('#quantity').val(suggestedQty);
        }
    }

    // Fetch suggested ETL code
    (async function() {
        const input = document.getElementById('etl_code');
        if (input.value) return;
        
        try {
            const resp = await fetch('/api/sequences/next?type=etl');
            if (!resp.ok) return;
            const data = await resp.json();
            if (data.etl_code) {
                input.value = data.etl_code;
                input.classList.add('bg-yellow-50');
                setTimeout(() => input.classList.remove('bg-yellow-50'), 2000);
            }
        } catch (e) {
            console.error('Failed to fetch ETL code:', e);
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
@endsection