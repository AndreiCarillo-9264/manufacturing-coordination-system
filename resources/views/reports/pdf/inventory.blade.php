@extends('reports.layouts.pdf-layout-manufacturing')

@section('report_title', 'INVENTORY STOCK REPORT')
@section('report_code', 'RPT-INV-' . now()->format('Ymd'))
@section('report_period')
    As of {{ now()->format('F d, Y') }}
@endsection

@section('content')

{{-- REPORT PARAMETERS --}}
<div class="report-parameters">
    <div class="param-title">REPORT PARAMETERS</div>
    <table class="param-table">
        <tr>
            <td><strong>Report Type:</strong> Finished Goods Inventory</td>
            <td><strong>Filter:</strong> {{ request('low_stock') ? 'Low Stock Items Only' : 'All Items' }}</td>
            <td><strong>Total SKUs:</strong> {{ $finishedGoods->count() }}</td>
        </tr>
    </table>
</div>

{{-- INVENTORY SUMMARY --}}
<div class="summary-section">
    <div class="summary-title">INVENTORY SUMMARY</div>
    <table class="summary-table">
        <tr>
            <td class="summary-box">
                <div class="summary-label">TOTAL SKUs</div>
                <div class="summary-value">{{ $finishedGoods->count() }}</div>
            </td>
            <td class="summary-box">
                <div class="summary-label">TOTAL STOCK UNITS</div>
                <div class="summary-value">{{ number_format($totalStock) }}</div>
            </td>
            <td class="summary-box">
                <div class="summary-label">INVENTORY VALUE</div>
                <div class="summary-value" style="font-size: 10pt;">
                    @if(isset($reportCurrency))
                        {{ currencySymbol($reportCurrency) }}{{ number_format($totalValue, 2) }}
                    @else
                        {{ number_format($totalValue, 2) }}
                    @endif
                </div>
            </td>
            <td class="summary-box">
                <div class="summary-label">LOW STOCK ITEMS</div>
                <div class="summary-value" style="color: #dc3545;">
                    {{ $finishedGoods->filter(function($item) {
                        return $item->qty_actual_ending < ($item->product->min_stock ?? 0);
                    })->count() }}
                </div>
            </td>
        </tr>
        <tr>
            <td class="summary-box" colspan="2">
                <div class="summary-label">TOTAL VARIANCE QTY</div>
                <div class="summary-value" style="@if($totalVariance != 0) color: #ea580c; @endif">
                    {{ number_format($totalVariance) }}
                </div>
            </td>
            <td class="summary-box" colspan="2">
                <div class="summary-label">TOTAL VARIANCE AMOUNT</div>
                <div class="summary-value" style="@if($totalVarianceAmount != 0) color: #ea580c; @endif font-size: 10pt;">
                    @if(isset($reportCurrency))
                        {{ currencySymbol($reportCurrency) }}{{ number_format($totalVarianceAmount, 2) }}
                    @else
                        {{ number_format($totalVarianceAmount, 2) }}
                    @endif
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- INVENTORY DETAILS --}}
<div class="data-section">
    <div class="data-title">INVENTORY STOCK DETAILS</div>
    
    @if($finishedGoods->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 6%;">Item Code</th>
                    <th style="width: 13%;">Product / Model</th>
                    <th style="width: 10%;">Customer</th>
                    <th style="width: 6%;">Begin Qty</th>
                    <th style="width: 6%;">Receipts</th>
                    <th style="width: 6%;">Issues</th>
                    <th style="width: 6%;">End Qty</th>
                    <th style="width: 5%;">UOM</th>
                    <th style="width: 6%;">Min Stock</th>
                    <th style="width: 6%;">Variance</th>
                    <th style="width: 9%;">Unit Cost</th>
                    <th style="width: 9%;">End Value</th>
                    <th style="width: 6%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($finishedGoods as $item)
                    @php
                        $isLowStock = $item->qty_actual_ending < ($item->product->min_stock ?? 0);
                        $hasVariance = $item->qty_variance != 0;
                        $receipts = $item->qty_receipts ?? 0; // Add if available
                        $issues = $item->qty_issues ?? 0; // Add if available
                    @endphp
                    <tr>
                        <td class="font-bold text-sm">{{ $item->product->product_code ?? '-' }}</td>
                        <td>{{ $item->product->model_name ?? $item->product->product_code ?? '-' }}</td>
                        <td class="text-sm">{{ $item->product->customer ?? '-' }}</td>
                        <td class="text-right">{{ number_format($item->qty_beginning) }}</td>
                        <td class="text-right">{{ number_format($receipts) }}</td>
                        <td class="text-right">{{ number_format($issues) }}</td>
                        <td class="text-right font-bold" style="@if($isLowStock) color: #dc3545; @endif">
                            {{ number_format($item->qty_actual_ending) }}
                        </td>
                        <td class="text-center text-sm">{{ $item->product->uom ?? 'PCS' }}</td>
                        <td class="text-right text-sm">{{ number_format($item->product->min_stock ?? 0) }}</td>
                        <td class="text-right" style="@if($hasVariance) color: #ea580c; font-weight: bold; @endif">
                            {{ number_format($item->qty_variance) }}
                        </td>
                        <td class="text-right text-sm">
                            {{ currencySymbol($item->product->currency ?? 'PHP') }}{{ number_format($item->product->unit_cost ?? 0, 2) }}
                        </td>
                        <td class="text-right font-bold">
                            {{ currencySymbol($item->product->currency ?? 'PHP') }}{{ number_format($item->amount_ending, 2) }}
                        </td>
                        <td class="text-center">
                            @if($isLowStock)
                                <span class="status-badge status-cancelled">LOW</span>
                            @elseif($hasVariance)
                                <span class="status-badge status-pending">VAR</span>
                            @else
                                <span class="status-badge status-completed">OK</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-right"><strong>TOTALS:</strong></td>
                    <td class="text-right font-bold">{{ number_format($totalStock) }}</td>
                    <td colspan="2"></td>
                    <td class="text-right font-bold" style="@if($totalVariance != 0) color: #ea580c; @endif">
                        {{ number_format($totalVariance) }}
                    </td>
                    <td></td>
                    <td class="text-right font-bold">
                        @if(isset($reportCurrency))
                            {{ currencySymbol($reportCurrency) }}{{ number_format($totalValue, 2) }}
                        @else
                            {{ number_format($totalValue, 2) }}
                        @endif
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    @else
        <div class="no-data">
            No inventory items found matching the selected criteria.
        </div>
    @endif
</div>

{{-- INVENTORY ANALYSIS --}}
@if($finishedGoods->count() > 0)
<div class="breakdown-section">
    <div class="breakdown-title">INVENTORY ANALYSIS</div>
    <table class="breakdown-table" style="width: 60%;">
        <thead>
            <tr>
                <th>Category</th>
                <th>Count</th>
                <th>Total Qty</th>
                <th>Total Value</th>
            </tr>
        </thead>
        <tbody>
            @php
                $lowStockItems = $finishedGoods->filter(function($item) {
                    return $item->qty_actual_ending < ($item->product->min_stock ?? 0);
                });
                $normalItems = $finishedGoods->filter(function($item) {
                    return $item->qty_actual_ending >= ($item->product->min_stock ?? 0);
                });
                $varianceItems = $finishedGoods->filter(function($item) {
                    return $item->qty_variance != 0;
                });
            @endphp
            <tr>
                <td>Normal Stock Level</td>
                <td class="text-center">{{ $normalItems->count() }}</td>
                <td class="text-right">{{ number_format($normalItems->sum('qty_actual_ending')) }}</td>
                <td class="text-right">{{ number_format($normalItems->sum('amount_ending'), 2) }}</td>
            </tr>
            <tr style="background: #fff3cd;">
                <td><strong>Low Stock Items</strong></td>
                <td class="text-center"><strong>{{ $lowStockItems->count() }}</strong></td>
                <td class="text-right"><strong>{{ number_format($lowStockItems->sum('qty_actual_ending')) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($lowStockItems->sum('amount_ending'), 2) }}</strong></td>
            </tr>
            <tr style="background: #fffbeb;">
                <td><strong>Items with Variance</strong></td>
                <td class="text-center"><strong>{{ $varianceItems->count() }}</strong></td>
                <td class="text-right"><strong>{{ number_format($varianceItems->sum('qty_variance')) }}</strong></td>
                <td class="text-right"><strong>{{ number_format($varianceItems->sum('amount_variance'), 2) }}</strong></td>
            </tr>
        </tbody>
    </table>
</div>
@endif

{{-- FOOTER --}}
<div class="report-footer">
    <div class="footer-notes">
        <strong>Inventory Report Notes:</strong>
        <ul>
            <li><strong>Begin Qty:</strong> Opening stock balance at start of period</li>
            <li><strong>Receipts:</strong> Stock received (production output, purchases, returns)</li>
            <li><strong>Issues:</strong> Stock released (sales, transfers, consumption)</li>
            <li><strong>End Qty:</strong> Closing stock balance (Begin + Receipts - Issues)</li>
            <li><strong>Variance:</strong> Difference between system count and physical count</li>
            <li><strong>Status Codes:</strong> OK (Normal stock), LOW (Below minimum), VAR (Has variance)</li>
            <li><strong>Action Required:</strong> Low stock items should be prioritized for production or procurement</li>
            <li>All amounts are in the respective product currency; multi-currency totals are indicative</li>
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
                        <div class="signature-label">Warehouse Supervisor</div>
                        <div class="signature-date">Date: _____________</div>
                    </div>
                </td>
                <td style="width: 33%;">
                    <div class="signature-block">
                        <div class="signature-line">_________________________</div>
                        <div class="signature-label">Inventory Manager</div>
                        <div class="signature-date">Date: _____________</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

@endsection