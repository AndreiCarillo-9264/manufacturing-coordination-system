@extends('layouts.app')

@section('title', 'Main Dashboard')
@section('page-icon') <i class="fas fa-chart-line"></i> @endsection
@section('page-title', 'Main Dashboard')
@section('page-description', 'Overview of all operations')

@section('content')

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    @component('kpi-card', ['title' => 'Total Job Orders', 'value' => number_format($totalJobOrders), 'color' => 'text-gray-900']) @endcomponent
    @component('kpi-card', ['title' => 'Total Produced',   'value' => number_format($totalProduced),   'color' => 'text-gray-900']) @endcomponent
    @component('kpi-card', ['title' => 'Total Delivered',  'value' => number_format($totalDelivered),  'color' => 'text-gray-900']) @endcomponent
    @component('kpi-card', ['title' => 'Current Inventory','value' => number_format($currentInventory),'color' => 'text-gray-900']) @endcomponent
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Ordered vs Produced vs Delivered Chart -->
    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Ordered vs Produced vs Delivered (Recent 10 JOs)</h3>
        <div class="h-80">
            <canvas id="comparisonChart"></canvas>
        </div>
    </div>

    <!-- Low Stock Products -->
    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Low Stock Products</h3>
        <div class="space-y-3 max-h-80 overflow-y-auto pr-2">
            @forelse($lowStockProducts ?? [] as $fg)
                <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg border border-red-100">
                    <div>
                        <p class="font-medium text-gray-900">{{ $fg->product->model_name ?? $fg->product->product_code ?? 'Unknown' }}</p>
                        <p class="text-sm text-gray-600">{{ $fg->product->product_code ?? 'N/A' }}</p>
                    </div>
                    <span class="text-red-700 font-bold text-lg">
                        {{ number_format($fg->ending_count) }} / {{ $fg->buffer_stocks ?? '?' }}
                    </span>
                </div>
            @empty
                <p class="text-gray-500 text-center py-10">No low stock items currently</p>
            @endforelse
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Job Orders -->
    <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
        <div class="p-6 border-b bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Recent Job Orders</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">J.O. #</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Encoded By</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentJobOrders as $jo)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $jo->jo_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $jo->product->model_name ?? $jo->product->product_code ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($jo->qty) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $jo->encodedBy->name ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full
                                {{ $jo->status === 'completed'  ? 'bg-green-100 text-green-800' : '' }}
                                {{ $jo->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $jo->status === 'approved'    ? 'bg-indigo-100 text-indigo-800' : '' }}
                                {{ $jo->status === 'pending'     ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $jo->status === 'cancelled'   ? 'bg-red-100 text-red-800' : '' }}">
                                {{ ucfirst(str_replace('_', ' ', $jo->status)) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">No recent job orders</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Deliveries -->
    <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
        <div class="p-6 border-b bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Recent Deliveries</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">JO #</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Encoded By</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentDeliveries as $ds)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $ds->date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $ds->product->model_name ?? $ds->product->product_code ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">{{ number_format($ds->qty) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $ds->jobOrder->jo_number ?? '-' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $ds->jobOrder?->encodedBy->name ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-10 text-center text-gray-500">No recent deliveries</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('comparisonChart')?.getContext('2d');
    if (!ctx) return;

    const labels   = @json($comparisonData->pluck('jo_number') ?? []);
    const ordered  = @json($comparisonData->pluck('ordered')  ?? []);
    const produced = @json($comparisonData->pluck('produced') ?? []);
    const delivered = @json($comparisonData->pluck('delivered') ?? []);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                { label: 'Ordered',   data: ordered,   backgroundColor: 'rgba(59, 130, 246, 0.6)',  borderColor: 'rgb(59, 130, 246)',  borderWidth: 1 },
                { label: 'Produced',  data: produced,  backgroundColor: 'rgba(34, 197, 94, 0.6)',   borderColor: 'rgb(34, 197, 94)',   borderWidth: 1 },
                { label: 'Delivered', data: delivered, backgroundColor: 'rgba(245, 158, 11, 0.6)',  borderColor: 'rgb(245, 158, 11)',  borderWidth: 1 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true } }
        }
    });
});
</script>
@endpush