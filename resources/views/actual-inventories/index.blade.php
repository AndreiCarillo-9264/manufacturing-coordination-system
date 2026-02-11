{{-- resources/views/actual-inventories/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Actual Inventory')
@section('page-icon') <i class="fas fa-boxes"></i> @endsection
@section('page-title', 'Actual Inventory')
@section('page-description', 'Manage and view current stock levels')

@section('content')
@php
use App\Models\ActualInventory;
@endphp
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <x-page-header title="Inventory Records" description="Physical stock counts and verifications">
        <x-slot name="actions">
            @can('create', App\Models\ActualInventory::class)
            <a href="{{ route('actual-inventories.create') }}" 
               class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm hover:shadow">
                <i class="fas fa-plus mr-2"></i> Add Inventory
            </a>
            @endcan

            <a href="{{ route('actual-inventories.export') }}"
               class="inline-flex items-center px-4 py-2 border border-green-600 rounded-lg text-sm font-medium text-green-600 bg-white hover:bg-green-50 transition-colors shadow-sm">
                <i class="fas fa-file-export mr-2"></i> Export CSV
            </a>

            <form id="inv-import-form" action="{{ route('actual-inventories.import') }}" method="POST" enctype="multipart/form-data" class="hidden">
                @csrf
                <input id="inv-import-file" type="file" name="file" accept=".csv,.xlsx" onchange="document.getElementById('inv-import-form').submit()">
            </form>
            <button type="button" 
                    onclick="document.getElementById('inv-import-file').click()" 
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors shadow-sm">
                <i class="fas fa-file-import mr-2"></i> Import
            </button>
        </x-slot>
    </x-page-header>

    {{-- SEARCH & FILTER --}}
    <div class="p-6 bg-gray-50 border-b border-gray-200">
        <form method="GET" action="{{ route('actual-inventories.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                    <div class="relative">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Tag number, product code, model..."
                               class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Product</label>
                    <select name="product_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                        <option value="">All Products</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                            {{ $product->product_code }} - {{ $product->model_name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                    <select name="location" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                        <option value="">All Locations</option>
                        @foreach($locations as $loc)
                        <option value="{{ $loc }}" {{ request('location') == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Stock Level</label>
                    <select name="stock_level" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-shadow">
                        <option value="">All Levels</option>
                        <option value="low" {{ request('stock_level') == 'low' ? 'selected' : '' }}>Low (&lt; 100)</option>
                        <option value="medium" {{ request('stock_level') == 'medium' ? 'selected' : '' }}>Medium (100-500)</option>
                        <option value="high" {{ request('stock_level') == 'high' ? 'selected' : '' }}>High (&gt; 500)</option>
                    </select>
                </div>
            </div>

            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Filter</label>
                    <div class="flex gap-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="verified" value="1" {{ request('verified') ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Verified</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="unverified" value="1" {{ request('unverified') ? 'checked' : '' }} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Unverified</span>
                        </label>
                    </div>
                </div>
                <button type="submit" 
                        class="inline-flex items-center justify-center px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors shadow-sm hover:shadow">
                    <i class="fas fa-filter mr-2"></i> Apply Filters
                </button>
                @if(request()->hasAny(['search', 'product_id', 'location', 'stock_level', 'verified', 'unverified']))
                <a href="{{ route('actual-inventories.index') }}" 
                   class="inline-flex items-center justify-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-colors">
                    <i class="fas fa-times mr-2"></i> Clear
                </a>
                @endif
            </div>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0 z-10">
                <tr>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Tag Number
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Product Code
                    </th>
                    <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Counted Qty
                    </th>
                    <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        System Qty
                    </th>
                    <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Variance
                    </th>
                    <th scope="col" class="px-6 py-4 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Location
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Counted By
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Verified By
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap sticky right-0 bg-gray-50 shadow-[-4px_0_6px_-1px_rgba(0,0,0,0.1)]">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($actualInventories as $inv)
                <tr class="hover:bg-blue-50/30 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-blue-50 text-blue-700 text-sm font-mono font-semibold">
                            {{ $inv->tag_number }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $inv->product_code }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="text-sm font-semibold text-gray-900">{{ number_format($inv->fg_quantity) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="text-sm text-gray-900">{{ number_format($inv->system_quantity ?? 0) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="text-sm {{ $inv->variance < 0 ? 'text-red-600' : ($inv->variance > 0 ? 'text-green-600' : 'text-gray-600') }}">
                            {{ number_format($inv->variance ?? 0) }}
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @php
                        $statusColors = [
                            'Verified' => 'bg-green-100 text-green-800',
                            'Counted' => 'bg-blue-100 text-blue-800',
                            'Discrepancy' => 'bg-red-100 text-red-800',
                            'Pending' => 'bg-yellow-100 text-yellow-800',
                        ];
                        $color = $statusColors[$inv->status] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $color }}">
                            {{ $inv->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-md bg-gray-100 text-gray-700 text-sm">
                            {{ $inv->location ?? '—' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $inv->counted_by ?? '—' }}</div>
                        <div class="text-xs text-gray-500">{{ $inv->counted_at?->format('M d, Y') ?? '' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $inv->verified_by ?? '—' }}</div>
                        <div class="text-xs text-gray-500">{{ $inv->verified_at?->format('M d, Y') ?? '' }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center sticky right-0 bg-white shadow-[-4px_0_6px_-1px_rgba(0,0,0,0.08)]">
                        <div class="flex items-center justify-center gap-3">
                            @can('update', $inv)
                            <a href="{{ route('actual-inventories.edit', $inv) }}" 
                               class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 hover:bg-amber-50 transition-colors" 
                               title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                            @can('delete', $inv)
                            <form action="{{ route('actual-inventories.destroy', $inv) }}" method="POST" class="inline" onsubmit="return confirm('Delete this inventory record?')">
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
                    <td colspan="10" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-500">
                            <i class="fas fa-boxes text-4xl mb-3 text-gray-300"></i>
                            <p class="text-lg font-medium">No inventory records found</p>
                            @if(request()->hasAny(['search', 'product_id', 'location']))
                            <p class="text-sm mt-1">Try adjusting your filters</p>
                            @else
                            <p class="text-sm mt-1">Get started by adding your first inventory count</p>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- PAGINATION --}}
    @if($actualInventories->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        {{ $actualInventories->links() }}
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