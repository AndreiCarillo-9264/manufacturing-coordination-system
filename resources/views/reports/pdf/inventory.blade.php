@extends('reports.layouts.pdf-layout')

@section('report_title')
    Inventory Report
@endsection

@section('report_period')
    Current Inventory Status as of {{ now()->format('M d, Y') }}
@endsection

@section('executive_summary')
    <div class="executive-summary">
        <div class="summary-title">Executive Summary</div>
        <div class="summary-metrics">
            <div class="metric-item">
                <div class="metric-label">Total Stock Items</div>
                <div class="metric-value">{{ $finishedGoods->count() }}</div>
            </div>
            <div class="metric-item">
                <div class="metric-label">Total Stock Units</div>
                <div class="metric-value">{{ number_format($totalStock) }}</div>
            </div>
            <div class="metric-item">
                <div class="metric-label">Total Inventory Value</div>
                <div class="metric-value">
                    @if(isset($reportCurrency))
                        {{ currencySymbol($reportCurrency) }}{{ number_format($totalValue, 2) }}
                    @else
                        {{ number_format($totalValue, 2) }}
                    @endif
                </div>
            </div>
            <div class="metric-item">
                <div class="metric-label">Total Variance Qty</div>
                <div class="metric-value" style="@if($totalVariance != 0) color: #ea580c; @endif">
                    {{ number_format($totalVariance) }}
                </div>
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
                <strong>Type:</strong> {{ $filters['type'] ?? 'All Items' }}
            </div>
            <div class="filter-badge">
                <strong>As of Date:</strong> {{ now()->format('M d, Y') }}
            </div>
        </div>
    </div>
@endsection

@section('content')
    @if($finishedGoods->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 16%;">Product / Model</th>
                    <th style="width: 12%;">Customer</th>
                    <th style="width: 8%;">Begin Count</th>
                    <th style="width: 8%;">End Count</th>
                    <th style="width: 7%;">UOM</th>
                    <th style="width: 8%;">Variance Qty</th>
                    <th style="width: 13%;">Variance Amount</th>
                    <th style="width: 13%;">End Amount</th>
                    <th style="width: 7%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($finishedGoods as $item)
                    <tr>
                        <td class="font-bold">{{ $item->product->model_name ?? $item->product->product_code ?? '—' }}</td>
                        <td class="text-sm">{{ $item->product->customer ?? '—' }}</td>
                        <td class="text-right">{{ number_format($item->qty_beginning) }}</td>
                        <td class="text-right font-bold @if($item->qty_actual_ending < ($item->product->min_stock ?? 0)) text-red @endif">
                            {{ number_format($item->qty_actual_ending) }}
                        </td>
                        <td class="text-center text-sm">{{ $item->product->uom ?? 'pcs' }}</td>
                        <td class="text-right @if($item->qty_variance != 0) style="color: #ea580c; font-weight: 700;" @endif">
                            {{ number_format($item->qty_variance) }}
                        </td>
                        <td class="text-right @if($item->amount_variance != 0) style="color: #ea580c; font-weight: 700;" @endif">
                            {{ currencySymbol($item->product->currency ?? 'PHP') }}{{ number_format($item->amount_variance, 2) }}
                        </td>
                        <td class="text-right font-bold">{{ currencySymbol($item->product->currency ?? 'PHP') }}{{ number_format($item->amount_ending, 2) }}</td>
                        <td class="text-center">
                            @if($item->qty_actual_ending < ($item->product->min_stock ?? 0))
                                <span class="badge badge-delayed">Low</span>
                            @elseif($item->qty_variance != 0)
                                <span class="badge badge-pending">Diff</span>
                            @else
                                <span class="badge badge-completed">OK</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div style="text-align: center; padding: 30px; color: #999; font-size: 11pt; background: #f9fafb; border-radius: 4px;">
            No inventory items found for the selected criteria.
        </div>
    @endif
@endsection

@section('summary_footer')
    @if($finishedGoods->count() > 0)
        <div class="summary-footer">
            <div class="totals-grid">
                <div class="total-box">
                    <div class="total-label">Total Stock Items</div>
                    <div class="total-value">{{ $finishedGoods->count() }}</div>
                </div>
                <div class="total-box">
                    <div class="total-label">Total Stock Units</div>
                    <div class="total-value">{{ number_format($totalStock) }}</div>
                </div>
                <div class="total-box">
                    <div class="total-label">Total Inventory Value</div>
                    <div class="total-value">
                        @if(isset($reportCurrency))
                            {{ currencySymbol($reportCurrency) }}{{ number_format($totalValue, 2) }}
                        @else
                            {{ number_format($totalValue, 2) }}
                        @endif
                    </div>
                </div>
                <div class="total-box @if($totalVariance != 0) style="border-left-color: #ea580c; background: #fffbeb;" @endif">
                    <div class="total-label @if($totalVariance != 0) style="color: #ea580c;" @endif">Total Variance Qty</div>
                    <div class="total-value @if($totalVariance != 0) style="color: #ea580c;" @endif">{{ number_format($totalVariance) }}</div>
                </div>
            </div>
        </div>
    @endif
@endsection
