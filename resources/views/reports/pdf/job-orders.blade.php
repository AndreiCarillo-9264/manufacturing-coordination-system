@extends('reports.layouts.pdf-layout-manufacturing')

@section('report_title', 'JOB ORDER SUMMARY REPORT')
@section('report_code', 'RPT-JO-' . now()->format('Ymd'))
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

{{-- SUMMARY STATISTICS --}}
<div class="summary-section">
    <div class="summary-title">SUMMARY STATISTICS</div>
    <table class="summary-table">
        <tr>
            <td class="summary-box">
                <div class="summary-label">TOTAL JOB ORDERS</div>
                <div class="summary-value">{{ $jobOrders->count() }}</div>
            </td>
            <td class="summary-box">
                <div class="summary-label">PENDING</div>
                <div class="summary-value">{{ $jobOrders->where('status', 'pending')->count() }}</div>
            </td>
            <td class="summary-box">
                <div class="summary-label">IN PROGRESS</div>
                <div class="summary-value">{{ $jobOrders->where('status', 'in_progress')->count() }}</div>
            </td>
            <td class="summary-box">
                <div class="summary-label">COMPLETED</div>
                <div class="summary-value">{{ $jobOrders->where('status', 'completed')->count() }}</div>
            </td>
        </tr>
        <tr>
            <td class="summary-box" colspan="2">
                <div class="summary-label">TOTAL QUANTITY</div>
                <div class="summary-value">{{ number_format($totalQty) }}</div>
            </td>
            <td class="summary-box" colspan="2">
                <div class="summary-label">TOTAL VALUE</div>
                <div class="summary-value">
                    @if(isset($reportCurrency))
                        {{ currencySymbol($reportCurrency) }}{{ number_format($totalAmount, 2) }}
                    @else
                        {{ number_format($totalAmount, 2) }}
                    @endif
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- DETAILED LISTING --}}
<div class="data-section">
    <div class="data-title">JOB ORDER DETAILS</div>
    
    @if($jobOrders->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 8%;">JO No.</th>
                    <th style="width: 8%;">PO No.</th>
                    <th style="width: 15%;">Product</th>
                    <th style="width: 12%;">Customer</th>
                    <th style="width: 7%;">Qty</th>
                    <th style="width: 5%;">UOM</th>
                    <th style="width: 9%;">Unit Price</th>
                    <th style="width: 10%;">Amount</th>
                    <th style="width: 9%;">Date Needed</th>
                    <th style="width: 8%;">Status</th>
                    <th style="width: 9%;">Prepared By</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jobOrders as $index => $jo)
                    <tr>
                        <td class="font-bold">{{ $jo->jo_number }}</td>
                        <td>{{ $jo->po_number ?? '-' }}</td>
                        <td>{{ $jo->product->model_name ?? $jo->product->product_code ?? '-' }}</td>
                        <td class="text-sm">{{ $jo->product->customer ?? '-' }}</td>
                        <td class="text-right font-bold">{{ number_format($jo->qty) }}</td>
                        <td class="text-center">{{ $jo->uom }}</td>
                        <td class="text-right">{{ currencySymbol($jo->product->currency ?? 'PHP') }}{{ number_format($jo->product->selling_price ?? 0, 2) }}</td>
                        <td class="text-right font-bold">{{ currencySymbol($jo->product->currency ?? 'PHP') }}{{ number_format(($jo->qty * ($jo->product->selling_price ?? 0)), 2) }}</td>
                        <td class="text-center">{{ $jo->date_needed?->format('m/d/Y') ?? '-' }}</td>
                        <td class="text-center">
                            <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $jo->status)) }}">
                                {{ strtoupper($jo->status) }}
                            </span>
                        </td>
                        <td class="text-sm">{{ $jo->encodedBy->name ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right"><strong>TOTALS:</strong></td>
                    <td class="text-right font-bold">{{ number_format($totalQty) }}</td>
                    <td colspan="2"></td>
                    <td class="text-right font-bold">
                        @if(isset($reportCurrency))
                            {{ currencySymbol($reportCurrency) }}{{ number_format($totalAmount, 2) }}
                        @else
                            {{ number_format($totalAmount, 2) }}
                        @endif
                    </td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">
            No job orders found matching the selected criteria.
        </div>
    @endif
</div>

{{-- STATUS BREAKDOWN --}}
@if($jobOrders->count() > 0)
<div class="breakdown-section">
    <div class="breakdown-title">STATUS BREAKDOWN</div>
    <table class="breakdown-table">
        <thead>
            <tr>
                <th>Status</th>
                <th>Count</th>
                <th>Total Quantity</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
            @php
                $statuses = ['pending', 'approved', 'in_progress', 'completed', 'cancelled'];
            @endphp
            @foreach($statuses as $status)
                @php
                    $statusJOs = $jobOrders->where('status', $status);
                    $statusCount = $statusJOs->count();
                    $statusQty = $statusJOs->sum('qty');
                    $percentage = $jobOrders->count() > 0 ? round(($statusCount / $jobOrders->count()) * 100, 1) : 0;
                @endphp
                @if($statusCount > 0)
                <tr>
                    <td>{{ strtoupper($status) }}</td>
                    <td class="text-center">{{ $statusCount }}</td>
                    <td class="text-right">{{ number_format($statusQty) }}</td>
                    <td class="text-right">{{ $percentage }}%</td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- FOOTER NOTES --}}
<div class="report-footer">
    <div class="footer-notes">
        <strong>Notes:</strong>
        <ul>
            <li>All amounts are displayed in the product's respective currency</li>
            <li>Status definitions: PENDING (Awaiting approval), APPROVED (Ready for production), IN PROGRESS (Currently being manufactured), COMPLETED (Finished), CANCELLED (Voided)</li>
            <li>This report reflects the status as of {{ now()->format('F d, Y h:i A') }}</li>
        </ul>
    </div>
    <div class="footer-signature">
        <table style="width: 100%;">
            <tr>
                <td style="width: 50%;">
                    <div class="signature-block">
                        <div class="signature-line">_________________________</div>
                        <div class="signature-label">Prepared By</div>
                        <div class="signature-date">Date: {{ now()->format('M d, Y') }}</div>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="signature-block">
                        <div class="signature-line">_________________________</div>
                        <div class="signature-label">Reviewed By</div>
                        <div class="signature-date">Date: _____________</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

@endsection