@props(['action' => '#', 'method' => 'POST', 'title' => '', 'description' => '', 'cancel' => '#', 'submit' => 'Save'])

<div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
    <div class="p-6 border-b bg-gray-50 flex items-center justify-between">
        <div>
            <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
            @if($description)
                <p class="text-sm text-gray-600 mt-1">{!! $description !!}</p>
            @endif
        </div>
        <div>
            {{ $headerRight ?? '' }}
        </div>
    </div>

    <form action="{{ $action }}" method="{{ strtoupper($method) === 'GET' ? 'GET' : 'POST' }}" class="p-6 space-y-8">
        @if(!in_array(strtoupper($method), ['GET','POST']))
            @method($method)
        @endif
        @csrf

        {{ $slot }}

        <div class="flex justify-end gap-4 pt-6 border-t">
            <a href="{{ $cancel }}" class="px-6 py-2.5 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Cancel</a>
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">{{ $submit }}</button>
        </div>
    </form>
</div>