@extends('reports.layouts.pdf-layout')

@section('report_title')
    Production Report
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
                <div class="metric-label">Total Quantity Ordered</div>
                <div class="metric-value">{{ number_format($totalQty) }}</div>
            </div>
            <div class="metric-item">
                <div class="metric-label">Deliverable Quantity</div>
                <div class="metric-value">{{ number_format($totalDeliverable) }}</div>
            </div>
            <div class="metric-item">
                <div class="metric-label">Completion Rate</div>
                <div class="metric-value">{{ $completionRate }}%</div>
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
    <table>
        <thead>
            <tr>
                <th style="width: 10%;">J.O. Number</th>
                <th style="width: 15%;">Product</th>
                <th style="width: 10%;">Customer</th>
                <th style="width: 9%;">Ordered Qty</th>
                <th style="width: 9%;">Deliverable</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 12%;">Date Needed</th>
                <th style="width: 13%;">Encoded By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jobOrders as $jo)
                <tr>
                    <td class="font-bold">{{ $jo->jo_number }}</td>
                    <td>{{ $jo->product->model_name ?? $jo->product->product_code }}</td>
                    <td class="text-sm">{{ $jo->customer_name ?? 'N/A' }}</td>
                    <td class="text-right font-bold">{{ number_format($jo->quantity) }}</td>
                    <td class="text-right font-bold">{{ number_format($jo->deliverable_qty ?? 0) }}</td>
                    <td>
                        <span class="badge badge-{{ strtolower(str_replace(' ', '-', $jo->jo_status)) }}">
                            {{ $jo->jo_status ?? 'Unknown' }}
                        </span>
                    </td>
                    <td class="text-center">{{ $jo->date_needed ? $jo->date_needed->format('M d, Y') : '—' }}</td>
                    <td class="text-sm">{{ $jo->encodedBy->name ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-sm" style="padding: 12px;">No records found for the selected criteria.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection

@section('summary_footer')
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
                <div class="total-label">Deliverable Qty</div>
                <div class="total-value">{{ number_format($totalDeliverable) }}</div>
            </div>
            <div class="total-box">
                <div class="total-label">Completion Rate</div>
                <div class="total-value">{{ $completionRate }}%</div>
            </div>
        </div>
    </div>
@endsection
