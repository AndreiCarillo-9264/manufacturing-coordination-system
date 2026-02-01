@extends('layouts.app')

@section('title', 'Transfer Details')
@section('page-icon') <i class="fas fa-arrow-right-arrow-left"></i> @endsection
@section('page-title', 'Transfer Details')
@section('page-description', 'View transfer information - ' . $transfer->ptt_number)

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="p-6 border-b bg-gray-50 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">{{ $transfer->ptt_number }}</h3>
            <p class="text-sm text-gray-600 mt-1">Production Transfer Details</p>
        </div>
        <div class="text-right">
            <span class="inline-block px-3 py-1 rounded-full text-sm font-medium
                @if($transfer->status === 'complete')
                    bg-green-100 text-green-800
                @else
                    bg-yellow-100 text-yellow-800
                @endif">
                {{ ucfirst($transfer->status) }}
            </span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="border-l-4 border-blue-500 pl-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide">PTT Number</p>
                <p class="text-lg font-semibold text-gray-800">{{ $transfer->ptt_number }}</p>
            </div>
            <div class="border-l-4 border-blue-500 pl-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Job Order</p>
                <p class="text-lg font-semibold text-gray-800">{{ $transfer->jobOrder->jo_number }}</p>
            </div>
            <div class="border-l-4 border-blue-500 pl-4">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Status</p>
                <p class="text-lg font-semibold text-gray-800">{{ ucfirst($transfer->status) }}</p>
            </div>
        </div>

        <!-- Production Information -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-semibold text-gray-800 mb-3">Production Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Product</p>
                    <p class="text-gray-800">{{ $transfer->product->product_code }} - {{ $transfer->product->model_name }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Unit</p>
                    <p class="text-gray-800">{{ $transfer->product->uom ?? 'pcs' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Section</p>
                    <p class="text-gray-800">{{ $transfer->section }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Category</p>
                    <p class="text-gray-800">{{ $transfer->category }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Grade</p>
                    <p class="text-gray-800">{{ $transfer->grade ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Dimension</p>
                    <p class="text-gray-800">{{ $transfer->dimension ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Quantity Information -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-semibold text-gray-800 mb-3">Quantity Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Quantity Produced</p>
                    <p class="text-lg font-semibold text-gray-800">{{ $transfer->qty_transferred }} units</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Quantity Received</p>
                    <p class="text-lg font-semibold text-gray-800">{{ $transfer->qty_received }} units</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Difference</p>
                    <p class="text-lg font-semibold {{ $transfer->qty_received === $transfer->qty_transferred ? 'text-green-600' : 'text-yellow-600' }}">
                        {{ $transfer->qty_received - $transfer->qty_transferred }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Timeline Information -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-semibold text-gray-800 mb-3">Timeline</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Date Transferred</p>
                    <p class="text-gray-800">{{ $transfer->date_transferred?->format('M d, Y') ?? '—' }}</p>
                    <p class="text-sm text-gray-600">{{ $transfer->time_transferred?->format('H:i') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Date Received</p>
                    <p class="text-gray-800">{{ $transfer->date_received?->format('M d, Y') ?? '—' }}</p>
                    <p class="text-sm text-gray-600">{{ $transfer->time_received?->format('H:i') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Delivery Date</p>
                    <p class="text-gray-800">{{ $transfer->date_delivery_scheduled?->format('M d, Y') ?? '—' }}</p>
                    <p class="text-sm text-gray-600">JIT: {{ $transfer->jit_days }} days</p>
                </div>
            </div>
        </div>

        <!-- Financial Information -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-semibold text-gray-800 mb-3">Financial Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Unit Price</p>
                    <p class="text-lg font-semibold text-gray-800">₱{{ number_format($transfer->unit_selling_price ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Total Amount</p>
                    <p class="text-lg font-semibold text-gray-800">₱{{ number_format($transfer->total_amount ?? 0, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Week Number</p>
                    <p class="text-lg font-semibold text-gray-800">Week {{ $transfer->week_number ?? '—' }}</p>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="border-l-4 border-blue-500 pl-4 py-2">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Received By</p>
            <p class="text-gray-800">{{ $transfer->receivedBy?->name ?? '—' }} ({{ $transfer->receivedBy?->department ?? '—' }})</p>
        </div>

        <!-- Remarks -->
        @if($transfer->remarks)
        <div class="border-l-4 border-yellow-500 pl-4 py-2">
            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Remarks</p>
            <p class="text-gray-700">{{ $transfer->remarks }}</p>
        </div>
        @endif

        <!-- Meta Information -->
        <div class="text-xs text-gray-500 border-t pt-4 space-y-1">
            <p><strong>Created:</strong> {{ $transfer->created_at->format('M d, Y H:i') }}</p>
            <p><strong>Last Updated:</strong> {{ $transfer->updated_at->format('M d, Y H:i') }}</p>
        </div>

    </div>

    <!-- Actions -->
    <div class="p-6 border-t bg-gray-50 flex justify-end gap-4">
        <a href="{{ route('transfers.index') }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition">
            Back to List
        </a>
        <a href="{{ route('transfers.edit', $transfer) }}" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
            Edit Transfer
        </a>
        <a href="{{ route('transfers.index') }}" class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition flex items-center gap-2">
            <i class="fas fa-check"></i> Continue
        </a>
    </div>
</div>
@endsection
