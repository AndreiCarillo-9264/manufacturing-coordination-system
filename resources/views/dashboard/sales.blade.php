@extends('layouts.app')

@section('title', 'Sales Dashboard')
@section('page-icon') <i class="fas fa-shopping-cart"></i> @endsection
@section('page-title', 'Sales Dashboard')
@section('page-description', 'Manage job orders and track sales performance')

@section('content')

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    @component('kpi-card', ['title' => 'Total Job Orders', 'value' => number_format($totalJobOrders), 'color' => 'text-yellow-600']) @endcomponent
    @component('kpi-card', ['title' => 'Pending',         'value' => number_format($pendingJobOrders), 'color' => 'text-blue-600']) @endcomponent
    @component('kpi-card', ['title' => 'Approved',        'value' => number_format($approvedJobOrders),'color' => 'text-green-600']) @endcomponent
    @component('kpi-card', ['title' => 'Canceled',        'value' => number_format($cancelledJobOrders),'color' => 'text-purple-600']) @endcomponent
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 bg-white rounded-xl shadow-md p-6 border border-gray-100">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Job Orders by Week</h3>
        <div class="h-80">
            <canvas id="weekly-jo-chart"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 flex flex-col">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Generate Report</h3>

        <form action="{{ route('reports.job-orders.pdf') }}" method="GET" class="space-y-4 flex-1">
            <div>
                <label for="customer" class="block text-sm font-medium text-gray-700">Customer</label>
                <select name="customer" id="customer" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All Customers</option>
                    @foreach($customers ?? [] as $customer)
                        <option value="{{ $customer }}">{{ $customer }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700">From</label>
                    <input type="date" name="date_from" id="date_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700">To</label>
                    <input type="date" name="date_to" id="date_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>

            <div class="mt-auto pt-4">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 px-4 rounded-md transition">
                    Download PDF Report
                </button>
            </div>
        </form>
    </div>
</div>

<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="p-6 border-b bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800">Pending Job Orders</h3>
        <p class="text-sm text-gray-600 mt-1">Orders awaiting approval</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">JO #</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Needed</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Encoded By</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse(\App\Models\JobOrder::pending()->get() as $jo)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">
                            <a href="{{ route('job-orders.show', $jo) }}" class="hover:underline">{{ $jo->jo_number }}</a>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $jo->product->model_name ?? $jo->product->product_code ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($jo->qty) }} {{ $jo->uom }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $jo->date_needed?->format('M d, Y') ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $jo->encodedBy->name ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                            <form action="{{ route('job-orders.approve', $jo) }}" method="POST" class="inline" onsubmit="return confirm('Approve this job order?');">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium text-xs transition">
                                    <i class="fas fa-check mr-1"></i> Approve
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">No pending job orders. All orders have been approved!</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden mt-8">
    <div class="p-6 border-b bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800">Recent Job Orders</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">JO #</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Encoded By</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Needed</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($recentJobOrders as $jo)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $jo->jo_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $jo->product->model_name ?? $jo->product->product_code ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($jo->qty) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $jo->encodedBy->name ?? '—' }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full {{ getStatusBadgeClass($jo->status) }}">
                                {{ ucfirst(str_replace('_', ' ', $jo->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $jo->date_needed?->format('M d, Y') ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">No recent job orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('weekly-jo-chart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($jobOrdersByWeek->pluck('week_number')->toArray()) !!},
            datasets: [{
                label: 'Job Orders',
                data: {!! json_encode($jobOrdersByWeek->pluck('count')->toArray()) !!},
                backgroundColor: 'rgba(59, 130, 246, 0.55)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1
            }, {
                label: 'Total Quantity',
                data: {!! json_encode($jobOrdersByWeek->pluck('total_qty')->toArray()) !!},
                type: 'line',
                borderColor: 'rgb(245, 158, 11)',
                backgroundColor: 'rgba(245, 158, 11, 0.2)',
                yAxisID: 'y1',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Count' } },
                y1: { position: 'right', beginAtZero: true, title: { display: true, text: 'Quantity' }, grid: { drawOnChartArea: false } }
            },
            plugins: { legend: { position: 'top' } }
        }
    });

    function getStatusBadgeClass(status) {
        const map = {
            pending:     'bg-yellow-100 text-yellow-800 border border-yellow-300',
            approved:    'bg-blue-100 text-blue-800 border border-blue-300',
            in_progress: 'bg-indigo-100 text-indigo-800 border border-indigo-300',
            completed:   'bg-green-100 text-green-800 border border-green-300',
            cancelled:   'bg-red-100 text-red-800 border border-red-300',
        };
        return map[status] || 'bg-gray-100 text-gray-800 border border-gray-300';
    }
</script>
@endpush