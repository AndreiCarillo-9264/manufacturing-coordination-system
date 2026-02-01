<!-- resources/views/products/edit.blade.php -->
@extends('layouts.app')

@section('title', 'Edit Product')
@section('page-icon') <i class="fas fa-edit"></i> @endsection
@section('page-title', 'Edit Product: ' . $product->product_code)
@section('page-description', 'Update product information')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="p-6 border-b bg-gray-50 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Edit Product</h3>
            <p class="text-sm text-gray-600 mt-1">Update the product details below. Fields marked with * are required.</p>
        </div>
        <div class="text-sm text-gray-500 font-mono">
            {{ $product->product_code }}
        </div>
    </div>

    <form action="{{ route('products.update', $product) }}" method="POST" class="p-6 space-y-8">
        @csrf
        @method('PUT')

        <!-- Read-only system fields -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-blue-50/40 p-5 rounded-lg border border-blue-100">
            <div>
                <label for="product_code" class="block text-sm font-medium text-gray-700 mb-1.5">Product Code</label>
                <input type="text" id="product_code" name="product_code" value="{{ old('product_code', $product->product_code) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('product_code') border-red-500 @enderror">
                @error('product_code') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                <!-- Suggestion UI (non-destructive) -->
                <div id="product_code_suggestion" class="mt-2 text-sm text-gray-500 hidden">
                    Suggested: <span id="product_code_suggestion_text" class="font-mono text-gray-700"></span>
                    <button type="button" id="product_code_use_suggestion" class="ml-3 px-2 py-1 bg-green-50 text-green-700 rounded text-xs">Use suggestion</button>
                    <button type="button" id="product_code_regenerate" class="ml-2 px-2 py-1 bg-gray-50 rounded text-xs">Regenerate</button>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 uppercase tracking-wide">Date Encoded</label>
                <div class="mt-1.5 text-base text-gray-900">{{ $product->date_encoded?->format('M d, Y') ?? '—' }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 uppercase tracking-wide">Encoded By</label>
                <div class="mt-1.5 text-base text-gray-900">{{ $product->encodedBy?->name ?? 'System' }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Customer -->
            <div>
                <label for="customer" class="block text-sm font-medium text-gray-700 mb-1.5">Customer *</label>
                <input type="text" id="customer" name="customer" value="{{ old('customer', $product->customer) }}" required
                       placeholder="e.g. ABC Corporation" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('customer') border-red-500 @enderror">
                @error('customer')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Model -->
            <div>
                <label for="model_name" class="block text-sm font-medium text-gray-700 mb-1.5">Model</label>
                <input type="text" id="model_name" name="model_name" value="{{ old('model_name', $product->model_name) }}"
                       placeholder="e.g. Widget-X 3000" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('model_name') border-red-500 @enderror">
                @error('model_name')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="md:col-span-2">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1.5">Description</label>
                <textarea id="description" name="description" rows="3"
                          placeholder="General product description / purpose..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('description') border-red-500 @enderror">{{ old('description', $product->description) }}</textarea>
                @error('description')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Specifications -->
            <div class="md:col-span-2">
                <label for="specs" class="block text-sm font-medium text-gray-700 mb-1.5">Specifications</label>
                <textarea id="specs" name="specs" rows="4"
                          placeholder="Technical specs, materials, tolerances..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('specs') border-red-500 @enderror">{{ old('specs', $product->specs) }}</textarea>
                @error('specs')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Dimension (with auto-format) -->
            <div>
                <label for="dimension" class="block text-sm font-medium text-gray-700 mb-1.5">Dimension / Size</label>
                <input type="text" id="dimension" name="dimension" value="{{ old('dimension', $product->dimension) }}"
                       placeholder="e.g. 120 × 80 × 45 mm" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('dimension') border-red-500 @enderror">
                <p class="mt-1.5 text-xs text-gray-500">
                    Type 3 numbers separated by space or comma → will auto-format to: 120 × 80 × 45 mm
                </p>
                @error('dimension')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- MOQ -->
            <div>
                <label for="moq" class="block text-sm font-medium text-gray-700 mb-1.5">MOQ (Minimum Order Quantity)</label>
                <input type="number" id="moq" name="moq" value="{{ old('moq', $product->moq ?? 1) }}" min="1"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('moq') border-red-500 @enderror">
                @error('moq')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- UOM -->
            <div>
                <label for="uom" class="block text-sm font-medium text-gray-700 mb-1.5">UOM <span class="text-gray-500 text-xs">(Unit of Measure)</span> *</label>
                <select id="uom" name="uom" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('uom') border-red-500 @enderror">
                    <option value="">— Select Unit —</option>
                    <option value="pcs"    {{ old('uom', $product->uom) == 'pcs'    ? 'selected' : '' }}>pcs – Pieces</option>
                    <option value="set"    {{ old('uom', $product->uom) == 'set'    ? 'selected' : '' }}>set – Set</option>
                    <option value="kg"     {{ old('uom', $product->uom) == 'kg'     ? 'selected' : '' }}>kg – Kilogram</option>
                    <option value="g"      {{ old('uom', $product->uom) == 'g'      ? 'selected' : '' }}>g – Gram</option>
                    <option value="m"      {{ old('uom', $product->uom) == 'm'      ? 'selected' : '' }}>m – Meter</option>
                    <option value="cm"     {{ old('uom', $product->uom) == 'cm'     ? 'selected' : '' }}>cm – Centimeter</option>
                    <option value="mm"     {{ old('uom', $product->uom) == 'mm'     ? 'selected' : '' }}>mm – Millimeter</option>
                    <option value="l"      {{ old('uom', $product->uom) == 'l'      ? 'selected' : '' }}>L – Liter</option>
                    <option value="ml"     {{ old('uom', $product->uom) == 'ml'     ? 'selected' : '' }}>mL – Milliliter</option>
                    <option value="box"    {{ old('uom', $product->uom) == 'box'    ? 'selected' : '' }}>box – Box</option>
                    <option value="pack"   {{ old('uom', $product->uom) == 'pack'   ? 'selected' : '' }}>pack – Pack</option>
                </select>
                @error('uom')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Currency -->
            <div>
                <label for="currency" class="block text-sm font-medium text-gray-700 mb-1.5">Currency</label>
                <select id="currency" name="currency"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('currency') border-red-500 @enderror">
                    <option value="PHP" {{ old('currency', $product->currency) == 'PHP' ? 'selected' : '' }}>PHP – Philippine Peso</option>
                    <option value="USD" {{ old('currency', $product->currency) == 'USD' ? 'selected' : '' }}>USD – US Dollar</option>
                    <option value="EUR" {{ old('currency', $product->currency) == 'EUR' ? 'selected' : '' }}>EUR – Euro</option>
                    <option value="JPY" {{ old('currency', $product->currency) == 'JPY' ? 'selected' : '' }}>JPY – Japanese Yen</option>
                    <option value="SGD" {{ old('currency', $product->currency) == 'SGD' ? 'selected' : '' }}>SGD – Singapore Dollar</option>
                </select>
                @error('currency')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Selling Price -->
            <div>
                <label for="selling_price" class="block text-sm font-medium text-gray-700 mb-1.5">Selling Price</label>
                <input type="number" id="selling_price" name="selling_price" step="0.01" min="0"
                       value="{{ old('selling_price', $product->selling_price ?? '0.00') }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('selling_price') border-red-500 @enderror">
                @error('selling_price')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- MC (Material Cost) -->
            <div>
                <label for="mc" class="block text-sm font-medium text-gray-700 mb-1.5">MC <span class="text-gray-500 text-xs">(Material Cost)</span></label>
                <input type="number" id="mc" name="mc" step="0.01" min="0"
                       value="{{ old('mc', $product->mc ?? '') }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('mc') border-red-500 @enderror">
                @error('mc')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Diff & MU display -->
            <div class="md:col-span-2 bg-gray-50 p-5 rounded-lg border border-gray-200">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 text-center">
                    <div>
                        <div class="text-sm text-gray-600 mb-1">Difference (Selling – MC)</div>
                        <div class="text-2xl font-semibold text-gray-800">₱{{ number_format($product->diff ?? 0, 2) }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-600 mb-1">MU <span class="text-xs text-gray-500">(Markup Percentage)</span></div>
                        <div class="text-2xl font-semibold text-gray-800">{{ number_format($product->mu ?? 0, 2) }}%</div>
                    </div>
                </div>
            </div>

            <!-- RSQF Number -->
            <div>
                <label for="rsqf_number" class="block text-sm font-medium text-gray-700 mb-1.5">RSQF Number</label>
                <input type="text" id="rsqf_number" name="rsqf_number" value="{{ old('rsqf_number', $product->rsqf_number) }}"
                       placeholder="Reference / quotation / source document number" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('rsqf_number') border-red-500 @enderror">
                @error('rsqf_number')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remarks PO -->
            <div class="md:col-span-2">
                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1.5">Remarks / Special Instructions (PO)</label>
                <textarea id="remarks" name="remarks" rows="3"
                          placeholder="Notes for production / purchasing / packaging..." class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('remarks') border-red-500 @enderror">{{ old('remarks', $product->remarks) }}</textarea>
                @error('remarks')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Location -->
            <div>
                <label for="location" class="block text-sm font-medium text-gray-700 mb-1.5">Storage Location</label>
                <input type="text" id="location" name="location" value="{{ old('location', $product->location) }}"
                       placeholder="e.g. WH-B-Row 5 – Shelf C4" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('location') border-red-500 @enderror">
                @error('location')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- PC -->
            <div>
                <label for="pc" class="block text-sm font-medium text-gray-700 mb-1.5">PC <span class="text-gray-500 text-xs">(Product Category / Internal Code)</span></label>
                <input type="text" id="pc" name="pc" value="{{ old('pc', $product->pc) }}"
                       placeholder="Category code or family identifier" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm @error('pc') border-red-500 @enderror">
                @error('pc')
                    <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>

        <div class="flex justify-end gap-4 pt-8 border-t mt-6">
            <a href="{{ route('products.index') }}"
               class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 font-medium transition">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium shadow-sm transition">
                Update Product
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Diff & MU calculation (static display in edit mode)
    // Note: In edit we show saved values, no live update needed unless you want it

    // Auto-format dimension on blur
    const dimensionInput = document.querySelector('input[name="dimension"]');
    
    if (dimensionInput) {
        dimensionInput.addEventListener('blur', function() {
            let val = this.value.trim();
            
            // Skip if already formatted or empty or has unit
            if (!val || val.includes('×') || val.includes('x') || val.match(/(mm|cm|m|in)$/i)) {
                return;
            }

            // Normalize: replace comma with dot, multiple spaces → single space
            val = val.replace(/,/g, '.').replace(/\s+/g, ' ');
            const parts = val.split(' ');

            // Take first 3 numeric-looking parts
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
                // Auto-append mm if no unit detected
                if (!formatted.match(/(mm|cm|m|in)$/i)) {
                    formatted += ' mm';
                }
                this.value = formatted;
            }
        });
    }
});

// Suggest product code (show suggestion, do not overwrite)
(async function() {
    const input = document.getElementById('product_code');
    const suggestionContainer = document.getElementById('product_code_suggestion');
    const suggestionText = document.getElementById('product_code_suggestion_text');
    const useBtn = document.getElementById('product_code_use_suggestion');
    const regenBtn = document.getElementById('product_code_regenerate');

    async function fetchSuggestion() {
        try {
            const resp = await fetch('/api/sequences/next?type=product');
            if (!resp.ok) return;
            const data = await resp.json();
            if (data.product_code) {
                suggestionText.textContent = data.product_code;
                suggestionContainer.classList.remove('hidden');
                useBtn.disabled = false;
            }
        } catch (e) { console.error(e); }
    }

    useBtn.addEventListener('click', function() {
        if (suggestionText.textContent) {
            input.value = suggestionText.textContent;
            suggestionContainer.classList.add('hidden');
        }
    });

    regenBtn.addEventListener('click', function() { fetchSuggestion(); });

    fetchSuggestion();
})();

</script>
@endsection