<div class="mt-6 bg-white rounded-xl border p-4">
    <h3 class="text-lg font-semibold mb-3">Edit History</h3>
    @if(isset($activityLogs) && $activityLogs->count())
        <div class="space-y-4">
            @foreach($activityLogs as $log)
                <div class="border rounded p-3 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold">{{ ucfirst($log->event) }} by {{ $log->user?->name ?? 'System' }}</p>
                            <p class="text-xs text-gray-500">{{ $log->created_at->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="text-xs text-gray-400">ID #{{ $log->id }}</div>
                    </div>

                    <div class="mt-3 text-sm text-gray-700">
                        <details class="mt-1">
                            <summary class="cursor-pointer text-blue-600">Show old / new values</summary>
                            <pre class="whitespace-pre-wrap mt-2 bg-white p-3 border rounded text-xs">Old: {{ json_encode($log->old_values, JSON_PRETTY_PRINT) }}

New: {{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                        </details>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-sm text-gray-500">No recent edits recorded.</p>
    @endif
</div>