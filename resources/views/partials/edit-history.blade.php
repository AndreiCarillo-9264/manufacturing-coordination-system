<div class="mt-6 bg-white rounded-xl border p-4">
    <h3 class="text-lg font-semibold mb-3">Edit History</h3>
    @if(isset($activityLogs) && $activityLogs->count())
        <div class="space-y-4">
            @foreach($activityLogs as $log)
                <div class="border rounded p-4 bg-gray-50 hover:bg-gray-100 transition-colors">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                @php
                                    $eventColors = [
                                        'created' => 'bg-green-100 text-green-800',
                                        'updated' => 'bg-blue-100 text-blue-800',
                                        'deleted' => 'bg-red-100 text-red-800',
                                    ];
                                    $color = $eventColors[$log->event] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $color }}">
                                    {{ ucfirst($log->event) }}
                                </span>
                                <p class="text-sm font-semibold text-gray-900">by {{ $log->user?->name ?? 'System' }}</p>
                            </div>
                            <div class="flex items-center gap-4 mt-1">
                                <p class="text-xs text-gray-500">{{ $log->created_at->format('M d, Y H:i:s') }}</p>
                                @if($log->ip_address)
                                <p class="text-xs text-gray-500">IP: <code class="font-mono">{{ $log->ip_address }}</code></p>
                                @endif
                            </div>
                        </div>
                        <div class="text-xs text-gray-400 font-mono">ID #{{ $log->id }}</div>
                    </div>

                    {{-- Description --}}
                    @if($log->description)
                    <div class="mb-3 p-2 bg-white rounded border-l-4 border-blue-500">
                        <p class="text-sm text-gray-700">{{ $log->description }}</p>
                    </div>
                    @endif

                    {{-- Request Details --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-2 mb-3 text-xs">
                        @if($log->method)
                        <div class="flex items-center gap-1">
                            <span class="font-semibold text-gray-600">Method:</span>
                            <code class="bg-gray-200 px-2 py-0.5 rounded">{{ $log->method }}</code>
                        </div>
                        @endif
                        @if($log->user_agent)
                        <div class="flex items-center gap-1 col-span-2">
                            <span class="font-semibold text-gray-600">User Agent:</span>
                            <span class="text-gray-700 truncate">{{ $log->user_agent }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- URL --}}
                    @if($log->url)
                    <div class="mb-3 text-xs">
                        <span class="font-semibold text-gray-600">URL:</span>
                        <p class="text-gray-700 font-mono break-all text-xs mt-1 bg-white p-2 rounded border">{{ $log->url }}</p>
                    </div>
                    @endif

                    {{-- Changes Details --}}
                    <div class="mt-3 text-sm text-gray-700">
                        <details class="cursor-pointer">
                            <summary class="font-semibold text-blue-600 flex items-center gap-2">
                                <i class="fas fa-chevron-right text-xs"></i>
                                Show Details of Changes
                            </summary>
                            <div class="mt-3 space-y-2 pl-6">
                                @php
                                    $allKeys = array_unique(array_merge(
                                        array_keys($log->old_values ?? []),
                                        array_keys($log->new_values ?? [])
                                    ));
                                @endphp

                                @forelse($allKeys as $key)
                                <div class="border rounded p-2 bg-white">
                                    <p class="font-medium text-gray-800 text-sm mb-1">{{ ucwords(str_replace('_', ' ', $key)) }}</p>
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        <div class="bg-red-50 p-2 rounded border border-red-200">
                                            <span class="text-red-700 font-semibold">Old:</span>
                                            <p class="text-gray-700 mt-1 break-words">
                                                @if(is_array($log->old_values[$key] ?? null))
                                                    <code class="text-xs">{{ json_encode($log->old_values[$key]) }}</code>
                                                @elseif(empty($log->old_values[$key]))
                                                    <em class="text-gray-500">empty</em>
                                                @else
                                                    {{ $log->old_values[$key] }}
                                                @endif
                                            </p>
                                        </div>
                                        <div class="bg-green-50 p-2 rounded border border-green-200">
                                            <span class="text-green-700 font-semibold">New:</span>
                                            <p class="text-gray-700 mt-1 break-words">
                                                @if(is_array($log->new_values[$key] ?? null))
                                                    <code class="text-xs">{{ json_encode($log->new_values[$key]) }}</code>
                                                @elseif(empty($log->new_values[$key]))
                                                    <em class="text-gray-500">empty</em>
                                                @else
                                                    {{ $log->new_values[$key] }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <p class="text-sm text-gray-500">No changes recorded</p>
                                @endforelse
                            </div>
                        </details>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-gray-500">No recent edits recorded.</p>
    @endif
</div>