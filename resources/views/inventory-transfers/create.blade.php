{{-- resources/views/transfers/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Create Inventory Transfer')
@section('page-icon') <i class="fas fa-plus-circle"></i> @endsection
@section('page-title', 'Create New Inventory Transfer')
@section('page-description', 'Add a new stock transfer')

@section('content')
<x-resource-form 
    :action="route('inventory-transfers.store')" 
    method="POST" 
    title="New Inventory Transfer" 
    description="Enter the transfer details below. Fields marked with * are required." 
    :cancel="route('inventory-transfers.index')" 
    submit="Create Transfer">

    {{-- INFO BANNER --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-2 mb-6">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
            <div class="text-sm text-blue-800 space-y-1">
                <p><strong>PTT Number</strong> and <strong>Transfer Code</strong> will be auto-generated</p>
                <p><strong>Date Encoded</strong> will be set to today</p>
                <p>Select a Job Order to auto-fill product details and suggested quantity from JO balance</p>
            </div>
        </div>
    </div>

    {{-- SYSTEM INFO --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-5 mb-6">
        <div>
            <label for="ptt_number" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                PTT Number
            </label>
            <input type="text" 
                   id="ptt_number" 
                   name="ptt_number" 
                   value="{{ old('ptt_number') }}" 
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg font-mono font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('ptt_number') border-red-500 ring-2 ring-red-200 @enderror">
            @error('ptt_number') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>
        <div>
            <label for="transfer_code" class="block text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                Transfer Code
            </label>
            <input type="text" 
                   id="transfer_code" 
                   name="transfer_code" 
                   value="{{ old('transfer_code') }}" 
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg font-mono font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('transfer_code') border-red-500 ring-2 ring-red-200 @enderror">
            @error('transfer_code') 
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
                        data-jo-number="{{ $jo->jo_number }}"
                        data-product-code="{{ $jo->product_code }}"
                        data-customer-name="{{ $jo->customer_name }}"
                        data-model-name="{{ $jo->model_name }}"
                        data-description="{{ $jo->description }}"
                        data-dimension="{{ $jo->dimension }}"
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

        {{-- SECTION --}}
        <div>
            <label for="section" class="block text-sm font-semibold text-gray-700 mb-2">
                Section <span class="text-red-500">*</span>
            </label>
            <select id="section" 
                    name="section" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('section') border-red-500 ring-2 ring-red-200 @enderror">
                <option value="">Select Section</option>
                <option value="LOCAL" {{ old('section') == 'LOCAL' ? 'selected' : '' }}>Local</option>
                <option value="IMPORTED" {{ old('section') == 'IMPORTED' ? 'selected' : '' }}>Imported</option>
                <option value="EXPORT" {{ old('section') == 'EXPORT' ? 'selected' : '' }}>Export</option>
            </select>
            @error('section')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- CATEGORY --}}
        <div>
            <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">
                Category <span class="text-red-500">*</span>
            </label>
            <select id="category" 
                    name="category" 
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('category') border-red-500 ring-2 ring-red-200 @enderror">
                <option value="">Select Category</option>
                <option value="Production" {{ old('category') == 'Production' ? 'selected' : '' }}>Production</option>
                <option value="Final" {{ old('category') == 'Final' ? 'selected' : '' }}>Final</option>
                <option value="Defective" {{ old('category') == 'Defective' ? 'selected' : '' }}>Defective</option>
            </select>
            @error('category')
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

        {{-- DATE TRANSFERRED --}}
        <div>
            <label for="date_transferred" class="block text-sm font-semibold text-gray-700 mb-2">
                Date Transferred <span class="text-red-500">*</span>
            </label>
            <input type="date" 
                   id="date_transferred" 
                   name="date_transferred" 
                   value="{{ old('date_transferred') }}" 
                   required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('date_transferred') border-red-500 ring-2 ring-red-200 @enderror">
            @error('date_transferred')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- TIME TRANSFERRED --}}
        <div>
            <label for="time_transferred" class="block text-sm font-semibold text-gray-700 mb-2">
                Time Transferred <span class="text-red-500">*</span>
            </label>
            <input type="time" 
                   id="time_transferred" 
                   name="time_transferred" 
                   value="{{ old('time_transferred') }}" 
                   required
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('time_transferred') border-red-500 ring-2 ring-red-200 @enderror">
            @error('time_transferred')
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
                <option value="Balance" {{ old('status', 'Balance') == 'Balance' ? 'selected' : '' }}>Balance</option>
                <option value="Complete" {{ old('status') == 'Complete' ? 'selected' : '' }}>Complete</option>
            </select>
        </div>

        {{-- TRANSFER BY --}}
        <div>
            <label for="transfer_by" class="block text-sm font-semibold text-gray-700 mb-2">
                Transfer By
            </label>
            <input type="text" 
                   id="transfer_by" 
                   name="transfer_by" 
                   value="{{ old('transfer_by') }}" 
                   placeholder="Name of the person transferring"
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('transfer_by') border-red-500 ring-2 ring-red-200 @enderror">
            @error('transfer_by')
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
                   value="{{ old('jo_number') }}" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('jo_number') border-red-500 ring-2 ring-red-200 @enderror" 
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

    {{-- RECEIVED INFORMATION SECTION --}}
    <div class="border-t pt-6 mt-6">
        <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-check-circle text-green-600 mr-2"></i>
            Received Information
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- RECEIVED BY NAME --}}
            <div>
                <label for="received_by_name" class="block text-sm font-semibold text-gray-700 mb-2">
                    Received By <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       id="received_by_name" 
                       name="received_by_name" 
                       value="{{ old('received_by_name') }}" 
                       placeholder="Name of person receiving"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('received_by_name') border-red-500 ring-2 ring-red-200 @enderror">
                @error('received_by_name')
                <p class="mt-2 text-sm text-red-600 flex items-center">
                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                </p>
                @enderror
            </div>

            {{-- HIDDEN FIELD: RECEIVED BY USER ID (for backend) --}}
            <input type="hidden" id="received_by_user_id" name="received_by_user_id" value="{{ old('received_by_user_id', auth()->id()) }}">

            {{-- DATE RECEIVED --}}
            <div>
                <label for="date_received" class="block text-sm font-semibold text-gray-700 mb-2">
                    Date Received <span class="text-red-500">*</span>
                </label>
                <input type="date" 
                       id="date_received" 
                       name="date_received" 
                       value="{{ old('date_received') }}" 
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('date_received') border-red-500 ring-2 ring-red-200 @enderror">
                @error('date_received')
                <p class="mt-2 text-sm text-red-600 flex items-center">
                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                </p>
                @enderror
            </div>

            {{-- TIME RECEIVED --}}
            <div>
                <label for="time_received" class="block text-sm font-semibold text-gray-700 mb-2">
                    Time Received <span class="text-red-500">*</span>
                </label>
                <input type="time" 
                       id="time_received" 
                       name="time_received" 
                       value="{{ old('time_received') }}" 
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('time_received') border-red-500 ring-2 ring-red-200 @enderror">
                @error('time_received')
                <p class="mt-2 text-sm text-red-600 flex items-center">
                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                </p>
                @enderror
            </div>

            {{-- QUANTITY RECEIVED --}}
            <div>
                <label for="quantity_received" class="block text-sm font-semibold text-gray-700 mb-2">
                    Quantity Received <span class="text-red-500">*</span>
                </label>
                <input type="number" 
                       id="quantity_received" 
                       name="quantity_received" 
                       value="{{ old('quantity_received') }}" 
                       min="1"
                       required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('quantity_received') border-red-500 ring-2 ring-red-200 @enderror">
                @error('quantity_received')
                <p class="mt-2 text-sm text-red-600 flex items-center">
                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                </p>
                @enderror
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
                          placeholder="Any special notes for the transfer..."
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
            $('#jo_number').val($selectedElement.data('jo-number') || '');
            $('#customer_name').val($selectedElement.data('customer-name') || '');
            $('#model_name').val($selectedElement.data('model-name') || '');
            $('#description').val($selectedElement.data('description') || '');
            $('#dimension').val($selectedElement.data('dimension') || '');
            $('#uom').val($selectedElement.data('uom') || '');
            
            // Suggest quantity from JO balance
            const suggestedQty = $selectedElement.data('jo-balance') || 0;
            if (suggestedQty > 0) {
                $('#quantity').val(suggestedQty);
            }

            // Highlight autofilled fields with yellow background for 2.5 seconds
            ['jo_number', 'customer_name', 'model_name', 'description', 'dimension', 'uom', 'quantity'].forEach(fieldId => {
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
        $('#jo_number').val(selectedOption.data('jo-number') || '');
        $('#customer_name').val(selectedOption.data('customer-name') || '');
        $('#model_name').val(selectedOption.data('model-name') || '');
        $('#description').val(selectedOption.data('description') || '');
        $('#dimension').val(selectedOption.data('dimension') || '');
        $('#uom').val(selectedOption.data('uom') || '');
        
        const suggestedQty = selectedOption.data('jo-balance') || 0;
        if (suggestedQty > 0) {
            $('#quantity').val(suggestedQty);
        }
    }

    // Fetch suggested PTT number
    (async function() {
        const input = document.getElementById('ptt_number');
        if (input.value) return;
        
        try {
            const resp = await fetch('/api/sequences/next?type=ptt');
            if (!resp.ok) return;
            const data = await resp.json();
            if (data.ptt_number) {
                input.value = data.ptt_number;
                input.classList.add('bg-yellow-50');
                setTimeout(() => input.classList.remove('bg-yellow-50'), 2000);
            }
        } catch (e) {
            console.error('Failed to fetch PTT number:', e);
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