{{-- resources/views/activity-logs/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Activity Logs')
@section('page-icon') <i class="fas fa-history"></i> @endsection
@section('page-title', 'Activity Logs')
@section('page-description', 'Audit trail of all system activities')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <x-page-header title="System Activity Log" description="Recent user actions and changes">
        {{-- No actions needed for activity logs --}}
    </x-page-header>

    {{-- SEARCH & FILTER --}}
    <div class="p-6 bg-gradient-to-br from-gray-50 to-gray-100/50 border-b border-gray-200">
        <form method="GET" action="{{ route('activity-logs.index') }}">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                {{-- Search Input --}}
                <div class="lg:col-span-5">
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">Search Logs</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 text-sm"></i>
                        </div>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Search by user, action, or module..."
                               class="block w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200 placeholder-gray-400">
                    </div>
                </div>

                {{-- Action Type Filter --}}
                <div class="lg:col-span-4">
                    <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">Action Type</label>
                    <select name="action" class="block w-full px-4 py-2.5 text-sm border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200">
                        <option value="">All Actions</option>
                        <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                        <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Updated</option>
                        <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Deleted</option>
                    </select>
                </div>

                {{-- Action Buttons --}}
                <div class="lg:col-span-3 flex items-end gap-2">
                    <button type="submit" 
                            class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                        <i class="fas fa-filter mr-2 text-xs"></i>
                        Filter
                    </button>
                    @if(request()->hasAny(['search', 'action']))
                    <a href="{{ route('activity-logs.index') }}" 
                       class="inline-flex items-center justify-center px-4 py-2.5 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg border border-gray-300 transition-all duration-200"
                       title="Clear filters">
                        <i class="fas fa-times text-xs"></i>
                    </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    {{-- TABLE --}}
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0 z-10">
                <tr>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap w-8">
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Timestamp
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        User
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Action
                    </th>
                    <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Module
                    </th>
                    <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider whitespace-nowrap">
                        Record ID
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($activityLogs as $log)
                <tr class="hover:bg-blue-50/30 transition-colors cursor-pointer" onclick="toggleDetails(event, 'details-{{ $log->id }}')">
                    <td class="px-4 py-4 whitespace-nowrap text-center">
                        <i class="fas fa-chevron-down text-gray-400 text-sm toggle-icon"></i>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">{{ $log->created_at->format('M d, Y h:i A') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">
                            {{ $log->causer?->name ?? 'System' }}
                            @if($log->causer)
                            <div class="text-xs text-gray-500 capitalize">({{ $log->causer->department }})</div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                        $actionColors = [
                            'created' => 'bg-green-100 text-green-800',
                            'updated' => 'bg-blue-100 text-blue-800',
                            'deleted' => 'bg-red-100 text-red-800',
                        ];
                        $color = $actionColors[$log->event] ?? 'bg-gray-100 text-gray-800';
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $color }}">
                            {{ ucfirst($log->event) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900 capitalize">{{ str_replace('_', ' ', $log->log_name ?? class_basename($log->subject_type)) }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="text-sm font-mono text-gray-600">#{{ $log->subject_id ?? 'N/A' }}</div>
                    </td>
                </tr>
                <!-- DETAILED INFORMATION ROW -->
                <tr class="hidden details-row" id="details-{{ $log->id }}">
                    <td colspan="6" class="px-6 py-6 bg-gray-50">
                        <div class="space-y-6">
                            <!-- Description -->
                            @if($log->description)
                            <div class="border-l-4 border-blue-500 pl-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2">Description</h4>
                                <p class="text-sm text-gray-900">{{ $log->description }}</p>
                            </div>
                            @endif

                            <!-- Request Details -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @if($log->method)
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-600 uppercase mb-1">HTTP Method</h4>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded text-xs font-medium {{ 
                                        $log->method === 'POST' ? 'bg-green-100 text-green-800' : 
                                        ($log->method === 'PUT' || $log->method === 'PATCH' ? 'bg-blue-100 text-blue-800' : 
                                        ($log->method === 'DELETE' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) 
                                    }}">
                                        {{ $log->method }}
                                    </span>
                                </div>
                                @endif

                                @if($log->ip_address)
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-600 uppercase mb-1">IP Address</h4>
                                    <p class="text-sm font-mono text-gray-900 break-all">{{ $log->ip_address }}</p>
                                </div>
                                @endif
                            </div>

                            <!-- URL -->
                            @if($log->url)
                            <div>
                                <h4 class="text-xs font-semibold text-gray-600 uppercase mb-1">Request URL</h4>
                                <p class="text-sm text-gray-900 break-all font-mono text-xs">{{ $log->url }}</p>
                            </div>
                            @endif

                            <!-- User Agent -->
                            @if($log->user_agent)
                            <div>
                                <h4 class="text-xs font-semibold text-gray-600 uppercase mb-1">User Agent</h4>
                                <p class="text-sm text-gray-700 break-all">{{ $log->user_agent }}</p>
                            </div>
                            @endif

                            <!-- Changes (Old vs New Values) -->
                            @if($log->old_values || $log->new_values)
                            <div class="border-t pt-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-4">Changes Made</h4>
                                
                                @php
                                    $allKeys = array_unique(array_merge(
                                        array_keys($log->old_values ?? []),
                                        array_keys($log->new_values ?? [])
                                    ));
                                @endphp

                                <div class="space-y-3">
                                    @forelse($allKeys as $key)
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-3 bg-white rounded border border-gray-200">
                                        <div>
                                            <h5 class="text-xs font-semibold text-gray-600 uppercase mb-1">Field</h5>
                                            <p class="text-sm font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $key)) }}</p>
                                        </div>
                                        <div>
                                            <h5 class="text-xs font-semibold text-red-600 uppercase mb-1">Old Value</h5>
                                            <p class="text-sm text-gray-700 break-words">
                                                @if(is_array($log->old_values[$key] ?? null))
                                                    <code class="text-xs bg-gray-100 px-2 py-1 rounded block">{{ json_encode($log->old_values[$key]) }}</code>
                                                @else
                                                    {{ $log->old_values[$key] ?? '—' }}
                                                @endif
                                            </p>
                                        </div>
                                        <div>
                                            <h5 class="text-xs font-semibold text-green-600 uppercase mb-1">New Value</h5>
                                            <p class="text-sm text-gray-700 break-words">
                                                @if(is_array($log->new_values[$key] ?? null))
                                                    <code class="text-xs bg-gray-100 px-2 py-1 rounded block">{{ json_encode($log->new_values[$key]) }}</code>
                                                @else
                                                    {{ $log->new_values[$key] ?? '—' }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    @empty
                                    <p class="text-sm text-gray-500">No changes recorded</p>
                                    @endforelse
                                </div>
                            </div>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-500">
                            <i class="fas fa-history text-4xl mb-3 text-gray-300"></i>
                            <p class="text-lg font-medium">No activity logs found</p>
                            @if(request('search') || request('action'))
                            <p class="text-sm mt-1">Try adjusting your filters</p>
                            @else
                            <p class="text-sm mt-1">Recent activity will appear here</p>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- PAGINATION --}}
    @if($activityLogs->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        {{ $activityLogs->links() }}
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-submit form on Enter key
    const searchInputs = document.querySelectorAll('input[name="search"]');
    searchInputs.forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.closest('form').submit();
            }
        });
    });
});

function toggleDetails(event, detailsId) {
    // Prevent toggling when clicking on links
    if (event.target.closest('a')) {
        return;
    }
    
    const detailsRow = document.getElementById(detailsId);
    const iconElement = event.currentTarget.querySelector('.toggle-icon');
    
    if (detailsRow.classList.contains('hidden')) {
        detailsRow.classList.remove('hidden');
        detailsRow.classList.add('block');
        iconElement.classList.add('rotate-180');
    } else {
        detailsRow.classList.add('hidden');
        detailsRow.classList.remove('block');
        iconElement.classList.remove('rotate-180');
    }
}
</script>
@endsection