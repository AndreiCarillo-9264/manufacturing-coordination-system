@extends('layouts.app')

@section('title', 'Job Orders')
@section('page-icon') <i class="fas fa-clipboard-list"></i> @endsection
@section('page-title', 'Job Orders')
@section('page-description', 'Manage all job orders')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="p-6 border-b bg-gray-50 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">All Job Orders</h3>
            <p class="text-sm text-gray-600 mt-1">Sales and production orders</p>
        </div>
        @can('create', App\Models\JobOrder::class)
        <a href="{{ route('job-orders.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
            <i class="fas fa-plus mr-2"></i> New Job Order
        </a>
        @endcan
    </div>

    {{-- SEARCH & FILTER --}}
    <div class="p-6 bg-gray-50 border-b">
        <form method="GET" action="{{ route('job-orders.index') }}" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="J.O. Number or Product..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">JO Status</label>
                    <select name="jo_status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="">All JO Status</option>
                        <option value="JO Full" {{ request('jo_status') == 'JO Full' ? 'selected' : '' }}>JO Full</option>
                        <option value="Balance" {{ request('jo_status') == 'Balance' ? 'selected' : '' }}>Balance</option>
                        <option value="Excess" {{ request('jo_status') == 'Excess' ? 'selected' : '' }}>Excess</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product</label>
                    <select name="product_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="">All Products</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->model_name ?? $product->product_code }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <div class="flex justify-end gap-4 mt-2">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md text-sm font-medium transition">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                <a href="{{ route('job-orders.index') }}" class="text-gray-600 hover:text-gray-800 text-sm flex items-center gap-1">
                    Clear Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Scrollable table with right scroll hint -->
    <div class="overflow-x-auto relative">
        <!-- Scroll indicator gradient -->
        <div class="absolute right-0 top-0 bottom-0 w-16 pointer-events-none bg-gradient-to-l from-white via-white/70 to-transparent z-30"></div>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0 z-20">
                <tr>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">JO Status</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">JO Number</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Date Needed</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">PO Number</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Product Code</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Customer</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Model</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Description</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Dimension</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Qty</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">UOM</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Encoded By</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Remarks</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($jobOrders as $jo)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-4 text-sm font-medium whitespace-nowrap">
                        @if($jo->jo_status)
                        <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full
                            {{ $jo->jo_status === 'JO Full' ? 'bg-green-100 text-green-800 ring-1 ring-green-200' : '' }}
                            {{ $jo->jo_status === 'Balance' ? 'bg-yellow-100 text-yellow-800 ring-1 ring-yellow-200' : '' }}
                            {{ $jo->jo_status === 'Excess'  ? 'bg-purple-100 text-purple-800 ring-1 ring-purple-200' : '' }}">
                            {{ $jo->jo_status }}
                        </span>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-4 text-sm font-mono text-gray-900 whitespace-nowrap">{{ $jo->jo_number }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $jo->date_needed?->format('M d, Y') ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $jo->po_number ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm font-mono text-gray-900 whitespace-nowrap">{{ $jo->product->product_code ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $jo->product->customer ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $jo->product->model_name ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600 whitespace-nowrap">{{ Str::limit($jo->product->description ?? '', 35) }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $jo->product->dimension ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap font-semibold">{{ number_format($jo->qty ?? 0) }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $jo->uom ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $jo->encodedBy?->name ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600 whitespace-nowrap">{{ Str::limit($jo->remarks ?? '', 30) }}</td>
                    <td class="px-5 py-4 text-sm whitespace-nowrap sticky right-0 bg-white shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.08)] z-10">
                        <div class="flex items-center gap-3 px-2">
                            <a href="{{ route('job-orders.show', $jo) }}" class="text-blue-600 hover:text-blue-800 transition" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            @can('update', $jo)
                            <a href="{{ route('job-orders.edit', $jo) }}" class="text-amber-600 hover:text-amber-800 transition" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                            @can('delete', $jo)
                            <form action="{{ route('job-orders.destroy', $jo) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 transition" title="Delete" 
                                        onclick="return confirm('Delete this job order? This will also delete related records.')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="14" class="px-6 py-16 text-center text-gray-500">
                        No job orders found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="p-6 border-t">
        {{ $jobOrders->links() }}
    </div>
</div>
@endsection