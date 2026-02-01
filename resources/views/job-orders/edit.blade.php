@extends('layouts.app')

@section('title', 'Edit Job Order')
@section('page-icon') <i class="fas fa-edit"></i> @endsection
@section('page-title', 'Edit Job Order: ' . $jobOrder->jo_number)
@section('page-description', 'Update job order details')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="p-6 border-b bg-gray-50 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Edit Job Order</h3>
            <p class="text-sm text-gray-600 mt-1">Update the details below. Fields marked with * are required.</p>
        </div>
        <div class="text-sm text-gray-500 font-mono">
            {{ $jobOrder->jo_number }}
        </div>
    </div>

    <form action="{{ route('job-orders.update', $jobOrder) }}" method="POST" class="p-6 space-y-8">
        @csrf
        @method('PUT')

        <!-- Read-only system fields -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-blue-50/40 p-5 rounded-lg border border-blue-100">
            <div>
                <label for="jo_number" class="block text-sm font-medium text-gray-700 mb-1.5">JO Number</label>
                <input type="text" id="jo_number" name="jo_number" value="{{ old('jo_number', $jobOrder->jo_number) }}" 
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('jo_number') border-red-500 @enderror">
                @error('jo_number') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                <div id="jo_number_suggestion" class="mt-2 text-sm text-gray-500 hidden">
                    Suggested: <span id="jo_number_suggestion_text" class="font-mono text-gray-700"></span>
                    <button type="button" id="jo_number_use_suggestion" class="ml-3 px-2 py-1 bg-green-50 text-green-700 rounded text-xs">Use suggestion</button>
                    <button type="button" id="jo_number_regenerate" class="ml-2 px-2 py-1 bg-gray-50 rounded text-xs">Regenerate</button>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 uppercase tracking-wide">Date Encoded</label>
                <div class="mt-1.5 text-base text-gray-900">{{ $jobOrder->date_encoded?->format('M d, Y') ?? '—' }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 uppercase tracking-wide">Encoded By</label>
                <div class="mt-1.5 text-base text-gray-900">{{ $jobOrder->encodedBy?->name ?? 'System' }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 uppercase tracking-wide">Week Number</label>
                <div class="mt-1.5 text-base text-gray-900">{{ $jobOrder->week_number ?? '—' }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 uppercase tracking-wide">Status</label>
                <div class="mt-1.5 text-base text-gray-900 capitalize">{{ $jobOrder->status }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 uppercase tracking-wide">JO Balance (Calculated)</label>
                <div class="mt-1.5 text-base text-gray-900">{{ $jobOrder->qty_balance }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- PO Number -->
            <div>
                <label for="po_number" class="block text-sm font-medium text-gray-700 mb-1.5">PO Number *</label>
                <input type="text" id="po_number" name="po_number" value="{{ old('po_number', $jobOrder->po_number) }}"
                       placeholder="e.g. PO-2024-001" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('po_number') border-red-500 @enderror">
                @error('po_number') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                <div id="po_number_suggestion" class="mt-2 text-sm text-gray-500 hidden">
                    Suggested: <span id="po_number_suggestion_text" class="font-mono text-gray-700"></span>
                    <button type="button" id="po_number_use_suggestion" class="ml-3 px-2 py-1 bg-green-50 text-green-700 rounded text-xs">Use suggestion</button>
                    <button type="button" id="po_number_regenerate" class="ml-2 px-2 py-1 bg-gray-50 rounded text-xs">Regenerate</button>
                </div>
            </div>

            <!-- Date Needed -->
            <div>
                <label for="date_needed" class="block text-sm font-medium text-gray-700 mb-1.5">Date Needed *</label>
                <input type="date" name="date_needed" value="{{ old('date_needed', $jobOrder->date_needed->format('Y-m-d')) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('date_needed') border-red-500 @enderror">
                @error('date_needed') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Product -->
            <div>
                <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1.5">Product *</label>
                <select id="product_id" name="product_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('product_id') border-red-500 @enderror">
                    <option value="">— Select Product —</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ old('product_id', $jobOrder->product_id) == $product->id ? 'selected' : '' }}>
                            {{ $product->product_code }} - {{ $product->model_name }} ({{ $product->customer ?? 'N/A' }})
                        </option>
                    @endforeach
                </select>
                @error('product_id') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Quantity Ordered -->
            <div>
                <label for="qty_ordered" class="block text-sm font-medium text-gray-700 mb-1.5">Quantity Ordered *</label>
                <input type="number" name="qty_ordered" value="{{ old('qty_ordered', $jobOrder->qty_ordered) }}" min="1"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('qty_ordered') border-red-500 @enderror">
                @error('qty_ordered') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- UOM -->
            <div>
                <label for="uom" class="block text-sm font-medium text-gray-700 mb-1.5">UOM <span class="text-gray-500 text-xs">(Unit of Measure)</span> *</label>
                <select name="uom" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('uom') border-red-500 @enderror">
                    <option value="">— Select Unit —</option>
                    <option value="pcs" {{ old('uom', $jobOrder->uom) == 'pcs' ? 'selected' : '' }}>pcs – Pieces</option>
                    <option value="set" {{ old('uom', $jobOrder->uom) == 'set' ? 'selected' : '' }}>set – Set</option>
                    <!-- Add other options as in products -->
                </select>
                @error('uom') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Remarks -->
            <div class="md:col-span-2">
                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1.5">Remarks</label>
                <textarea name="remarks" rows="3" placeholder="Additional notes or special instructions..."
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('remarks') border-red-500 @enderror">{{ old('remarks', $jobOrder->remarks) }}</textarea>
                @error('remarks') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Advanced fields (if editable) -->
            <div>
                <label for="ppqc_transfer" class="block text-sm font-medium text-gray-700 mb-1.5">PPQC Transfer</label>
                <input type="number" name="ppqc_transfer" value="{{ old('ppqc_transfer', $jobOrder->ppqc_transfer) }}" min="0"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('ppqc_transfer') border-red-500 @enderror">
                @error('ppqc_transfer') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Add similar for ds_quantity, withdrawal, withdrawal_number if needed -->

        </div>

        <div class="flex justify-end gap-4 pt-8 border-t mt-6">
            <a href="{{ route('job-orders.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">Update Job Order</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const joInput = document.getElementById('jo_number');
    const poInput = document.getElementById('po_number');
    const dateNeeded = document.querySelector('input[name="date_needed"]');

    async function fetchJoPo() {
        try {
            const date = dateNeeded?.value || '';
            const resp = await fetch('/api/sequences/next?type=job_order' + (date ? '&date=' + encodeURIComponent(date) : ''));
            if (!resp.ok) return;
            const data = await resp.json();

            // JO number suggestion
            const joSuggestionContainer = document.getElementById('jo_number_suggestion');
            const joSuggestionText = document.getElementById('jo_number_suggestion_text');
            const joUseBtn = document.getElementById('jo_number_use_suggestion');
            if (joSuggestionText && data.jo_number) {
                joSuggestionText.textContent = data.jo_number;
                joSuggestionContainer.classList.remove('hidden');
                joUseBtn.disabled = false;
            }

            // PO number suggestion
            const poSuggestionContainer = document.getElementById('po_number_suggestion');
            const poSuggestionText = document.getElementById('po_number_suggestion_text');
            const poUseBtn = document.getElementById('po_number_use_suggestion');
            if (poSuggestionText && data.po_number) {
                poSuggestionText.textContent = data.po_number;
                poSuggestionContainer.classList.remove('hidden');
                poUseBtn.disabled = false;
            }
        } catch (e) { console.error(e); }
    }

    fetchJoPo();
    dateNeeded?.addEventListener('change', fetchJoPo);

    // Wire up use/regenerate buttons
    document.getElementById('jo_number_use_suggestion').addEventListener('click', function() {
        const txt = document.getElementById('jo_number_suggestion_text').textContent;
        if (txt) {
            joInput.value = txt;
            document.getElementById('jo_number_suggestion').classList.add('hidden');
        }
    });
    document.getElementById('jo_number_regenerate').addEventListener('click', fetchJoPo);

    document.getElementById('po_number_use_suggestion').addEventListener('click', function() {
        const txt = document.getElementById('po_number_suggestion_text').textContent;
        if (txt) {
            poInput.value = txt;
            document.getElementById('po_number_suggestion').classList.add('hidden');
        }
    });
    document.getElementById('po_number_regenerate').addEventListener('click', fetchJoPo);
});
</script>
@endsection