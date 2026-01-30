@extends('layouts.app')

@section('title', 'Finished Goods')
@section('page-icon') <i class="fas fa-box"></i> @endsection
@section('page-title', 'Finished Goods')
@section('page-description', 'Production output records')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="p-6 border-b bg-gray-50 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">All Finished Goods</h3>
            <p class="text-sm text-gray-600 mt-1">Production outputs (auto-created via Transfers)</p>
        </div>
    </div>

    {{-- SEARCH & FILTER --}}
    <div class="p-6 bg-gray-50 border-b">
        <form method="GET" action="{{ route('finished-goods.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="J.O. Number or Product..."
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
                    <a href="{{ route('finished-goods.index') }}" class="text-gray-600 hover:text-gray-800 text-sm flex items-center">Clear Filters</a>
                </div>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto relative">
        <div class="absolute right-0 top-0 bottom-0 w-16 pointer-events-none bg-gradient-to-l from-white via-white/70 to-transparent z-30"></div>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0 z-20">
                <tr>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">#</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">PC</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Area</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Product Code / ID</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Customer</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Model</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Description</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Dimension</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">UOM</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Beginning</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">In</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Out</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Theo End</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Remarks</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Buffer Stocks</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Encoded By</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Date Encoded</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($finishedGoods as $fg)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $loop->iteration + ($finishedGoods->currentPage()-1) * $finishedGoods->perPage() }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $fg->product->pc ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $fg->count_pc_area ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm font-mono text-gray-900 whitespace-nowrap">{{ $fg->product->product_code ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $fg->product->customer ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $fg->product->model_name ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600 whitespace-nowrap">{{ Str::limit($fg->product->description ?? '', 40) }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $fg->product->dimension ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $fg->uom3 ?? $fg->product->uom ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ number_format($fg->beg) }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ number_format($fg->in_qty) }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ number_format($fg->out_qty) }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ number_format($fg->theo_end) }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600 whitespace-nowrap">{{ Str::limit($fg->remarks ?? '', 30) }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ number_format($fg->buffer_stocks) }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $fg->product->encodedBy?->name ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $fg->product->date_encoded?->format('M d, Y') ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm whitespace-nowrap sticky right-0 bg-white shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.08)] z-10">
                        <div class="flex items-center gap-3 px-2">
                            @can('update', $fg)
                            <a href="{{ route('finished-goods.edit', $fg) }}" class="text-blue-600 hover:text-blue-800 transition" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                            <a href="{{ route('finished-goods.show', $fg) }}" class="text-gray-600 hover:text-gray-800 transition" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="18" class="px-6 py-16 text-center text-gray-500">
                        No finished goods records found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-6">{{ $finishedGoods->links() }}</div>
</div>
@endsection