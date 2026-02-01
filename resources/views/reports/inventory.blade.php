@extends('layouts.app')

@section('title', 'Inventory Report')
@section('page-icon') <i class="fas fa-file-alt"></i> @endsection
@section('page-title', 'Inventory Report')
@section('page-description', 'Export and view inventory reports')

@section('content')

<div class="bg-white rounded-xl shadow-md border border-gray-100 p-6 mb-8">
    <h3 class="text-lg font-semibold text-gray-800 mb-6">Report Filters</h3>
    
    <form action="{{ route('reports.inventory') }}" method="GET" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <!-- Low Stock Filter -->
            <div class="flex items-end">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }} 
                        class="w-4 h-4 rounded border-gray-300">
                    <span class="text-sm font-medium text-gray-700">Show Only Low Stock Items</span>
                </label>
            </div>
        </div>

        <div class="flex gap-3 pt-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium transition">
                <i class="fas fa-search mr-2"></i>Apply Filters
            </button>
            <a href="{{ route('reports.inventory') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-md text-sm font-medium transition">
                <i class="fas fa-redo mr-2"></i>Reset
            </a>
            <form action="{{ route('reports.inventory.pdf') }}" method="GET" class="flex gap-2" style="margin-left: auto;">
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
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product / Model</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Beginning Count</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ending Count</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Variance Qty</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Variance Amount</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">End Amount</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($finishedGoods as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        {{ $item->product->model_name ?? $item->product->product_code ?? '—' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $item->product->customer ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">{{ number_format($item->qty_beginning) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium
                        {{ $item->qty_actual_ending < ($item->product->min_stock ?? 0) ? 'text-red-600' : 'text-gray-900' }}">
                        {{ number_format($item->qty_actual_ending) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium
                        {{ $item->qty_variance != 0 ? 'text-orange-600' : 'text-gray-900' }}">
                        {{ number_format($item->qty_variance) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium
                        {{ $item->amount_variance != 0 ? 'text-orange-600' : 'text-gray-900' }}">
                        ₱{{ number_format($item->amount_variance, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                        ₱{{ number_format($item->amount_ending, 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                        <p>No inventory items found matching the criteria.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Summary Footer -->
    @if($finishedGoods->count() > 0)
    <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div>
                <p class="text-sm text-gray-600">Total Stock</p>
                <p class="text-2xl font-bold text-gray-900">{{ number_format($totalStock) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Total Variance Qty</p>
                <p class="text-2xl font-bold {{ $totalVariance != 0 ? 'text-orange-600' : 'text-gray-900' }}">
                    {{ number_format($totalVariance) }}
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Total Variance Amount</p>
                <p class="text-2xl font-bold {{ $totalVarianceAmount != 0 ? 'text-orange-600' : 'text-gray-900' }}">
                    ₱{{ number_format($totalVarianceAmount, 2) }}
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Total Value</p>
                <p class="text-2xl font-bold text-gray-900">₱{{ number_format($totalValue, 2) }}</p>
            </div>
        </div>
    </div>
    @endif
</div>

@endsection
