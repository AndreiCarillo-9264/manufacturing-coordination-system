@extends('reports.layouts.pdf-layout')

@section('report_title')
    Delivery Schedule Report
@endsection

@section('report_period')
    {{ $filters['date_from'] ?? 'All Records' }} to {{ $filters['date_to'] ?? 'Present' }}
@endsection

@section('executive_summary')
    <div class="executive-summary">
        <div class="summary-title">Executive Summary</div>
        <div class="summary-metrics">
            <div class="metric-item">
                <div class="metric-label">Total Deliveries</div>
                <div class="metric-value">{{ $deliveries->count() }}</div>
            </div>
            <div class="metric-item">
                <div class="metric-label">Total Quantity</div>
                <div class="metric-value">{{ number_format($totalQty) }}</div>
            </div>
            <div class="metric-item">
                <div class="metric-label">Delivered Quantity</div>
                <div class="metric-value">{{ number_format($deliveredQty) }}</div>
            </div>
            <div class="metric-item">
                <div class="metric-label">Delivery Rate</div>
                <div class="metric-value">{{ $totalQty > 0 ? round(($deliveredQty / $totalQty) * 100, 2) : 0 }}%</div>
            </div>
        </div>
    </div>
@endsection

@section('filters_section')
    <div class="filters-section">
        <div class="filters-title">Filters Applied</div>
        <div class="filter-badges">
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
    @if($deliveries->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 9%;">DS Code</th>
                    <th style="width: 16%;">Product / Model</th>
                    <th style="width: 9%;">Customer</th>
                    <th style="width: 8%;">Quantity</th>
                    <th style="width: 6%;">UOM</th>
                    <th style="width: 10%;">Delivery Date</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 12%;">J.O. Number</th>
                </tr>
            </thead>
            <tbody>
                @foreach($deliveries as $delivery)
                    <tr>
                        <td class="font-bold">{{ $delivery->ds_code ?? '—' }}</td>
                        <td>{{ $delivery->product->model_name ?? $delivery->product->product_code }}</td>
                        <td class="text-sm">{{ $delivery->customer_name ?? '—' }}</td>
                        <td class="text-right font-bold">{{ number_format($delivery->quantity) }}</td>
                        <td class="text-center">{{ $delivery->uom }}</td>
                        <td class="text-center">{{ $delivery->delivery_date?->format('M d, Y') ?? '—' }}</td>
                        <td>
                            <span class="badge badge-{{ strtolower($delivery->ds_status) }}">{{ $delivery->ds_status }}</span>
                        </td>
                        <td class="text-center">{{ $delivery->jobOrder?->jo_number ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 30px; color: #999; font-size: 11pt; background: #f9fafb; border-radius: 4px;">
            No delivery data found for the selected filters.
        </div>
    @endif
@endsection

@section('summary_footer')
    @if($deliveries->count() > 0)
        <div class="summary-footer">
            <div class="totals-grid">
                <div class="total-box">
                    <div class="total-label">Total Deliveries</div>
                    <div class="total-value">{{ $deliveries->count() }}</div>
                </div>
                <div class="total-box">
                    <div class="total-label">Total Quantity</div>
                    <div class="total-value">{{ number_format($totalQty) }}</div>
                </div>
                <div class="total-box">
                    <div class="total-label">Delivered Qty</div>
                    <div class="total-value">{{ number_format($deliveredQty) }}</div>
                </div>
                <div class="total-box">
                    <div class="total-label">Delivery Rate</div>
                    <div class="total-value">{{ $totalQty > 0 ? round(($deliveredQty / $totalQty) * 100, 2) : 0 }}%</div>
                </div>
            </div>
        </div>
    @endif
@endsection

    <div class="footer">Report generated on {{ now()->format('Y-m-d H:i:s') }}</div>
</div>
</body>
</html>
