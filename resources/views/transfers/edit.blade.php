@extends('layouts.app')

@section('title', 'Edit Transfer')
@section('page-icon') <i class="fas fa-edit"></i> @endsection
@section('page-title', 'Edit Transfer')
@section('page-description', 'Update transfer details - PTT: ' . $transfer->ptt_number)

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden max-w-4xl mx-auto">
    <div class="p-6 border-b bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800">Edit Transfer Form</h3>
        <p class="text-sm text-gray-600 mt-1">Update the transfer details below.</p>
    </div>

    <form action="{{ route('transfers.update', $transfer) }}" method="POST" class="p-6 space-y-8">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- PTT Number -->
            <div>
                <label for="ptt_number" class="block text-sm font-medium text-gray-700 mb-1.5">PTT Number</label>
                <input type="text" id="ptt_number" name="ptt_number" value="{{ old('ptt_number', $transfer->ptt_number) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('ptt_number') border-red-500 @enderror">
                @error('ptt_number') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
                <!-- Suggestion UI (non-destructive) -->
                <div id="ptt_number_suggestion" class="mt-2 text-sm text-gray-500 hidden">
                    Suggested: <span id="ptt_number_suggestion_text" class="font-mono text-gray-700"></span>
                    <button type="button" id="ptt_number_use_suggestion" class="ml-3 px-2 py-1 bg-green-50 text-green-700 rounded text-xs">Use suggestion</button>
                    <button type="button" id="ptt_number_regenerate" class="ml-2 px-2 py-1 bg-gray-50 rounded text-xs">Regenerate</button>
                </div>
                <input type="hidden" name="job_order_id" value="{{ old('job_order_id', $transfer->job_order_id) }}">
            </div>

            <!-- Product Info -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">Product</label>
                <input type="text" readonly value="{{ $transfer->product->product_code }} — {{ $transfer->product->model_name }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
            </div>

            <!-- Section -->
            <div>
                <label for="section" class="block text-sm font-medium text-gray-700 mb-1.5">Section *</label>
                <select name="section" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('section') border-red-500 @enderror">
                    <option value="Assembly" {{ old('section', $transfer->section) == 'Assembly' ? 'selected' : '' }}>Assembly</option>
                    <option value="QC" {{ old('section', $transfer->section) == 'QC' ? 'selected' : '' }}>QC (Quality Control)</option>
                    <option value="Packaging" {{ old('section', $transfer->section) == 'Packaging' ? 'selected' : '' }}>Packaging</option>
                    <option value="Inspection" {{ old('section', $transfer->section) == 'Inspection' ? 'selected' : '' }}>Inspection</option>
                </select>
                @error('section') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Date Transferred -->
            <div>
                <label for="date_transferred" class="block text-sm font-medium text-gray-700 mb-1.5">Date Transferred *</label>
                <input type="date" name="date_transferred" value="{{ old('date_transferred', $transfer->date_transferred->format('Y-m-d')) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('date_transferred') border-red-500 @enderror">
                @error('date_transferred') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Transfer Time -->
            <div>
                <label for="time_transferred" class="block text-sm font-medium text-gray-700 mb-1.5">Transfer Time *</label>
                <input type="time" name="time_transferred" value="{{ old('time_transferred', $transfer->time_transferred) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('time_transferred') border-red-500 @enderror">
                @error('time_transferred') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Quantity -->
            <div>
                <label for="qty_transferred" class="block text-sm font-medium text-gray-700 mb-1.5">Quantity Produced *</label>
                <input type="number" name="qty_transferred" value="{{ old('qty_transferred', $transfer->qty_transferred) }}" min="1" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('qty_transferred') border-red-500 @enderror">
                @error('qty_transferred') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Grade (Optional) -->
            <div>
                <label for="grade" class="block text-sm font-medium text-gray-700 mb-1.5">Grade</label>
                <input type="text" name="grade" value="{{ old('grade', $transfer->grade) }}" placeholder="e.g., A, B, C"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('grade') border-red-500 @enderror">
                @error('grade') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Dimension (Optional) -->
            <div>
                <label for="dimension" class="block text-sm font-medium text-gray-700 mb-1.5">Dimension</label>
                <input type="text" name="dimension" value="{{ old('dimension', $transfer->dimension) }}" placeholder="e.g., 10x10x5 cm"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('dimension') border-red-500 @enderror">
                @error('dimension') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Category -->
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1.5">Category *</label>
                <select name="category" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('category') border-red-500 @enderror">
                    <option value="Electronics" {{ old('category', $transfer->category) == 'Electronics' ? 'selected' : '' }}>Electronics</option>
                    <option value="Mechanical" {{ old('category', $transfer->category) == 'Mechanical' ? 'selected' : '' }}>Mechanical</option>
                    <option value="Plastic" {{ old('category', $transfer->category) == 'Plastic' ? 'selected' : '' }}>Plastic</option>
                    <option value="Metal" {{ old('category', $transfer->category) == 'Metal' ? 'selected' : '' }}>Metal</option>
                    <option value="Other" {{ old('category', $transfer->category) == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('category') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Delivery Date -->
            <div>
                <label for="date_delivery_scheduled" class="block text-sm font-medium text-gray-700 mb-1.5">Delivery Date *</label>
                <input type="date" name="date_delivery_scheduled" value="{{ old('date_delivery_scheduled', $transfer->date_delivery_scheduled->format('Y-m-d')) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('date_delivery_scheduled') border-red-500 @enderror">
                @error('date_delivery_scheduled') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Received By -->
            <div>
                <label for="received_by_user_id" class="block text-sm font-medium text-gray-700 mb-1.5">Received By *</label>
                <select name="received_by_user_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('received_by_user_id') border-red-500 @enderror">
                    <option value="">— Select Staff —</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('received_by_user_id', $transfer->received_by_user_id ?? ($transfer->receivedBy->id ?? '')) == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->department ?? '—' }})
                        </option>
                    @endforeach
                </select>
                @error('received_by_user_id') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Date Received -->
            <div>
                <label for="date_received" class="block text-sm font-medium text-gray-700 mb-1.5">Date Received *</label>
                <input type="date" name="date_received" value="{{ old('date_received', $transfer->date_received->format('Y-m-d')) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('date_received') border-red-500 @enderror">
                @error('date_received') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Time Received -->
            <div>
                <label for="time_received" class="block text-sm font-medium text-gray-700 mb-1.5">Time Received *</label>
                <input type="time" name="time_received" value="{{ old('time_received', $transfer->time_received) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('time_received') border-red-500 @enderror">
                @error('time_received') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Quantity Received -->
            <div>
                <label for="qty_received" class="block text-sm font-medium text-gray-700 mb-1.5">Quantity Received *</label>
                <input type="number" name="qty_received" value="{{ old('qty_received', $transfer->qty_received) }}" min="1" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('qty_received') border-red-500 @enderror">
                @error('qty_received') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Remarks -->
            <div class="md:col-span-2">
                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1.5">Remarks</label>
                <textarea name="remarks" rows="3" placeholder="Any additional notes..."
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('remarks') border-red-500 @enderror">{{ old('remarks', $transfer->remarks) }}</textarea>
                @error('remarks') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

        </div>

        <div class="flex justify-end gap-4 pt-6 border-t">
            <a href="{{ route('transfers.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">Update Transfer</button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('ptt_number');
    const suggestionContainer = document.getElementById('ptt_number_suggestion');
    const suggestionText = document.getElementById('ptt_number_suggestion_text');
    const useBtn = document.getElementById('ptt_number_use_suggestion');
    const regenBtn = document.getElementById('ptt_number_regenerate');

    async function fetchSuggestion() {
        try {
            const resp = await fetch('/api/sequences/next?type=ptt');
            if (!resp.ok) return;
            const data = await resp.json();
            if (data.ptt_number) {
                suggestionText.textContent = data.ptt_number;
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

    // Fetch a suggestion on load (do not auto-overwrite existing value)
    fetchSuggestion();
});
</script>

@endsection