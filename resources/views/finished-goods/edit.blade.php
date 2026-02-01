{{-- resources/views/finished-goods/edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Manage Finished Good')
@section('page-title', 'Edit Finished Good')
@section('page-description', 'Update inventory counts and remarks')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('finished-goods.update', $finishedGood) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Product</label>
                <input type="text" value="{{ $finishedGood->product->product_code }} - {{ $finishedGood->product->model_name }}" 
                       class="w-full px-3 py-2 border rounded-lg bg-gray-100" disabled readonly>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-gray-700 font-bold mb-2">Theoretical Ending Count</label>
                    <input type="number" value="{{ $finishedGood->qty_theoretical_ending }}" 
                           class="w-full px-3 py-2 border rounded-lg bg-gray-100" disabled readonly>
                </div>

                <div>
                    <label class="block text-gray-700 font-bold mb-2">Actual Ending Count *</label>
                    <input type="number" name="qty_actual_ending" value="{{ old('qty_actual_ending', $finishedGood->qty_actual_ending) }}" 
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" min="0" required>
                    @error('qty_actual_ending') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Buffer Stocks *</label>
                <input type="number" name="qty_buffer_stock" value="{{ old('qty_buffer_stock', $finishedGood->qty_buffer_stock) }}" 
                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500" min="0" required>
                @error('qty_buffer_stock') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Remarks</label>
                <textarea name="remarks" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500 h-20">{{ old('remarks', $finishedGood->remarks) }}</textarea>
                @error('remarks') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('finished-goods.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">Update Record</button>
            </div>
        </form>
    </div>
</div>
@endsection