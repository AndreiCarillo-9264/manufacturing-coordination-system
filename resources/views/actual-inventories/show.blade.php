@extends('layouts.app')

@section('title', 'Actual Inventory Details')
@section('page-icon') <i class="fas fa-boxes"></i> @endsection
@section('page-title', 'Inventory Record: ' . ($actualInventory->job_order?->jo_number ?? 'N/A'))
@section('page-description', 'Detailed view of actual inventory count')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">

    <!-- Header -->
    <div class="p-6 border-b bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-xl font-semibold text-gray-800">{{ $actualInventory->job_order?->jo_number ?? 'Inventory Record' }}</h3>
            <p class="text-sm text-gray-600 mt-1">Count Date: {{ $actualInventory->count_date?->format('M d, Y') ?? 'N/A' }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @can('update', $actualInventory)
                <a href="{{ route('actual-inventories.edit', $actualInventory) }}" 
                   class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium shadow-sm transition text-sm">
                    <i class="fas fa-edit mr-1.5"></i> Edit
                </a>
            @endcan
            @can('delete', $actualInventory)
                <form action="{{ route('actual-inventories.destroy', $actualInventory) }}" 
                      method="POST" class="inline" onsubmit="return confirm('Delete this record?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium shadow-sm transition text-sm">
                        <i class="fas fa-trash mr-1.5"></i> Delete
                    </button>
                </form>
            @endcan
        </div>
    </div>

    <!-- Main Content -->
    <div class="p-6 space-y-10">

        <!-- Related Job Order -->
        <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Related Job Order</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Job Order Number</label>
                    <p class="mt-1.5 text-gray-900 font-mono">
                        @if($actualInventory->job_order)
                            <a href="{{ route('job-orders.show', $actualInventory->job_order) }}" 
                               class="text-blue-600 hover:underline">{{ $actualInventory->job_order->jo_number }}</a>
                        @else
                            N/A
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Customer</label>
                    <p class="mt-1.5 text-gray-900">{{ $actualInventory->product?->customer_name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Status</label>
                    <p class="mt-1.5">
                        @if($actualInventory->job_order)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium 
                                {{ $actualInventory->job_order->status === 'completed' ? 'bg-green-100 text-green-800' : ($actualInventory->job_order->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ ucfirst(str_replace('_', ' ', $actualInventory->job_order->status)) }}
                            </span>
                        @else
                            N/A
                        @endif
                    </p>
                </div>
            </div>
        </div>

        <!-- Count Information -->
        <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Count Details</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Count Date</label>
                    <p class="mt-1.5 text-gray-900">{{ $actualInventory->count_date?->format('M d, Y') ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Count By</label>
                    <p class="mt-1.5 text-gray-900">{{ $actualInventory->count_by ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Verified By</label>
                    <p class="mt-1.5 text-gray-900">{{ $actualInventory->verified_by ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Inventory Count Summary -->
        <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Inventory Summary</h4>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
                    <label class="block text-xs font-medium text-blue-700 uppercase tracking-wide">Beginning Balance</label>
                    <p class="mt-2 text-2xl font-bold text-blue-900">{{ $actualInventory->beg_balance ?? 0 }}</p>
                    <p class="text-xs text-blue-600 mt-1">Starting inventory</p>
                </div>
                <div class="bg-purple-50 border border-purple-200 p-4 rounded-lg">
                    <label class="block text-xs font-medium text-purple-700 uppercase tracking-wide">Receipts</label>
                    <p class="mt-2 text-2xl font-bold text-purple-900">{{ $actualInventory->receipts ?? 0 }}</p>
                    <p class="text-xs text-purple-600 mt-1">Goods received</p>
                </div>
                <div class="bg-orange-50 border border-orange-200 p-4 rounded-lg">
                    <label class="block text-xs font-medium text-orange-700 uppercase tracking-wide">Issuances</label>
                    <p class="mt-2 text-2xl font-bold text-orange-900">{{ $actualInventory->issuances ?? 0 }}</p>
                    <p class="text-xs text-orange-600 mt-1">Goods issued</p>
                </div>
                <div class="bg-green-50 border border-green-200 p-4 rounded-lg">
                    <label class="block text-xs font-medium text-green-700 uppercase tracking-wide">Ending Balance</label>
                    <p class="mt-2 text-2xl font-bold text-green-900">{{ $actualInventory->ending_balance ?? 0 }}</p>
                    <p class="text-xs text-green-600 mt-1">Final count</p>
                </div>
            </div>
        </div>

        <!-- Variance Information -->
        <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Variance & Adjustments</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Physical Count</label>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $actualInventory->phys_count ?? 0 }}</p>
                    <p class="text-xs text-gray-600 mt-1">Physically counted items</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">System Count</label>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $actualInventory->system_count ?? 0 }}</p>
                    <p class="text-xs text-gray-600 mt-1">System calculated count</p>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Variance</label>
                    <p class="mt-2 text-2xl font-bold {{ ($actualInventory->variance ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ ($actualInventory->variance ?? 0) >= 0 ? '+' : '' }}{{ $actualInventory->variance ?? 0 }}
                    </p>
                    <p class="text-xs text-gray-600 mt-1">Difference</p>
                </div>
            </div>
        </div>

        <!-- Remarks -->
        <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Additional Notes</h4>
            <div class="bg-gray-50 p-4 rounded-lg">
                <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Remarks</label>
                <p class="text-gray-800 whitespace-pre-line">{{ $actualInventory->remarks ?: 'No remarks.' }}</p>
            </div>
        </div>

    </div>

    <!-- Footer Actions -->
    <div class="p-6 border-t bg-gray-50 flex justify-end gap-3">
        <a href="{{ route('actual-inventories.index') }}" 
           class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-100 transition flex items-center gap-2">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
        <a href="{{ route('actual-inventories.index') }}" 
           class="px-6 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition flex items-center gap-2">
            <i class="fas fa-check"></i> Continue
        </a>
    </div>

</div>
@endsection
