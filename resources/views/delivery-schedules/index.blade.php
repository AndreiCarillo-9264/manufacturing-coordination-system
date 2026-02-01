@extends('layouts.app')

@section('title', 'Delivery Schedules')
@section('page-icon') <i class="fas fa-truck"></i> @endsection
@section('page-title', 'Delivery Schedules')
@section('page-description', 'Manage delivery schedules')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="p-6 border-b bg-gray-50 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">All Delivery Schedules</h3>
            <p class="text-sm text-gray-600 mt-1">Upcoming and past deliveries</p>
        </div>
        @can('create', App\Models\DeliverySchedule::class)
        <a href="{{ route('delivery-schedules.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
            <i class="fas fa-plus mr-2"></i> New Schedule
        </a>
        @endcan
    </div>

    {{-- SEARCH & FILTER --}}
    <div class="p-6 bg-gray-50 border-b">
        <form method="GET" action="{{ route('delivery-schedules.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="J.O., Product or PO..."
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

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:border-blue-500">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="urgent" {{ request('status') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                        <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Delivered</option>
                    </select>
                </div>

                <div class="flex items-end gap-3">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
                        <i class="fas fa-filter mr-2"></i> Filter
                    </button>
                    <a href="{{ route('delivery-schedules.index') }}" class="text-gray-600 hover:text-gray-800 text-sm">Clear</a>
                </div>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Status</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Delivery Code</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Delivery Date</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">JO Number</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">PO Number</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Product Code</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Customer</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Model</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Description</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Dimension</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Qty Scheduled</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">UOM</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Remarks</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">PMP Commitment</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">PPQC Commitment</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Encoded By</th>
                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($deliverySchedules as $ds)
                <tr class="hover:bg-gray-50 {{ $ds->isDelayed() ? 'bg-red-50' : '' }}">
                    <td class="px-4 py-3 text-sm font-medium whitespace-nowrap">
                        @if($ds->status)
                        <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full
                            {{ $ds->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $ds->status === 'urgent' ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ $ds->status === 'delivered' || $ds->status === 'complete' ? 'bg-green-100 text-green-800' : '' }}">
                            {{ ucfirst($ds->status) }}
                            @if($ds->isDelayed())
                                <span class="ml-1 text-red-600 font-bold">(Delayed)</span>
                            @endif
                        </span>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm font-mono text-gray-900 whitespace-nowrap">{{ $ds->delivery_code }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $ds->delivery_date?->format('M d, Y') ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $ds->po_number ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm font-mono text-gray-900 whitespace-nowrap">{{ $ds->product->product_code ?? $ds->product->id ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $ds->product->customer ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $ds->product->model_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ Str::limit($ds->product->description, 30) ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $ds->product->dimension ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap font-bold">{{ number_format($ds->qty_scheduled) }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $ds->uom ?? ($ds->product->uom ?? '—') }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ Str::limit($ds->remarks, 25) ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ Str::limit($ds->pmp_commitment, 25) ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ Str::limit($ds->ppqc_commitment, 25) ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900 whitespace-nowrap">{{ $ds->date_encoded?->format('M d, Y') ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ Str::limit($ds->delivery_remarks, 25) ?? '—' }}</td>
                    <td class="px-4 py-3 text-sm whitespace-nowrap sticky right-0 bg-white">
                        <div class="flex gap-2">
                            <a href="{{ route('delivery-schedules.show', $ds) }}" class="text-blue-600 hover:text-blue-800" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            @can('update', $ds)
                            <a href="{{ route('delivery-schedules.edit', $ds) }}" class="text-amber-600 hover:text-amber-800" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            @endcan
                            @can('delete', $ds)
                            <form action="{{ route('delivery-schedules.destroy', $ds) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800" title="Delete" onclick="return confirm('Delete this schedule?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="17" class="px-6 py-10 text-center text-gray-500">No delivery schedules found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-6">{{ $deliverySchedules->links() }}</div>
</div>
@endsection