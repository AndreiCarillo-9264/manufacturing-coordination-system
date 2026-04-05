@extends('reports.layouts.pdf-layout-manufacturing')

@section('report_title', 'PRODUCTION REPORT')
@section('report_code', 'RPT-PROD-' . now()->format('Ymd'))
@section('report_period')
    Period: {{ $filters['date_from'] ?? 'All' }} to {{ $filters['date_to'] ?? now()->format('M d, Y') }}
@endsection

@section('content')

{{-- REPORT PARAMETERS --}}
<div class="report-parameters">
    <div class="param-title">REPORT PARAMETERS</div>
    <table class="param-table">
        <tr>
            <td><strong>Status Filter:</strong> {{ ucfirst($filters['status'] ?? 'All Statuses') }}</td>
            <td><strong>Date Range:</strong> {{ $filters['date_from'] ?? 'Start' }} - {{ $filters['date_to'] ?? 'End' }}</td>
            <td><strong>Total Records:</strong> {{ $jobOrders->count() }}</td>
        </tr>
    </table>
</div>

{{-- PRODUCTION SUMMARY --}}
<div class="summary-section">
    <div class="summary-title">PRODUCTION SUMMARY</div>
    <table class="summary-table">
        <tr>
            <td class="summary-box">
                <div class="summary-label">TOTAL JOB ORDERS</div>
                <div class="summary-value">{{ $jobOrders->count() }}</div>
            </td>
            <td class="summary-box">
                <div class="summary-label">ORDERED QTY</div>
                <div class="summary-value">{{ number_format($totalQty) }}</div>
            </td>
            <td class="summary-box">
                <div class="summary-label">DELIVERABLE QTY</div>
                <div class="summary-value">{{ number_format($totalDeliverable) }}</div>
            </td>
            <td class="summary-box">
                <div class="summary-label">COMPLETION RATE</div>
                <div class="summary-value">{{ $completionRate }}%</div>
            </td>
        </tr>
        <tr>
            <td class="summary-box">
                <div class="summary-label">IN PROGRESS</div>
                <div class="summary-value">{{ $jobOrders->where('jo_status', 'In Progress')->count() }}</div>
            </td>
            <td class="summary-box">
                <div class="summary-label">COMPLETED (JO FULL)</div>
                <div class="summary-value">{{ $jobOrders->where('jo_status', 'JO Full')->count() }}</div>
            </td>
            <td class="summary-box">
                <div class="summary-label">PENDING/APPROVED</div>
                <div class="summary-value">{{ $jobOrders->where('jo_status', 'Approved')->count() }}</div>
            </td>
            <td class="summary-box">
                <div class="summary-label">CANCELLED</div>
                <div class="summary-value">{{ $jobOrders->where('jo_status', 'Cancelled')->count() }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- PRODUCTION DETAILS --}}
<div class="data-section">
    <div class="data-title">PRODUCTION DETAILS</div>
    
    @if($jobOrders->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 9%;">J.O. No.</th>
                    <th style="width: 14%;">Product / Model</th>
                    <th style="width: 11%;">Customer</th>
                    <th style="width: 7%;">Ordered</th>
                    <th style="width: 7%;">Deliverable</th>
                    <th style="width: 7%;">Balance</th>
                    <th style="width: 5%;">UOM</th>
                    <th style="width: 9%;">Date Needed</th>
                    <th style="width: 9%;">Status</th>
                    <th style="width: 6%;">Progress</th>
                    <th style="width: 10%;">Encoded By</th>
                    <th style="width: 6%;">Days Left</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jobOrders as $jo)
                    @php
                        $balance = $jo->quantity - ($jo->deliverable_qty ?? 0);
                        $progress = $jo->quantity > 0 ? round((($jo->deliverable_qty ?? 0) / $jo->quantity) * 100, 1) : 0;
                        $daysLeft = $jo->date_needed ? now()->diffInDays($jo->date_needed, false) : null;
                        $isOverdue = $daysLeft !== null && $daysLeft < 0;
                    @endphp
                    <tr>
                        <td class="font-bold">{{ $jo->jo_number }}</td>
                        <td>{{ $jo->product->model_name ?? $jo->product->product_code }}</td>
                        <td class="text-sm">{{ $jo->customer_name ?? ($jo->product->customer ?? 'N/A') }}</td>
                        <td class="text-right font-bold">{{ number_format($jo->quantity) }}</td>
                        <td class="text-right font-bold">{{ number_format($jo->deliverable_qty ?? 0) }}</td>
                        <td class="text-right" style="@if($balance > 0) color: #dc3545; font-weight: bold; @endif">
                            {{ number_format($balance) }}
                        </td>
                        <td class="text-center">{{ $jo->uom ?? 'PCS' }}</td>
                        <td class="text-center">{{ $jo->date_needed?->format('m/d/Y') ?? '-' }}</td>
                        <td class="text-center">
                            <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $jo->jo_status)) }}">
                                {{ strtoupper($jo->jo_status) }}
                            </span>
                        </td>
                        <td class="text-center">{{ $progress }}%</td>
                        <td class="text-sm">{{ $jo->encodedBy->name ?? '-' }}</td>
                        <td class="text-center" style="@if($isOverdue) color: #dc3545; font-weight: bold; @endif">
                            @if($daysLeft !== null)
                                @if($isOverdue)
                                    {{ abs($daysLeft) }} overdue
                                @else
                                    {{ $daysLeft }}
                                @endif
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right"><strong>TOTALS:</strong></td>
                    <td class="text-right font-bold">{{ number_format($totalQty) }}</td>
                    <td class="text-right font-bold">{{ number_format($totalDeliverable) }}</td>
                    <td class="text-right font-bold">{{ number_format($totalQty - $totalDeliverable) }}</td>
                    <td colspan="6"></td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">
            No production data found matching the selected criteria.
        </div>
    @endif
</div>

{{-- PRODUCTION EFFICIENCY ANALYSIS --}}
@if($jobOrders->count() > 0)
<div class="breakdown-section">
    <div class="breakdown-title">PRODUCTION EFFICIENCY ANALYSIS</div>
    <table class="breakdown-table">
        <thead>
            <tr>
                <th>Metric</th>
                <th>Value</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Total Jobs in Production</td>
                <td class="text-center">{{ $jobOrders->whereIn('jo_status', ['Approved', 'In Progress'])->count() }}</td>
                <td class="text-sm">Active manufacturing orders</td>
            </tr>
            <tr>
                <td>Average Completion Rate</td>
                <td class="text-center">{{ $completionRate }}%</td>
                <td class="text-sm">Overall production progress</td>
            </tr>
            <tr>
                <td>On-Time Delivery Risk</td>
                <td class="text-center">
                    {{ $jobOrders->filter(function($jo) {
                        $daysLeft = $jo->date_needed ? now()->diffInDays($jo->date_needed, false) : null;
                        return $daysLeft !== null && $daysLeft < 0;
                    })->count() }}
                </td>
                <td class="text-sm">Orders past due date</td>
            </tr>
            <tr>
                <td>Remaining Production Qty</td>
                <td class="text-center">{{ number_format($totalQty - $totalDeliverable) }}</td>
                <td class="text-sm">Units yet to be produced</td>
            </tr>
        </tbody>
    </table>
</div>
@endif

{{-- FOOTER --}}
<div class="report-footer">
    <div class="footer-notes">
        <strong>Production Report Notes:</strong>
        <ul>
            <li><strong>Ordered:</strong> Total quantity as per job order</li>
            <li><strong>Deliverable:</strong> Quantity completed and ready for delivery</li>
            <li><strong>Balance:</strong> Remaining quantity to be produced</li>
            <li><strong>Progress:</strong> Percentage of job order completion (Deliverable / Ordered × 100)</li>
            <li><strong>Days Left:</strong> Calendar days until due date; negative values indicate overdue orders</li>
            <li><strong>Status Codes:</strong> APPROVED (Ready to start), IN PROGRESS (Currently manufacturing), JO FULL (Completed), CANCELLED (Voided)</li>
        </ul>
    </div>
    <div class="footer-signature">
        <table style="width: 100%;">
            <tr>
                <td style="width: 33%;">
                    <div class="signature-block">
                        <div class="signature-line">_________________________</div>
                        <div class="signature-label">Prepared By</div>
                        <div class="signature-date">Date: {{ now()->format('M d, Y') }}</div>
                    </div>
                </td>
                <td style="width: 34%;">
                    <div class="signature-block">
                        <div class="signature-line">_________________________</div>
                        <div class="signature-label">Production Manager</div>
                        <div class="signature-date">Date: _____________</div>
                    </div>
                </td>
                <td style="width: 33%;">
                    <div class="signature-block">
                        <div class="signature-line">_________________________</div>
                        <div class="signature-label">General Manager</div>
                        <div class="signature-date">Date: _____________</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

@endsection