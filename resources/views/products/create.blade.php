@extends('layouts.app')

@section('title', 'Create Product')
@section('page-icon') <i class="fas fa-plus-circle"></i> @endsection
@section('page-title', 'Create New Product')
@section('page-description', 'Add a new item to the product catalog')

@section('content')
<x-resource-form 
    :action="route('products.store')" 
    method="POST" 
    title="New Product Registration" 
    description="Enter the product details below. Fields marked with * are required." 
    :cancel="route('products.index')" 
    submit="Create Product">

    {{-- INFO BANNER --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 space-y-2">
        <div class="flex items-start">
            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
            <div class="text-sm text-blue-800 space-y-1">
                <p><strong>Product Code</strong> will be automatically generated (PRD-YYYY-NNNN)</p>
                <p><strong>Date Encoded</strong> will be set to today ({{ now()->format('M d, Y') }})</p>
                <p><strong>Encoded By</strong> will be set to current user</p>
            </div>
        </div>
    </div>

    {{-- PRODUCT CODE --}}
    <div>
        <label for="product_code" class="block text-sm font-semibold text-gray-700 mb-2">
            Product Code
        </label>
        <input type="text" 
               id="product_code" 
               name="product_code" 
               value="{{ old('product_code') }}" 
               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('product_code') border-red-500 ring-2 ring-red-200 @enderror" 
               placeholder="Will be auto-generated (editable)">
        <p class="mt-2 text-xs text-gray-500">
            <i class="fas fa-lightbulb mr-1"></i>A suggested code will be generated automatically, but you can edit it if needed
        </p>
        @error('product_code') 
        <p class="mt-2 text-sm text-red-600 flex items-center">
            <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
        </p> 
        @enderror
    </div>

    {{-- MAIN FORM FIELDS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- CUSTOMER --}}
        <div>
            <label for="customer" class="block text-sm font-semibold text-gray-700 mb-2">
                Customer <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   id="customer" 
                   name="customer" 
                   value="{{ old('customer') }}" 
                   required
                   placeholder="e.g. ABC Corporation" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('customer') border-red-500 ring-2 ring-red-200 @enderror">
            @error('customer_name') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>

        {{-- CUSTOMER LOCATION --}}
        <div>
            <label for="location" class="block text-sm font-semibold text-gray-700 mb-2">
                Customer Location
            </label>
            <input type="text" 
                   id="location" 
                   name="location" 
                   value="{{ old('location') }}"
                   placeholder="e.g. Manila, Cebu" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('location') border-red-500 ring-2 ring-red-200 @enderror">
            @error('location') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>

        {{-- MODEL --}}
        <div>
            <label for="model_name" class="block text-sm font-semibold text-gray-700 mb-2">
                Model Name
            </label>
            <input type="text" 
                   id="model_name" 
                   name="model_name" 
                   value="{{ old('model_name') }}"
                   placeholder="e.g. Widget-X 3000" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('model_name') border-red-500 ring-2 ring-red-200 @enderror">
            @error('model_name') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>

        {{-- PC (Product Category) --}}
        <div>
            <label for="pc" class="block text-sm font-semibold text-gray-700 mb-2">
                Product Category <span class="text-gray-500 text-xs font-normal">(PC)</span>
            </label>
            <input type="text" 
                   id="pc" 
                   name="pc" 
                   value="{{ old('pc') }}"
                   placeholder="Category or internal code" 
                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('pc') border-red-500 ring-2 ring-red-200 @enderror">
            @error('pc') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>

        {{-- DESCRIPTION --}}
        <div class="md:col-span-2">
            <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                Description
            </label>
            <textarea id="description" 
                      name="description" 
                      rows="3" 
                      placeholder="General product description and purpose..."
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('description') border-red-500 ring-2 ring-red-200 @enderror">{{ old('description') }}</textarea>
            @error('description') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>

        {{-- SPECIFICATIONS --}}
        <div class="md:col-span-2">
            <label for="specs" class="block text-sm font-semibold text-gray-700 mb-2">
                Specifications
            </label>
            <textarea id="specs" 
                      name="specs" 
                      rows="4" 
                      placeholder="Technical specifications, materials, tolerances, etc..."
                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('specs') border-red-500 ring-2 ring-red-200 @enderror">{{ old('specs') }}</textarea>
            @error('specs') 
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
            </p> 
            @enderror
        </div>

    </div>

    {{-- DIMENSIONS & QUANTITY SECTION --}}
    <div class="border-t pt-6 mt-2">
        <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-ruler-combined text-blue-600 mr-2"></i>
            Dimensions & Quantity
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            {{-- DIMENSION --}}
            <div>
                <label for="dimension" class="block text-sm font-semibold text-gray-700 mb-2">
                    Dimension / Size
                </label>
                <input type="text" 
                       id="dimension" 
                       name="dimension" 
                       value="{{ old('dimension') }}"
                       placeholder="e.g. 120 80 45" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('dimension') border-red-500 ring-2 ring-red-200 @enderror">
                <p class="mt-2 text-xs text-gray-500">
                    <i class="fas fa-magic mr-1"></i>Type 3 numbers → auto-formats to: 120 × 80 × 45 mm
                </p>
                @error('dimension') 
                <p class="mt-2 text-sm text-red-600 flex items-center">
                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                </p> 
                @enderror
            </div>

            {{-- MOQ --}}
            <div>
                <label for="moq" class="block text-sm font-semibold text-gray-700 mb-2">
                    MOQ <span class="text-gray-500 text-xs font-normal">(Minimum Order Quantity)</span>
                </label>
                <input type="number" 
                       id="moq" 
                       name="moq" 
                       value="{{ old('moq', 1) }}" 
                       min="1"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('moq') border-red-500 ring-2 ring-red-200 @enderror">
                @error('moq') 
                <p class="mt-2 text-sm text-red-600 flex items-center">
                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                </p> 
                @enderror
            </div>

            {{-- UOM --}}
            <div>
                <label for="uom" class="block text-sm font-semibold text-gray-700 mb-2">
                    UOM <span class="text-red-500">*</span> <span class="text-gray-500 text-xs font-normal">(Unit of Measure)</span>
                </label>
                <select id="uom" 
                        name="uom" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('uom') border-red-500 ring-2 ring-red-200 @enderror">
                    <option value="">— Select Unit —</option>
                    <option value="pcs" {{ old('uom', 'pcs') == 'pcs' ? 'selected' : '' }}>pcs – Pieces</option>
                    <option value="set" {{ old('uom') == 'set' ? 'selected' : '' }}>set – Set</option>
                    <option value="box" {{ old('uom') == 'box' ? 'selected' : '' }}>box – Box</option>
                    <option value="pack" {{ old('uom') == 'pack' ? 'selected' : '' }}>pack – Pack</option>
                    <option value="kg" {{ old('uom') == 'kg' ? 'selected' : '' }}>kg – Kilogram</option>
                    <option value="g" {{ old('uom') == 'g' ? 'selected' : '' }}>g – Gram</option>
                    <option value="m" {{ old('uom') == 'm' ? 'selected' : '' }}>m – Meter</option>
                    <option value="cm" {{ old('uom') == 'cm' ? 'selected' : '' }}>cm – Centimeter</option>
                    <option value="mm" {{ old('uom') == 'mm' ? 'selected' : '' }}>mm – Millimeter</option>
                    <option value="l" {{ old('uom') == 'l' ? 'selected' : '' }}>L – Liter</option>
                    <option value="ml" {{ old('uom') == 'ml' ? 'selected' : '' }}>mL – Milliliter</option>
                </select>
                @error('uom') 
                <p class="mt-2 text-sm text-red-600 flex items-center">
                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                </p> 
                @enderror
            </div>

        </div>
    </div>

    {{-- PRICING SECTION --}}
    <div class="border-t pt-6 mt-2">
        <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-dollar-sign text-green-600 mr-2"></i>
            Pricing Information
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            {{-- CURRENCY --}}
            <div>
                <label for="currency" class="block text-sm font-semibold text-gray-700 mb-2">
                    Currency
                </label>
                <select id="currency" 
                        name="currency" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('currency') border-red-500 ring-2 ring-red-200 @enderror">
                    <option value="PHP" {{ old('currency', 'PHP') == 'PHP' ? 'selected' : '' }}>PHP – Philippine Peso</option>
                    <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD – US Dollar</option>
                    <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR – Euro</option>
                    <option value="JPY" {{ old('currency') == 'JPY' ? 'selected' : '' }}>JPY – Japanese Yen</option>
                    <option value="SGD" {{ old('currency') == 'SGD' ? 'selected' : '' }}>SGD – Singapore Dollar</option>
                </select>
                @error('currency') 
                <p class="mt-2 text-sm text-red-600 flex items-center">
                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                </p> 
                @enderror
            </div>

            {{-- SELLING PRICE --}}
            <div>
                <label for="selling_price" class="block text-sm font-semibold text-gray-700 mb-2">
                    Selling Price
                </label>
                <input type="number" 
                       id="selling_price" 
                       name="selling_price" 
                       step="0.01" 
                       min="0" 
                       value="{{ old('selling_price', '0.00') }}"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('selling_price') border-red-500 ring-2 ring-red-200 @enderror">
                @error('selling_price') 
                <p class="mt-2 text-sm text-red-600 flex items-center">
                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                </p> 
                @enderror
            </div>

            {{-- MC (Material Cost) --}}
            <div>
                <label for="mc" class="block text-sm font-semibold text-gray-700 mb-2">
                    Material Cost <span class="text-gray-500 text-xs font-normal">(MC)</span>
                </label>
                <input type="number" 
                       id="mc" 
                       name="mc" 
                       step="0.01" 
                       min="0" 
                       value="{{ old('mc') }}"
                       placeholder="0.00"
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('mc') border-red-500 ring-2 ring-red-200 @enderror">
                @error('mc') 
                <p class="mt-2 text-sm text-red-600 flex items-center">
                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                </p> 
                @enderror
            </div>

        </div>

        {{-- CALCULATED FIELDS DISPLAY --}}
        <div class="mt-6 bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="text-center">
                    <div class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                        Difference (Selling – MC)
                    </div>
                    <div id="diff-display" class="text-3xl font-bold text-gray-800">—</div>
                </div>
                <div class="text-center">
                    <div class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">
                        Markup Percentage (MU)
                    </div>
                    <div id="mu-display" class="text-3xl font-bold text-gray-800">—</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ADDITIONAL INFORMATION SECTION --}}
    <div class="border-t pt-6 mt-2">
        <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-clipboard-list text-purple-600 mr-2"></i>
            Additional Information
        </h3>
        <div class="space-y-6">
            
            {{-- RSQF NUMBER --}}
            <div>
                <label for="rsqf_number" class="block text-sm font-semibold text-gray-700 mb-2">
                    RSQF Number
                </label>
                <input type="text" 
                       id="rsqf_number" 
                       name="rsqf_number" 
                       value="{{ old('rsqf_number') }}"
                       placeholder="Reference or quotation number" 
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-shadow @error('rsqf_number') border-red-500 ring-2 ring-red-200 @enderror">
                @error('rsqf_number') 
                <p class="mt-2 text-sm text-red-600 flex items-center">
                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                </p> 
                @enderror
            </div>

            {{-- REMARKS --}}
            <div>
                <label for="remarks" class="block text-sm font-semibold text-gray-700 mb-2">
                    Remarks / Special Instructions
                </label>
                <textarea id="remarks" 
                          name="remarks" 
                          rows="3" 
                          placeholder="Special instructions or notes for purchase orders, production, etc..."
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Live Diff & MU calculation
    const mcInput = document.getElementById('mc');
    const priceInput = document.getElementById('selling_price');
    const diffEl = document.getElementById('diff-display');
    const muEl = document.getElementById('mu-display');

    function updateCalculations() {
        const mc = parseFloat(mcInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;

        if (price > 0 || mc > 0) {
            const diff = (price - mc).toFixed(2);
            const mu = mc > 0 ? (((price - mc) / mc) * 100).toFixed(2) : '—';

            diffEl.textContent = `₱${Number(diff).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
            muEl.textContent = mc > 0 ? `${mu}%` : '—';
            
            // Add visual feedback
            if (parseFloat(diff) < 0) {
                diffEl.classList.add('text-red-600');
                diffEl.classList.remove('text-gray-800', 'text-green-600');
            } else if (parseFloat(diff) > 0) {
                diffEl.classList.add('text-green-600');
                diffEl.classList.remove('text-gray-800', 'text-red-600');
            } else {
                diffEl.classList.add('text-gray-800');
                diffEl.classList.remove('text-red-600', 'text-green-600');
            }
        } else {
            diffEl.textContent = '—';
            muEl.textContent = '—';
            diffEl.classList.add('text-gray-800');
            diffEl.classList.remove('text-red-600', 'text-green-600');
        }
    }

    if (mcInput) mcInput.addEventListener('input', updateCalculations);
    if (priceInput) priceInput.addEventListener('input', updateCalculations);
    updateCalculations();

    // Fetch suggested product code
    (async function() {
        const input = document.getElementById('product_code');
        if (!input || input.value) return;
        
        try {
            const resp = await fetch('/api/sequences/next?type=product');
            if (!resp.ok) return;
            const data = await resp.json();
            if (data.product_code) {
                input.value = data.product_code;
                input.classList.add('bg-yellow-50');
                setTimeout(() => input.classList.remove('bg-yellow-50'), 2000);
            }
        } catch (e) {
            console.error('Failed to fetch product code:', e);
        }
    })();

    // Auto-format dimension on blur
    const dimensionInput = document.getElementById('dimension');
    if (dimensionInput) {
        dimensionInput.addEventListener('blur', function() {
            let val = this.value.trim();
            if (!val || val.includes('×') || val.includes('x') || val.match(/(mm|cm|m|in)$/i)) {
                return;
            }

            val = val.replace(/,/g, ' ').replace(/\s+/g, ' ');
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
                this.classList.add('bg-green-50');
                setTimeout(() => this.classList.remove('bg-green-50'), 1500);
            }
        });
    }
});
</script>
@endsection