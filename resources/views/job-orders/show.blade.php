@extends('layouts.app')

@section('title', 'Job Order Details')
@section('page-icon') <i class="fas fa-cube"></i> @endsection
@section('page-title', 'Job Order: ' . $jobOrder->jo_number)
@section('page-description', 'Detailed view of the job order')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">

    <!-- Header -->
    <div class="p-6 border-b bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-xl font-semibold text-gray-800">{{ $jobOrder->jo_number }}</h3>
            <p class="text-sm text-gray-600 mt-1">
                Product: {{ $jobOrder->product->model_name ?? 'N/A' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            @can('update', $jobOrder)
                <a href="{{ route('job-orders.edit', $jobOrder) }}" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition text-sm">
                    <i class="fas fa-edit mr-1.5"></i> Edit
                </a>
            @endcan
            @can('delete', $jobOrder)
                <form action="{{ route('job-orders.destroy', $jobOrder) }}" method="POST" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition text-sm" onclick="return confirm('Delete this job order?')">
                        <i class="fas fa-trash mr-1.5"></i> Delete
                    </button>
                </form>
            @endcan
            @if(session('success'))
            <div class="flex gap-3 ml-auto">
                <a href="{{ route('job-orders.index') }}" class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition text-sm">
                    <i class="fas fa-check mr-1.5"></i> Continue
                </a>
                <a href="{{ route('job-orders.create') }}" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium transition text-sm">
                    <i class="fas fa-plus mr-1.5"></i> Create Another
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Main Content -->
    <div class="p-6 space-y-10">

        <!-- Basic Information -->
        <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Basic Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">PO Number</label>
                    <p class="mt-1.5 text-gray-900">{{ $jobOrder->po_number ?? '—' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Date Needed</label>
                    <p class="mt-1.5 text-gray-900">{{ $jobOrder->date_needed?->format('M d, Y') ?? '—' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Product</label>
                    <p class="mt-1.5 text-gray-900">{{ $jobOrder->product->model_name ?? '—' }} ({{ $jobOrder->product->product_code ?? '—' }})</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Quantity</label>
                    <p class="mt-1.5 text-gray-900">{{ $jobOrder->qty ?? '—' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">UOM</label>
                    <p class="mt-1.5 text-gray-900">{{ $jobOrder->uom ?? '—' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Week Number</label>
                    <p class="mt-1.5 text-gray-900">{{ $jobOrder->week_number ?? '—' }}</p>
                </div>
                <div class="md:col-span-3">
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Remarks</label>
                    <p class="mt-1.5 text-gray-800 whitespace-pre-line">{{ $jobOrder->remarks ?: 'No remarks.' }}</p>
                </div>
            </div>
        </div>

        <!-- Calculated / Advanced -->
        <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Calculated Details</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 p-6 rounded-xl border border-gray-200">
                <div>
                    <label class="block text-xs font-medium text-gray-600 uppercase tracking-wide">Total Amount</label>
                    <p class="mt-2 text-xl font-semibold text-gray-900">{{ number_format($jobOrder->total_amount, 2) }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 uppercase tracking-wide">JO Balance</label>
                    <p class="mt-2 text-xl font-semibold text-gray-900">{{ $jobOrder->jo_balance }}</p>
                </div>
                <!-- Add more if needed -->
            </div>
        </div>

        <!-- Related Transfers -->
        @if($jobOrder->transfers->count() > 0)
        <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Related Transfers</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th>Transfer ID</th>
                            <th>Quantity</th>
                            <th>Date</th>
                            <!-- Add more -->
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jobOrder->transfers as $transfer)
                        <tr>
                            <td>{{ $transfer->id }}</td>
                            <td>{{ $transfer->quantity ?? 'N/A' }}</td>
                            <td>{{ $transfer->created_at->format('M d, Y') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <!-- Related Delivery Schedules -->
        @if($jobOrder->deliverySchedules->count() > 0)
        <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Related Delivery Schedules</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th>Schedule ID</th>
                            <th>Date</th>
                            <th>Status</th>
                            <!-- Add more -->
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jobOrder->deliverySchedules as $schedule)
                        <tr>
                            <td>{{ $schedule->id }}</td>
                            <td>{{ $schedule->date ?? 'N/A' }}</td>
                            <td>{{ ucfirst($schedule->status) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection