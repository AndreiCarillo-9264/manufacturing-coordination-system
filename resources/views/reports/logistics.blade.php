@extends('layouts.app')

@section('title', 'Logistics Report')
@section('page-icon') <i class="fas fa-file-alt"></i> @endsection
@section('page-title', 'Logistics Report')
@section('page-description', 'Export and view logistics/endorsement reports')

@section('content')

<div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-800 mb-6">Report Filters</h3>
    
    <form action="{{ route('reports.logistics') }}" method="GET" class="space-y-4">
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
            <a href="{{ route('reports.logistics') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-md text-sm font-medium transition">
                <i class="fas fa-redo mr-2"></i>Reset
            </a> 
            <form action="{{ route('reports.logistics.pdf') }}" method="GET" class="flex gap-2" style="margin-left: auto;">
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
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg border border-blue-200 p-4">
        <div class="text-sm text-gray-600 font-medium mb-1">Total Quantity</div>
        <div class="text-2xl font-bold text-blue-700">{{ number_format($totalQty) }}</div>
    </div>
    <div class="bg-gradient-to-br from-yellow-50 to-amber-50 rounded-lg border border-yellow-200 p-4">
        <div class="text-sm text-gray-600 font-medium mb-1">Pending/New</div>
        <div class="text-2xl font-bold text-yellow-700">{{ number_format($endorsements->where('status', 'pending')->sum('quantity')) }}</div>
    </div>
    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg border border-green-200 p-4">
        <div class="text-sm text-gray-600 font-medium mb-1">Approved Qty</div>
        <div class="text-2xl font-bold text-green-700">{{ number_format($approvedQty) }}</div>
    </div>
    <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-lg border border-purple-200 p-4">
        <div class="text-sm text-gray-600 font-medium mb-1">Completed Qty</div>
        <div class="text-2xl font-bold text-purple-700">{{ number_format($completedQty) }}</div>
    </div>
</div>

<!-- Data Table -->
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">ETL Code</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">UOM</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($endorsements as $endorsement)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-sm font-semibold text-blue-600">{{ $endorsement->etl_code }}</td>
                        <td class="px-6 py-3 text-sm text-gray-800">{{ $endorsement->model_name ?? $endorsement->product_code ?? '—' }}</td>
                        <td class="px-6 py-3 text-sm font-medium text-gray-700">{{ number_format($endorsement->quantity) }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ $endorsement->uom }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ $endorsement->customer_name ?? '—' }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ $endorsement->date?->format('M d, Y') ?? '—' }}</td>
                        <td class="px-6 py-3 text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold
                                {{ $endorsement->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $endorsement->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $endorsement->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $endorsement->status === 'completed' ? 'bg-purple-100 text-purple-800' : '' }}">
                                {{ ucfirst($endorsement->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-inbox text-gray-400 text-3xl mb-3"></i>
                                <p class="text-gray-600 font-medium">No logistics data found</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
