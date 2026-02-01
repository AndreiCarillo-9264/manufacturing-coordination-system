@extends('layouts.app')

@section('title', 'Create Inventory Record')
@section('page-icon') <i class="fas fa-plus-circle"></i> @endsection
@section('page-title', 'New Inventory Record')
@section('page-description', 'Add initial stock count for a product')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden max-w-4xl mx-auto">
    <div class="p-6 border-b bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800">Create Inventory Record</h3>
        <p class="text-sm text-gray-600 mt-1">Add an initial stock count. Fields marked with * are required.</p>
    </div>

    <form action="{{ route('actual-inventories.store') }}" method="POST" class="p-6 space-y-8">
        @csrf

        <!-- Info Banner -->
        <div class="bg-blue-50 border border-blue-100 p-4 rounded-lg text-sm text-blue-800 space-y-1">
            <p><strong>Purpose:</strong> Initial stock setup or adding products with no inventory record yet</p>
            <p><strong>UOM:</strong> Will be automatically set from the selected product</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Tag Number (Suggested, editable) -->
            <div>
                <label for="tag_number" class="block text-sm font-medium text-gray-700 mb-1.5">Tag Number</label>
                <input type="text" id="tag_number" name="tag_number" value="{{ old('tag_number') }}" placeholder="Auto-suggested tag number, editable"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tag_number') border-red-500 @enderror">
                <p class="mt-1.5 text-xs text-gray-500">Tag will be suggested (TAG-YYYY-NNNN) but you may change it.</p>
                @error('tag_number') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Product -->
            <div class="md:col-span-2">
                <label for="product_id" class="block text-sm font-medium text-gray-700 mb-1.5">Product *</label>
                <select id="product_id" name="product_id" required data-uom-field="uom"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('product_id') border-red-500 @enderror">
                    <option value="">— Select Product —</option>
                    @foreach($products as $product)
                    <option value="{{ $product->id }}" data-uom="{{ $product->uom }}" 
                           {{ old('product_id') == $product->id ? 'selected' : '' }}>
                        {{ $product->product_code }} — {{ $product->model_name }} ({{ $product->customer ?? 'N/A' }})
                    </option>
                    @endforeach
                </select>
                @error('product_id') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Counted Quantity -->
            <div>
                <label for="qty_counted" class="block text-sm font-medium text-gray-700 mb-1.5">Quantity *</label>
                <input type="number" id="qty_counted" name="qty_counted" value="{{ old('qty_counted', 0) }}" 
                       min="0" step="1" required
                       placeholder="Physical count"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('qty_counted') border-red-500 @enderror">
                @error('qty_counted') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- UOM (Auto-populated) -->
            <div>
                <label for="uom" class="block text-sm font-medium text-gray-700 mb-1.5">UOM (Unit of Measure)</label>
                <input type="text" id="uom" name="uom" value="{{ old('uom', '') }}" 
                       readonly
                       placeholder="Auto-populated from product"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
            </div>

            <!-- Location -->
            <div>
                <label for="location" class="block text-sm font-medium text-gray-700 mb-1.5">Location *</label>
                <input type="text" id="location" name="location" value="{{ old('location', '') }}" 
                       required
                       placeholder="Warehouse location, shelf, bin, etc."
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('location') border-red-500 @enderror">
                @error('location') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Counted By -->
            <div>
                <label for="counted_by_user_id" class="block text-sm font-medium text-gray-700 mb-1.5">Counted By *</label>
                <select id="counted_by_user_id" name="counted_by_user_id" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('counted_by_user_id') border-red-500 @enderror">
                    <option value="">— Select User —</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('counted_by_user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                    @endforeach
                </select>
                @error('counted_by_user_id') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Verified By (Optional) -->
            <div>
                <label for="verified_by_user_id" class="block text-sm font-medium text-gray-700 mb-1.5">Verified By</label>
                <select id="verified_by_user_id" name="verified_by_user_id"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('verified_by_user_id') border-red-500 @enderror">
                    <option value="">— None —</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ old('verified_by_user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                    @endforeach
                </select>
                @error('verified_by_user_id') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Remarks -->
            <div class="md:col-span-2">
                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1.5">Remarks</label>
                <textarea id="remarks" name="remarks" rows="3" placeholder="Additional notes..."
                         class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('remarks') border-red-500 @enderror">{{ old('remarks') }}</textarea>
                @error('remarks') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- Buttons -->
        <div class="flex justify-end gap-3 pt-4 border-t">
            <a href="{{ route('actual-inventories.index') }}" 
               class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                Cancel
            </a>
            <button type="submit" 
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2">
                <i class="fas fa-plus"></i> Create Record
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id');
    const uomField = document.getElementById('uom');
    
    // Auto-populate UOM when product changes
    productSelect.addEventListener('change', function() {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const uom = selectedOption.getAttribute('data-uom');
        
        if (uom) {
            uomField.value = uom;
        } else {
            uomField.value = '';
        }
    });
    
    // Trigger on page load if product is already selected (form validation error case)
    if (productSelect.value) {
        const selectedOption = productSelect.options[productSelect.selectedIndex];
        const uom = selectedOption.getAttribute('data-uom');
        
        if (uom && !uomField.value) {
            uomField.value = uom;
        }
    }

    // Fetch suggested Tag Number
    (async function() {
        const input = document.getElementById('tag_number');
        if (!input || input.value) return;
        try {
            const resp = await fetch('/api/sequences/next?type=tag');
            if (!resp.ok) return;
            const data = await resp.json();
            if (data.tag_number) input.value = data.tag_number;
        } catch (e) {
            console.error('Sequence fetch failed', e);
        }
    })();
});
</script>
@endsection