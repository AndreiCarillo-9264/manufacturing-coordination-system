@extends('layouts.app')

@section('title', 'Logistics Dashboard')
@section('page-icon') <i class="fas fa-truck"></i> @endsection
@section('page-title', 'Logistics Dashboard')
@section('page-description', 'Monitor deliveries, transfers and shipping status')

@section('content')

<!-- KPI Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
    <div class="bg-gradient-to-br from-emerald-50 to-green-100 rounded-2xl shadow-sm p-6 border border-emerald-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 bg-emerald-500 rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-calendar-check text-white text-xl"></i>
            </div>
            <span class="text-3xl font-bold text-emerald-700">{{ $deliveriesToday }}</span>
        </div>
        <h3 class="text-sm font-semibold text-emerald-900 uppercase tracking-wide">Deliveries Today</h3>
    </div>

    <div class="bg-gradient-to-br from-amber-50 to-yellow-100 rounded-2xl shadow-sm p-6 border border-amber-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 bg-amber-500 rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-clock text-white text-xl"></i>
            </div>
            <span class="text-3xl font-bold text-amber-700">{{ $pendingDeliveries }}</span>
        </div>
        <h3 class="text-sm font-semibold text-amber-900 uppercase tracking-wide">Pending Deliveries</h3>
    </div>

    <div class="bg-gradient-to-br from-blue-50 to-cyan-100 rounded-2xl shadow-sm p-6 border border-blue-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-check-double text-white text-xl"></i>
            </div>
            <span class="text-3xl font-bold text-blue-700">{{ $completedDeliveries }}</span>
        </div>
        <h3 class="text-sm font-semibold text-blue-900 uppercase tracking-wide">Completed Deliveries</h3>
    </div>

    <div class="bg-gradient-to-br from-orange-50 to-amber-100 rounded-2xl shadow-sm p-6 border border-orange-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 bg-orange-500 rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-hourglass-half text-white text-xl"></i>
            </div>
            <span class="text-3xl font-bold text-orange-700">{{ $pendingApprovalsCount }}</span>
        </div>
        <h3 class="text-sm font-semibold text-orange-900 uppercase tracking-wide">Pending Approvals</h3>
    </div>

    <div class="bg-gradient-to-br from-red-50 to-rose-100 rounded-2xl shadow-sm p-6 border border-red-200 hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
        <div class="flex items-center justify-between mb-3">
            <div class="w-12 h-12 bg-red-500 rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-exclamation-triangle text-white text-xl"></i>
            </div>
            <span class="text-3xl font-bold text-red-700">{{ $delayedShipments }}</span>
        </div>
        <h3 class="text-sm font-semibold text-red-900 uppercase tracking-wide">Delayed Shipments</h3>
    </div>
</div>

<!-- Chart and Report Section -->
@php
    $isLogisticsOrAdmin = auth()->user()?->isLogistics() || auth()->user()?->isAdmin();
@endphp

<div class="grid grid-cols-1 {{ $isLogisticsOrAdmin ? 'lg:grid-cols-3' : 'lg:grid-cols-1' }} gap-6 mb-8">
    <!-- Delivery Performance Chart - Full width when no report access -->
    <div class="{{ $isLogisticsOrAdmin ? 'lg:col-span-2' : 'lg:col-span-1' }} bg-white rounded-2xl shadow-md p-6 border border-gray-200 hover:shadow-xl transition-shadow duration-300">
        <div class="flex items-center mb-6">
            <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center mr-3">
                <i class="fas fa-chart-bar text-emerald-600"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">Weekly Delivery Performance</h3>
                <p class="text-sm text-gray-500">Deliveries and endorsements by week</p>
            </div>
        </div>
        <div class="h-80">
            <canvas id="weekly-logistics-chart"></canvas>
        </div>
    </div>

    <!-- Generate Report Card - Only visible for logistics department -->
    @if($isLogisticsOrAdmin)
    <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-2xl shadow-md p-6 border border-emerald-200 hover:shadow-xl transition-shadow duration-300">
        <div class="flex items-center mb-6">
            <div class="w-10 h-10 bg-emerald-500 rounded-lg flex items-center justify-center mr-3 shadow-md">
                <i class="fas fa-file-pdf text-white"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-800">Generate Report</h3>
                <p class="text-sm text-gray-500">Export logistics data</p>
            </div>
        </div>

        <form action="{{ route('reports.logistics.pdf') }}" method="GET" class="space-y-4">
            <div>
                <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-info-circle text-gray-400 mr-1"></i> Status
                </label>
                <select name="status" id="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all text-sm">
                    <option value="">All Statuses</option>
                    <option value="SCHEDULED">Scheduled</option>
                    <option value="DELIVERED">Delivered</option>
                    <option value="BACKLOG">Backlog</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="date_from" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt text-gray-400 mr-1"></i> From
                    </label>
                    <input type="date" name="date_from" id="date_from" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all text-sm">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-check text-gray-400 mr-1"></i> To
                    </label>
                    <input type="date" name="date_to" id="date_to" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all text-sm">
                </div>
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-gradient-to-r from-emerald-600 to-green-600 hover:from-emerald-700 hover:to-green-700 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center justify-center">
                    <i class="fas fa-download mr-2"></i>
                    Download PDF Report
                </button>
            </div>
        </form>

        <!-- Divider -->
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-emerald-300"></div>
            </div>
            <div class="relative flex justify-center text-xs uppercase">
                <span class="bg-emerald-50 px-2 text-emerald-600 font-semibold">Driver Report</span>
            </div>
        </div>

        <!-- Delivery Report -->
        <form action="{{ route('reports.deliveries.pdf') }}" method="GET" class="space-y-4">
            <div>
                <label for="driver" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-user text-gray-400 mr-1"></i> Driver
                </label>
                <select name="driver" id="driver" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all text-sm">
                    <option value="">Select Driver</option>
                    @foreach($drivers ?? [] as $driver)
                        <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="delivery_date" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-calendar text-gray-400 mr-1"></i> Delivery Date
                </label>
                <input type="date" name="delivery_date" id="delivery_date" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all text-sm" value="{{ date('Y-m-d') }}">
            </div>

            <div class="pt-2">
                <button type="submit" class="w-full bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl flex items-center justify-center">
                    <i class="fas fa-truck mr-2"></i>
                    Download Driver Report
                </button>
            </div>
        </form>
    </div>
    @endif
</div>

<div class="space-y-6">
    <!-- Delivery Schedules (Recent + Delayed) -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
        <div class="p-6 border-b bg-gradient-to-r from-blue-50 to-indigo-50">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-3 shadow-md">
                    <i class="fas fa-shipping-fast text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Delivery Schedules</h3>
                    <p class="text-sm text-gray-500">Recent and delayed delivery schedules</p>
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
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Encoded By</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($deliverySchedulesCombined as $delivery)
                    <tr class="hover:bg-blue-50 transition-colors duration-150" data-delivery-id="{{ $delivery->id }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-medium">{{ $delivery->delivery_date?->format('M d, Y') ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">{{ $delivery->product->model_name ?? $delivery->product->product_code ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-bold">{{ number_format($delivery->quantity) }} {{ $delivery->uom }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $delivery->jobOrder?->encodedBy->name ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="delivery-status inline-flex items-center px-3 py-1.5 text-xs font-bold rounded-full {{ $delivery->ds_status === 'DELIVERED' ? 'bg-green-100 text-green-700' : ($delivery->ds_status === 'BACKLOG' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                                <span class="w-2 h-2 rounded-full mr-2 {{ $delivery->ds_status === 'DELIVERED' ? 'bg-green-500' : ($delivery->ds_status === 'BACKLOG' ? 'bg-red-500' : 'bg-yellow-500') }}"></span>
                                {{ $delivery->ds_status }}
                                @if($delivery->delivery_date && $delivery->delivery_date->isPast() && $delivery->ds_status !== 'DELIVERED')
                                    <span class="ml-2 px-2 py-0.5 bg-red-500 text-white rounded-full text-xs font-bold">DELAYED</span>
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            @if($delivery->ds_status === 'DELIVERED')
                            <span class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-500 rounded-lg font-semibold text-xs border border-gray-300"><i class="fas fa-check mr-2"></i>Delivered</span>
                            @elseif($delivery->can_mark_delivered)
                            <button onclick="showDeliveryModal({{ $delivery->id }}, '{{ $delivery->delivery_date?->format('M d, Y') }}', '{{ $delivery->product->model_name ?? $delivery->product->product_code ?? 'Unknown' }}', {{ $delivery->quantity }}, '{{ $delivery->uom }}')" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-lg font-semibold text-xs shadow-md hover:from-green-600 hover:to-emerald-600 transition-all">
                                <i class="fas fa-check-circle mr-2"></i> Mark Delivered
                            </button>
                            @else
                            <span class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-500 rounded-lg font-semibold text-xs border border-gray-300"><i class="fas fa-lock mr-2"></i>Locked</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <i class="fas fa-calendar-times text-5xl mb-3 opacity-50"></i>
                                <p class="text-sm font-medium">No delivery schedules found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Logistics Endorsements -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-200 overflow-hidden hover:shadow-xl transition-shadow duration-300">
        <div class="p-6 border-b bg-gradient-to-r from-orange-50 to-amber-50">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-orange-500 rounded-lg flex items-center justify-center mr-3 shadow-md">
                    <i class="fas fa-cubes text-white"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">Logistics Endorsements</h3>
                    <p class="text-sm text-gray-500">Warehouse transfers and endorsements</p>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">ETL Number</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Product</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Quantity</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Source</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Destination</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @forelse($endorsementsPending as $endorsement)
                    <tr class="hover:bg-orange-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap font-bold text-blue-600">{{ $endorsement->etl_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-800 font-medium">{{ $endorsement->product->model_name ?? $endorsement->product->product_code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-800 font-semibold">{{ number_format($endorsement->qty) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-600 text-sm">{{ $endorsement->source_warehouse ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-gray-600 text-sm">{{ $endorsement->destination_warehouse ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-3 py-1.5 text-xs font-bold rounded-full 
                                {{ $endorsement->status === 'pending' ? 'bg-yellow-100 text-yellow-700 border border-yellow-300' : '' }}
                                {{ $endorsement->status === 'approved' ? 'bg-blue-100 text-blue-700 border border-blue-300' : '' }}
                                {{ $endorsement->status === 'in_progress' ? 'bg-indigo-100 text-indigo-700 border border-indigo-300' : '' }}
                                {{ $endorsement->status === 'completed' ? 'bg-green-100 text-green-700 border border-green-300' : '' }}">
                                <span class="w-2 h-2 rounded-full mr-2
                                    {{ $endorsement->status === 'pending' ? 'bg-yellow-500' : '' }}
                                    {{ $endorsement->status === 'approved' ? 'bg-blue-500' : '' }}
                                    {{ $endorsement->status === 'in_progress' ? 'bg-indigo-500' : '' }}
                                    {{ $endorsement->status === 'completed' ? 'bg-green-500' : '' }}">
                                </span>
                                {{ ucfirst($endorsement->status) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($endorsement->status === 'pending' && (auth()->user()?->isAdmin() || auth()->user()?->isLogistics()))
                                <form id="approve-form-{{ $endorsement->id }}" action="{{ route('endorse-to-logistics.approve', $endorsement->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button 
                                        type="button"
                                        onclick="showLogisticsActionModal(this, 'Approve')"
                                        data-form-id="approve-form-{{ $endorsement->id }}"
                                        data-etl="{{ $endorsement->etl_number }}"
                                        data-product="{{ $endorsement->product->model_name ?? $endorsement->product->product_code }}"
                                        data-qty="{{ $endorsement->qty }}"
                                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white rounded-lg font-semibold text-xs transition-all duration-300 transform hover:scale-105 shadow-md hover:shadow-lg">
                                        <i class="fas fa-check-circle mr-2"></i>
                                        Approve
                                    </button>
                                </form>
                            @elseif($endorsement->status === 'approved' && (auth()->user()?->isAdmin() || auth()->user()?->isLogistics()))
                                <form id="dispatch-form-{{ $endorsement->id }}" action="{{ route('endorse-to-logistics.dispatch', $endorsement->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button 
                                        type="button"
                                        onclick="showLogisticsActionModal(this, 'Dispatch')"
                                        data-form-id="dispatch-form-{{ $endorsement->id }}"
                                        data-etl="{{ $endorsement->etl_number }}"
                                        data-product="{{ $endorsement->product->model_name ?? $endorsement->product->product_code }}"
                                        data-qty="{{ $endorsement->qty }}"
                                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white rounded-lg font-semibold text-xs transition-all duration-300 transform hover:scale-105 shadow-md hover:shadow-lg">
                                        <i class="fas fa-shipping-fast mr-2"></i>
                                        Dispatch
                                    </button>
                                </form>
                            @else
                                <span class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-500 rounded-lg font-semibold text-xs border border-gray-300">
                                    <i class="fas fa-check mr-2"></i>{{ ucfirst($endorsement->status) }}
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-400">
                                <i class="fas fa-boxes text-5xl mb-3 opacity-50"></i>
                                <p class="text-sm font-medium">No endorsements found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delivery Modal -->
<div id="deliveryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-truck text-green-600 text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Mark as Delivered</h3>
                    <p class="text-sm text-gray-500">Confirm delivery completion</p>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="bg-gray-50 rounded-lg p-4 mb-6 space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Delivery Date:</span>
                    <span id="modalDeliveryDate" class="text-sm font-bold text-blue-600">Jan 1, 2024</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Product:</span>
                    <span id="modalProduct" class="text-sm font-semibold text-gray-800">Product Name</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Quantity:</span>
                    <span id="modalQty" class="text-sm font-bold text-emerald-600">1,000 PCS</span>
                </div>
            </div>
            
            <div class="mb-6">
                <label for="deliveryJustification" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-edit text-gray-400 mr-1"></i> Remarks (Optional)
                </label>
                <textarea id=\"deliveryJustification\" placeholder=\"Add any remarks for this delivery...\" class=\"w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all\"></textarea>
                <p class=\"text-xs text-gray-500 mt-1\">Remarks will be recorded in the audit log.</p>
            </div>
            
            <p class="text-sm text-gray-600 mb-6">
                This will mark the delivery as completed and update the status. All data will be preserved in the system.
            </p>
        </div>

        <div class="p-6 bg-gray-50 rounded-b-2xl flex gap-3">
            <button 
                onclick="closeDeliveryModal()" 
                class="flex-1 px-4 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-100 transition-all duration-300">
                <i class="fas fa-times mr-2"></i>Cancel
            </button>
            <button 
                onclick="confirmDelivery()" 
                class="flex-1 px-4 py-3 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl">
                <i class="fas fa-check-circle mr-2"></i>Confirm Delivery
            </button>
        </div>
    </div>
</div>

<!-- Logistics Action Modal -->
<div id="logisticsActionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                    <i class="fas fa-check-circle text-blue-600 text-2xl"></i>
                </div>
                <div>
                    <h3 id="logisticsModalTitle" class="text-xl font-bold text-gray-800">Approve Endorsement</h3>
                </div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="bg-gray-50 rounded-lg p-4 mb-6 space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">ETL Number:</span>
                    <span id="logisticsModalEtl" class="text-sm font-bold text-blue-600">#ETL-001</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Product:</span>
                    <span id="logisticsModalProduct" class="text-sm font-semibold text-gray-800">Product Name</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Quantity:</span>
                    <span id="logisticsModalQty" class="text-sm font-bold text-emerald-600">1,000</span>
                </div>
            </div>
            
            <p id="logisticsModalNotice" class="text-sm text-gray-600 mb-6">
                This will approve the selected endorsement. Are you sure you want to proceed?
            </p>
        </div>

        <div class="p-6 bg-gray-50 rounded-b-2xl flex gap-3">
            <button 
                onclick="closeLogisticsActionModal()" 
                class="flex-1 px-4 py-3 bg-white border-2 border-gray-300 text-gray-700 rounded-xl font-semibold hover:bg-gray-100 transition-all duration-300">
                <i class="fas fa-times mr-2"></i>Cancel
            </button>
            <button 
                onclick="confirmLogisticsAction()" 
                class="flex-1 px-4 py-3 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white rounded-xl font-semibold transition-all duration-300 shadow-lg hover:shadow-xl">
                <i class="fas fa-check-circle mr-2"></i>Confirm
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Initialize weekly logistics chart
    try {
        const ctx = document.getElementById('weekly-logistics-chart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($logisticsByWeek->pluck('week')->toArray() ?? []) !!},
                    datasets: [
                        {
                            label: 'Deliveries',
                            data: {!! json_encode($logisticsByWeek->pluck('deliveries')->toArray() ?? []) !!},
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderColor: 'rgb(16, 185, 129)',
                            borderWidth: 2,
                            borderRadius: 8,
                            barPercentage: 0.7
                        },
                        {
                            label: 'Endorsements',
                            data: {!! json_encode($logisticsByWeek->pluck('endorsements')->toArray() ?? []) !!},
                            backgroundColor: 'rgba(249, 115, 22, 0.8)',
                            borderColor: 'rgb(249, 115, 22)',
                            borderWidth: 2,
                            borderRadius: 8,
                            barPercentage: 0.7
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

    // Delivery modal functions
    let currentDeliveryId = null;

    function showDeliveryModal(id, date, product, qty, uom) {
        currentDeliveryId = id;
        document.getElementById('modalDeliveryDate').textContent = date;
        document.getElementById('modalProduct').textContent = product;
        document.getElementById('modalQty').textContent = Number(qty).toLocaleString() + ' ' + uom;
        document.getElementById('deliveryModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeDeliveryModal() {
        document.getElementById('deliveryModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        currentDeliveryId = null;
        // Clear justification field
        const justificationField = document.getElementById('deliveryJustification');
        if (justificationField) justificationField.value = '';
    }

    async function confirmDelivery() {
        if (!currentDeliveryId) return;

        console.log('[confirmDelivery] Starting delivery confirmation for ID:', currentDeliveryId);

        try {
            const url = `/delivery-schedules/${currentDeliveryId}/mark-delivered`;
            const justification = document.getElementById('deliveryJustification')?.value || '';
            
            console.log('[confirmDelivery] Posting to URL:', url);
            console.log('[confirmDelivery] Justification:', justification);

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ justification: justification })
            });

            console.log('[confirmDelivery] Response status:', response.status, 'statusText:', response.statusText);
            console.log('[confirmDelivery] Response headers:', {
                'content-type': response.headers.get('content-type')
            });

            // Get response text first for error checking
            const responseText = await response.text();
            console.log('[confirmDelivery] Response body (first 500 chars):', responseText.substring(0, 500));

            // Try to parse as JSON
            let data;
            try {
                data = JSON.parse(responseText);
                console.log('[confirmDelivery] Parsed JSON response:', data);
            } catch (parseError) {
                console.error('[confirmDelivery] Failed to parse JSON:', parseError);
                console.error('[confirmDelivery] Response was:', responseText);
                showToast('Server returned invalid JSON. Check console for details.', 'error');
                return;
            }

            // Check if we got a valid response
            if (!response.ok && !data.success) {
                console.error('[confirmDelivery] HTTP error. Status:', response.status, 'Message:', data.message);
                // Show detailed error message
                let errorMsg = data.message || `Server error: ${response.status} ${response.statusText}`;
                if (data.error) {
                    errorMsg += '\n' + data.error;
                }
                showToast(errorMsg, 'error');
                return;
            }
            
            if (data.success) {
                showToast(data.message || 'Delivery marked successfully', 'success');
                closeDeliveryModal();
                console.log('[confirmDelivery] Success! Reloading page in 1.5s...');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message || 'Failed to mark as delivered', 'error');
                console.warn('[confirmDelivery] Server returned success=false:', data);
            }
        } catch (error) {
            console.error('[confirmDelivery] Caught exception:', error);
            console.error('[confirmDelivery] Error stack:', error.stack);
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

    // Enhanced showDeliveryModal with error logging
    const originalShowDeliveryModal = showDeliveryModal;
    showDeliveryModal = function(id, date, product, qty, uom) {
        console.log('[showDeliveryModal] Called with params:', { id, date, product, qty, uom });
        try {
            originalShowDeliveryModal(id, date, product, qty, uom);
            console.log('[showDeliveryModal] Modal opened successfully');
        } catch (error) {
            console.error('[showDeliveryModal] Error opening modal:', error);
            showToast('Error opening delivery modal: ' + error.message, 'error');
        }
    };

    // Enhanced confirmDelivery with error logging
    const originalConfirmDelivery = confirmDelivery;
    confirmDelivery = async function() {
        console.log('[confirmDelivery] Called with currentDeliveryId:', currentDeliveryId);
        try {
            await originalConfirmDelivery();
            console.log('[confirmDelivery] Delivery confirmation completed');
        } catch (error) {
            console.error('[confirmDelivery] Error confirming delivery:', error);
            showToast('Error confirming delivery: ' + error.message, 'error');
        }
    };
</script>

<script type="module">
    window.Echo.channel('delivery-schedules')
        .listen('.updated', (e) => {
            console.log('Delivery updated', e);
            showToast('Delivery status updated', 'success');
            setTimeout(() => location.reload(), 1500);
        });
</script>

<script>
// Logistics action modal handling (approve / dispatch)
let currentLogisticsFormId = null;
let currentLogisticsAction = null;
let currentLogisticsButtonEl = null;

function showLogisticsActionModal(buttonEl, actionLabel) {
    try {
        console.log('showLogisticsActionModal invoked', {
            actionLabel,
            formId: buttonEl?.getAttribute?.('data-form-id') || null,
            etl: buttonEl?.getAttribute?.('data-etl') || null,
            product: buttonEl?.getAttribute?.('data-product') || null,
            qty: buttonEl?.getAttribute?.('data-qty') || null,
            buttonEl
        });
    } catch (e) {
        console.error('Logging failed in showLogisticsActionModal', e);
    }

    const formId = buttonEl?.getAttribute?.('data-form-id') || null;
    const etl = buttonEl?.getAttribute?.('data-etl') || '';
    const product = buttonEl?.getAttribute?.('data-product') || '';
    const qty = buttonEl?.getAttribute?.('data-qty') || '';

    currentLogisticsFormId = formId;
    currentLogisticsAction = actionLabel;
    currentLogisticsButtonEl = buttonEl;

    try {
        const modalEl = document.getElementById('logisticsActionModal');
        console.log('Modal element found?', !!modalEl, 'Modal id:', modalEl?.id);
        if (!formId) console.warn('Button missing data-form-id attribute', buttonEl);
        const formEl = formId ? document.getElementById(formId) : null;
        console.log('Form element for action', formId, 'exists?', !!formEl);

        document.getElementById('logisticsModalTitle').textContent = actionLabel + ' Endorsement';
        document.getElementById('logisticsModalEtl').textContent = etl;
        document.getElementById('logisticsModalProduct').textContent = product;
        document.getElementById('logisticsModalQty').textContent = Number(qty).toLocaleString();
        document.getElementById('logisticsModalNotice').textContent = `This will ${actionLabel.toLowerCase()} the selected endorsement. Are you sure you want to proceed?`;

        try {
            modalEl?.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        } catch (err2) {
            console.error('Error while showing logistics modal', err2);
        }
    } catch (err) {
        console.error('Error while preparing logistics modal', err, { actionLabel, formId, etl, product, qty });
    }
}

function closeLogisticsActionModal() {
    try {
        console.log('closeLogisticsActionModal invoked', { currentLogisticsFormId, currentLogisticsAction });
        const modalEl = document.getElementById('logisticsActionModal');
        modalEl?.classList.add('hidden');
        console.log('Modal classes after hide:', modalEl?.className);
    } catch (e) {
        console.error('Error while closing logistics modal', e);
    }
    document.body.style.overflow = 'auto';
    currentLogisticsFormId = null;
    currentLogisticsAction = null;
    currentLogisticsButtonEl = null;
}

function confirmLogisticsAction() {
    console.log('confirmLogisticsAction invoked', { currentLogisticsFormId });
    if (!currentLogisticsFormId) {
        console.warn('No currentLogisticsFormId set, aborting submit');
        return;
    }
    const form = document.getElementById(currentLogisticsFormId);
    console.log('Form element found for confirm:', !!form, form);
    if (!form) {
        console.error('Confirm action: form not found for id', currentLogisticsFormId);
        return;
    }
    // Submit via AJAX so we can update the UI inline without a full page reload
    try {
        const url = form.action;
        const method = (form.method || 'POST').toUpperCase();
        const formData = new FormData(form);

        fetch(url, {
            method: method,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        }).then(res => res.json())
          .then(data => {
              if (data.success) {
                  showToast(data.message || 'Action completed', 'success');

                  // Update row status and action button
                  try {
                      const btn = currentLogisticsButtonEl;
                      const row = btn ? btn.closest('tr') : null;
                      if (row) {
                          const tds = row.querySelectorAll('td');
                          // status is the 6th column (0-based index 5)
                          const statusTd = tds[5];
                          const actionsTd = tds[6];

                          if (statusTd) {
                              // replace status badge
                              statusTd.innerHTML = `<span class="inline-flex items-center px-3 py-1.5 text-xs font-bold rounded-full bg-blue-100 text-blue-700 border border-blue-300"><span class="w-2 h-2 rounded-full mr-2 bg-blue-500"></span>${data.status ? (data.status.charAt(0).toUpperCase() + data.status.slice(1)) : 'Updated'}</span>`;
                          }

                          if (actionsTd) {
                              // If we just approved, show dispatch button; if dispatched, show completed/disabled
                              const id = currentLogisticsFormId.split('-').pop();
                              const etl = btn.getAttribute('data-etl') || '';
                              const product = btn.getAttribute('data-product') || '';
                              const qty = btn.getAttribute('data-qty') || '';

                              if (data.status === 'approved') {
                                  // create dispatch form/button
                                  actionsTd.innerHTML = '';
                                  const formHtml = `
                                    <form id="dispatch-form-${id}" action="/endorse-to-logistics/${id}/dispatch" method="POST" style="display:inline;">
                                        <input type="hidden" name="_token" value="${document.querySelector('meta[name=\'csrf-token\']').content}">
                                        <button type="button" onclick="showLogisticsActionModal(this, 'Dispatch')" data-form-id="dispatch-form-${id}" data-etl="${etl}" data-product="${product}" data-qty="${qty}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-500 hover:from-blue-600 hover:to-indigo-600 text-white rounded-lg font-semibold text-xs transition-all duration-300 transform hover:scale-105 shadow-md hover:shadow-lg">
                                            <i class="fas fa-truck mr-2"></i> Dispatch
                                        </button>
                                    </form>
                                  `;
                                  actionsTd.insertAdjacentHTML('beforeend', formHtml);
                              } else if (data.status === 'in_progress') {
                                  // when dispatched, show a disabled indicator on endorsement row
                                  actionsTd.innerHTML = `<span class="inline-flex items-center px-3 py-1.5 bg-indigo-100 text-indigo-700 rounded-full text-xs font-semibold">In Progress</span>`;

                                  // Try to update related delivery row in-place.
                                  // Preferred: use delivery_schedule_id returned from backend. Fallback: match by product+qty.
                                  try {
                                      const deliveryId = data.delivery_schedule_id;
                                      const product = btn.getAttribute('data-product') || '';
                                      const qty = btn.getAttribute('data-qty') || '';

                                      const insertMarkButton = (deliveryRow, id) => {
                                          const actionCell = deliveryRow.querySelector('td:last-child');
                                          if (!actionCell) return false;
                                          // pull display date and product text from row for modal
                                          const dateText = (deliveryRow.querySelector('td')?.textContent || '').trim().replace(/'/g, "\\'");
                                          const productText = (deliveryRow.querySelectorAll('td')[1]?.textContent || '').trim().replace(/'/g, "\\'");
                                          // parse numeric qty from cell
                                          const qtyText = (deliveryRow.querySelectorAll('td')[2]?.textContent || '').trim() || '';
                                          const numericQty = Number(qtyText.replace(/[^0-9.-]+/g, '')) || Number(qty) || 0;
                                          actionCell.innerHTML = '';
                                          const modalHtml = `<button onclick="showDeliveryModal(${id}, '${dateText}', '${productText}', ${numericQty}, '')" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-500 text-white rounded-lg font-semibold text-xs shadow-md hover:from-green-600 hover:to-emerald-600 transition-all"><i class="fas fa-check-circle mr-2"></i> Mark Delivered</button>`;
                                          actionCell.insertAdjacentHTML('beforeend', modalHtml);
                                          return true;
                                      };

                                      if (deliveryId) {
                                          const deliveryRow = document.querySelector(`tr[data-delivery-id="${deliveryId}"]`);
                                          if (deliveryRow) {
                                              insertMarkButton(deliveryRow, deliveryId);
                                          } else {
                                              // fallback to matching by product+qty
                                              const rows = Array.from(document.querySelectorAll('tr[data-delivery-id]'));
                                              let matched = false;
                                              for (const r of rows) {
                                                  const prod = (r.querySelectorAll('td')[1]?.textContent || '').trim();
                                                  const qtext = (r.querySelectorAll('td')[2]?.textContent || '').trim();
                                                  const qnum = Number(qtext.replace(/[^0-9.-]+/g, '')) || 0;
                                                  const qcmp = Number(qty) || 0;
                                                  if (prod && product && prod.indexOf(product) !== -1 && Math.abs(qnum - qcmp) <= 1) {
                                                      const idAttr = r.getAttribute('data-delivery-id');
                                                      insertMarkButton(r, idAttr || deliveryId);
                                                      matched = true;
                                                      break;
                                                  }
                                              }
                                              if (!matched) {
                                                  console.warn('No delivery row matched for dispatch', deliveryId, product, qty);
                                                  setTimeout(() => location.reload(), 700);
                                              }
                                          }
                                      } else {
                                          // no delivery id returned - try matching by product+qty across delivery rows
                                          const rows = Array.from(document.querySelectorAll('tr[data-delivery-id]'));
                                          let matched = false;
                                          for (const r of rows) {
                                              const prod = (r.querySelectorAll('td')[1]?.textContent || '').trim();
                                              const qtext = (r.querySelectorAll('td')[2]?.textContent || '').trim();
                                              const qnum = Number(qtext.replace(/[^0-9.-]+/g, '')) || 0;
                                              const qcmp = Number(qty) || 0;
                                              if (prod && product && prod.indexOf(product) !== -1 && Math.abs(qnum - qcmp) <= 1) {
                                                  const idAttr = r.getAttribute('data-delivery-id');
                                                  insertMarkButton(r, idAttr || 0);
                                                  matched = true;
                                                  break;
                                              }
                                          }
                                          if (!matched) {
                                              console.warn('No delivery row matched (no delivery_schedule_id returned)', product, qty);
                                              setTimeout(() => location.reload(), 700);
                                          }
                                      }
                                  } catch (err2) {
                                      console.error('Failed to update delivery row after dispatch', err2);
                                      // fallback: reload page to ensure consistency
                                      setTimeout(() => location.reload(), 700);
                                  }
                              }
                          }
                      }
                  } catch (err) {
                      console.error('Failed to update row DOM after action', err);
                      // fallback to reload
                      setTimeout(() => location.reload(), 700);
                  }
              } else {
                  showToast(data.message || 'Action failed', 'error');
              }
          })
          .catch(err => {
              console.error('AJAX action error', err);
              showToast('An error occurred while performing the action', 'error');
          })
          .finally(() => {
              closeLogisticsActionModal();
          });

    } catch (e) {
        console.error('Error submitting form via AJAX', e, { formId: currentLogisticsFormId });
    }
}

// Close on ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDeliveryModal();
        closeLogisticsActionModal();
    }
});

// Close when clicking outside
document.getElementById('deliveryModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeliveryModal();
    }
});

document.getElementById('logisticsActionModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeLogisticsActionModal();
    }
});
</script>

@endpush