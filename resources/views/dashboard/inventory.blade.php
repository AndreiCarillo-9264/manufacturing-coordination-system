@extends('layouts.app')

@section('title', 'Inventory Dashboard')
@section('page-icon') <i class="fas fa-boxes"></i> @endsection
@section('page-title', 'Inventory Dashboard')
@section('page-description', 'Track stock levels and manage finished goods inventory')

@section('content')

{{-- Alert Messages --}}
@if ($errors->any())
<div class="mb-6 bg-red-50 border border-red-200/50 rounded-lg p-4 flex items-start">
    <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-3 flex-shrink-0"></i>
    <div class="flex-1">
        <h3 class="font-bold text-red-800 mb-1">Error</h3>
        @foreach ($errors->all() as $error)
        <p class="text-sm text-red-700">{{ $error }}</p>
        @endforeach
    </div>
</div>
@endif

@if (session('success'))
<div class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 flex items-start">
    <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3 flex-shrink-0"></i>
    <div class="flex-1">
        <h3 class="font-bold text-green-800 mb-1">Success</h3>
        <p class="text-sm text-green-700">{{ session('success') }}</p>
    </div>
</div>
@endif

@if (session('error'))
<div class="mb-6 bg-red-50 border border-red-200/50 rounded-lg p-4 flex items-start">
    <i class="fas fa-exclamation-circle text-red-500 mt-0.5 mr-3 flex-shrink-0"></i>
    <div class="flex-1">
        <h3 class="font-bold text-red-800 mb-1">Error</h3>
        <p class="text-sm text-red-700">{{ session('error') }}</p>
    </div>
</div>
@endif

<!-- KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-2xl shadow-sm p-6 border border-orange-200/50 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-4">
            <div class="w-14 h-14 bg-gradient-to-br from-[#8d2909] to-[#b03410] rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-boxes text-white text-xl"></i>
            </div>
            <span class="text-4xl font-bold bg-gradient-to-br from-[#8d2909] to-[#b03410] bg-clip-text text-transparent">{{ number_format($totalProducts ?? 0) }}</span>
        </div>
        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide">Total Products</h3>
    </div>

    <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-2xl shadow-sm p-6 border border-emerald-200/50 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-4">
            <div class="w-14 h-14 bg-emerald-500 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-layer-group text-white text-xl"></i>
            </div>
            <span class="text-4xl font-bold text-emerald-700">{{ number_format($totalStock ?? 0) }}</span>
        </div>
        <h3 class="text-sm font-bold text-emerald-900 uppercase tracking-wide">Total Stock Units</h3>
    </div>

    <div class="bg-gradient-to-br from-red-50 to-rose-50 rounded-2xl shadow-sm p-6 border border-red-200/50 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-4">
            <div class="w-14 h-14 bg-red-500 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-exclamation-triangle text-white text-xl"></i>
            </div>
            <span class="text-4xl font-bold text-red-700">{{ $lowStockCount ?? 0 }}</span>
        </div>
        <h3 class="text-sm font-bold text-red-900 uppercase tracking-wide">Low Stock Items</h3>
    </div>

    <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl shadow-sm p-6 border border-amber-200/50 hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-4">
            <div class="w-14 h-14 bg-amber-500 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-dollar-sign text-white text-xl"></i>
            </div>
            <span class="text-4xl font-bold text-amber-700">₱{{ number_format($totalValue ?? 0, 2) }}</span>
        </div>
        <h3 class="text-sm font-bold text-amber-900 uppercase tracking-wide">Total Inventory Value</h3>
    </div>
</div>

<!-- Chart and Report Section -->
@php
    $isInventoryOrAdmin = auth()->user()?->isInventory() || auth()->user()?->isAdmin();
@endphp

<div class="grid grid-cols-1 {{ $isInventoryOrAdmin ? 'lg:grid-cols-3' : 'lg:grid-cols-1' }} gap-6 mb-8">
    <!-- Stock Movement Chart -->
    <div class="{{ $isInventoryOrAdmin ? 'lg:col-span-2' : 'lg:col-span-1' }} bg-white rounded-2xl shadow-md p-6 border border-gray-200 hover:shadow-xl transition-shadow duration-300">
        <div class="flex items-center mb-6">
            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-chart-line text-purple-600"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">Stock Movement</h3>
                <p class="text-sm text-gray-500">Daily stock in and stock out trends</p>
            </div>
        </div>
        <div class="h-80">
            <canvas id="stockMovementChart"></canvas>
        </div>
    </div>

    <!-- Generate Report Card - Only visible for inventory department -->
    @if($isInventoryOrAdmin)
    <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-2xl shadow-md p-6 border border-purple-200 hover:shadow-xl transition-shadow duration-300">
        <div class="flex items-center mb-6">
            <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center mr-3 shadow-md">
                <i class="fas fa-file-pdf text-white"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">Generate Report</h3>
                <p class="text-sm text-gray-500">Export inventory data</p>
            </div>
        </div>

        <form action="{{ route('reports.inventory.pdf') }}" method="GET" class="space-y-4">
            <div>
                <label for="customer" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-user-tie text-gray-400 mr-1"></i> Customer
                </label>
                <select name="customer" id="customer" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all text-sm">
                    <option value="">All Customers</option>
                    @foreach($customers ?? [] as $customer)
                        <option value="{{ $customer }}">{{ $customer }}</option>
                    @endforeach
                </select>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center justify-center">
                    <i class="fas fa-download mr-2"></i>
                    Download PDF Report
                </button>
            </div>
        </form>
    </div>
    @endif
</div>

<!-- Inventory Overview -->
<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow duration-300 mb-8">
    <div class="p-6 border-b bg-gradient-to-r from-[#8d2909]/5 to-orange-50">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-gradient-to-br from-[#8d2909] to-[#b03410] rounded-lg flex items-center justify-center mr-3">
                    <i class="fas fa-warehouse text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Current Inventory Status</h3>
                    <p class="text-sm text-gray-500">Real-time stock levels for all products</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <div class="flex items-center px-3 py-1.5 bg-green-100 rounded-lg">
                    <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                    <span class="text-xs font-bold text-green-700">Healthy</span>
                </div>
                <div class="flex items-center px-3 py-1.5 bg-yellow-100 rounded-lg">
                    <span class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></span>
                    <span class="text-xs font-bold text-yellow-700">Warning</span>
                </div>
                <div class="flex items-center px-3 py-1.5 bg-red-100 rounded-lg">
                    <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                    <span class="text-xs font-bold text-red-700">Critical</span>
                </div>
            </div>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Product Code</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Model Name</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Current Stock</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Buffer Stock</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Stock Health</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Last Updated</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($inventoryItems ?? [] as $item)
                @php
                    if ($item->current_qty == 0) {
                        $stockPercentage = 0;
                        $healthClass = 'bg-red-100 text-red-700 border-red-300';
                        $dotClass = 'bg-red-500';
                        $healthText = 'Critical';
                        $rowClass = 'hover:bg-red-50';
                    } elseif ($item->buffer_stocks > 0) {
                        $stockPercentage = min(100, ($item->current_qty / $item->buffer_stocks) * 100);
                        if ($stockPercentage >= 100) {
                            $healthClass = 'bg-green-100 text-green-700 border-green-300';
                            $dotClass = 'bg-green-500';
                            $healthText = 'Healthy';
                            $rowClass = 'hover:bg-green-50';
                        } elseif ($stockPercentage >= 50) {
                            $healthClass = 'bg-yellow-100 text-yellow-700 border-yellow-300';
                            $dotClass = 'bg-yellow-500';
                            $healthText = 'Warning';
                            $rowClass = 'hover:bg-yellow-50';
                        } else {
                            $healthClass = 'bg-red-100 text-red-700 border-red-300';
                            $dotClass = 'bg-red-500';
                            $healthText = 'Critical';
                            $rowClass = 'hover:bg-red-50';
                        }
                    } else {
                        $stockPercentage = 0;
                        $healthClass = 'bg-gray-100 text-gray-700 border-gray-300';
                        $dotClass = 'bg-gray-500';
                        $healthText = 'Unknown';
                        $rowClass = 'hover:bg-gray-50';
                    }
                @endphp
                <tr class="{{ $rowClass }} transition-colors duration-150">
                    <td class="px-6 py-4 whitespace-nowrap font-bold text-blue-600">{{ $item->product_code ?? $item->product?->product_code ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800 font-medium">{{ $item->model_name ?? $item->product?->model_name ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-bold {{ $item->current_qty == 0 ? 'text-red-600' : 'text-gray-900' }}">
                            {{ number_format($item->current_qty) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($item->buffer_stocks ?? 0) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-3 py-1.5 text-xs font-bold rounded-full border {{ $healthClass }}">
                            <span class="w-2 h-2 rounded-full mr-2 {{ $dotClass }}"></span>
                            {{ $healthText }}
                            @if($item->buffer_stocks > 0)
                                <span class="ml-2 text-xs">({{ number_format($stockPercentage, 0) }}%)</span>
                            @endif
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $item->updated_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-box-open text-5xl mb-3 opacity-50"></i>
                            <p class="text-sm font-medium">No inventory items found</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Unverified Inventory Counts (Pending Verification Table) -->
<div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden hover:shadow-xl transition-shadow duration-300 mb-8">
    <div class="p-6 border-b bg-gradient-to-r from-orange-50 to-amber-50">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-amber-600 rounded-xl shadow-md flex items-center justify-center mr-3">
                    <i class="fas fa-tasks text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Pending Inventory Verification</h3>
                    <p class="text-sm text-gray-500">Physical counts awaiting verification</p>
                </div>
            </div>
            <span class="px-4 py-2 bg-orange-100 text-orange-700 rounded-full text-sm font-bold border border-orange-300">
                {{ count($unverifiedInventories ?? []) }} Pending
            </span>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Tag Number</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Product</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Count</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Location</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Counted At</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($unverifiedInventories ?? [] as $inv)
                <tr class="hover:bg-orange-50 transition-colors duration-150">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-orange-700">{{ $inv->tag_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800">{{ $inv->product?->model_name ?? $inv->product?->product_code ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ number_format($inv->fg_quantity) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $inv->location ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $inv->counted_at?->format('M d, Y H:i') ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-3 py-1 text-xs font-bold rounded-full bg-orange-100 text-orange-700 border border-orange-300">
                            <span class="w-2 h-2 bg-orange-500 rounded-full mr-2"></span>
                            Pending
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <button type="button" 
                                onclick="showVerifyModal('{{ $inv->id }}', '{{ $inv->tag_number }}', '{{ $inv->product?->model_name ?? $inv->product?->product_code ?? '—' }}', '{{ number_format($inv->fg_quantity) }}')"
                                class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium text-sm transition-all duration-200 shadow-md hover:shadow-lg"
                                title="Verify Count">
                            <i class="fas fa-check-circle mr-1.5"></i> Verify
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-check-circle text-green-500 text-2xl"></i>
                            </div>
                            <p class="text-gray-600 font-medium">All inventories verified</p>
                            <p class="text-sm text-gray-400 mt-1">No pending inventory counts awaiting verification</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Verify Modal -->
<div id="verifyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center">
                <div class="w-14 h-14 bg-green-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-check-double text-green-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Verify Inventory Count</h3>
                    <p class="text-sm text-gray-500">Confirm physical count verification</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-4 mb-6 border border-green-200">
                <div class="space-y-3">
                    <div>
                        <p class="text-xs uppercase font-bold text-gray-600">Tag Number</p>
                        <span id="modalTagNumber" class="text-lg font-bold text-green-600"></span>
                    </div>
                    <div>
                        <p class="text-xs uppercase font-bold text-gray-600">Product</p>
                        <span id="modalProduct" class="text-sm font-medium text-gray-800"></span>
                    </div>
                    <div>
                        <p class="text-xs uppercase font-bold text-gray-600">Count Quantity</p>
                        <span id="modalQuantity" class="text-sm font-bold text-gray-800"></span>
                    </div>
                </div>
            </div>
            
            <p class="text-sm text-gray-600 mb-6">
                Once verified, this inventory count status will be marked as complete and cannot be changed. Are you sure you want to proceed?
            </p>
        </div>

        <div class="p-6 bg-gray-50 rounded-b-2xl flex gap-3">
            <button 
                onclick="closeVerifyModal()" 
                class="flex-1 px-4 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-100 transition-all duration-300">
                <i class="fas fa-times mr-2"></i>Cancel
            </button>
            <button 
                id="confirmVerifyBtn"
                onclick="submitVerifyForm()" 
                class="flex-1 px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-semibold transition-all duration-300 shadow-md hover:shadow-lg transform hover:scale-105">
                <i class="fas fa-check-circle mr-2"></i>Verify Count
            </button>
        </div>
    </div>
</div>

<!-- Hidden form for verification -->
<form id="verifyForm" method="POST" style="display: none;">
    @csrf
</form>

@endsection

@push('styles')
<style>
    /* Custom scrollbar with theme color */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #fef2f2;
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: linear-gradient(to bottom, #8d2909, #b03410);
        border-radius: 10px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(to bottom, #6b1f07, #8d2909);
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('stockMovementChart')?.getContext('2d');
    if (!ctx) {
        console.log('Stock movement chart canvas not found');
        return;
    }

    const labels = @json($stockMovementData->pluck('date') ?? []);
    const stockIn = @json($stockMovementData->pluck('stock_in') ?? []);
    const stockOut = @json($stockMovementData->pluck('stock_out') ?? []);

    console.log('Stock movement data:', { labels, stockIn, stockOut });

    if (labels.length === 0) {
        console.log('No stock movement data available');
        return;
    }

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Stock In',
                    data: stockIn,
                    borderColor: 'rgb(34, 197, 94)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgb(34, 197, 94)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                },
                {
                    label: 'Stock Out',
                    data: stockOut,
                    borderColor: 'rgb(239, 68, 68)',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgb(239, 68, 68)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
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
// Verify Modal Functions
let currentVerifyId = null;

function showVerifyModal(id, tagNumber, product, quantity) {
    currentVerifyId = id;
    document.getElementById('modalTagNumber').textContent = tagNumber;
    document.getElementById('modalProduct').textContent = product;
    document.getElementById('modalQuantity').textContent = quantity;
    document.getElementById('verifyModal').classList.remove('hidden');
}

function closeVerifyModal() {
    document.getElementById('verifyModal').classList.add('hidden');
    currentVerifyId = null;
}

function submitVerifyForm() {
    if (!currentVerifyId) return;
    
    const form = document.getElementById('verifyForm');
    form.action = `/actual-inventories/${currentVerifyId}/verify`;
    form.submit();
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeVerifyModal();
    }
});
</script>
@endpush