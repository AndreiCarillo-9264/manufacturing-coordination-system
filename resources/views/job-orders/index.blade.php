{{-- resources/views/job-orders/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Job Orders')
@section('page-icon') <i class="fas fa-tasks"></i> @endsection
@section('page-title', 'Job Orders')
@section('page-description', 'Manage production job orders')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <x-page-header title="All Job Orders" description="Production orders and scheduling">
        <x-slot name="actions">
            @can('create', App\Models\JobOrder::class)
            <a href="{{ route('job-orders.create') }}" 
               class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm hover:shadow">
                <i class="fas fa-plus mr-2"></i> Create Job Order
            </a>
            @endcan

            <a href="{{ route('job-orders.export') }}"
               class="inline-flex items-center px-4 py-2 border border-green-600 rounded-lg text-sm font-medium text-green-600 bg-white hover:bg-green-50 transition-colors shadow-sm">
                <i class="fas fa-file-export mr-2"></i> Export CSV
            </a>

            <form id="jo-import-form" action="{{ route('job-orders.import') }}" method="POST" enctype="multipart/form-data" class="hidden">
                @csrf
                <input id="jo-import-file" type="file" name="file" accept=".csv,.xlsx" onchange="document.getElementById('jo-import-form').submit()">
            </form>
            <button type="button" 
                    onclick="document.getElementById('jo-import-file').click()" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm">
                <i class="fas fa-file-import mr-2"></i> Import
            </button>
        </x-slot>
    </x-page-header>

    {{-- SEARCH & FILTER --}}
    <div class="p-6 bg-gradient-to-br from-gray-50 to-gray-100/50 border-b border-gray-200">
        <form method="GET" action="{{ route('job-orders.index') }}">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Search Input --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">Search</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 text-sm"></i>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="JO#, PO#, product code..."
                               class="block w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 placeholder-gray-400">
                    </div>
                </div>

                {{-- Status Filter --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">Status</label>
                    <select name="status" class="block w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                        <option value="">All Statuses</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                        <option value="JO Full" {{ request('status') == 'JO Full' ? 'selected' : '' }}>JO Full</option>
                        <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                {{-- Product Filter --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">Product</label>
                    <select name="product_id" class="block w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                        <option value="">All Products</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->product_code }} - {{ $product->model_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-end gap-2">
                    <button type="submit" 
                            class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                        <i class="fas fa-filter mr-2 text-xs"></i>
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'status', 'product_id']))
                    <a href="{{ route('job-orders.index') }}" 
                       class="inline-flex items-center justify-center px-4 py-2.5 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg border border-gray-300 transition-all duration-200"
                       title="Clear filters">
                        <i class="fas fa-times text-xs"></i>
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0 z-10">
                <tr>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        JO Number
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        PO Number
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Product
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Customer
                    </th>
                    <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Quantity
                    </th>
                    <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Balance
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Date Needed
                    </th>
                    <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap sticky right-0 bg-gray-50 shadow-[-4px_0_6px_-1px_rgba(0,0,0,0.1)]">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($jobOrders as $jo)
                <tr class="hover:bg-blue-50/30 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 text-sm font-mono font-semibold">
                            {{ $jo->jo_number }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $jo->po_number ?? '—' }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900">{{ $jo->product_code }}</div>
                        <div class="text-xs text-gray-500 max-w-xs" title="{{ $jo->description }}">{{ Str::limit($jo->model_name ?? $jo->description ?? '—', 30) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $jo->customer_name ?? '—' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="text-sm font-semibold text-gray-900">{{ number_format($jo->quantity) }}</div>
                        <div class="text-xs text-gray-500">{{ strtoupper($jo->uom ?? '—') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="text-sm font-semibold {{ $jo->jo_balance > 0 ? 'text-orange-600' : 'text-green-600' }}">
                            {{ number_format($jo->jo_balance) }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $jo->date_needed?->format('M d, Y') ?? '—' }}</div>
                        @if($jo->isOverdue)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Overdue
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @php
                        $statusColors = [
                            'Pending' => 'bg-yellow-100 text-yellow-800',
                            'Partial' => 'bg-blue-100 text-blue-800',
                            'JO Full' => 'bg-green-100 text-green-800',
                            'Cancelled' => 'bg-red-100 text-red-800',
                        ];
                        $color = $statusColors[$jo->jo_status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $color }}">
                            {{ $jo->jo_status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center sticky right-0 bg-white shadow-[-4px_0_6px_-1px_rgba(0,0,0,0.08)]">
                        <div class="flex items-center justify-center gap-3">
                            @can('update', $jo)
                            <a href="{{ route('job-orders.edit', $jo) }}" 
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 hover:bg-amber-50 hover:text-amber-700 transition-colors" 
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                            @can('delete', $jo)
                            <form action="{{ route('job-orders.destroy', $jo) }}" method="POST" class="inline" onsubmit="return confirm('Delete this job order?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors" 
                                        title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-500">
                            <i class="fas fa-tasks text-4xl mb-3 text-gray-300"></i>
                            <p class="text-lg font-medium">No job orders found</p>
                            @if(request()->hasAny(['search', 'status', 'product_id']))
                            <p class="text-sm mt-1">Try adjusting your filters</p>
                            @else
                            <p class="text-sm mt-1">Create your first job order to get started</p>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- PAGINATION --}}
    @if($jobOrders->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        {{ $jobOrders->links() }}
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form on Enter key
    const searchInputs = document.querySelectorAll('input[name="search"]');
    searchInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.closest('form').submit();
            }
        });
    });
});
</script>

@endsection