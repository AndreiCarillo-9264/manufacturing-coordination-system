@extends('layouts.app')

@section('title', 'Activity Logs')
@section('page-icon') <i class="fas fa-history"></i> @endsection
@section('page-title', 'Activity Logs')
@section('page-description', 'Audit trail of all system activities')

@section('content')
<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="p-6 border-b bg-gray-50">
        <h3 class="text-lg font-semibold text-gray-800">System Activity Log</h3>
        <p class="text-sm text-gray-600 mt-1">Recent user actions and changes</p>
    </div>
    
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Module</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Record ID</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($activityLogs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $log->created_at->format('M d, Y h:i A') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        <div class="flex items-center">
                            {{ $log->user->name ?? 'System' }}
                            @if($log->user)
                            <span class="ml-2 text-xs text-gray-500 capitalize">({{ $log->user->department }})</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full
                            {{ $log->action === 'created' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $log->action === 'updated' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $log->action === 'deleted' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ ucfirst($log->action) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 capitalize">{{ str_replace('_', ' ', $log->module ?? class_basename($log->model_type)) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">#{{ $log->record_id ?? $log->model_id }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">No activity logs found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <div class="p-6">{{ $activityLogs->links() }}</div>
</div>
@endsection