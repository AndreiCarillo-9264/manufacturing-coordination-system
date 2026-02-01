@extends('layouts.app')

@section('title', 'Product Masterlist')
@section('page-icon') <i class="fas fa-cubes"></i> @endsection
@section('page-title', 'Product Masterlist')
@section('page-description', 'Manage product catalog')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="p-6 border-b bg-gray-50 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">All Products</h3>
            <p class="text-sm text-gray-600 mt-1">Product details and specifications</p>
        </div>
        @can('create', App\Models\JobOrder::class)
        <a href="{{ route('products.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
            <i class="fas fa-plus mr-2"></i> Add Product
        </a>
        @endcan
    </div>

    {{-- SEARCH & FILTER --}}
    <div class="p-6 bg-gray-50 border-b">
        <form method="GET" action="{{ route('products.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Product name or code..."
                               class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                    <input type="text" name="customer" value="{{ request('customer') }}" 
                           placeholder="Filter by customer..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
                    <i class="fas fa-filter mr-2"></i> Filter
                </button>
                <a href="{{ route('products.index') }}" class="text-gray-600 hover:text-gray-800 text-sm flex items-center">Clear Filters</a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto relative">
        <div class="absolute right-0 top-0 bottom-0 w-16 pointer-events-none bg-gradient-to-l from-white via-white/70 to-transparent z-30"></div>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0 z-20">
                <tr>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Customer</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Product Code</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Model</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Description</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Date Encoded</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Specs</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Dimension</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">MOQ</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">UOM</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Currency</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Selling Price</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">RSQF #</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Remarks</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Location</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Encoded By</th>
                    <th scope="col" class="px-5 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $product->customer ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm font-mono text-gray-900 whitespace-nowrap">{{ $product->product_code }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $product->model_name ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600 whitespace-nowrap">{{ Str::limit($product->description ?? '', 35) }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $product->date_encoded?->format('M d, Y') ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600 whitespace-nowrap">{{ Str::limit($product->specs ?? '', 25) }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $product->dimension ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $product->moq ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $product->uom ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $product->currency ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">
                        {{ $product->currency === 'PHP' ? '₱' : ($product->currency ?? '') }}
                        {{ number_format($product->selling_price ?? 0, 2) }}
                    </td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $product->rsqf_number ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-600 whitespace-nowrap">{{ Str::limit($product->remarks ?? '', 25) }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $product->location ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">{{ $product->encodedBy?->name ?? '—' }}</td>
                    <td class="px-5 py-4 text-sm whitespace-nowrap sticky right-0 bg-white shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.08)] z-10">
                        <div class="flex items-center gap-3 px-2">
                            <a href="{{ route('products.show', $product) }}" class="text-blue-600 hover:text-blue-800 transition" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            @can('create', App\Models\JobOrder::class)
                            <a href="{{ route('products.edit', $product) }}" class="text-amber-600 hover:text-amber-800 transition" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 transition" title="Delete" 
                                        onclick="return confirm('Delete this product? This may affect related records.')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="16" class="px-6 py-16 text-center text-gray-500">
                        No products found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-6 border-t">
        {{ $products->links() }}
    </div>
</div>
@endsection