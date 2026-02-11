{{-- resources/views/delivery-schedules/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Edit Delivery Schedule')
@section('page-icon') <i class="fas fa-edit"></i> @endsection
@section('page-title', 'Edit Delivery Schedule: ' . $deliverySchedule->ds_code)
@section('page-description', 'Update delivery schedule information')

@section('content')
<x-resource-form 
    :action="route('delivery-schedules.update', $deliverySchedule)" 
    method="PUT" 
    title="Edit Delivery Schedule" 
    description="Update the delivery schedule details below. Fields marked with * are required." 
    :cancel="route('delivery-schedules.index')" 
    submit="Update Delivery Schedule">
    
    <x-slot name="headerRight">
        <div class="text-sm text-gray-500 font-mono bg-gray-100 px-3 py-1 rounded">
            {{ $deliverySchedule->ds_code }}
        </div>
    </x-slot>

    {{-- SYSTEM INFO --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-5 mb-6">
        <div>
            <label for="ds_code" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                DS Code
            </label>
            <input type="text" 
                   id="ds_code" 
                   name="ds_code" 
                   value="{{ old('ds_code', $deliverySchedule->ds_code) }}"
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
                {{ $deliverySchedule->date_encoded?->format('M d, Y H:i') ?? '—' }}
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
                        data-jo-number="{{ $jo->jo_number }}"
                        data-po-number="{{ $jo->po_number }}"
                        data-product-id="{{ $jo->product_id }}"
                        data-product-code="{{ $jo->product_code }}"
                        data-customer="{{ $jo->customer_name }}"
                        data-model="{{ $jo->model_name }}"
                        data-description="{{ $jo->description }}"
                        data-dimension="{{ $jo->dimension }}"
                        data-uom="{{ $jo->uom }}"
                        data-jo-balance="{{ $jo->jo_balance }}"
                        data-date-needed="{{ $jo->date_needed?->format('Y-m-d') }}"
                        {{ old('job_order_id', $deliverySchedule->job_order_id) == $jo->id ? 'selected' : '' }}>
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
                   value="{{ old('quantity', $deliverySchedule->quantity) }}" 
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
                   value="{{ old('delivery_date', $deliverySchedule->delivery_date?->format('Y-m-d')) }}" 
                   required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('delivery_date') border-red-500 ring-2 ring-red-200 @enderror">
            @error('delivery_date')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- STATUS --}}
        <div>
            <label for="ds_status" class="block text-sm font-semibold text-gray-700 mb-2">
                Status <span class="text-red-500">*</span>
            </label>
            <select id="ds_status" 
                    name="ds_status" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('ds_status') border-red-500 ring-2 ring-red-200 @enderror">
                <option value="ON SCHEDULE" {{ old('ds_status', $deliverySchedule->ds_status) == 'ON SCHEDULE' ? 'selected' : '' }}>On Schedule</option>
                <option value="BACKLOG" {{ old('ds_status', $deliverySchedule->ds_status) == 'BACKLOG' ? 'selected' : '' }}>Backlog</option>
                <option value="DELIVERED" {{ old('ds_status', $deliverySchedule->ds_status) == 'DELIVERED' ? 'selected' : '' }}>Delivered</option>
                <option value="CANCELLED" {{ old('ds_status', $deliverySchedule->ds_status) == 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
            </select>
            @error('ds_status')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- JO NUMBER (AUTO-FILL) --}}
        <div>
            <label for="jo_number" class="block text-sm font-semibold text-gray-700 mb-2">
                JO Number
            </label>
            <input type="text" 
                   id="jo_number" 
                   name="jo_number" 
                   value="{{ old('jo_number', $deliverySchedule->jo_number) }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow bg-gray-50 @error('jo_number') border-red-500 ring-2 ring-red-200 @enderror" 
                   readonly>
        </div>

        {{-- PO NUMBER (AUTO-FILL) --}}
        <div>
            <label for="po_number" class="block text-sm font-semibold text-gray-700 mb-2">
                PO Number
            </label>
            <input type="text" 
                   id="po_number" 
                   name="po_number" 
                   value="{{ old('po_number', $deliverySchedule->po_number) }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow bg-gray-50 @error('po_number') border-red-500 ring-2 ring-red-200 @enderror" 
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
                   value="{{ old('customer_name', $deliverySchedule->customer_name) }}" 
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
                   value="{{ old('model_name', $deliverySchedule->model_name) }}" 
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
                      readonly>{{ old('description', $deliverySchedule->description) }}</textarea>
        </div>

        {{-- DIMENSION (AUTO-FILL) --}}
        <div>
            <label for="dimension" class="block text-sm font-semibold text-gray-700 mb-2">
                Dimension
            </label>
            <input type="text" 
                   id="dimension" 
                   name="dimension" 
                   value="{{ old('dimension', $deliverySchedule->dimension) }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow bg-gray-50 @error('dimension') border-red-500 ring-2 ring-red-200 @enderror" 
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
                   value="{{ old('uom', $deliverySchedule->uom) }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow bg-gray-50 @error('uom') border-red-500 ring-2 ring-red-200 @enderror" 
                   readonly>
        </div>

        {{-- DELIVERED QUANTITY (READONLY) --}}
        <div>
            <label for="delivered_quantity" class="block text-sm font-semibold text-gray-700 mb-2">
                Delivered Quantity
            </label>
            <input type="number" 
                   id="delivered_quantity" 
                   value="{{ $deliverySchedule->delivered_quantity ?? 0 }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600 font-medium" 
                   readonly>
        </div>

        {{-- FG STOCKS (READONLY) --}}
        <div>
            <label for="fg_stocks" class="block text-sm font-semibold text-gray-700 mb-2">
                FG Stocks
            </label>
            <input type="number" 
                   id="fg_stocks" 
                   value="{{ $deliverySchedule->fg_stocks ?? 0 }}" 
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
                Remarks / Delivery Instructions
            </label>
            <textarea id="remarks" 
                      name="remarks" 
                      rows="3" 
                      placeholder="Special delivery instructions, notes for logistics, or any other remarks..."
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('remarks') border-red-500 ring-2 ring-red-200 @enderror">{{ old('remarks', $deliverySchedule->remarks) }}</textarea>
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
                $(data.element).data('customer').toLowerCase().indexOf(term) > -1) {
                return data;
            }
            return null;
        }
    });

    // Auto-fill fields on job order selection (for changes)
    jobOrderSelect.on('select2:select', function(e) {
        const element = $(e.currentTarget).find('option:selected');
        
        $('#jo_number').val(element.data('jo-number') || '');
        $('#po_number').val(element.data('po-number') || '');
        $('#customer_name').val(element.data('customer') || '');
        $('#model_name').val(element.data('model') || '');
        $('#description').val(element.data('description') || '');
        $('#dimension').val(element.data('dimension') || '');
        $('#uom').val(element.data('uom') || '');

        // Visual feedback
        jobOrderSelect.next('.select2-container').addClass('bg-green-50');
        setTimeout(() => jobOrderSelect.next('.select2-container').removeClass('bg-green-50'), 1500);
    });

    // Pre-populate fields if job order is already selected
    const selectedOption = $('#job_order_id option:selected');
    if (selectedOption.val()) {
        $('#jo_number').val(selectedOption.data('jo-number') || '');
        $('#po_number').val(selectedOption.data('po-number') || '');
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