@extends('layouts.app')

@section('title', 'Sales Dashboard')
@section('page-icon') <i class="fas fa-shopping-cart"></i> @endsection
@section('page-title', 'Sales Dashboard')
@section('page-description', 'Manage job orders and track sales performance')

@section('content')

<!-- KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-2xl shadow-sm p-6 border border-amber-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 bg-amber-500 rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-clipboard-list text-white text-xl"></i>
            </div>
            <span class="text-3xl font-bold text-amber-700">{{ number_format($totalJobOrders) }}</span>
        </div>
        <h3 class="text-sm font-semibold text-amber-900 uppercase tracking-wide">Total Job Orders</h3>
    </div>

    <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl shadow-sm p-6 border border-blue-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-clock text-white text-xl"></i>
            </div>
            <span class="text-3xl font-bold text-blue-700">{{ number_format($pendingJobOrders) }}</span>
        </div>
        <h3 class="text-sm font-semibold text-blue-900 uppercase tracking-wide">Pending</h3>
    </div>

    <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 rounded-2xl shadow-sm p-6 border border-emerald-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 bg-emerald-500 rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-check-circle text-white text-xl"></i>
            </div>
            <span class="text-3xl font-bold text-emerald-700">{{ number_format($approvedJobOrders) }}</span>
        </div>
        <h3 class="text-sm font-semibold text-emerald-900 uppercase tracking-wide">Approved</h3>
    </div>

    <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-2xl shadow-sm p-6 border border-purple-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 bg-purple-500 rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-ban text-white text-xl"></i>
            </div>
            <span class="text-3xl font-bold text-purple-700">{{ number_format($cancelledJobOrders) }}</span>
        </div>
        <h3 class="text-sm font-semibold text-purple-900 uppercase tracking-wide">Canceled</h3>
    </div>
</div>

<!-- Chart and Report Section -->
@php
    $isSalesOrAdmin = auth()->user()?->isSales() || auth()->user()?->isAdmin();
@endphp

<div class="grid grid-cols-1 {{ $isSalesOrAdmin ? 'lg:grid-cols-3' : 'lg:grid-cols-1' }} gap-6 mb-8">
    <!-- Job Orders Chart - Full width when no report access -->
    <div class="{{ $isSalesOrAdmin ? 'lg:col-span-2' : 'lg:col-span-1' }} bg-white rounded-2xl shadow-md p-6 border border-gray-200 hover:shadow-xl transition-shadow duration-300">
        <div class="flex items-center mb-6">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-chart-line text-blue-600"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">Weekly Performance</h3>
                <p class="text-sm text-gray-500">Job orders and quantities by week</p>
            </div>
        </div>
        <div class="h-80">
            <canvas id="weekly-jo-chart"></canvas>
        </div>
    </div>

    <!-- Generate Report Card - Only visible for sales department -->
    @if($isSalesOrAdmin )
    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl shadow-md p-6 border border-indigo-200 hover:shadow-xl transition-shadow duration-300">
        <div class="flex items-center mb-6">
            <div class="w-10 h-10 bg-indigo-500 rounded-lg flex items-center justify-center mr-3 shadow-md">
                <i class="fas fa-file-pdf text-white"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">Generate Report</h3>
                <p class="text-sm text-gray-500">Export job order data</p>
            </div>
        </div>

        <form action="{{ route('reports.job-orders.pdf') }}" method="GET" class="space-y-4">
            <div>
                <label for="customer" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-user-tie text-gray-400 mr-1"></i> Customer
                </label>
                <select name="customer" id="customer" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all text-sm">
                    <option value="">All Customers</option>
                    @foreach($customers ?? [] as $customer)
                        <option value="{{ $customer }}">{{ $customer }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-info-circle text-gray-400 mr-1"></i> Status
                </label>
                <select name="status" id="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all text-sm">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="date_from" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt text-gray-400 mr-1"></i> From
                    </label>
                    <input type="date" name="date_from" id="date_from" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all text-sm">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-check text-gray-400 mr-1"></i> To
                    </label>
                    <input type="date" name="date_to" id="date_to" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 transition-all text-sm">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center justify-center">
                    <i class="fas fa-download mr-2"></i>
                    Download PDF Report
                </button>
            </div>
        </form>
    </div>
    @endif
</div>

<!-- Pending Job Orders Table -->
<div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow duration-300 mb-8">
    <div class="p-6 border-b bg-gradient-to-r from-yellow-50 to-orange-50">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center mr-3 shadow-md">
                    <i class="fas fa-exclamation-circle text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Pending Job Orders</h3>
                    <p class="text-sm text-gray-500">Orders awaiting approval</p>
                </div>
            </div>
            @if(\App\Models\JobOrder::pending()->count() > 0)
                <span class="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-full text-sm font-bold border border-yellow-300">
                    {{ \App\Models\JobOrder::pending()->count() }} Pending
                </span>
            @endif
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">JO #</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Product</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Qty</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Date Needed</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Encoded By</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse(\App\Models\JobOrder::pending()->get() as $jo)
                    {{-- Debug: Show job order details --}}
                    <!-- DEBUG: JO={{ $jo->id }}, status={{ $jo->jo_status }}, user_dept={{ auth()->user()?->department }} -->
                    <tr class="hover:bg-yellow-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600">
                            <a href="{{ route('job-orders.index') }}" class="hover:underline flex items-center">
                                <i class="fas fa-external-link-alt text-xs mr-2"></i>
                                {{ $jo->jo_number }}
                            </a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">{{ $jo->product->model_name ?? $jo->product->product_code ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-semibold">{{ number_format($jo->qty) }} {{ $jo->uom }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $jo->date_needed?->format('M d, Y') ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $jo->encodedBy->name ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($jo->jo_status === 'Pending' && (auth()->user()?->isAdmin() || auth()->user()?->isSales() || auth()->user()?->isInventory()))
                            <button 
                                type="button"
                                id="approveBtn{{ $jo->id }}"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white rounded-lg font-semibold text-xs transition-all duration-300 transform hover:scale-105 shadow-md hover:shadow-lg cursor-pointer"
                                data-jo-id="{{ $jo->id }}"
                                data-jo-number="{{ $jo->jo_number }}"
                                data-jo-product="{{ $jo->product->model_name ?? $jo->product->product_code ?? 'Unknown' }}"
                                data-jo-qty="{{ (int) $jo->qty }}">
                                <i class="fas fa-check-circle mr-2"></i> 
                                Approve Order
                            </button>
                            @else
                            <span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold text-gray-500 bg-gray-100 rounded-lg">
                                <i class="fas fa-lock mr-1"></i>Not Available
                            </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-check-double text-green-500 text-2xl"></i>
                                </div>
                                <p class="text-gray-600 font-medium">All clear!</p>
                                <p class="text-sm text-gray-400 mt-1">No pending job orders. All orders have been approved.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Job Orders Table -->
<div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
    <div class="p-6 border-b bg-gradient-to-r from-gray-50 to-gray-100">
        <div class="flex items-center">
            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-history text-blue-600"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">Recent Job Orders</h3>
                <p class="text-sm text-gray-500">Latest order history</p>
            </div>
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">JO #</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Product</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Qty</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Encoded By</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Needed</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($recentJobOrders as $jo)
                    <tr class="hover:bg-blue-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-600">{{ $jo->jo_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">{{ $jo->product->model_name ?? $jo->product->product_code ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-semibold">{{ number_format($jo->qty) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $jo->encodedBy->name ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1.5 text-xs font-bold rounded-full {{ getStatusBadgeClass($jo->status) }}">
                                <span class="w-2 h-2 rounded-full mr-2 {{ getStatusDotClass($jo->status) }}"></span>
                                {{ ucfirst(str_replace('_', ' ', $jo->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $jo->date_needed?->format('M d, Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-inbox text-gray-300 text-4xl mb-3"></i>
                                <p class="text-gray-500">No recent job orders found.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Approval Confirmation Modal -->
<div id="approvalModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-check-circle text-green-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Approve Job Order</h3>
                    <p class="text-sm text-gray-500">Confirm order approval</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-4 mb-6 border border-blue-200">
                <div class="space-y-2">
                    <div class="flex items-center">
                        <span class="text-sm font-semibold text-gray-600 w-24">JO Number:</span>
                        <span id="modalJoNumber" class="text-sm font-bold text-blue-600"></span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-sm font-semibold text-gray-600 w-24">Product:</span>
                        <span id="modalProduct" class="text-sm font-medium text-gray-800"></span>
                    </div>
                    <div class="flex items-center">
                        <span class="text-sm font-semibold text-gray-600 w-24">Quantity:</span>
                        <span id="modalQty" class="text-sm font-bold text-gray-800"></span>
                    </div>
                </div>
            </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 mr-3"></i>
                    <div>
                        <p class="text-sm font-semibold text-yellow-800">Important Notice</p>
                        <p class="text-xs text-yellow-700 mt-1">This action will approve the job order and move it to production queue. This cannot be easily undone.</p>
                    </div>
                </div>
            </div>

            <p class="text-sm text-gray-600 mb-6">Are you sure you want to approve this job order?</p>
        </div>
        
        <div class="p-6 bg-gray-50 rounded-b-2xl flex gap-3">
            <button 
                onclick="closeApprovalModal()" 
                class="flex-1 px-4 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-100 transition-all duration-300">
                <i class="fas fa-times mr-2"></i>Cancel
            </button>
            <button 
                id="confirmApproveBtn"
                onclick="confirmApproval()" 
                class="flex-1 px-4 py-3 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl">
                <i class="fas fa-check mr-2"></i>Yes, Approve
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    console.log('✓ Sales dashboard script loading...');
    
    // Initialize Chart
    try {
        const ctx = document.getElementById('weekly-jo-chart');
        if (ctx) {
            new Chart(ctx.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! json_encode($jobOrdersByWeek->pluck('week_number')->toArray()) !!},
                    datasets: [
                        {
                            label: 'Job Orders',
                            data: {!! json_encode($jobOrdersByWeek->pluck('count')->toArray()) !!},
                            backgroundColor: 'rgba(59, 130, 246, 0.7)',
                            borderColor: 'rgb(59, 130, 246)',
                            borderWidth: 2,
                            borderRadius: 8
                        },
                        {
                            label: 'Total Quantity',
                            data: {!! json_encode($jobOrdersByWeek->pluck('total_qty')->toArray()) !!},
                            type: 'line',
                            borderColor: 'rgb(245, 158, 11)',
                            backgroundColor: 'rgba(245, 158, 11, 0.2)',
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

    // Status badge classes
    function getStatusBadgeClass(status) {
        const statusMap = {
            pending: 'bg-yellow-100 text-yellow-700 border border-yellow-300',
            approved: 'bg-blue-100 text-blue-700 border border-blue-300',
            in_progress: 'bg-indigo-100 text-indigo-700 border border-indigo-300',
            completed: 'bg-green-100 text-green-700 border border-green-300',
            cancelled: 'bg-red-100 text-red-700 border border-red-300'
        };
        return statusMap[status] || 'bg-gray-100 text-gray-700 border border-gray-300';
    }

    function getStatusDotClass(status) {
        const statusMap = {
            pending: 'bg-yellow-500',
            approved: 'bg-blue-500',
            in_progress: 'bg-indigo-500',
            completed: 'bg-green-500',
            cancelled: 'bg-red-500'
        };
        return statusMap[status] || 'bg-gray-500';
    }

    // Modal state
    let currentJobOrderId = null;

    // Show approval modal
    function showApprovalModal(id, joNumber, product, qty) {
        console.log('✓ showApprovalModal called with:', { id, joNumber, product, qty });
        currentJobOrderId = id;
        document.getElementById('modalJoNumber').textContent = joNumber;
        document.getElementById('modalProduct').textContent = product;
        document.getElementById('modalQty').textContent = qty.toLocaleString();
        const modal = document.getElementById('approvalModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            console.log('✓ Modal displayed');
        }
    }

    // Close approval modal
    function closeApprovalModal() {
        const modal = document.getElementById('approvalModal');
        if (modal) {
            modal.classList.add('hidden');
        }
        document.body.style.overflow = 'auto';
        currentJobOrderId = null;
    }

    // Confirm approval
    function confirmApproval() {
        console.log('✓ confirmApproval called with ID:', currentJobOrderId);
        if (!currentJobOrderId) {
            console.error('✗ No currentJobOrderId set!');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/job-orders/' + currentJobOrderId + '/approve';
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        console.log('✓ CSRF token found:', token ? 'Yes' : 'No');
        csrfToken.value = token;
        
        form.appendChild(csrfToken);
        document.body.appendChild(form);
        console.log('✓ Submitting form to:', form.action);
        form.submit();
    }

    // Handle approve button clicks
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('[data-jo-id]');
        if (btn) {
            console.log('✓ Approve button clicked');
            const joId = btn.getAttribute('data-jo-id');
            const joNumber = btn.getAttribute('data-jo-number');
            const joProduct = btn.getAttribute('data-jo-product');
            const joQty = parseInt(btn.getAttribute('data-jo-qty'));
            showApprovalModal(joId, joNumber, joProduct, joQty);
        }
    });

    // Event listeners
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeApprovalModal();
        }
    });

    const approvalModal = document.getElementById('approvalModal');
    if (approvalModal) {
        approvalModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeApprovalModal();
            }
        });
    }

    console.log('✓ Sales dashboard script loaded successfully');
    console.log('✓ Available functions:', typeof showApprovalModal, typeof confirmApproval, typeof closeApprovalModal);
</script>
@endpush