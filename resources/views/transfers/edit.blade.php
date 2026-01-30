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

            <!-- PTT Number (Read-only) -->
            <div>
                <label for="ptt_number" class="block text-sm font-medium text-gray-700 mb-1.5">PTT Number</label>
                <input type="text" value="{{ $transfer->ptt_number }}" readonly
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-gray-100 text-gray-600">
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
                <label for="transfer_time" class="block text-sm font-medium text-gray-700 mb-1.5">Transfer Time *</label>
                <input type="time" name="transfer_time" value="{{ old('transfer_time', $transfer->transfer_time) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('transfer_time') border-red-500 @enderror">
                @error('transfer_time') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Quantity -->
            <div>
                <label for="qty" class="block text-sm font-medium text-gray-700 mb-1.5">Quantity Produced *</label>
                <input type="number" name="qty" value="{{ old('qty', $transfer->qty) }}" min="0" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('qty') border-red-500 @enderror">
                @error('qty') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
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
                <label for="delivery_date" class="block text-sm font-medium text-gray-700 mb-1.5">Delivery Date *</label>
                <input type="date" name="delivery_date" value="{{ old('delivery_date', $transfer->delivery_date->format('Y-m-d')) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('delivery_date') border-red-500 @enderror">
                @error('delivery_date') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
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
                <input type="number" name="qty_received" value="{{ old('qty_received', $transfer->qty_received) }}" min="0" required
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
@endsection