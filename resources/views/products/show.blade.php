<!-- resources/views/products/show.blade.php -->
@extends('layouts.app')

@section('title', 'Product Details')
@section('page-icon') <i class="fas fa-cube"></i> @endsection
@section('page-title', 'Product: ' . $product->product_code)
@section('page-description', 'Full product information and specifications')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">

    <!-- Header -->
    <div class="p-6 border-b bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-xl font-semibold text-gray-800">{{ $product->product_code }}</h3>
            <p class="text-sm text-gray-600 mt-1">
                {{ $product->model_name ?: 'No model name specified' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-3">
            @can('update', $product)
                <a href="{{ route('products.edit', $product) }}"
                   class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium shadow-sm transition text-sm">
                    <i class="fas fa-edit mr-1.5"></i> Edit
                </a>
            @endcan
            @can('delete', $product)
                <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium shadow-sm transition text-sm"
                            onclick="return confirm('Are you sure you want to delete this product?\nThis action cannot be undone.')">
                        <i class="fas fa-trash-alt mr-1.5"></i> Delete
                    </button>
                </form>
            @endcan
            @if(session('success'))
            <div class="flex gap-3 ml-auto">
                <a href="{{ route('products.index') }}" class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium shadow-sm transition text-sm">
                    <i class="fas fa-check mr-1.5"></i> Continue
                </a>
                <a href="{{ route('products.create') }}" class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg font-medium shadow-sm transition text-sm">
                    <i class="fas fa-plus mr-1.5"></i> Create Another
                </a>
            </div>
            @endif
        </div>
    </div>

    <!-- Main Content -->
    <div class="p-6 space-y-10">

        <!-- Basic Information -->
        <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Basic Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Customer</label>
                    <p class="mt-1.5 text-gray-900 font-medium">{{ $product->customer ?? '—' }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Model</label>
                    <p class="mt-1.5 text-gray-900">{{ $product->model_name ?? '—' }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">UOM <span class="normal-case font-normal text-gray-600">(Unit of Measure)</span></label>
                    <p class="mt-1.5 text-gray-900">{{ $product->uom ? $product->uom . ' – ' . ucfirst(str_replace(['pcs','set','kg','g','m','cm','mm','l','ml','box','pack'], ['Pieces','Set','Kilogram','Gram','Meter','Centimeter','Millimeter','Liter','Milliliter','Box','Pack'], $product->uom)) : '—' }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">MOQ</label>
                    <p class="mt-1.5 text-gray-900">{{ $product->moq ?? '—' }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Dimension / Size</label>
                    <p class="mt-1.5 text-gray-900">{{ $product->dimension ?? '—' }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Location</label>
                    <p class="mt-1.5 text-gray-900">{{ $product->location ?? '—' }}</p>
                </div>

                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Description</label>
                    <p class="mt-1.5 text-gray-800 whitespace-pre-line">{{ $product->description ?: 'No description provided.' }}</p>
                </div>

                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Specifications</label>
                    <p class="mt-1.5 text-gray-800 whitespace-pre-line">{{ $product->specs ?: 'No specifications listed.' }}</p>
                </div>

            </div>
        </div>

        <!-- Pricing & Margin -->
        <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Pricing & Margin</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 bg-gray-50 p-6 rounded-xl border border-gray-200">

                <div>
                    <label class="block text-xs font-medium text-gray-600 uppercase tracking-wide">Currency</label>
                    <p class="mt-2 text-xl font-semibold text-gray-900">{{ $product->currency ?? '—' }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 uppercase tracking-wide">Selling Price</label>
                    <p class="mt-2 text-xl font-semibold text-green-700">
                        {{ $product->currency === 'PHP' ? '₱' : ($product->currency ?? '') }}
                        {{ number_format($product->selling_price ?? 0, 2) }}
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 uppercase tracking-wide">MC <span class="normal-case font-normal text-gray-500">(Material Cost)</span></label>
                    <p class="mt-2 text-xl font-semibold text-amber-700">
                        {{ $product->currency === 'PHP' ? '₱' : ($product->currency ?? '') }}
                        {{ number_format($product->mc ?? 0, 2) }}
                    </p>
                </div>

                <div class="sm:col-span-2 lg:col-span-4 bg-white p-5 rounded-lg shadow-sm border">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 text-center">
                        <div>
                            <div class="text-sm text-gray-600 mb-2">Difference (Selling – MC)</div>
                            <div class="text-3xl font-bold text-gray-800">
                                {{ $product->currency === 'PHP' ? '₱' : ($product->currency ?? '') }}
                                {{ number_format($product->diff ?? 0, 2) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-gray-600 mb-2">MU <span class="text-xs text-gray-500">(Markup %)</span></div>
                            <div class="text-3xl font-bold text-indigo-700">
                                {{ number_format($product->mu ?? 0, 2) }}%
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Additional / Reference Info -->
        <div>
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">Additional Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">RSQF Number</label>
                    <p class="mt-1.5 text-gray-900">{{ $product->rsqf_number ?? '—' }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">PC <span class="normal-case font-normal text-gray-600">(Product Category / Code)</span></label>
                    <p class="mt-1.5 text-gray-900">{{ $product->pc ?? '—' }}</p>
                </div>

                <div class="md:col-span-2 lg:col-span-3">
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Remarks / PO Notes</label>
                    <p class="mt-1.5 text-gray-800 whitespace-pre-line">{{ $product->remarks ?: 'No special remarks.' }}</p>
                </div>

            </div>
        </div>

        <!-- System / Audit Info -->
        <div class="pt-4 border-t">
            <h4 class="text-lg font-semibold text-gray-800 mb-4 pb-2 border-b">System Information</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">

                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Date Encoded</label>
                    <p class="mt-1.5 text-gray-900">{{ $product->date_encoded?->format('M d, Y') ?? '—' }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Encoded By</label>
                    <p class="mt-1.5 text-gray-900">{{ $product->encodedBy?->name ?? 'System / Unknown' }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide">Last Updated</label>
                    <p class="mt-1.5 text-gray-900">{{ $product->updated_at?->diffForHumans() ?? '—' }}</p>
                </div>

            </div>
        </div>

    </div>

</div>
@endsection