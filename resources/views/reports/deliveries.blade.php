@extends('layouts.app')

@section('title', 'Delivery Report')
@section('page-icon') <i class="fas fa-file-alt"></i> @endsection
@section('page-title', 'Delivery Report')
@section('page-description', 'Export and view delivery reports')

@section('content')

<div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-800 mb-6">Report Filters</h3>
    
    <form action="{{ route('reports.deliveries') }}" method="GET" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>

            <!-- Date To -->
            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
            </div>
        </div>

        <div class="flex gap-3 pt-4 items-center">
            <a href="{{ route('reports.deliveries') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-md text-sm font-medium transition">
                <i class="fas fa-redo mr-2"></i>Reset
            </a> 
            <form action="{{ route('reports.deliveries.pdf') }}" method="GET" class="flex gap-2" style="margin-left: auto;">
                @foreach(request()->query() as $key => $value)
                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium transition">
                    <i class="fas fa-download mr-2"></i>Download PDF
                </button>
            </form>
        </div>
    </form>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg border border-blue-200 p-4">
        <div class="text-sm text-gray-600 font-medium mb-1">Total Quantity</div>
        <div class="text-2xl font-bold text-blue-700">{{ number_format($totalQty) }}</div>
    </div>
    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg border border-green-200 p-4">
        <div class="text-sm text-gray-600 font-medium mb-1">Delivered Qty</div>
        <div class="text-2xl font-bold text-green-700">{{ number_format($deliveredQty) }}</div>
    </div>
    <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg border border-amber-200 p-4">
        <div class="text-sm text-gray-600 font-medium mb-1">Delivery Rate</div>
        <div class="text-2xl font-bold text-amber-700">{{ $totalQty > 0 ? round(($deliveredQty / $totalQty) * 100, 2) : 0 }}%</div>
    </div>
</div>

<!-- Data Table -->
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">DS Code</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">UOM</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Delivery Date</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">J.O. Number</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($deliveries as $delivery)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-sm font-semibold text-blue-600">{{ $delivery->ds_code ?? '—' }}</td>
                        <td class="px-6 py-3 text-sm text-gray-800">{{ $delivery->product->model_name ?? $delivery->product->product_code }}</td>
                        <td class="px-6 py-3 text-sm font-medium text-gray-700">{{ number_format($delivery->quantity) }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ $delivery->uom }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ $delivery->delivery_date?->format('M d, Y') ?? '—' }}</td>
                        <td class="px-6 py-3 text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                {{ $delivery->ds_status === 'DELIVERED' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $delivery->ds_status === 'BACKLOG' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $delivery->ds_status === 'SCHEDULED' ? 'bg-blue-100 text-blue-800' : '' }}">
                                {{ $delivery->ds_status }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ $delivery->jobOrder?->jo_number ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-inbox text-gray-400 text-3xl mb-3"></i>
                                <p class="text-gray-600 font-medium">No delivery data found</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
