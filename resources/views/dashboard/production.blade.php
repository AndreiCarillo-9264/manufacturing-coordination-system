@extends('layouts.app')

@section('title', 'Production Dashboard')
@section('page-icon') <i class="fas fa-industry"></i> @endsection
@section('page-title', 'Production Dashboard')
@section('page-description', 'Monitor production output and job order progress')

@section('content')

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    @component('kpi-card', ['title' => 'Pending Production', 'value' => number_format($pendingProduction), 'color' => 'text-red-600']) @endcomponent
    @component('kpi-card', ['title' => 'Produced Today',     'value' => number_format($producedToday),     'color' => 'text-green-600']) @endcomponent
    @component('kpi-card', ['title' => 'Completion Rate',    'value' => $completionRate . '%',            'color' => 'text-blue-600']) @endcomponent
    @component('kpi-card', ['title' => 'Backlog Quantity',   'value' => number_format($backlogQuantity),   'color' => 'text-yellow-600']) @endcomponent
</div>

<!-- Job Orders Awaiting Production -->
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="p-6 border-b bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800">Job Orders Awaiting / In Production</h3>
        <p class="text-sm text-gray-600 mt-1">Quick actions to start or complete production</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="jo-awaiting-table">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">J.O. Number</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ordered Qty</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Encoded By</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quick Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200" id="jo-awaiting-tbody">
                @forelse($awaitingJobs as $jo)
                <tr class="hover:bg-gray-50" data-jo-id="{{ $jo->id }}">
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-blue-700">{{ $jo->jo_number }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $jo->product->model_name ?? $jo->product->product_code }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ number_format($jo->qty) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-900">{{ $jo->encodedBy->name ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $jo->status === 'approved'    ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $jo->status === 'in_progress' ? 'bg-blue-100 text-blue-800'   : '' }}
                            {{ $jo->status === 'completed'   ? 'bg-green-100 text-green-800' : '' }}">
                            {{ ucwords(str_replace('_', ' ', $jo->status)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                        @if($jo->status === 'approved')
                            <button onclick="updateJobStatus({{ $jo->id }}, 'in_progress')" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-md transition">
                                Start Production
                            </button>
                        @endif
                        @if($jo->status === 'in_progress')
                            <button onclick="updateJobStatus({{ $jo->id }}, 'completed')" 
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-1.5 rounded-md transition">
                                Complete
                            </button>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-10 text-center text-gray-500">No job orders awaiting or in production</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Real-time updates using Laravel Echo
    window.Echo.channel('job-orders')
        .listen('.status-updated', (e) => {
            const row = document.querySelector(`tr[data-jo-id="${e.id}"]`);
            if (row) {
                const badge = row.querySelector('.status-badge');
                badge.className = 'status-badge px-2 py-1 text-xs rounded-full';
                
                if (e.status === 'approved') {
                    badge.classList.add('bg-yellow-100', 'text-yellow-800');
                } else if (e.status === 'in_progress') {
                    badge.classList.add('bg-blue-100', 'text-blue-800');
                } else if (e.status === 'completed') {
                    badge.classList.add('bg-green-100', 'text-green-800');
                }
                
                badge.textContent = e.status.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
                
                // Show toast notification
                showToast(`J.O. ${e.jo_number} status updated to ${e.status} by ${e.updated_by}`);
                
                // Remove row if status is completed
                if (e.status === 'completed') {
                    setTimeout(() => {
                        row.remove();
                    }, 2000);
                }
            }
        });

    // Listen for new finished goods
    window.Echo.channel('finished-goods')
        .listen('.created', (e) => {
            console.log('New finished good created:', e);
            showToast(`New production recorded: ${e.quantity_produced} units of ${e.product_name}`);
            
            // Optionally reload the page or add row dynamically
            setTimeout(() => location.reload(), 2000);
        });
</script>

<script>
    // Function to update job order status
    async function updateJobStatus(joId, newStatus) {
        try {
            const response = await fetch(`/job-orders/${joId}/update-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: newStatus })
            });

            const data = await response.json();
            
            if (data.success) {
                showToast(data.message, 'success');
                // The real-time update will handle the UI changes
            } else {
                showToast('Failed to update status', 'error');
            }
        } catch (error) {
            console.error('Error updating status:', error);
            showToast('An error occurred', 'error');
        }
    }

    // Toast notification function
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 ${
            type === 'success' ? 'bg-green-500' : 
            type === 'error' ? 'bg-red-500' : 
            'bg-blue-500'
        }`;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }
</script>
@endpush