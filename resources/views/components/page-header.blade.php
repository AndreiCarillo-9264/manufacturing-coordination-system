<div class="p-6 border-b bg-gray-50 flex items-center justify-between">
    <div>
        <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
        @if(isset($description) && $description)
            <p class="text-sm text-gray-600 mt-1">{{ $description }}</p>
        @endif
    </div>
    <div class="flex items-center gap-3">
        {{ $actions ?? '' }}
    </div>
</div>