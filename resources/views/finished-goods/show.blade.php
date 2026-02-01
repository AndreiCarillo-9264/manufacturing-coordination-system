@extends('layouts.app')

@section('title', 'Finished Good Details')
@section('page-icon') <i class="fas fa-cube"></i> @endsection
@section('page-title', 'Finished Good: ' . $finishedGood->product->product_code)
@section('page-description', 'Detailed view of finished goods inventory')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">

    <!-- Header -->
    <div class="p-6 border-b bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-xl font-semibold text-gray-800">{{ $finishedGood->product->product_code }}</h3>
            <p class="text-sm text-gray-600 mt-1">{{ $finishedGood->product->model_name ?? 'N/A' }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @can('update', $finishedGood)
                <a href="{{ route('finished-goods.edit', $finishedGood) }}" 
                   class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium shadow-sm transition text-sm">
                    <i class="fas fa-edit mr-1.5"></i> Edit
                </a>
            @endcan
        </div>
    </div>

    <!-- Main Content -->
    <div class="p-6 space-y-10">

        <!-- Product Information -->
        <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Product Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Product Code</label>
                    <p class="mt-1.5 text-gray-900 font-mono">{{ $finishedGood->product->product_code }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Model</label>
                    <p class="mt-1.5 text-gray-900">{{ $finishedGood->product->model_name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Customer</label>
                    <p class="mt-1.5 text-gray-900">{{ $finishedGood->product->customer ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Stock Summary -->
        <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Inventory Summary</h4>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
                    <label class="block text-xs font-medium text-blue-700 uppercase tracking-wide">In Quantity</label>
                    <p class="mt-2 text-2xl font-bold text-blue-900">{{ $finishedGood->qty_in ?? 0 }}</p>
                    <p class="text-xs text-blue-600 mt-1">From transfers</p>
                </div>
                <div class="bg-purple-50 border border-purple-200 p-4 rounded-lg">
                    <label class="block text-xs font-medium text-purple-700 uppercase tracking-wide">Out Quantity</label>
                    <p class="mt-2 text-2xl font-bold text-purple-900">{{ $finishedGood->qty_out ?? 0 }}</p>
                    <p class="text-xs text-purple-600 mt-1">Delivered out</p>
                </div>
                <div class="bg-green-50 border border-green-200 p-4 rounded-lg">
                    <label class="block text-xs font-medium text-green-700 uppercase tracking-wide">Theoretical End</label>
                    <p class="mt-2 text-2xl font-bold text-green-900">{{ $finishedGood->qty_theoretical_ending ?? 0 }}</p>
                    <p class="text-xs text-green-600 mt-1">Calculated count</p>
                </div>
                <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg">
                    <label class="block text-xs font-medium text-yellow-700 uppercase tracking-wide">Actual End</label>
                    <p class="mt-2 text-2xl font-bold text-yellow-900">{{ $finishedGood->qty_actual_ending ?? 0 }}</p>
                    <p class="text-xs text-yellow-600 mt-1">Physical count</p>
                </div>
            </div>
        </div>

        <!-- Variance Analysis -->
        <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Variance Analysis</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Variance</label>
                    <p class="mt-2 text-2xl font-bold {{ ($finishedGood->qty_variance ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ ($finishedGood->qty_variance ?? 0) >= 0 ? '+' : '' }}{{ $finishedGood->qty_variance ?? 0 }}
                    </p>
                    <p class="text-xs text-gray-600 mt-1">Difference between theoretical and actual</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Buffer Stocks</label>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $finishedGood->qty_buffer_stock ?? 0 }}</p>
                    <p class="text-xs text-gray-600 mt-1">Safety stock level</p>
                </div>
            </div>
        </div>

        <!-- Financial Summary -->
        <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Financial Summary</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">In Amount</label>
                    <p class="mt-1.5 text-gray-900 text-lg font-semibold">₱{{ number_format($finishedGood->amount_in ?? 0, 2) }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Out Amount</label>
                    <p class="mt-1.5 text-gray-900 text-lg font-semibold">₱{{ number_format($finishedGood->amount_out ?? 0, 2) }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">End Amount</label>
                    <p class="mt-1.5 text-gray-900 text-lg font-semibold">₱{{ number_format($finishedGood->amount_ending ?? 0, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Aging & Remarks -->
        <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Additional Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Last In Date</label>
                    <p class="mt-1.5 text-gray-900">{{ $finishedGood->date_last_in?->format('M d, Y') ?? 'Never' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Last Out Date</label>
                    <p class="mt-1.5 text-gray-900">{{ $finishedGood->date_oldest?->format('M d, Y') ?? 'Never' }}</p>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Remarks</label>
                    <p class="mt-1.5 text-gray-800 whitespace-pre-line">{{ $finishedGood->remarks ?: 'No remarks.' }}</p>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
