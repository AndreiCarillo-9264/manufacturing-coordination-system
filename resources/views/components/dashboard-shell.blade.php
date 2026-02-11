<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="p-6 border-b bg-gray-50 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $title }}</h2>
            @if(isset($description) && $description)
                <p class="text-sm text-gray-600 mt-1">{{ $description }}</p>
            @endif
        </div>
        <div class="flex items-center gap-3">
            {{ $actions ?? '' }}
        </div>
    </div>

    <div class="p-6">
        {{ $slot }}
    </div>
</div>