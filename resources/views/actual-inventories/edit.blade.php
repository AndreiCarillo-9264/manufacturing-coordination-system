@extends('layouts.app')

@section('title', 'Edit Inventory')
@section('page-icon') <i class="fas fa-edit"></i> @endsection
@section('page-title', 'Edit Inventory Record')
@section('page-description', 'Update stock count')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden max-w-4xl mx-auto">
    <div class="p-6 border-b bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800">Edit Inventory Record</h3>
        <p class="text-sm text-gray-600 mt-1">Update the inventory details below.</p>
    </div>

    <form action="{{ route('actual-inventories.update', $actualInventory) }}" method="POST" class="p-6 space-y-8">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Tag Number -->
            <div>
                <label for="tag_number" class="block text-sm font-medium text-gray-700 mb-1.5">Tag Number</label>
                <input type="text" id="tag_number" name="tag_number" value="{{ old('tag_number', $actualInventory->tag_number) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tag_number') border-red-500 @enderror">
                @error('tag_number') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                <div id="tag_number_suggestion" class="mt-2 text-sm text-gray-500 hidden">
                    Suggested: <span id="tag_number_suggestion_text" class="font-mono text-gray-700"></span>
                    <button type="button" id="tag_number_use_suggestion" class="ml-3 px-2 py-1 bg-green-50 text-green-700 rounded text-xs">Use suggestion</button>
                    <button type="button" id="tag_number_regenerate" class="ml-2 px-2 py-1 bg-gray-50 rounded text-xs">Regenerate</button>
                </div>
            </div>

            <!-- Product (Read-only) -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Product</label>
                <input type="text" value="{{ $actualInventory->product->product_code }} — {{ $actualInventory->product->model_name }} ({{ $actualInventory->product->customer ?? 'N/A' }})" 
                       readonly class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
            </div>

            <!-- Counted Quantity -->
            <div>
                <label for="qty_counted" class="block text-sm font-medium text-gray-700 mb-1.5">Quantity *</label>
                <input type="number" id="qty_counted" name="qty_counted" value="{{ old('qty_counted', $actualInventory->qty_counted) }}" 
                       min="0" step="1" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('qty_counted') border-red-500 @enderror">
                @error('qty_counted') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- UOM (Read-only) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">UOM (Unit of Measure)</label>
                <input type="text" value="{{ $actualInventory->product->uom ?? '—' }}" 
                       readonly class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
            </div>

            <!-- Location -->
            <div>
                <label for="location" class="block text-sm font-medium text-gray-700 mb-1.5">Location *</label>
                <input type="text" id="location" name="location" value="{{ old('location', $actualInventory->location) }}" 
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
                    <option value="{{ $user->id }}" {{ old('counted_by_user_id', $actualInventory->counted_by_user_id) == $user->id ? 'selected' : '' }}>
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
                    <option value="{{ $user->id }}" {{ old('verified_by_user_id', $actualInventory->verified_by_user_id) == $user->id ? 'selected' : '' }}>
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
                         class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('remarks') border-red-500 @enderror">{{ old('remarks', $actualInventory->remarks) }}</textarea>
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
                <i class="fas fa-save"></i> Update Record
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('tag_number');
    const suggestionContainer = document.getElementById('tag_number_suggestion');
    const suggestionText = document.getElementById('tag_number_suggestion_text');
    const useBtn = document.getElementById('tag_number_use_suggestion');
    const regenBtn = document.getElementById('tag_number_regenerate');

    async function fetchSuggestion() {
        try {
            const resp = await fetch('/api/sequences/next?type=tag');
            if (!resp.ok) return;
            const data = await resp.json();
            if (data.tag_number) {
                suggestionText.textContent = data.tag_number;
                suggestionContainer.classList.remove('hidden');
                useBtn.disabled = false;
            }
        } catch (e) { console.error(e); }
    }

    useBtn.addEventListener('click', function() {
        const txt = suggestionText.textContent;
        if (txt) {
            input.value = txt;
            suggestionContainer.classList.add('hidden');
        }
    });

    regenBtn.addEventListener('click', function() { fetchSuggestion(); });

    fetchSuggestion();
});
</script>

@endsection