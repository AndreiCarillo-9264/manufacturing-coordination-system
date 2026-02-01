@extends('layouts.app')

@section('title', 'Delivery Schedule - ' . $deliverySchedule->delivery_code)
@section('page-icon') <i class="fas fa-truck"></i> @endsection
@section('page-title', 'Delivery Schedule Details')
@section('page-description', $deliverySchedule->delivery_code)

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <!-- Header -->
    <div class="p-6 border-b bg-gray-50 flex items-center justify-between">
        <div>
            <h3 class="text-2xl font-bold text-gray-800">{{ $deliverySchedule->delivery_code }}</h3>
            <p class="text-sm text-gray-600 mt-1">Delivery Schedule Details</p>
        </div>
        <div>
            <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold
                @if($deliverySchedule->status === 'delivered')
                    bg-green-100 text-green-800
                @elseif($deliverySchedule->status === 'urgent')
                    bg-red-100 text-red-800
                @elseif($deliverySchedule->status === 'backlog')
                    bg-yellow-100 text-yellow-800
                @else
                    bg-blue-100 text-blue-800
                @endif">
                {{ ucfirst($deliverySchedule->status) }}
            </span>
        </div>
    </div>

    <!-- Content -->
    <div class="p-6 space-y-6">

        <!-- Basic Information Section -->
        <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
            <h4 class="font-semibold text-gray-800 mb-4">Basic Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Delivery Code</p>
                    <p class="text-lg font-semibold text-gray-800">{{ $deliverySchedule->delivery_code }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Job Order</p>
                    <p class="text-lg font-semibold text-gray-800">
                        <a href="{{ route('job-orders.show', $deliverySchedule->jobOrder) }}" class="text-blue-600 hover:text-blue-800">
                            {{ $deliverySchedule->jobOrder->jo_number }}
                        </a>
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">PO Number</p>
                    <p class="text-lg font-semibold text-gray-800">{{ $deliverySchedule->po_number ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Status</p>
                    <p class="text-lg font-semibold text-gray-800">{{ ucfirst($deliverySchedule->status) }}</p>
                </div>
            </div>
        </div>

        <!-- Product Information Section -->
        <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
            <h4 class="font-semibold text-gray-800 mb-4">Product Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Product Code</p>
                    <p class="text-gray-800">{{ $deliverySchedule->product->product_code }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Product Name</p>
                    <p class="text-gray-800">{{ $deliverySchedule->product->model_name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Customer</p>
                    <p class="text-gray-800">{{ $deliverySchedule->product->customer ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Unit Price</p>
                    <p class="text-gray-800">₱{{ number_format($deliverySchedule->product->selling_price ?? 0, 2) }}</p>
                </div>
            </div>
        </div>

        <!-- Delivery Details Section -->
        <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
            <h4 class="font-semibold text-gray-800 mb-4">Delivery Details</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Quantity</p>
                    <p class="text-lg font-semibold text-gray-800">{{ number_format($deliverySchedule->qty_scheduled ?? 0) }} {{ $deliverySchedule->uom ?? $deliverySchedule->product->uom ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Delivery Date</p>
                    <p class="text-lg font-semibold text-gray-800">{{ $deliverySchedule->delivery_date?->format('M d, Y') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Week Number</p>
                    <p class="text-lg font-semibold text-gray-800">Week {{ $deliverySchedule->week_number ?? '—' }}</p>
                </div>
            </div>
        </div>

        <!-- Stock Information Section -->
        <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
            <h4 class="font-semibold text-gray-800 mb-4">Stock Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Finished Good Stocks</p>
                    <p class="text-lg font-semibold text-gray-800">{{ $deliverySchedule->fg_stocks ?? '—' }} units</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Buffer Stocks</p>
                    <p class="text-lg font-semibold text-gray-800">{{ $deliverySchedule->buffer_stocks ?? '—' }} units</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Delivered (DSD)</p>
                    <p class="text-lg font-semibold text-gray-800">{{ $deliverySchedule->delivered_dsd ?? '—' }} units</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-1">Backlog</p>
                    <p class="text-lg font-semibold text-gray-800">{{ $deliverySchedule->backlog ?? '—' }} units</p>
                </div>
            </div>
        </div>

        <!-- Remarks Section -->
        @if($deliverySchedule->remarks)
        <div class="border-l-4 border-yellow-500 bg-yellow-50 p-4 rounded-r-lg">
            <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide mb-2">Remarks</p>
            <p class="text-gray-800">{{ $deliverySchedule->remarks }}</p>
        </div>
        @endif

        <!-- Metadata -->
        <div class="text-xs text-gray-500 border-t pt-4 space-y-1">
            <p><strong>Created:</strong> {{ $deliverySchedule->created_at->format('M d, Y \a\t H:i') }}</p>
            <p><strong>Last Updated:</strong> {{ $deliverySchedule->updated_at->format('M d, Y \a\t H:i') }}</p>
        </div>

    </div>

    <!-- Footer Actions -->
    <div class="p-6 border-t bg-gray-50 flex justify-end gap-3">
        <a href="{{ route('delivery-schedules.index') }}" 
           class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
        <a href="{{ route('delivery-schedules.edit', $deliverySchedule) }}" 
           class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center gap-2">
            <i class="fas fa-edit"></i> Edit Schedule
        </a>
        <a href="{{ route('delivery-schedules.index') }}" 
           class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition flex items-center gap-2">
            <i class="fas fa-check"></i> Continue
        </a>
    </div>

</div>
@endsection
