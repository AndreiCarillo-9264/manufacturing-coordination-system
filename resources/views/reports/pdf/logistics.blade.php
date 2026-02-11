@extends('reports.layouts.pdf-layout')

@section('report_title')
    Endorsement & Logistics Report
@endsection

@section('report_period')
    {{ $filters['date_from'] ?? 'All Records' }} to {{ $filters['date_to'] ?? 'Present' }}
@endsection

@section('executive_summary')
    <div class="executive-summary">
        <div class="summary-title">Executive Summary</div>
        <div class="summary-metrics">
            <div class="metric-item">
                <div class="metric-label">Total Endorsements</div>
                <div class="metric-value">{{ $endorsements->count() }}</div>
            </div>
            <div class="metric-item">
                <div class="metric-label">Total Quantity</div>
                <div class="metric-value">{{ number_format($totalQty) }}</div>
            </div>
            <div class="metric-item">
                <div class="metric-label">Approved Quantity</div>
                <div class="metric-value">{{ number_format($approvedQty) }}</div>
            </div>
            <div class="metric-item">
                <div class="metric-label">Completion Rate</div>
                <div class="metric-value">{{ $totalQty > 0 ? round(($completedQty / $totalQty) * 100, 2) : 0 }}%</div>
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
    @if($endorsements->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">ETL Code</th>
                    <th style="width: 15%;">Product / Model</th>
                    <th style="width: 12%;">Customer</th>
                    <th style="width: 8%;">Quantity</th>
                    <th style="width: 6%;">UOM</th>
                    <th style="width: 9%;">Date</th>
                    <th style="width: 10%;">Status</th>
                    <th style="width: 12%;">Driver</th>
                    <th style="width: 14%;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($endorsements as $endorsement)
                    <tr>
                        <td class="font-bold">{{ $endorsement->etl_code }}</td>
                        <td>{{ $endorsement->model_name ?? $endorsement->product_code ?? '—' }}</td>
                        <td class="text-sm">{{ $endorsement->customer_name ?? '—' }}</td>
                        <td class="text-right font-bold">{{ number_format($endorsement->quantity) }}</td>
                        <td class="text-center">{{ $endorsement->uom }}</td>
                        <td class="text-center">{{ $endorsement->date?->format('M d, Y') ?? '—' }}</td>
                        <td>
                            <span class="badge badge-{{ strtolower($endorsement->status) }}">{{ ucfirst($endorsement->status) }}</span>
                        </td>
                        <td class="text-sm">{{ $endorsement->driver_name ?? '—' }}</td>
                        <td class="text-xs">{{ Str::limit($endorsement->remarks, 50) ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 30px; color: #999; font-size: 11pt; background: #f9fafb; border-radius: 4px;">
            No logistics data found for the selected filters.
        </div>
    @endif
@endsection

@section('summary_footer')
    @if($endorsements->count() > 0)
        <div class="summary-footer">
            <div class="totals-grid">
                <div class="total-box">
                    <div class="total-label">Total Endorsements</div>
                    <div class="total-value">{{ $endorsements->count() }}</div>
                </div>
                <div class="total-box">
                    <div class="total-label">Total Quantity</div>
                    <div class="total-value">{{ number_format($totalQty) }}</div>
                </div>
                <div class="total-box">
                    <div class="total-label">Approved Qty</div>
                    <div class="total-value">{{ number_format($approvedQty) }}</div>
                </div>
                <div class="total-box">
                    <div class="total-label">Completed Qty</div>
                    <div class="total-value">{{ number_format($completedQty) }}</div>
                </div>
            </div>
        </div>
    @endif
@endsection
