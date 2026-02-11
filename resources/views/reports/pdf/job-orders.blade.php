@extends('reports.layouts.pdf-layout')

@section('report_title')
    Job Orders Report
@endsection

@section('report_period')
    {{ $filters['date_from'] ?? 'All Records' }} to {{ $filters['date_to'] ?? 'Present' }}
@endsection

@section('executive_summary')
    <div class="executive-summary">
        <div class="summary-title">Executive Summary</div>
        <div class="summary-metrics">
            <div class="metric-item">
                <div class="metric-label">Total Job Orders</div>
                <div class="metric-value">{{ $jobOrders->count() }}</div>
            </div>
            <div class="metric-item">
                <div class="metric-label">Total Quantity</div>
                <div class="metric-value">{{ number_format($totalQty) }}</div>
            </div>
            <div class="metric-item">
                <div class="metric-label">Total Amount</div>
                <div class="metric-value">
                    @if(isset($reportCurrency))
                        {{ currencySymbol($reportCurrency) }}{{ number_format($totalAmount, 2) }}
                    @else
                        {{ number_format($totalAmount, 2) }}
                    @endif
                </div>
            </div>
            <div class="metric-item">
                <div class="metric-label">Pending JOs</div>
                <div class="metric-value">{{ $jobOrders->where('status', 'pending')->count() }}</div>
            </div>
        </div>
    </div>
@endsection

@section('filters_section')
    <div class="filters-section">
        <div class="filters-title">Filters Applied</div>
        <div class="filter-badges">
            <div class="filter-badge">
                <strong>Customer:</strong> {{ $filters['customer'] ?? 'All Customers' }}
            </div>
            <div class="filter-badge">
                <strong>Status:</strong> {{ $filters['status'] ?? 'All Statuses' }}
            </div>
            <div class="filter-badge">
                <strong>Date From:</strong> {{ $filters['date_from'] ?? 'N/A' }}
            </div>
            <div class="filter-badge">
                <strong>Date To:</strong> {{ $filters['date_to'] ?? 'N/A' }}
            </div>
        </div>
    </div>
@endsection

@section('content')
    @if($jobOrders->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">JO Number</th>
                    <th style="width: 8%;">PO Number</th>
                    <th style="width: 14%;">Product / Model</th>
                    <th style="width: 12%;">Customer</th>
                    <th style="width: 7%;">Qty</th>
                    <th style="width: 5%;">UOM</th>
                    <th style="width: 9%;">Date Needed</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 11%;">Unit Price</th>
                    <th style="width: 10%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jobOrders as $jo)
                    <tr>
                        <td class="font-bold">{{ $jo->jo_number }}</td>
                        <td>{{ $jo->po_number ?? '—' }}</td>
                        <td>{{ $jo->product->model_name ?? $jo->product->product_code ?? '—' }}</td>
                        <td class="text-sm">{{ $jo->product->customer ?? '—' }}</td>
                        <td class="text-right font-bold">{{ number_format($jo->qty) }}</td>
                        <td class="text-center">{{ $jo->uom }}</td>
                        <td class="text-center">{{ $jo->date_needed?->format('M d, Y') ?? '—' }}</td>
                        <td>
                            <span class="badge badge-{{ strtolower(str_replace(' ', '-', $jo->status)) }}">
                                {{ ucfirst(str_replace('_', ' ', $jo->status)) }}
                            </span>
                        </td>
                        <td class="text-right text-sm">{{ currencySymbol($jo->product->currency ?? 'PHP') }}{{ number_format($jo->product->selling_price ?? 0, 2) }}</td>
                        <td class="text-right font-bold">{{ currencySymbol($jo->product->currency ?? 'PHP') }}{{ number_format(($jo->qty * ($jo->product->selling_price ?? 0)), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 30px; color: #999; font-size: 11pt; background: #f9fafb; border-radius: 4px;">
            No job orders found for the selected criteria.
        </div>
    @endif
@endsection

@section('summary_footer')
    @if($jobOrders->count() > 0)
        <div class="summary-footer">
            <div class="totals-grid">
                <div class="total-box">
                    <div class="total-label">Total Job Orders</div>
                    <div class="total-value">{{ $jobOrders->count() }}</div>
                </div>
                <div class="total-box">
                    <div class="total-label">Total Quantity</div>
                    <div class="total-value">{{ number_format($totalQty) }}</div>
                </div>
                <div class="total-box">
                    <div class="total-label">Total Amount</div>
                    <div class="total-value">
                        @if(isset($reportCurrency))
                            {{ currencySymbol($reportCurrency) }}{{ number_format($totalAmount, 2) }}
                        @else
                            {{ number_format($totalAmount, 2) }}
                        @endif
                    </div>
                </div>
                <div class="total-box">
                    <div class="total-label">Pending Orders</div>
                    <div class="total-value">{{ $jobOrders->where('status', 'pending')->count() }}</div>
                </div>
            </div>
        </div>
    @endif
@endsection