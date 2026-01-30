@extends('layouts.app')

@section('title', 'Logistics Dashboard')
@section('page-icon') <i class="fas fa-truck"></i> @endsection
@section('page-title', 'Logistics Dashboard')
@section('page-description', 'Monitor deliveries, transfers and shipping status')

@section('content')

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    @component('kpi-card', ['title' => 'Deliveries Today',    'value' => $deliveriesToday,    'color' => 'text-green-600']) @endcomponent
    @component('kpi-card', ['title' => 'Pending Deliveries',  'value' => $pendingDeliveries,  'color' => 'text-yellow-600']) @endcomponent
    @component('kpi-card', ['title' => 'Completed Deliveries','value' => $completedDeliveries,'color' => 'text-blue-600']) @endcomponent
    @component('kpi-card', ['title' => 'Delayed Shipments',   'value' => $delayedShipments,   'color' => 'text-red-600']) @endcomponent
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Delivery Schedules -->
    <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
        <div class="p-6 border-b bg-gray-50 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Recent Delivery Schedules</h3>
                <p class="text-sm text-gray-600 mt-1">Latest scheduled / ongoing deliveries</p>
            </div>
            @can('create', App\Models\DeliverySchedule::class)
            <a href="{{ route('delivery-schedules.create') }}" 
               class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
                <i class="fas fa-plus mr-2"></i> New Schedule
            </a>
            @endcan
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Encoded By</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentDeliveries as $delivery)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $delivery->date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $delivery->product->model_name ?? $delivery->product->product_code ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($delivery->qty) }} {{ $delivery->uom }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $delivery->jobOrder?->encodedBy->name ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $delivery->ds_status === 'delivered' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $delivery->ds_status === 'pending'   ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $delivery->ds_status === 'urgent'    ? 'bg-orange-100 text-orange-800' : '' }}">
                                {{ ucfirst($delivery->ds_status) }}
                                @if($delivery->isDelayed() && $delivery->ds_status !== 'delivered')
                                    <span class="ml-1 text-red-600 font-bold">(Delayed)</span>
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            @if($delivery->ds_status !== 'delivered')
                            <form action="{{ route('delivery-schedules.mark-delivered', $delivery) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-800 font-semibold transition">
                                    <i class="fas fa-check mr-1"></i>Mark Delivered
                                </button>
                            </form>
                            @else
                            <span class="text-gray-400">Delivered</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">No recent delivery schedules found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Delayed Shipments -->
    <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
        <div class="p-6 border-b bg-gray-50 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Delayed Shipments</h3>
                <p class="text-sm text-gray-600 mt-1">Deliveries past due date</p>
            </div>
            <a href="{{ route('delivery-schedules.index') }}?delayed=1" 
               class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
                <i class="fas fa-exclamation-triangle mr-2"></i> View All
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Encoded By</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($delayedList as $delivery)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $delivery->date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $delivery->product->model_name ?? $delivery->product->product_code ?? '—' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($delivery->qty) }} {{ $delivery->uom }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $delivery->jobOrder?->encodedBy->name ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $delivery->ds_status === 'delivered' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $delivery->ds_status === 'pending'   ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $delivery->ds_status === 'urgent'    ? 'bg-orange-100 text-orange-800' : '' }}">
                                {{ ucfirst($delivery->ds_status) }}
                                @if($delivery->isDelayed() && $delivery->ds_status !== 'delivered')
                                    <span class="ml-1 text-red-600 font-bold">(Delayed)</span>
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            @if($delivery->ds_status !== 'delivered')
                            <form action="{{ route('delivery-schedules.mark-delivered', $delivery) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="text-green-600 hover:text-green-800 font-semibold transition">
                                    <i class="fas fa-check mr-1"></i>Mark Delivered
                                </button>
                            </form>
                            @else
                            <span class="text-gray-400">Delivered</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">No delayed shipments currently</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- If you want real-time updates here too, you can add similar Echo listener as in other dashboards -->
<!-- Example placeholder: -->
<!-- 
<script type="module">
    // ... Echo setup ...
    window.Echo.channel('delivery-schedules')
        .listen('.updated', (e) => {
            console.log('Delivery updated', e);
            // Optional: toast + partial refresh
        });
</script>
-->
@endpush