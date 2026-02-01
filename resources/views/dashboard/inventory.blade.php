@extends('layouts.app')

@section('title', 'Inventory Dashboard')
@section('page-icon') <i class="fas fa-boxes"></i> @endsection
@section('page-title', 'Inventory Dashboard')
@section('page-description', 'Manage stock levels, approve transfers, and monitor recent changes')

@section('content')

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    @component('kpi-card', ['title' => 'Stock On Hand',   'value' => number_format($stocksOnHand),   'color' => 'text-blue-600']) @endcomponent
    @component('kpi-card', ['title' => 'Low Stock Items', 'value' => $lowStockItems,               'color' => 'text-red-600']) @endcomponent
    @component('kpi-card', ['title' => 'Stock In Today',  'value' => number_format($stockInToday),  'color' => 'text-green-600']) @endcomponent
    @component('kpi-card', ['title' => 'Stock Out Today', 'value' => number_format($stockOutToday), 'color' => 'text-yellow-600']) @endcomponent
</div>

<!-- Pending Transfer Approvals -->
<div class="bg-white rounded-xl shadow-md border border-gray-100 mb-8 overflow-hidden">
    <div class="p-6 border-b bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-3">
            Pending Transfer Approvals
            <span class="px-2.5 py-1 bg-red-500 text-white text-xs font-medium rounded-full" id="pending-count">
                {{ App\Models\Transfer::where('status', 'pending')->count() }}
            </span>
        </h3>
        <p class="text-sm text-gray-600 mt-1">Approve transfers to automatically update inventory levels</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">J.O. Number</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destination</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested By</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Encoded By</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200" id="pending-approvals">
                @php
                    $pendingTransfers = App\Models\Transfer::with(['product', 'jobOrder', 'receivedBy'])
                        ->where('status', 'pending')
                        ->latest()
                        ->take(10)
                        ->get();
                @endphp
                
                @forelse($pendingTransfers as $transfer)
                <tr data-transfer-id="{{ $transfer->id }}" class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transfer->jobOrder?->jo_number ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $transfer->product?->model_name ?? $transfer->product?->product_code ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                        {{ number_format($transfer->qty_received) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transfer->destination ?? 'Finished Goods' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transfer->receivedBy?->name ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $transfer->jobOrder?->encodedBy->name ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                        <button onclick="approveTransfer({{ $transfer->id }})" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-md text-sm transition">
                            Approve
                        </button>
                        <button onclick="rejectTransfer({{ $transfer->id }})" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-md text-sm transition">
                            Reject
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-10 text-center text-gray-500">No pending transfers</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Recent Inventory Updates -->
    <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
        <div class="p-6 border-b bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Recent Inventory Updates</h3>
            <p class="text-sm text-gray-600 mt-1">Latest stock movements, transfers, deliveries & adjustments (last 15 actions)</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">When</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($recentActivities as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            {{ $log->created_at->diffForHumans() }}
                            <div class="text-xs text-gray-400 mt-0.5">{{ $log->created_at->format('M d, Y H:i') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $log->user?->name ?? ($log->user_id ? 'User #'.$log->user_id : 'System') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full
                                {{ $log->action === 'created'   ? 'bg-green-100 text-green-800' : '' }}
                                {{ $log->action === 'updated'   ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $log->action === 'deleted'   ? 'bg-red-100 text-red-800' : '' }}
                                {{ str_contains(strtolower($log->action), 'approve') || str_contains(strtolower($log->action), 'delivered') ? 'bg-indigo-100 text-indigo-800' : '' }}">
                                {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ class_basename($log->model_type) }}
                            @if($log->subject && $log->subject->product)
                                — {{ $log->subject->product->model_name ?? $log->subject->product->product_code ?? 'Unnamed' }}
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600 truncate max-w-xs">
                            @if($log->new_values && count($log->new_values) > 0)
                                {{ collect($log->new_values)->map(fn($v, $k) => "$k: <strong>" . (is_array($v) ? json_encode($v) : $v) . "</strong>")->implode(', ') }}
                            @elseif($log->old_values && count($log->old_values) > 0)
                                {{ collect($log->old_values)->map(fn($v, $k) => "$k: " . (is_array($v) ? json_encode($v) : $v))->implode(', ') }}
                            @else
                                Action performed
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">No recent inventory-related activity yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Low Stock Items -->
    <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
        <div class="p-6 border-b bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-800">Low Stock Items</h3>
            <p class="text-sm text-gray-600 mt-1">Products below buffer stock levels</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product Code</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Buffer Stock</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($lowStockProducts as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->product->product_code }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->product->model_name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-red-600">{{ number_format($item->qty_actual_ending) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ number_format($item->qty_buffer_stock) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-10 text-center text-gray-500">No low stock items</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Inventory Aging Analysis -->
<div class="bg-white rounded-xl shadow-md border border-gray-100">
    <div class="p-6 border-b bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800">Inventory Aging Analysis</h3>
        <p class="text-sm text-gray-600 mt-1">Stock distribution by age</p>
    </div>
    <div class="p-6">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
            @foreach($agingData as $range => $count)
            <div class="bg-gray-50 p-5 rounded-lg text-center border border-gray-200">
                <p class="text-sm text-gray-600 font-medium">{{ $range }}</p>
                <p class="text-3xl font-bold text-gray-800 mt-2">{{ number_format($count) }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>

@endsection

@push('scripts')
<!-- Existing approve/reject + Echo scripts remain unchanged -->
<script>
    async function approveTransfer(transferId) {
        if (!confirm('Approve this transfer?')) return;
        
        try {
            const response = await fetch(`/transfers/${transferId}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                showToast(data.message, 'success');
                const row = document.querySelector(`tr[data-transfer-id="${transferId}"]`);
                if (row) row.remove();
                
                const countBadge = document.getElementById('pending-count');
                if (countBadge) {
                    const current = parseInt(countBadge.textContent);
                    countBadge.textContent = current - 1;
                }
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        }
    }

    async function rejectTransfer(transferId) {
        if (!confirm('Reject this transfer request?')) return;
        
        try {
            const response = await fetch(`/transfers/${transferId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                showToast(data.message, 'success');
                const row = document.querySelector(`tr[data-transfer-id="${transferId}"]`);
                if (row) row.remove();
                
                const countBadge = document.getElementById('pending-count');
                if (countBadge) {
                    const current = parseInt(countBadge.textContent);
                    countBadge.textContent = current - 1;
                }
            }
        } catch (error) {
            console.error('Error:', error);
            showToast('An error occurred', 'error');
        }
    }

    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 ${
            type === 'success' ? 'bg-green-500' : 
            type === 'error' ? 'bg-red-500' : 
            'bg-blue-500'
        }`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => toast.remove(), 3000);
    }

    // Optional: refresh recent activities periodically (every 60s)
    setInterval(() => {
        location.reload(); // or implement AJAX refresh for just the recent table
    }, 60000);
</script>

<script type="module">
    import Echo from 'laravel-echo';
    import Pusher from 'pusher-js';
    
    window.Pusher = Pusher;
    
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    // Listen for inventory updates
    window.Echo.channel('inventory')
        .listen('.updated', (e) => {
            console.log('Inventory updated:', e);
            
            // Update the inventory row
            const row = document.querySelector(`tr[data-inventory-id="${e.id}"]`);
            if (row) {
                const qtyCell = row.querySelector('.inventory-quantity');
                if (qtyCell) {
                    qtyCell.textContent = e.actual_quantity.toLocaleString();
                }
            }
            
            // Update total stock
            const stockOnHand = document.getElementById('stock-on-hand');
            if (stockOnHand) {
                setTimeout(() => location.reload(), 2000);
            }
            
            showToast(`Inventory updated: ${e.product_name} now has ${e.actual_quantity} units`);
        });

    // Listen for new transfer requests
    window.Echo.channel('transfers')
        .listen('.transfer.requested', (e) => {
            console.log('New approval request:', e);
            showToast(`New transfer request: ${e.qty_received} units to ${e.destination ?? 'Finished Goods'}`);
            setTimeout(() => location.reload(), 2000);
        })
        .listen('.transfer.approved', (e) => {
            showToast(`Transfer approved: ${e.qty_received} units to ${e.destination ?? 'Finished Goods'}`);
        });

    // Optional: Listen for activity log events if you broadcast them
    window.Echo.channel('activity')
        .listen('.created', (e) => {
            if (['FinishedGood', 'ActualInventory', 'Transfer', 'DeliverySchedule'].includes(e.model_type.split('\\').pop())) {
                showToast(`New inventory activity: ${e.action} on ${e.model_short ?? 'item'}`, 'info');
                setTimeout(() => location.reload(), 2500);
            }
        });
</script>
@endpush