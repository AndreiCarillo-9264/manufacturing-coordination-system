@extends('layouts.app')

@section('title', 'Job Orders Report')
@section('page-icon') <i class="fas fa-file-alt"></i> @endsection
@section('page-title', 'Job Orders Report')
@section('page-description', 'Export and view job order reports')

@section('content')

<div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-800 mb-6">Report Filters</h3>
    
    <form action="{{ route('reports.job-orders') }}" method="GET" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" id="status" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
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
            <!-- filters submitted by pressing Enter or via form submission -->
            <a href="{{ route('reports.job-orders') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-md text-sm font-medium transition">
                <i class="fas fa-redo mr-2"></i>Reset
            </a> 
            <form action="{{ route('reports.job-orders.pdf') }}" method="GET" class="flex gap-2" style="margin-left: auto;">
                @foreach(request()->query() as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                @endforeach
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-md text-sm font-medium transition">
                    <i class="fas fa-file-pdf mr-2"></i>Export PDF
                </button>
            </form>
        </div>
    </form>
</div>

<!-- Report Table -->
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">JO Number</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product / Model</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">UoM</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Needed</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($jobOrders as $jo)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $jo->jo_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $jo->po_number ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $jo->product->model_name ?? $jo->product->product_code ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $jo->product->customer ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">{{ number_format($jo->qty) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $jo->uom }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $jo->date_needed?->format('M d, Y') ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                            {{ $jo->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $jo->status === 'approved' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $jo->status === 'in_progress' ? 'bg-purple-100 text-purple-800' : '' }}
                            {{ $jo->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $jo->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ ucfirst($jo->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                        {{ currencySymbol($jo->product->currency ?? 'PHP') }}{{ number_format($jo->qty * $jo->product->selling_price, 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-10 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                        <p>No job orders found matching the criteria.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Summary Footer -->
    @if($jobOrders->count() > 0)
    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
        <div class="grid grid-cols-2 gap-8">
            <div>
                <p class="text-sm text-gray-600">Total Quantity</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalQty) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Total Amount</p>
                @if(isset($reportCurrency))
                <p class="text-2xl font-bold text-gray-900">{{ currencySymbol($reportCurrency) }}{{ number_format($totalAmount, 2) }}</p>
                @else
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalAmount, 2) }} <small class="text-gray-500">(Multiple Currencies)</small></p>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

@endsection
