@extends('layouts.app')

@section('title', 'Main Dashboard')
@section('page-icon') <i class="fas fa-chart-line"></i> @endsection
@section('page-title', 'Main Dashboard')
@section('page-description', 'Overview of all operations')

@section('content')

<!-- KPI Cards with improved design -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl shadow-sm p-6 border border-blue-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-clipboard-list text-white text-xl"></i>
            </div>
            <span class="text-3xl font-bold text-blue-700">{{ number_format($totalJobOrders) }}</span>
        </div>
        <h3 class="text-sm font-semibold text-blue-900 uppercase tracking-wide">Total Job Orders</h3>
    </div>

    <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-2xl shadow-sm p-6 border border-emerald-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 bg-emerald-500 rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-industry text-white text-xl"></i>
            </div>
            <span class="text-3xl font-bold text-emerald-700">{{ number_format($totalProduced) }}</span>
        </div>
        <h3 class="text-sm font-semibold text-emerald-900 uppercase tracking-wide">Total Produced</h3>
    </div>

    <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-2xl shadow-sm p-6 border border-amber-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 bg-amber-500 rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-truck text-white text-xl"></i>
            </div>
            <span class="text-3xl font-bold text-amber-700">{{ number_format($totalDelivered) }}</span>
        </div>
        <h3 class="text-sm font-semibold text-amber-900 uppercase tracking-wide">Total Delivered</h3>
    </div>

    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl shadow-sm p-6 border border-purple-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-boxes text-white text-xl"></i>
            </div>
            <span class="text-3xl font-bold text-purple-700">{{ number_format($currentInventory) }}</span>
        </div>
        <h3 class="text-sm font-semibold text-purple-900 uppercase tracking-wide">Current Inventory</h3>
    </div>
</div>

<!-- System Status Notify Button -->
<div class="mb-6">
    <button id="systemStatusBtn" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow">
        Notify Admin: System Status
    </button>
    <span id="systemStatusSpinner" class="ml-3 hidden text-sm text-gray-600">Sending...</span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Ordered vs Produced vs Delivered Chart -->
    <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-200 hover:shadow-xl transition-shadow duration-300">
        <div class="flex items-center mb-6">
            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-chart-bar text-indigo-600"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">Production Analytics</h3>
                <p class="text-sm text-gray-500">Recent 10 Job Orders</p>
            </div>
        </div>
        <div class="h-80">
            <canvas id="comparisonChart"></canvas>
        </div>
    </div>

    <!-- Low Stock Products -->
    <div class="bg-white rounded-2xl shadow-md p-6 border border-gray-200 hover:shadow-xl transition-shadow duration-300">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Low Stock Alert</h3>
                    <p class="text-sm text-gray-500">Products below buffer level</p>
                </div>
            </div>
            @if(count($lowStockProducts ?? []) > 0)
                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold">
                    {{ count($lowStockProducts) }} Items
                </span>
            @endif
        </div>
        <div class="space-y-3 max-h-80 overflow-y-auto pr-2 custom-scrollbar">
            @forelse($lowStockProducts ?? [] as $fg)
                <div class="flex justify-between items-center p-4 bg-gradient-to-r from-red-50 to-orange-50 rounded-xl border border-red-200 hover:shadow-md transition-all duration-200">
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900 mb-1">{{ $fg->product->model_name ?? $fg->product->product_code ?? 'Unknown' }}</p>
                        <p class="text-xs text-gray-600 flex items-center">
                            <i class="fas fa-barcode mr-1.5 text-gray-400"></i>
                            {{ $fg->product->product_code ?? 'N/A' }}
                        </p>
                    </div>
                    <div class="text-right ml-4">
                        <p class="text-2xl font-bold text-red-600">{{ number_format($fg->current_qty) }}</p>
                        <p class="text-xs text-gray-500">of {{ $fg->buffer_stocks ?? '?' }}</p>
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                    </div>
                    <p class="text-gray-500 font-medium">All stock levels are healthy</p>
                    <p class="text-sm text-gray-400 mt-1">No low stock items currently</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Job Orders -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
        <div class="p-6 border-b bg-gradient-to-r from-gray-50 to-gray-100">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-list-alt text-blue-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Recent Job Orders</h3>
                    <p class="text-sm text-gray-500">Latest production orders</p>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">J.O. #</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Product</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Qty</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Encoded By</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($recentJobOrders as $jo)
                    <tr class="hover:bg-blue-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600">{{ $jo->jo_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                            {{ $jo->product->model_name ?? $jo->product->product_code ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ number_format($jo->quantity) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $jo->encodedBy->name ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1.5 text-xs font-bold rounded-full
                                {{ $jo->jo_status === 'JO Full'  ? 'bg-green-100 text-green-700 border border-green-300' : '' }}
                                    {{ $jo->jo_status === 'Approved'  ? 'bg-blue-100 text-blue-700 border border-blue-300' : '' }}
                                    {{ $jo->date_approved ? 'bg-indigo-100 text-indigo-700 border border-indigo-300' : '' }}
                                    {{ $jo->jo_status === 'Pending'     ? 'bg-yellow-100 text-yellow-700 border border-yellow-300' : '' }}
                                    {{ $jo->jo_status === 'Cancelled'   ? 'bg-red-100 text-red-700 border border-red-300' : '' }}">
                                <span class="w-2 h-2 rounded-full mr-2
                                    {{ $jo->jo_status === 'JO Full'  ? 'bg-green-500' : '' }}
                                    {{ $jo->jo_status === 'Approved'  ? 'bg-blue-500' : '' }}
                                    {{ $jo->date_approved ? 'bg-indigo-500' : '' }}
                                    {{ $jo->jo_status === 'Pending'     ? 'bg-yellow-500' : '' }}
                                    {{ $jo->jo_status === 'Cancelled'   ? 'bg-red-500' : '' }}">
                                </span>
                                {{ $jo->date_approved ? 'Approved' : $jo->jo_status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-inbox text-gray-300 text-4xl mb-3"></i>
                                <p class="text-gray-500">No recent job orders</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Deliveries -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
        <div class="p-6 border-b bg-gradient-to-r from-gray-50 to-gray-100">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-shipping-fast text-green-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Recent Deliveries</h3>
                    <p class="text-sm text-gray-500">Latest shipments</p>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Product</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Qty</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">JO #</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Encoded By</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($recentDeliveries as $ds)
                    <tr class="hover:bg-green-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">{{ $ds->delivery_date->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                            {{ $ds->product->model_name ?? $ds->product->product_code ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-800">{{ number_format($ds->qty_scheduled) }} {{ $ds->product->uom }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 font-semibold">{{ $ds->jobOrder->jo_number ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $ds->jobOrder?->encodedBy->name ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-truck text-gray-300 text-4xl mb-3"></i>
                                <p class="text-gray-500">No recent deliveries</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Custom scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>
@endpush

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
                { 
                    label: 'Ordered',   
                    data: ordered,   
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',  
                    borderColor: 'rgb(59, 130, 246)',  
                    borderWidth: 2,
                    borderRadius: 6
                },
                { 
                    label: 'Produced',  
                    data: produced,  
                    backgroundColor: 'rgba(34, 197, 94, 0.7)',   
                    borderColor: 'rgb(34, 197, 94)',   
                    borderWidth: 2,
                    borderRadius: 6
                },
                { 
                    label: 'Delivered', 
                    data: delivered, 
                    backgroundColor: 'rgba(245, 158, 11, 0.7)',  
                    borderColor: 'rgb(245, 158, 11)',  
                    borderWidth: 2,
                    borderRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { 
                    position: 'top',
                    labels: {
                        font: { size: 12, weight: 'bold' },
                        padding: 15,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    padding: 12,
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    borderColor: 'rgba(255, 255, 255, 0.1)',
                    borderWidth: 1
                }
            },
            scales: { 
                y: { 
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        font: { size: 11, weight: '500' }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: { size: 11, weight: '500' }
                    }
                }
            }
        }
    });
});
</script>
<script>
document.getElementById('systemStatusBtn')?.addEventListener('click', async function() {
    const btn = this;
    const spinner = document.getElementById('systemStatusSpinner');
    btn.disabled = true;
    spinner.classList.remove('hidden');
    try {
        const res = await fetch('{{ route('system-status.notify') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({})
        });
        const data = await res.json();
        window.showToast(data.message || 'Notification sent', data.success ? 'success' : 'error');
    } catch (err) {
        console.error('System status notify failed', err);
        window.showToast('Failed to notify admins', 'error');
    } finally {
        btn.disabled = false;
        spinner.classList.add('hidden');
    }
});
</script>
@endpush