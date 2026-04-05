@extends('reports.layouts.pdf-layout-manufacturing')

@section('report_title', 'LOGISTICS & MATERIAL MOVEMENT REPORT')
@section('report_code', 'RPT-LOG-' . now()->format('Ymd'))
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
            <td><strong>Total Transactions:</strong> {{ $endorsements->count() }}</td>
        </tr>
    </table>
</div>

{{-- LOGISTICS SUMMARY --}}
<div class="summary-section">
    <div class="summary-title">LOGISTICS SUMMARY</div>
    <table class="summary-table">
        <tr>
            <td class="summary-box">
                <div class="summary-label">TOTAL ENDORSEMENTS</div>
                <div class="summary-value">{{ $endorsements->count() }}</div>
            </td>
            <td class="summary-box">
                <div class="summary-label">TOTAL QUANTITY</div>
                <div class="summary-value">{{ number_format($totalQty) }}</div>
            </td>
            <td class="summary-box">
                <div class="summary-label">PENDING/NEW</div>
                <div class="summary-value">{{ $endorsements->where('status', 'pending')->count() }}</div>
            </td>
            <td class="summary-box">
                <div class="summary-label">IN TRANSIT</div>
                <div class="summary-value">{{ $endorsements->where('status', 'in_progress')->count() }}</div>
            </td>
        </tr>
        <tr>
            <td class="summary-box">
                <div class="summary-label">APPROVED QTY</div>
                <div class="summary-value">{{ number_format($approvedQty) }}</div>
            </td>
            <td class="summary-box">
                <div class="summary-label">COMPLETED QTY</div>
                <div class="summary-value">{{ number_format($completedQty) }}</div>
            </td>
            <td class="summary-box">
                <div class="summary-label">DELIVERY RATE</div>
                <div class="summary-value">
                    {{ $totalQty > 0 ? round(($completedQty / $totalQty) * 100, 1) : 0 }}%
                </div>
            </td>
            <td class="summary-box">
                <div class="summary-label">COMPLETED</div>
                <div class="summary-value">{{ $endorsements->where('status', 'completed')->count() }}</div>
            </td>
        </tr>
    </table>
</div>

{{-- ENDORSEMENT DETAILS --}}
<div class="data-section">
    <div class="data-title">ENDORSEMENT & MOVEMENT DETAILS</div>
    
    @if($endorsements->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 7%;">ETL Code</th>
                    <th style="width: 13%;">Product / Model</th>
                    <th style="width: 10%;">Customer</th>
                    <th style="width: 6%;">Quantity</th>
                    <th style="width: 5%;">UOM</th>
                    <th style="width: 8%;">Date</th>
                    <th style="width: 9%;">From Location</th>
                    <th style="width: 9%;">To Location</th>
                    <th style="width: 8%;">Status</th>
                    <th style="width: 10%;">Driver/Hauler</th>
                    <th style="width: 8%;">Vehicle</th>
                    <th style="width: 7%;">Remarks</th>
                </tr>
            </thead>
            <tbody>
                @foreach($endorsements as $endorsement)
                    <tr>
                        <td class="font-bold">{{ $endorsement->etl_code }}</td>
                        <td>{{ $endorsement->model_name ?? $endorsement->product_code ?? '-' }}</td>
                        <td class="text-sm">{{ $endorsement->customer_name ?? '-' }}</td>
                        <td class="text-right font-bold">{{ number_format($endorsement->quantity) }}</td>
                        <td class="text-center">{{ $endorsement->uom }}</td>
                        <td class="text-center">{{ $endorsement->date?->format('m/d/Y') ?? '-' }}</td>
                        <td class="text-sm">{{ $endorsement->from_location ?? 'Warehouse' }}</td>
                        <td class="text-sm">{{ $endorsement->to_location ?? $endorsement->customer_name }}</td>
                        <td class="text-center">
                            <span class="status-badge status-{{ strtolower($endorsement->status) }}">
                                {{ strtoupper($endorsement->status) }}
                            </span>
                        </td>
                        <td class="text-sm">{{ $endorsement->driver_name ?? '-' }}</td>
                        <td class="text-sm">{{ $endorsement->vehicle_plate ?? '-' }}</td>
                        <td class="text-xs">{{ Str::limit($endorsement->remarks ?? '-', 20) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right"><strong>TOTALS:</strong></td>
                    <td class="text-right font-bold">{{ number_format($totalQty) }}</td>
                    <td colspan="8"></td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">
            No logistics data found matching the selected criteria.
        </div>
    @endif
</div>

{{-- LOGISTICS PERFORMANCE --}}
@if($endorsements->count() > 0)
<div class="breakdown-section">
    <div class="breakdown-title">LOGISTICS PERFORMANCE ANALYSIS</div>
    <table class="breakdown-table">
        <thead>
            <tr>
                <th>Status</th>
                <th>Count</th>
                <th>Quantity</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>
            @php
                $statuses = [
                    'pending' => 'Pending/New',
                    'approved' => 'Approved',
                    'in_progress' => 'In Transit',
                    'completed' => 'Completed'
                ];
            @endphp
            @foreach($statuses as $statusKey => $statusLabel)
                @php
                    $statusEndorsements = $endorsements->where('status', $statusKey);
                    $statusCount = $statusEndorsements->count();
                    $statusQty = $statusEndorsements->sum('quantity');
                    $percentage = $endorsements->count() > 0 ? round(($statusCount / $endorsements->count()) * 100, 1) : 0;
                @endphp
                @if($statusCount > 0)
                <tr>
                    <td>{{ $statusLabel }}</td>
                    <td class="text-center">{{ $statusCount }}</td>
                    <td class="text-right">{{ number_format($statusQty) }}</td>
                    <td class="text-right">{{ $percentage }}%</td>
                </tr>
                @endif
            @endforeach
            <tr style="background: #e0e0e0; font-weight: bold;">
                <td>TOTAL</td>
                <td class="text-center">{{ $endorsements->count() }}</td>
                <td class="text-right">{{ number_format($totalQty) }}</td>
                <td class="text-right">100%</td>
            </tr>
        </tbody>
    </table>
    
    <div style="margin-top: 15px;">
        <div style="background: #f9f9f9; border: 1px solid #ccc; padding: 8px; font-size: 8pt;">
            <strong>Key Performance Indicators:</strong><br>
            <table style="width: 100%; margin-top: 5px;">
                <tr>
                    <td style="width: 50%; padding: 3px;">
                        <strong>On-Time Delivery Rate:</strong> 
                        {{ $endorsements->count() > 0 ? round(($completedQty / $totalQty) * 100, 1) : 0 }}%
                    </td>
                    <td style="width: 50%; padding: 3px;">
                        <strong>Pending Deliveries:</strong> 
                        {{ $endorsements->where('status', 'pending')->count() }} transactions
                    </td>
                </tr>
                <tr>
                    <td style="padding: 3px;">
                        <strong>Average Qty per Endorsement:</strong> 
                        {{ $endorsements->count() > 0 ? number_format($totalQty / $endorsements->count(), 2) : 0 }}
                    </td>
                    <td style="padding: 3px;">
                        <strong>Completion Status:</strong> 
                        {{ $endorsements->where('status', 'completed')->count() }} of {{ $endorsements->count() }} completed
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
@endif

{{-- FOOTER --}}
<div class="report-footer">
    <div class="footer-notes">
        <strong>Logistics Report Notes:</strong>
        <ul>
            <li><strong>ETL Code:</strong> Endorsement to Logistics tracking number</li>
            <li><strong>Status Definitions:</strong>
                <ul style="margin-left: 15px; margin-top: 2px;">
                    <li>PENDING - Endorsement created, awaiting approval or dispatch</li>
                    <li>APPROVED - Authorized for movement, ready for pickup</li>
                    <li>IN PROGRESS - Currently in transit to destination</li>
                    <li>COMPLETED - Successfully delivered and received</li>
                </ul>
            </li>
            <li><strong>From/To Location:</strong> Material movement origin and destination points</li>
            <li><strong>Driver/Hauler:</strong> Person responsible for transport</li>
            <li><strong>Vehicle:</strong> Transportation unit (truck, van, etc.)</li>
            <li>This report tracks finished goods movement from warehouse to customers or between facilities</li>
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
                        <div class="signature-label">Logistics Coordinator</div>
                        <div class="signature-date">Date: _____________</div>
                    </div>
                </td>
                <td style="width: 33%;">
                    <div class="signature-block">
                        <div class="signature-line">_________________________</div>
                        <div class="signature-label">Operations Manager</div>
                        <div class="signature-date">Date: _____________</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

@endsection