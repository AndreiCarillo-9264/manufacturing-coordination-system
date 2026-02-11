{{-- Reusable Report Generator Component --}}
@props([
    'title' => 'Generate Report',
    'subtitle' => 'Export data to PDF',
    'reportRoute' => 'reports.index',
    'customers' => [],
    'hasDateRange' => false,
    'hasStatusFilter' => false,
    'statuses' => [],
    'hasCustomerFilter' => true,
    'icon' => 'fa-file-pdf',
    'color' => 'indigo',
])

<div class="bg-gradient-to-br from-{{ $color }}-50 to-{{ str_replace('indigo', 'purple', $color) }}-50 rounded-2xl shadow-md p-6 border border-{{ $color }}-200 hover:shadow-xl transition-shadow duration-300">
    <div class="flex items-center mb-6">
        <div class="w-10 h-10 bg-{{ $color }}-500 rounded-lg flex items-center justify-center mr-3 shadow-md">
            <i class="fas {{ $icon }} text-white"></i>
        </div>
        <div>
            <h3 class="text-lg font-bold text-gray-800">{{ $title }}</h3>
            <p class="text-sm text-gray-500">{{ $subtitle }}</p>
        </div>
    </div>

    <form action="{{ route($reportRoute) }}" method="GET" class="space-y-4">
        {{-- Customer Filter --}}
        @if($hasCustomerFilter)
        <div>
            <label for="customer-{{ uniqid() }}" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-user-tie text-gray-400 mr-1"></i> Customer
            </label>
            <select name="customer" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-{{ $color }}-500 focus:ring-2 focus:ring-{{ $color }}-200 transition-all text-sm">
                <option value="">All Customers</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer }}">{{ $customer }}</option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Status Filter --}}
        @if($hasStatusFilter && count($statuses) > 0)
        <div>
            <label for="status-{{ uniqid() }}" class="block text-sm font-semibold text-gray-700 mb-2">
                <i class="fas fa-filter text-gray-400 mr-1"></i> Status
            </label>
            <select name="status" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-{{ $color }}-500 focus:ring-2 focus:ring-{{ $color }}-200 transition-all text-sm">
                <option value="">All Statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        @endif

        {{-- Date Range --}}
        @if($hasDateRange)
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label for="date_from-{{ uniqid() }}" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-calendar text-gray-400 mr-1"></i> From
                </label>
                <input type="date" name="date_from" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-{{ $color }}-500 focus:ring-2 focus:ring-{{ $color }}-200 transition-all text-sm">
            </div>
            <div>
                <label for="date_to-{{ uniqid() }}" class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-calendar text-gray-400 mr-1"></i> To
                </label>
                <input type="date" name="date_to" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-{{ $color }}-500 focus:ring-2 focus:ring-{{ $color }}-200 transition-all text-sm">
            </div>
        </div>
        @endif

        {{-- Slot for additional filters --}}
        {{ $slot }}

        {{-- Submit Button --}}
        <button type="submit" class="w-full bg-gradient-to-r from-{{ $color }}-600 to-{{ str_replace('indigo', 'purple', $color) }}-600 hover:from-{{ $color }}-700 hover:to-{{ str_replace('indigo', 'purple', $color) }}-700 text-white font-bold py-2.5 px-4 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg flex items-center justify-center">
            <i class="fas fa-download mr-2"></i> Generate PDF Report
        </button>

        <p class="text-xs text-gray-500 text-center mt-3">
            <i class="fas fa-info-circle mr-1"></i> Report will be downloaded as PDF
        </p>
    </form>
</div>
