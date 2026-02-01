@extends('layouts.app')

@section('title', 'Actual Inventory')
@section('page-icon') <i class="fas fa-boxes"></i> @endsection
@section('page-title', 'Actual Inventory')
@section('page-description', 'Manage and view current stock levels')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="p-6 border-b bg-gray-50 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">Inventory Records</h3>
            <p class="text-sm text-gray-600 mt-1">Physical stock counts and verifications</p>
        </div>
        @can('create', App\Models\ActualInventory::class)
        <a href="{{ route('actual-inventories.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
            <i class="fas fa-plus mr-2"></i> Add Inventory
        </a>
        @endcan
    </div>

    {{-- SEARCH & FILTER --}}
    <div class="p-6 bg-gray-50 border-b">
        <form method="GET" action="{{ route('actual-inventories.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Product name or code..."
                               class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stock Level</label>
                    <select name="stock_level" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500">
                        <option value="">All Levels</option>
                        <option value="low" {{ request('stock_level') == 'low' ? 'selected' : '' }}>Low Stock (&lt; 100)</option>
                        <option value="medium" {{ request('stock_level') == 'medium' ? 'selected' : '' }}>Medium Stock (100-500)</option>
                        <option value="high" {{ request('stock_level') == 'high' ? 'selected' : '' }}>High Stock (&gt; 500)</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="{{ route('actual-inventories.index') }}" class="text-gray-600 hover:text-gray-800 text-sm flex items-center">Clear Filters</a>
                </div>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto relative">
        <div class="absolute right-0 top-0 bottom-0 w-16 pointer-events-none bg-gradient-to-l from-white via-white/70 to-transparent z-30"></div>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0 z-20">
                <tr>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Tag Number</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Product Code</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Qty Counted</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">UOM</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Customer</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Model</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Description</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Dimension</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Location</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Counted</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Verified</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Remarks</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Encoded By</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Date Encoded</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($actualInventories as $inv)
                <tr class="{{ $inv->qty_counted < 100 ? 'bg-red-50' : ($inv->qty_counted <= 500 ? 'bg-yellow-50' : '') }} hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $inv->tag_number }}</td>
                    <td class="px-5 py-4 text-sm font-mono text-gray-900 whitespace-nowrap">{{ $inv->product->product_code ?? $inv->product->id ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm font-bold text-gray-900 whitespace-nowrap">{{ number_format($inv->qty_counted) }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $inv->product->uom ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $inv->product->customer ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $inv->product->model_name ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600 whitespace-nowrap">{{ Str::limit($inv->product->description ?? '', 40) }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $inv->product->dimension ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $inv->location ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $inv->countedBy?->name ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $inv->verifiedBy?->name ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600 whitespace-nowrap">{{ Str::limit($inv->remarks ?? '', 30) }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $inv->product->encodedBy?->name ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $inv->product->date_encoded?->format('M d, Y') ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm whitespace-nowrap sticky right-0 bg-white shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.08)] z-10">
                        <div class="flex items-center gap-3 px-2">
                            @can('update', $inv)
                            <a href="{{ route('actual-inventories.edit', $inv) }}" class="text-blue-600 hover:text-blue-800 transition" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                            <a href="{{ route('actual-inventories.show', $inv) }}" class="text-gray-600 hover:text-gray-800 transition" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="15" class="px-6 py-16 text-center text-gray-500">
                        No inventory records found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-6">{{ $actualInventories->links() }}</div>
</div>
@endsection