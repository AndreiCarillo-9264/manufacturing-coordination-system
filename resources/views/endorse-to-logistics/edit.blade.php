{{-- resources/views/endorse-to-logistics/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Endorse To Logistics')
@section('page-icon') <i class="fas fa-edit"></i> @endsection
@section('page-title', 'Edit ETL: ' . $endorseToLogistic->etl_code)
@section('page-description', 'Update endorsement to logistics information')

@section('content')
<x-resource-form 
    :action="route('endorse-to-logistics.update', $endorseToLogistic)" 
    method="PUT" 
    title="Edit ETL Record" 
    description="Update the endorsement details below. Fields marked with * are required." 
    :cancel="route('endorse-to-logistics.index')" 
    submit="Update ETL">
    
    <x-slot name="headerRight">
        <div class="text-sm text-gray-500 font-mono bg-gray-100 px-3 py-1 rounded">
            {{ $endorseToLogistic->etl_code }}
        </div>
    </x-slot>

    {{-- SYSTEM INFO --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-5 mb-6">
        <div>
            <label for="etl_code" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                ETL Code
            </label>
            <input type="text" 
                   id="etl_code" 
                   name="etl_code" 
                   value="{{ old('etl_code', $endorseToLogistic->etl_code) }}" 
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg font-mono font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('etl_code') border-red-500 ring-2 ring-red-200 @enderror" 
                   readonly>
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
                {{ $endorseToLogistic->date_encoded?->format('M d, Y H:i') ?? '—' }}
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
                        {{ old('job_order_id', $endorseToLogistic->job_order_id) == $jo->id ? 'selected' : '' }}>
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
                   value="{{ old('quantity', $endorseToLogistic->quantity) }}" 
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
                   value="{{ old('date', $endorseToLogistic->date?->format('Y-m-d')) }}" 
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
                   value="{{ old('time', $endorseToLogistic->time) }}" 
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
                   value="{{ old('delivery_date', $endorseToLogistic->delivery_date?->format('Y-m-d')) }}" 
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
                   value="{{ old('dr_number', $endorseToLogistic->dr_number) }}" 
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
                   value="{{ old('si_number', $endorseToLogistic->si_number) }}" 
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
                   value="{{ old('received_by', $endorseToLogistic->received_by) }}" 
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
                   value="{{ old('date_received', $endorseToLogistic->date_received?->format('Y-m-d')) }}" 
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
                   value="{{ old('stretch_film_code', $endorseToLogistic->stretch_film_code) }}" 
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
                   value="{{ old('product_code', $endorseToLogistic->product_code) }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow bg-gray-50 @error('product_code') border-red-500 ring-2 ring-red-200 @enderror" 
                   readonly>
        </div>

        {{-- CUSTOMER NAME (AUTO-FILL) --}}
        <div>
            <label for="customer_name" class="block text-sm font-semibold text-gray-700 mb-2">
                Customer Name
            </label>
            <input type="text" 
                   id="customer_name" 
                   name="customer_name" 
                   value="{{ old('customer_name', $endorseToLogistic->customer_name) }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow bg-gray-50 @error('customer_name') border-red-500 ring-2 ring-red-200 @enderror" 
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
                   value="{{ old('model_name', $endorseToLogistic->model_name) }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow bg-gray-50 @error('model_name') border-red-500 ring-2 ring-red-200 @enderror" 
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
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow bg-gray-50 @error('description') border-red-500 ring-2 ring-red-200 @enderror" 
                      readonly>{{ old('description', $endorseToLogistic->description) }}</textarea>
        </div>

        {{-- UOM (AUTO-FILL) --}}
        <div>
            <label for="uom" class="block text-sm font-semibold text-gray-700 mb-2">
                UOM
            </label>
            <input type="text" 
                   id="uom" 
                   name="uom" 
                   value="{{ old('uom', $endorseToLogistic->uom) }}" 
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
        <div class="md:col-span-2">
            <label for="remarks" class="block text-sm font-semibold text-gray-700 mb-2">
                Remarks / Notes
            </label>
            <textarea id="remarks" 
                      name="remarks" 
                      rows="3" 
                      placeholder="Any special notes for logistics or delivery..."
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('remarks') border-red-500 ring-2 ring-red-200 @enderror">{{ old('remarks', $endorseToLogistic->remarks) }}</textarea>
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
    // Initialize Select2 for searchable job order dropdown
    const jobOrderSelect = $('#job_order_id').select2({
        placeholder: 'Search by JO number, product code, or customer...',
        allowClear: true,
        width: '100%',
        minimumResultsForSearch: 0,
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
                $(data.element).data('jo-number').toLowerCase().indexOf(term) > -1 ||
                $(data.element).data('product-code').toLowerCase().indexOf(term) > -1 ||
                $(data.element).data('customer-name').toLowerCase().indexOf(term) > -1) {
                return data;
            }
            return null;
        }
    });

    // Auto-fill fields on job order selection (for changes)
    jobOrderSelect.on('select2:select', function(e) {
        const element = $(e.currentTarget).find('option:selected');
        
        $('#product_code').val(element.data('product-code') || '');
        $('#customer_name').val(element.data('customer-name') || '');
        $('#model_name').val(element.data('model-name') || '');
        $('#description').val(element.data('description') || '');
        $('#uom').val(element.data('uom') || '');

        // Visual feedback
        jobOrderSelect.next('.select2-container').addClass('bg-green-50');
        setTimeout(() => jobOrderSelect.next('.select2-container').removeClass('bg-green-50'), 1500);
    });

    // Pre-populate fields if job order is already selected
    const selectedOption = $('#job_order_id option:selected');
    if (selectedOption.val()) {
        $('#product_code').val(selectedOption.data('product-code') || '');
        $('#customer_name').val(selectedOption.data('customer-name') || '');
        $('#model_name').val(selectedOption.data('model-name') || '');
        $('#description').val(selectedOption.data('description') || '');
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
@endpush