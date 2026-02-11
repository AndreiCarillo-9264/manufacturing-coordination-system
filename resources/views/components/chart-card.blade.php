<div class="bg-white rounded-lg border border-gray-100 shadow-sm p-4">
    <div class="flex items-start justify-between mb-3">
        <div>
            <h4 class="text-sm font-semibold text-gray-800">{{ $title }}</h4>
            @if(isset($subtitle) && $subtitle)
                <p class="text-xs text-gray-500 mt-1">{{ $subtitle }}</p>
            @endif
        </div>
        <div class="text-xs text-gray-400">{{ $meta ?? '' }}</div>
    </div>

    <div class="h-48">
        {{ $slot }}
    </div>

    @if(isset($footer))
    <div class="mt-3 text-xs text-gray-500">
        {{ $footer }}
    </div>
    @endif
</div>