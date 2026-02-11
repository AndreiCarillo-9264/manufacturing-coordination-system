{{-- resources/views/transfers/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Inventory Transfers')
@section('page-icon') <i class="fas fa-exchange-alt"></i> @endsection
@section('page-title', 'Inventory Transfers')
@section('page-description', 'Manage stock transfers between locations')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <x-page-header title="All Transfers" description="Stock movements and relocations">
        <x-slot name="actions">
            @can('create', App\Models\InventoryTransfer::class)
            <a href="{{ route('inventory-transfers.create') }}" 
               class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm hover:shadow">
                <i class="fas fa-plus mr-2"></i> New Transfer
            </a>
            @endcan

            <a href="{{ route('inventory-transfers.export') }}"
               class="inline-flex items-center px-4 py-2 border border-green-600 rounded-lg text-sm font-medium text-green-600 bg-white hover:bg-green-50 transition-colors shadow-sm">
                <i class="fas fa-file-export mr-2"></i> Export CSV
            </a>

            <form id="transfers-import-form" action="{{ route('inventory-transfers.import') }}" method="POST" enctype="multipart/form-data" class="hidden">
                @csrf
                <input id="transfers-import-file" type="file" name="file" accept=".csv,.xlsx" onchange="document.getElementById('transfers-import-form').submit()">
            </form>
            <button type="button" 
                    onclick="document.getElementById('transfers-import-file').click()" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm">
                <i class="fas fa-file-import mr-2"></i> Import
            </button>
        </x-slot>
    </x-page-header>

    {{-- SEARCH & FILTER --}}
    <div class="p-6 bg-gradient-to-br from-gray-50 to-gray-100/50 border-b border-gray-200">
        <form method="GET" action="{{ route('inventory-transfers.index') }}">
            {{-- First Row: Main Filters --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
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
                               placeholder="PTT#, JO#, product code..."
                               class="block w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 placeholder-gray-400">
                    </div>
                </div>

                {{-- Status Filter --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">Status</label>
                    <select name="status" class="block w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                        <option value="">All Statuses</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Balance" {{ request('status') == 'Balance' ? 'selected' : '' }}>Balance</option>
                        <option value="Complete" {{ request('status') == 'Complete' ? 'selected' : '' }}>Complete</option>
                    </select>
                </div>

                {{-- Section Filter --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">Section</label>
                    <select name="section" class="block w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                        <option value="">All Sections</option>
                        <option value="Production" {{ request('section') == 'Production' ? 'selected' : '' }}>Production</option>
                        <option value="Warehouse" {{ request('section') == 'Warehouse' ? 'selected' : '' }}>Warehouse</option>
                        <option value="PPQC" {{ request('section') == 'PPQC' ? 'selected' : '' }}>PPQC</option>
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
            </div>

            {{-- Second Row: Date Range and Actions --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">Date From</label>
                    <input type="date" 
                           name="date_from" 
                           value="{{ request('date_from') }}" 
                           class="block w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">Date To</label>
                    <input type="date" 
                           name="date_to" 
                           value="{{ request('date_to') }}" 
                           class="block w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                </div>
                <div class="lg:col-span-2 flex items-end gap-2">
                    <button type="submit" 
                            class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                        <i class="fas fa-filter mr-2 text-xs"></i>
                        Apply Filters
                    </button>
                    @if(request()->hasAny(['search', 'status', 'section', 'product_id', 'date_from', 'date_to']))
                    <a href="{{ route('inventory-transfers.index') }}" 
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
                        PTT Number
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Transfer Code
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Section
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        JO Number
                    </th>
                    <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Quantity
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Date Transferred
                    </th>
                    <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Received By
                    </th>
                    <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap sticky right-0 bg-gray-50 shadow-[-4px_0_6px_-1px_rgba(0,0,0,0.1)]">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($transfers as $transfer)
                <tr class="hover:bg-blue-50/30 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 text-sm font-mono font-semibold">
                            {{ $transfer->ptt_number }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $transfer->transfer_code ?? '—' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ $transfer->section ?? '—' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $transfer->jo_number ?? '—' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="text-sm font-semibold text-gray-900">{{ number_format($transfer->quantity) }}</div>
                        <div class="text-xs text-gray-500">{{ strtoupper($transfer->uom ?? '—') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $transfer->date_transferred?->format('M d, Y') ?? '—' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @php
                        $statusColors = [
                            'Pending' => 'bg-yellow-100 text-yellow-800',
                            'Balance' => 'bg-orange-100 text-orange-800',
                            'Complete' => 'bg-green-100 text-green-800',
                        ];
                        $color = $statusColors[$transfer->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $color }}">
                            {{ $transfer->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $transfer->receivedBy?->name ?? '—' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center sticky right-0 bg-white shadow-[-4px_0_6px_-1px_rgba(0,0,0,0.08)]">
                        <div class="flex items-center justify-center gap-3">
                            @can('update', $transfer)
                            <a href="{{ route('inventory-transfers.edit', $transfer) }}" 
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 hover:bg-amber-50 transition-colors" 
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                            @can('delete', $transfer)
                            <form action="{{ route('inventory-transfers.destroy', $transfer) }}" method="POST" class="inline" onsubmit="return confirm('Delete this transfer?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-600 hover:bg-red-50 transition-colors" 
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
                            <i class="fas fa-exchange-alt text-4xl mb-3 text-gray-300"></i>
                            <p class="text-lg font-medium">No transfers found</p>
                            @if(request()->hasAny(['search', 'status', 'section', 'product_id']))
                            <p class="text-sm mt-1">Try adjusting your filters</p>
                            @else
                            <p class="text-sm mt-1">Create your first transfer to get started</p>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- PAGINATION --}}
    @if($transfers->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        {{ $transfers->links() }}
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