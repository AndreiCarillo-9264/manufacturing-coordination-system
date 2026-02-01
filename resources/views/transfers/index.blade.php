@extends('layouts.app')

@section('title', 'Inventory Transfers')
@section('page-icon') <i class="fas fa-exchange-alt"></i> @endsection
@section('page-title', 'Inventory Transfers')
@section('page-description', 'Manage stock transfers between locations')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="p-6 border-b bg-gray-50 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">All Transfers</h3>
            <p class="text-sm text-gray-600 mt-1">Stock movements and relocations</p>
        </div>
        @can('create', App\Models\Transfer::class)
        <a href="{{ route('transfers.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
            <i class="fas fa-plus mr-2"></i> New Transfer
        </a>
        @endcan
    </div>

    {{-- SEARCH & FILTER --}}
    <div class="p-6 bg-gray-50 border-b">
        <form method="GET" action="{{ route('transfers.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Product or Location..."
                               class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                    <select name="product_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500">
                        <option value="">All Products</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->model_name ?? $product->product_code }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500">
                    </div>
                </div>

                <div class="flex items-end gap-3">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="{{ route('transfers.index') }}" class="text-gray-600 hover:text-gray-800 text-sm flex items-center">Clear Filters</a>
                </div>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">PTT Number</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Section</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Date Transferred</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">JO Number</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Qty Transferred</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Delivery Date</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Remarks</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Time Transferred</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">JO Status</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">JO Balance</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Product Code</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Customer</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Model</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Description</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Grade</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Dimension</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Received By</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Date Received</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Time Received</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($transfers as $transfer)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-mono text-gray-900 whitespace-nowrap">{{ $transfer->ptt_number }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $transfer->section ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $transfer->date_transferred?->format('M d, Y') ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm font-mono text-gray-900 whitespace-nowrap">{{ $transfer->jobOrder?->jo_number ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap font-bold">{{ number_format($transfer->qty_transferred) }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $transfer->date_delivery_scheduled?->format('M d, Y') ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ Str::limit($transfer->remarks, 25) ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $transfer->time_transferred?->format('H:i') ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm font-medium whitespace-nowrap">
                        @if($transfer->jobOrder?->status)
                        <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full
                            {{ $transfer->jobOrder->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $transfer->jobOrder->status === 'approved' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $transfer->jobOrder->status === 'in_progress' ? 'bg-indigo-100 text-indigo-800' : '' }}
                            {{ $transfer->jobOrder->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $transfer->jobOrder->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ ucfirst(str_replace('_', ' ', $transfer->jobOrder->status)) }}
                        </span>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $transfer->qty_jo_balance ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm font-mono text-gray-900 whitespace-nowrap">{{ $transfer->product->product_code ?? $transfer->product->id ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $transfer->product->customer ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $transfer->product->model_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ Str::limit($transfer->product->description, 30) ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $transfer->grade ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $transfer->dimension ?? $transfer->product->dimension ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $transfer->receivedBy?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $transfer->date_received?->format('M d, Y') ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $transfer->time_received?->format('H:i') ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm whitespace-nowrap sticky right-0 bg-white">
                        <div class="flex gap-2">
                            <a href="{{ route('transfers.show', $transfer) }}" class="text-blue-600 hover:text-blue-800" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            @can('update', $transfer)
                            <a href="{{ route('transfers.edit', $transfer) }}" class="text-amber-600 hover:text-amber-800" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                            @can('delete', $transfer)
                            <form action="{{ route('transfers.destroy', $transfer) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800" title="Delete" onclick="return confirm('Delete this transfer?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="20" class="px-6 py-10 text-center text-gray-500">No transfers found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-6">{{ $transfers->links() }}</div>
</div>
@endsection