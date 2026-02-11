@props(['title' => '', 'description' => ''])

<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="p-6 border-b bg-gray-50 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h3 class="text-xl font-semibold text-gray-800">{{ $title }}</h3>
            @if($description)
                <p class="text-sm text-gray-600 mt-1">{!! $description !!}</p>
            @endif
        </div>
        <div>
            {{ $headerRight ?? '' }}
        </div>
    </div>

    <div class="p-6">
        {{ $slot }}
    </div>
</div>