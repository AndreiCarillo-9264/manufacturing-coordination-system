<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report - {{ now()->format('Y-m-d') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 11pt;
            color: #333;
            line-height: 1.4;
        }

        .container {
            padding: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #059669;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 24pt;
            color: #059669;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 10pt;
            color: #666;
            margin: 3px 0;
        }

        .filter-info {
            background-color: #f0fdf4;
            border-left: 4px solid #059669;
            padding: 10px 12px;
            margin-bottom: 20px;
            font-size: 10pt;
            line-height: 1.6;
        }

        .filter-info label {
            font-weight: bold;
            color: #059669;
            width: 100px;
            display: inline-block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        thead {
            background-color: #059669;
            color: white;
            font-weight: bold;
        }

        th {
            padding: 10px;
            text-align: left;
            font-size: 10pt;
            border: 1px solid #059669;
        }

        td {
            padding: 9px 10px;
            border: 1px solid #ddd;
            font-size: 10pt;
        }

        tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        tbody tr:hover {
            background-color: #f0fdf4;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-red {
            color: #dc2626;
        }

        .text-orange {
            color: #ea580c;
        }

        .totals-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #059669;
        }

        .total-item {
            text-align: right;
        }

        .total-item label {
            font-weight: bold;
            color: #059669;
            display: block;
            margin-bottom: 3px;
            font-size: 10pt;
        }

        .total-item value {
            font-size: 14pt;
            font-weight: bold;
            color: #059669;
            display: block;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 9pt;
            color: #666;
        }

        .no-data {
            text-align: center;
            padding: 40px;
            color: #999;
            font-size: 11pt;
        }

        .highlight-low {
            background-color: #fee2e2;
        }

        .highlight-variance {
            background-color: #fef3c7;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>INVENTORY REPORT</h1>
            <p>Generated on {{ now()->format('F d, Y \a\t H:i A') }}</p>
        </div>

        <!-- Filter Information -->
        @if(isset($filters) && ($filters['customer'] !== 'All' || $filters['type'] !== 'All Items'))
        <div class="filter-info">
            <div><label>Customer:</label> <span>{{ $filters['customer'] ?? 'All' }}</span></div>
            <div><label>Type:</label> <span>{{ $filters['type'] ?? 'All Items' }}</span></div>
        </div>
        @endif

        <!-- Data Table -->
        @if($finishedGoods->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 18%;">Product / Model</th>
                    <th style="width: 14%;">Customer</th>
                    <th style="width: 12%; text-align: right;">Beginning Count</th>
                    <th style="width: 12%; text-align: right;">Ending Count</th>
                    <th style="width: 12%; text-align: right;">Variance Qty</th>
                    <th style="width: 14%; text-align: right;">Variance Amount</th>
                    <th style="width: 14%; text-align: right;">End Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($finishedGoods as $item)
                <tr class="
                    @if($item->qty_actual_ending < ($item->product->min_stock ?? 0))
                        highlight-low
                    @elseif($item->qty_variance != 0)
                        highlight-variance
                    @endif
                ">
                    <td>{{ $item->product->model_name ?? $item->product->product_code ?? '—' }}</td>
                    <td>{{ $item->product->customer ?? '—' }}</td>
                    <td class="text-right">{{ number_format($item->qty_beginning) }}</td>
                    <td class="text-right @if($item->qty_actual_ending < ($item->product->min_stock ?? 0)) text-red @endif">
                        <strong>{{ number_format($item->qty_actual_ending) }}</strong>
                        @if($item->qty_actual_ending < ($item->product->min_stock ?? 0)) ⚠ @endif
                    </td>
                    <td class="text-right @if($item->qty_variance != 0) text-orange @endif">
                        {{ number_format($item->qty_variance) }}
                    </td>
                    <td class="text-right @if($item->amount_variance != 0) text-orange @endif">
                        ₱{{ number_format($item->amount_variance, 2) }}
                    </td>
                    <td class="text-right">₱{{ number_format($item->amount_ending, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Section -->
        <div class="totals-grid">
            <div class="total-item">
                <label>Total Stock:</label>
                <value>{{ number_format($totalStock) }} units</value>
            </div>
            <div class="total-item">
                <label>Total Value:</label>
                <value>₱{{ number_format($totalValue, 2) }}</value>
            </div>
            <div class="total-item">
                <label>Total Variance Qty:</label>
                <value @if($totalVariance != 0) class="text-orange" @endif>{{ number_format($totalVariance) }}</value>
            </div>
            <div class="total-item">
                <label>Total Variance Amount:</label>
                <value @if($totalVarianceAmount != 0) class="text-orange" @endif>₱{{ number_format($totalVarianceAmount, 2) }}</value>
            </div>
        </div>

        @else
        <div class="no-data">
            No inventory items found matching the selected criteria.
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>This is a computer-generated report. No signature is required.</p>
            <p style="margin-top: 5px;">{{ config('app.name') }} - Thesis System</p>
        </div>
    </div>

</body>
</html>
