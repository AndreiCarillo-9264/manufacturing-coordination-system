@extends('layouts.app')

@section('title', 'Create Transfer')
@section('page-icon') <i class="fas fa-arrow-right-arrow-left"></i> @endsection
@section('page-title', 'Create New Transfer')
@section('page-description', 'Record production output as transfer')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden max-w-4xl mx-auto">
    <div class="p-6 border-b bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800">New Transfer Form</h3>
        <p class="text-sm text-gray-600 mt-1">Record production output. Fields marked with * are required.</p>
    </div>

    <form action="{{ route('transfers.store') }}" method="POST" class="p-6 space-y-8">
        @csrf

        <!-- Info Banner -->
        <div class="bg-blue-50 border border-blue-100 p-4 rounded-lg text-sm text-blue-800 space-y-1">
            <p><strong>PTT Number</strong> will be automatically generated (PTT-YYYY-NNNN)</p>
            <p><strong>Job Order</strong> must be in "approved" status</p>
            <p><strong>Week Number</strong> will be calculated from Date Transferred</p>
            <p><strong>Total Amount</strong> will be calculated automatically</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Job Order -->
            <div>
                <label for="jo_id" class="block text-sm font-medium text-gray-700 mb-1.5">Job Order *</label>
                <select id="jo_id" name="jo_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('jo_id') border-red-500 @enderror">
                    <option value="">— Select Approved Job Order —</option>
                    @foreach($jobOrders as $jo)
                        <option value="{{ $jo->id }}" {{ old('jo_id') == $jo->id ? 'selected' : '' }}>
                            {{ $jo->jo_number }} - {{ $jo->product->model_name }} (Customer: {{ $jo->product->customer_name ?? 'N/A' }} | Qty: {{ $jo->qty }})
                        </option>
                    @endforeach
                </select>
                @error('jo_id') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Section -->
            <div>
                <label for="section" class="block text-sm font-medium text-gray-700 mb-1.5">Section *</label>
                <select name="section" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('section') border-red-500 @enderror">
                    <option value="">— Select Section —</option>
                    <option value="Assembly" {{ old('section') == 'Assembly' ? 'selected' : '' }}>Assembly</option>
                    <option value="QC" {{ old('section') == 'QC' ? 'selected' : '' }}>QC (Quality Control)</option>
                    <option value="Packaging" {{ old('section') == 'Packaging' ? 'selected' : '' }}>Packaging</option>
                    <option value="Inspection" {{ old('section') == 'Inspection' ? 'selected' : '' }}>Inspection</option>
                </select>
                @error('section') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Date Transferred -->
            <div>
                <label for="date_transferred" class="block text-sm font-medium text-gray-700 mb-1.5">Date Transferred *</label>
                <input type="date" name="date_transferred" value="{{ old('date_transferred', now()->format('Y-m-d')) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('date_transferred') border-red-500 @enderror">
                @error('date_transferred') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Transfer Time -->
            <div>
                <label for="transfer_time" class="block text-sm font-medium text-gray-700 mb-1.5">Transfer Time *</label>
                <input type="time" name="transfer_time" value="{{ old('transfer_time') }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('transfer_time') border-red-500 @enderror">
                @error('transfer_time') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Quantity -->
            <div>
                <label for="qty" class="block text-sm font-medium text-gray-700 mb-1.5">Quantity Produced *</label>
                <input type="number" name="qty" value="{{ old('qty', 0) }}" min="0" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('qty') border-red-500 @enderror">
                @error('qty') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Grade (Optional) -->
            <div>
                <label for="grade" class="block text-sm font-medium text-gray-700 mb-1.5">Grade</label>
                <input type="text" name="grade" value="{{ old('grade') }}" placeholder="e.g., A, B, C"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('grade') border-red-500 @enderror">
                @error('grade') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Dimension (Optional) -->
            <div>
                <label for="dimension" class="block text-sm font-medium text-gray-700 mb-1.5">Dimension</label>
                <input type="text" name="dimension" value="{{ old('dimension') }}" placeholder="e.g., 10x10x5 cm"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('dimension') border-red-500 @enderror">
                @error('dimension') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Category -->
            <div>
                <label for="category" class="block text-sm font-medium text-gray-700 mb-1.5">Category *</label>
                <select name="category" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('category') border-red-500 @enderror">
                    <option value="">— Select Category —</option>
                    <option value="Electronics" {{ old('category') == 'Electronics' ? 'selected' : '' }}>Electronics</option>
                    <option value="Mechanical" {{ old('category') == 'Mechanical' ? 'selected' : '' }}>Mechanical</option>
                    <option value="Plastic" {{ old('category') == 'Plastic' ? 'selected' : '' }}>Plastic</option>
                    <option value="Metal" {{ old('category') == 'Metal' ? 'selected' : '' }}>Metal</option>
                    <option value="Other" {{ old('category') == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('category') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Delivery Date -->
            <div>
                <label for="delivery_date" class="block text-sm font-medium text-gray-700 mb-1.5">Delivery Date *</label>
                <input type="date" name="delivery_date" value="{{ old('delivery_date') }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('delivery_date') border-red-500 @enderror">
                @error('delivery_date') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Received By -->
            <div>
                <label for="received_by_user_id" class="block text-sm font-medium text-gray-700 mb-1.5">Received By *</label>
                <select name="received_by_user_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('received_by_user_id') border-red-500 @enderror">
                    <option value="">— Select Staff —</option>
                    @forelse($jobOrders as $jo)
                        @if($jo->encodedBy)
                            <option value="{{ $jo->encodedBy->id }}" {{ old('received_by_user_id') == $jo->encodedBy->id ? 'selected' : '' }}>
                                {{ $jo->encodedBy->name }} ({{ $jo->encodedBy->department }})
                            </option>
                        @endif
                    @empty
                    @endforelse
                </select>
                @error('received_by_user_id') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Date Received -->
            <div>
                <label for="date_received" class="block text-sm font-medium text-gray-700 mb-1.5">Date Received *</label>
                <input type="date" name="date_received" value="{{ old('date_received', now()->format('Y-m-d')) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('date_received') border-red-500 @enderror">
                @error('date_received') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Time Received -->
            <div>
                <label for="time_received" class="block text-sm font-medium text-gray-700 mb-1.5">Time Received *</label>
                <input type="time" name="time_received" value="{{ old('time_received') }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('time_received') border-red-500 @enderror">
                @error('time_received') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Quantity Received -->
            <div>
                <label for="qty_received" class="block text-sm font-medium text-gray-700 mb-1.5">Quantity Received *</label>
                <input type="number" name="qty_received" value="{{ old('qty_received', 0) }}" min="0" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('qty_received') border-red-500 @enderror">
                @error('qty_received') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Remarks -->
            <div class="md:col-span-2">
                <label for="remarks" class="block text-sm font-medium text-gray-700 mb-1.5">Remarks</label>
                <textarea name="remarks" rows="3" placeholder="Any additional notes..."
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('remarks') border-red-500 @enderror">{{ old('remarks') }}</textarea>
                @error('remarks') <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

        </div>

        <div class="flex justify-end gap-4 pt-6 border-t">
            <a href="{{ route('transfers.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">Create Transfer</button>
        </div>
    </form>
</div>
@endsection