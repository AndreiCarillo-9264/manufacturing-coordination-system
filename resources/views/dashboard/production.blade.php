@extends('layouts.app')

@section('title', 'Production Dashboard')
@section('page-icon') <i class="fas fa-industry"></i> @endsection
@section('page-title', 'Production Dashboard')
@section('page-description', 'Monitor production output and job order progress')

@section('content')

<!-- KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gradient-to-br from-red-50 to-rose-100 rounded-2xl shadow-sm p-6 border border-red-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-hourglass-half text-white text-xl"></i>
            </div>
            <span class="text-3xl font-bold text-red-700">{{ number_format($pendingProduction) }}</span>
        </div>
        <h3 class="text-sm font-semibold text-red-900 uppercase tracking-wide">Pending Production</h3>
    </div>

    <div class="bg-gradient-to-br from-emerald-50 to-green-100 rounded-2xl shadow-sm p-6 border border-emerald-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 bg-emerald-500 rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-box-open text-white text-xl"></i>
            </div>
            <span class="text-3xl font-bold text-emerald-700">{{ number_format($producedToday) }}</span>
        </div>
        <h3 class="text-sm font-semibold text-emerald-900 uppercase tracking-wide">Produced Today</h3>
    </div>

    <div class="bg-gradient-to-br from-blue-50 to-cyan-100 rounded-2xl shadow-sm p-6 border border-blue-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-percentage text-white text-xl"></i>
            </div>
            <span class="text-3xl font-bold text-blue-700">{{ $completionRate }}%</span>
        </div>
        <h3 class="text-sm font-semibold text-blue-900 uppercase tracking-wide">Completion Rate</h3>
    </div>

    <div class="bg-gradient-to-br from-amber-50 to-yellow-100 rounded-2xl shadow-sm p-6 border border-amber-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 bg-amber-500 rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-tasks text-white text-xl"></i>
            </div>
            <span class="text-3xl font-bold text-amber-700">{{ number_format($backlogQuantity) }}</span>
        </div>
        <h3 class="text-sm font-semibold text-amber-900 uppercase tracking-wide">Backlog Quantity</h3>
    </div>
</div>

<!-- Chart and Report Section -->
@php
    $isProductionOrAdmin = auth()->user()?->isProduction() || auth()->user()?->isAdmin();
@endphp

<div class="grid grid-cols-1 {{ $isProductionOrAdmin ? 'lg:grid-cols-3' : 'lg:grid-cols-1' }} gap-6 mb-8">
    <!-- Production Chart - Full width when no report access -->
    <div class="{{ $isProductionOrAdmin ? 'lg:col-span-2' : 'lg:col-span-1' }} bg-white rounded-2xl shadow-md p-6 border border-gray-200 hover:shadow-xl transition-shadow duration-300">
        <div class="flex items-center mb-6">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-chart-bar text-blue-600"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">Weekly Production Output</h3>
                <p class="text-sm text-gray-500">Production quantities by week</p>
            </div>
        </div>
        <div class="h-80">
            <canvas id="weekly-production-chart"></canvas>
        </div>
    </div>

    <!-- Generate Report Card - Only visible for production department -->
    @if($isProductionOrAdmin)
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl shadow-md p-6 border border-blue-200 hover:shadow-xl transition-shadow duration-300">
        <div class="flex items-center mb-6">
            <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-3 shadow-md">
                <i class="fas fa-file-pdf text-white"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">Generate Report</h3>
                <p class="text-sm text-gray-500">Export production data</p>
            </div>
        </div>

        <form action="{{ route('reports.production.pdf') }}" method="GET" class="space-y-4">
            <div>
                <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-info-circle text-gray-400 mr-1"></i> Status
                </label>
                <select name="status" id="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm">
                    <option value="">All Statuses</option>
                    <option value="Approved">Approved</option>
                    <option value="In Progress">In Progress</option>
                    <option value="JO Full">JO Full</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="date_from" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt text-gray-400 mr-1"></i> From
                    </label>
                    <input type="date" name="date_from" id="date_from" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-check text-gray-400 mr-1"></i> To
                    </label>
                    <input type="date" name="date_to" id="date_to" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all text-sm">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center justify-center">
                    <i class="fas fa-download mr-2"></i>
                    Download PDF Report
                </button>
            </div>
        </form>
    </div>
    @endif
</div>

<!-- Recent Production Table -->
<div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow duration-300 mb-8">
    <div class="p-6 border-b bg-gradient-to-r from-green-50 to-emerald-50">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center mr-3 shadow-md">
                    <i class="fas fa-check-double text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Recent Production</h3>
                    <p class="text-sm text-gray-500">Recently completed job orders</p>
                </div>
            </div>
            @if(isset($recentProduction) && count($recentProduction) > 0)
                <span class="px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-bold border border-green-300">
                    {{ count($recentProduction) }} Completed
                </span>
            @endif
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">J.O. Number</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Product</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Quantity</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Completed Date</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($recentProduction ?? [] as $production)
                <tr class="hover:bg-green-50 transition-colors duration-150">
                    <td class="px-6 py-4 whitespace-nowrap font-bold text-blue-600">{{ $production->jobOrder->jo_number ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800 font-medium">{{ $production->product->model_name ?? $production->product->product_code ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800 font-semibold">{{ number_format($production->jobOrder->qty ?? 0) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-600 text-sm">{{ $production->updated_at->format('M d, Y h:i A') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-3 py-1.5 text-xs font-bold rounded-full bg-green-100 text-green-700 border border-green-300">
                            <span class="w-2 h-2 rounded-full bg-green-500 mr-2"></span>
                            Completed
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-clipboard-check text-5xl mb-3 opacity-50"></i>
                            <p class="text-sm font-medium">No recent production found</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Production Queue Table -->
<div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
    <div class="p-6 border-b bg-gradient-to-r from-blue-50 to-indigo-50">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-3 shadow-md">
                    <i class="fas fa-cogs text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Production Queue</h3>
                    <p class="text-sm text-gray-500">Job orders awaiting or in production</p>
                </div>
            </div>
            @if(count($awaitingJobs) > 0)
                <span class="px-4 py-2 bg-blue-100 text-blue-700 rounded-full text-sm font-bold border border-blue-300">
                    {{ count($awaitingJobs) }} Active
                </span>
            @endif
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="jo-awaiting-table">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">J.O. Number</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Product</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Ordered Qty</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Encoded By</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100" id="jo-awaiting-tbody">
                @forelse($awaitingJobs as $jo)
                <tr class="hover:bg-blue-50 transition-colors duration-150" data-jo-id="{{ $jo->id }}">
                    <td class="px-6 py-4 whitespace-nowrap font-bold text-blue-600">{{ $jo->jo_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800 font-medium">{{ $jo->product->model_name ?? $jo->product->product_code }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-800 font-semibold">{{ number_format($jo->qty) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-600">{{ $jo->encodedByUser->name ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="status-badge inline-flex items-center px-3 py-1.5 text-xs font-bold rounded-full 
                            {{ $jo->jo_status === 'Approved'  ? 'bg-yellow-100 text-yellow-700 border border-yellow-300' : '' }}
                            {{ $jo->jo_status === 'In Progress' ? 'bg-blue-100 text-blue-700 border border-blue-300' : '' }}
                            {{ $jo->jo_status === 'JO Full'   ? 'bg-green-100 text-green-700 border border-green-300' : '' }}">
                            <span class="w-2 h-2 rounded-full mr-2
                                {{ $jo->jo_status === 'Approved'  ? 'bg-yellow-500' : '' }}
                                {{ $jo->jo_status === 'In Progress' ? 'bg-blue-500' : '' }}
                                {{ $jo->jo_status === 'JO Full'   ? 'bg-green-500' : '' }}">
                            </span>
                            {{ $jo->jo_status ?? 'Unknown' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($jo->jo_status === 'Approved' && (auth()->user()?->isAdmin() || auth()->user()?->isProduction()))
                            <button 
                                type="button"
                                onclick="testClick('{{ $jo->id }}', '{{ $jo->jo_number }}', '{{ $jo->product->model_name ?? $jo->product->product_code }}', '{{ (int) $jo->qty }}', 'start')"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white rounded-lg font-semibold text-xs transition-all duration-300 transform hover:scale-105 shadow-md hover:shadow-lg cursor-pointer"
                                data-jo-id="{{ $jo->id }}"
                                data-jo-number="{{ $jo->jo_number }}"
                                data-jo-product="{{ $jo->product->model_name ?? $jo->product->product_code }}"
                                data-jo-qty="{{ (int) $jo->qty }}"
                                data-action="start">
                                <i class="fas fa-play-circle mr-2"></i>
                                Start Production
                            </button>
                        @endif
                        @if($jo->jo_status === 'In Progress' && (auth()->user()?->isAdmin() || auth()->user()?->isProduction()))
                            <button 
                                type="button"
                                onclick="testClick('{{ $jo->id }}', '{{ $jo->jo_number }}', '{{ $jo->product->model_name ?? $jo->product->product_code }}', '{{ (int) $jo->qty }}', 'complete')"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white rounded-lg font-semibold text-xs transition-all duration-300 transform hover:scale-105 shadow-md hover:shadow-lg cursor-pointer"
                                data-jo-id="{{ $jo->id }}"
                                data-jo-number="{{ $jo->jo_number }}"
                                data-jo-product="{{ $jo->product->model_name ?? $jo->product->product_code }}"
                                data-jo-qty="{{ (int) $jo->qty }}"
                                data-action="complete">
                                <i class="fas fa-check-circle mr-2"></i>
                                Complete Production
                            </button>
                        @endif
                        @if($jo->jo_status === 'JO Full')
                            <span class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-500 rounded-lg font-semibold text-xs border border-gray-300">
                                <i class="fas fa-check mr-2"></i>Completed
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-clipboard-list text-5xl mb-3 opacity-50"></i>
                            <p class="text-sm font-medium">No job orders in production queue</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Production Modal -->
<div id="productionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <div id="modalIconContainer" class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                    <i id="modalIcon" class="fas fa-play-circle text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <h3 id="modalTitle" class="text-xl font-bold text-gray-800">Start Production</h3>
                    <p id="modalSubtitle" class="text-sm text-gray-500">Begin manufacturing process</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div id="modalWarning" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <i class="fas fa-info-circle text-blue-600 mt-0.5 mr-3"></i>
                    <div>
                        <p class="text-sm font-semibold text-blue-800">Production Starting</p>
                        <p id="modalWarningText" class="text-xs text-blue-700 mt-1">This will mark the job order as "In Progress" and notify relevant departments.</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 rounded-lg p-4 mb-6 space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">J.O. Number:</span>
                    <span id="modalJoNumber" class="text-sm font-bold text-blue-600">#JO-001</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Product:</span>
                    <span id="modalProduct" class="text-sm font-semibold text-gray-800">Product Name</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Quantity:</span>
                    <span id="modalQty" class="text-sm font-bold text-emerald-600">1,000</span>
                </div>
            </div>
            
            <p id="modalQuestion" class="text-sm text-gray-600 mb-6">
                Are you ready to start production for this job order?
            </p>
        </div>

        <div class="p-6 bg-gray-50 rounded-b-2xl flex gap-3">
            <button 
                onclick="closeProductionModal()" 
                class="flex-1 px-4 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-100 transition-all duration-300">
                <i class="fas fa-times mr-2"></i>Cancel
            </button>
            <button 
                id="confirmActionBtn"
                onclick="confirmProductionAction()" 
                class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl">
                <i class="fas fa-play mr-2"></i>Yes, Start Production
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Initialize weekly production chart
    try {
        const ctx = document.getElementById('weekly-production-chart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($productionByWeek->pluck('week')->toArray() ?? []) !!},
                    datasets: [
                        {
                            label: 'Production Count',
                            data: {!! json_encode($productionByWeek->pluck('count')->toArray() ?? []) !!},
                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                            borderColor: 'rgb(59, 130, 246)',
                            borderWidth: 2,
                            borderRadius: 8,
                            barPercentage: 0.7
                        },
                        {
                            label: 'Total Quantity',
                            data: {!! json_encode($productionByWeek->pluck('total_qty')->toArray() ?? []) !!},
                            type: 'line',
                            borderColor: 'rgb(16, 185, 129)',
                            backgroundColor: 'rgba(16, 185, 129, 0.2)',
                            yAxisID: 'y1',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 3,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Count', font: { weight: 'bold', size: 12 } },
                            grid: { color: 'rgba(0, 0, 0, 0.05)' }
                        },
                        y1: {
                            position: 'right',
                            beginAtZero: true,
                            title: { display: true, text: 'Quantity', font: { weight: 'bold', size: 12 } },
                            grid: { drawOnChartArea: false }
                        }
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
                    }
                }
            });
        }
    } catch(e) {
        console.error('Chart error:', e);
    }

    // Modal state
    let currentJobOrderId = null;
    let currentAction = null;

    function testClick(id, joNumber, product, qty, action) {
        console.log('✓ testClick invoked', { id, joNumber, product, qty, action });
        showProductionModal(id, joNumber, product, qty, action);
    }

    function showProductionModal(id, joNumber, product, qty, action) {
        console.log('✓ showProductionModal called', { id, joNumber, product, qty, action });
        currentJobOrderId = id;
        currentAction = action;
        
        document.getElementById('modalJoNumber').textContent = joNumber;
        document.getElementById('modalProduct').textContent = product;
        document.getElementById('modalQty').textContent = Number(qty).toLocaleString();
        
        const iconContainer = document.getElementById('modalIconContainer');
        const icon = document.getElementById('modalIcon');
        const title = document.getElementById('modalTitle');
        const subtitle = document.getElementById('modalSubtitle');
        const warning = document.getElementById('modalWarning');
        const warningIcon = warning ? warning.querySelector('i') : null;
        const warningTitle = warning ? warning.querySelector('p:first-of-type') : null;
        const warningText = document.getElementById('modalWarningText');
        const question = document.getElementById('modalQuestion');
        const confirmBtn = document.getElementById('confirmActionBtn');
        
        if (action === 'start') {
            iconContainer.className = 'w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4';
            icon.className = 'fas fa-play-circle text-blue-600 text-2xl';
            title.textContent = 'Start Production';
            subtitle.textContent = 'Begin manufacturing process';
            warning.className = 'bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6';
            if (warningIcon) warningIcon.className = 'fas fa-info-circle text-blue-600 mt-0.5 mr-3';
            if (warningTitle) {
                warningTitle.textContent = 'Production Starting';
                warningTitle.className = 'text-sm font-semibold text-blue-800';
            }
            warningText.textContent = 'This will mark the job order as "In Progress" and notify relevant departments.';
            warningText.className = 'text-xs text-blue-700 mt-1';
            question.textContent = 'Are you ready to start production for this job order?';
            confirmBtn.className = 'flex-1 px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl';
            confirmBtn.innerHTML = '<i class="fas fa-play mr-2"></i>Yes, Start Production';
        } else {
            iconContainer.className = 'w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4';
            icon.className = 'fas fa-check-circle text-green-600 text-2xl';
            title.textContent = 'Complete Production';
            subtitle.textContent = 'Finish manufacturing process';
            warning.className = 'bg-green-50 border border-green-200 rounded-lg p-4 mb-6';
            if (warningIcon) warningIcon.className = 'fas fa-check-circle text-green-600 mt-0.5 mr-3';
            if (warningTitle) {
                warningTitle.textContent = 'Production Completion';
                warningTitle.className = 'text-sm font-semibold text-green-800';
            }
            warningText.textContent = 'This will mark the job order as "Completed" and update inventory records.';
            warningText.className = 'text-xs text-green-700 mt-1';
            question.textContent = 'Have you finished production for this job order?';
            confirmBtn.className = 'flex-1 px-4 py-3 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl';
            confirmBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Yes, Mark Complete';
        }
        
        const modal = document.getElementById('productionModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            console.log('✓ Production modal displayed');
        } else {
            console.error('✗ Production modal element not found');
        }
    }

    function closeProductionModal() {
        document.getElementById('productionModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        currentJobOrderId = null;
        currentAction = null;
    }

    async function confirmProductionAction() {
        console.log('✓ confirmProductionAction called');
        if (!currentJobOrderId || !currentAction) {
            console.error('✗ Missing currentJobOrderId or currentAction');
            return;
        }

        const newStatus = currentAction === 'start' ? 'In Progress' : 'JO Full';
        console.log('✓ Setting status to:', newStatus);

        try {
            const response = await fetch(`/job-orders/${currentJobOrderId}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ jo_status: newStatus })
            });

            console.log('✓ Response received:', response.status);
            const data = await response.json();
            console.log('✓ Response data:', data);
            
            if (data.success) {
                showToast(data.message, 'success');
                closeProductionModal();
                
                // Auto-refresh page after 1.5 seconds to show updated status
                setTimeout(() => location.reload(), 1500);
                
                return;
            } else {
                console.error('✗ Response not successful:', data);
                showToast(data.message || 'Failed to update status', 'error');
            }
        } catch (error) {
            console.error('✗ Fetch error:', error);
            showToast('An error occurred: ' + error.message, 'error');
        }
    }

    // Toast notification function
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
        const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-times-circle' : 'fa-info-circle';
        
        toast.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-xl shadow-2xl z-50 flex items-center transform transition-all duration-300 translate-x-0`;
        toast.innerHTML = `
            <i class="fas ${icon} mr-3 text-xl"></i>
            <span class="font-medium">${message}</span>
        `;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Close modal on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeProductionModal();
        }
    });

    // Close modal when clicking outside
    document.getElementById('productionModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeProductionModal();
        }
    });

    console.log('✓ Production dashboard script loaded successfully');
    console.log('✓ Available functions:', typeof showProductionModal, typeof confirmProductionAction, typeof closeProductionModal);
</script>
@endpush