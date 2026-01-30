@extends('layouts.app')

@section('title', 'Create Product')
@section('page-icon') <i class="fas fa-plus-circle"></i> @endsection
@section('page-title', 'Create New Product')
@section('page-description', 'Add a new item to the product catalog')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="p-6 border-b bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800">New Product Registration</h3>
        <p class="text-sm text-gray-600 mt-1">Enter the product details below. Fields marked with * are required.</p>
    </div>

    <form action="{{ route('products.store') }}" method="POST" class="p-6 space-y-8">
        @csrf

        <!-- Auto-generated info banner -->
        <div class="bg-blue-50 border border-blue-100 p-4 rounded-lg text-sm text-blue-800 space-y-1">
            <p><strong>Product Code</strong> will be automatically generated (PRD-YYYY-NNNN)</p>
            <p><strong>Date Encoded</strong> will be set to today ({{ now()->format('M d, Y') }})</p>
            <p><strong>Encoded By</strong> will be set to current user</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Customer -->
            <div>
                <label for="customer" class="block text-sm font-medium text-gray-700 mb-1.5">Customer *</label>
                <input type="text" id="customer" name="customer" value="{{ old('customer') }}" required
                       placeholder="e.g. ABC Corporation" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('customer') border-red-500 @enderror">
                @error('customer') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Model Name -->
            <div>
                <label for="model_name" class="block text-sm font-medium text-gray-700 mb-1.5">Model Name</label>
                <input type="text" name="model_name" value="{{ old('model_name') }}"
                       placeholder="e.g. Widget-X 3000" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('model_name') border-red-500 @enderror">
                @error('model_name') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Description -->
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea name="description" rows="3" placeholder="General product description / purpose..."
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                @error('description') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Specs -->
            <div class="md:col-span-2">
                <label for="specs" class="block text-sm font-medium text-gray-700 mb-1.5">Specifications</label>
                <textarea name="specs" rows="4" placeholder="Technical specs, materials, tolerances..."
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('specs') border-red-500 @enderror">{{ old('specs') }}</textarea>
                @error('specs') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Dimension with auto-format -->
            <div>
                <label for="dimension" class="block text-sm font-medium text-gray-700 mb-1.5">Dimension / Size</label>
                <input type="text" name="dimension" value="{{ old('dimension') }}"
                       placeholder="e.g. 120 80 45" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('dimension') border-red-500 @enderror">
                <p class="mt-1.5 text-xs text-gray-500">
                    Type 3 numbers separated by space or comma → will auto-format to: 120 × 80 × 45 mm
                </p>
                @error('dimension') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- MOQ -->
            <div>
                <label for="moq" class="block text-sm font-medium text-gray-700 mb-1.5">MOQ (Minimum Order Quantity)</label>
                <input type="number" name="moq" value="{{ old('moq', 1) }}" min="1"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('moq') border-red-500 @enderror">
                @error('moq') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- UOM -->
            <div>
                <label for="uom" class="block text-sm font-medium text-gray-700 mb-1.5">UOM <span class="text-gray-500 text-xs">(Unit of Measure)</span> *</label>
                <select name="uom" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('uom') border-red-500 @enderror">
                    <option value="">— Select Unit —</option>
                    <option value="pcs"    {{ old('uom', 'pcs') == 'pcs'    ? 'selected' : '' }}>pcs – Pieces</option>
                    <option value="set"    {{ old('uom') == 'set'    ? 'selected' : '' }}>set – Set</option>
                    <option value="kg"     {{ old('uom') == 'kg'     ? 'selected' : '' }}>kg – Kilogram</option>
                    <option value="g"      {{ old('uom') == 'g'      ? 'selected' : '' }}>g – Gram</option>
                    <option value="m"      {{ old('uom') == 'm'      ? 'selected' : '' }}>m – Meter</option>
                    <option value="cm"     {{ old('uom') == 'cm'     ? 'selected' : '' }}>cm – Centimeter</option>
                    <option value="mm"     {{ old('uom') == 'mm'     ? 'selected' : '' }}>mm – Millimeter</option>
                    <option value="l"      {{ old('uom') == 'l'      ? 'selected' : '' }}>L – Liter</option>
                    <option value="ml"     {{ old('uom') == 'ml'     ? 'selected' : '' }}>mL – Milliliter</option>
                    <option value="box"    {{ old('uom') == 'box'    ? 'selected' : '' }}>box – Box</option>
                    <option value="pack"   {{ old('uom') == 'pack'   ? 'selected' : '' }}>pack – Pack</option>
                </select>
                @error('uom') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Currency -->
            <div>
                <label for="currency" class="block text-sm font-medium text-gray-700 mb-1.5">Currency</label>
                <select name="currency" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('currency') border-red-500 @enderror">
                    <option value="PHP" {{ old('currency', 'PHP') == 'PHP' ? 'selected' : '' }}>PHP – Philippine Peso</option>
                    <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD – US Dollar</option>
                    <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR – Euro</option>
                    <option value="JPY" {{ old('currency') == 'JPY' ? 'selected' : '' }}>JPY – Japanese Yen</option>
                    <option value="SGD" {{ old('currency') == 'SGD' ? 'selected' : '' }}>SGD – Singapore Dollar</option>
                </select>
                @error('currency') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Selling Price -->
            <div>
                <label for="selling_price" class="block text-sm font-medium text-gray-700 mb-1.5">Selling Price</label>
                <input type="number" name="selling_price" step="0.01" min="0" value="{{ old('selling_price', '0.00') }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('selling_price') border-red-500 @enderror">
                @error('selling_price') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- MC -->
            <div>
                <label for="mc" class="block text-sm font-medium text-gray-700 mb-1.5">MC <span class="text-gray-500 text-xs">(Material Cost)</span></label>
                <input type="number" name="mc" step="0.01" min="0" value="{{ old('mc') }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('mc') border-red-500 @enderror">
                @error('mc') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Diff & MU display -->
            <div class="md:col-span-2 bg-gray-50 p-5 rounded-lg border border-gray-200">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 text-center">
                    <div>
                        <div class="text-sm text-gray-600 mb-1">Difference (Selling – MC)</div>
                        <div id="diff-display" class="text-2xl font-semibold text-gray-800">—</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 mb-1">MU <span class="text-xs text-gray-500">(Markup %)</span></div>
                        <div id="mu-display" class="text-2xl font-semibold text-gray-800">—</div>
                    </div>
                </div>
            </div>

            <!-- RSQF Number -->
            <div>
                <label for="rsqf_number" class="block text-sm font-medium text-gray-700 mb-1.5">RSQF Number</label>
                <input type="text" name="rsqf_number" value="{{ old('rsqf_number') }}"
                       placeholder="Reference / quotation number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('rsqf_number') border-red-500 @enderror">
                @error('rsqf_number') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Remarks PO -->
            <div class="md:col-span-2">
                <label for="remarks_po" class="block text-sm font-medium text-gray-700 mb-1.5">Remarks for PO</label>
                <textarea name="remarks_po" rows="3" placeholder="Special instructions or notes for purchase orders..."
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('remarks_po') border-red-500 @enderror">{{ old('remarks_po') }}</textarea>
                @error('remarks_po') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Location -->
            <div>
                <label for="location" class="block text-sm font-medium text-gray-700 mb-1.5">Storage Location</label>
                <input type="text" name="location" value="{{ old('location') }}"
                       placeholder="e.g. WH-A-Row3-Shelf4" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('location') border-red-500 @enderror">
                @error('location') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- PC -->
            <div>
                <label for="pc" class="block text-sm font-medium text-gray-700 mb-1.5">PC <span class="text-gray-500 text-xs">(Product Category / Code)</span></label>
                <input type="text" name="pc" value="{{ old('pc') }}"
                       placeholder="Category or internal code" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('pc') border-red-500 @enderror">
                @error('pc') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

        </div>

        <div class="flex justify-end gap-4 pt-6 border-t">
            <a href="{{ route('products.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">Create Product</button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Live Diff & MU calculation
    const mcInput   = document.querySelector('input[name="mc"]');
    const priceInput = document.querySelector('input[name="selling_price"]');
    const diffEl     = document.getElementById('diff-display');
    const muEl       = document.getElementById('mu-display');

    function updateCalculations() {
        const mc   = parseFloat(mcInput?.value)   || 0;
        const price = parseFloat(priceInput?.value) || 0;

        if (price > 0 || mc > 0) {
            const diff = (price - mc).toFixed(2);
            const mu   = mc > 0 ? (((price - mc) / mc) * 100).toFixed(2) : '—';

            diffEl.textContent = `₱${diff}`;
            muEl.textContent   = mc > 0 ? `${mu}%` : '—';
        } else {
            diffEl.textContent = '—';
            muEl.textContent   = '—';
        }
    }

    mcInput?.addEventListener('input', updateCalculations);
    priceInput?.addEventListener('input', updateCalculations);
    updateCalculations();

    // Auto-format dimension on blur
    const dimensionInput = document.querySelector('input[name="dimension"]');

    if (dimensionInput) {
        dimensionInput.addEventListener('blur', function() {
            let val = this.value.trim();

            // Skip if already formatted or has unit
            if (!val || val.includes('×') || val.includes('x') || val.match(/(mm|cm|m|in)$/i)) {
                return;
            }

            val = val.replace(/,/g, '.').replace(/\s+/g, ' ');
            const parts = val.split(' ');

            const nums = [];
            for (let part of parts) {
                const num = parseFloat(part);
                if (!isNaN(num)) {
                    nums.push(num.toString().replace(/\.0+$/, ''));
                }
                if (nums.length >= 3) break;
            }

            if (nums.length === 3) {
                let formatted = nums.join(' × ');
                if (!formatted.match(/(mm|cm|m|in)$/i)) {
                    formatted += ' mm';
                }
                this.value = formatted;
            }
        });
    }
});
</script>
@endsection