<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Job Orders Report - {{ now()->format('Y-m-d') }}</title>
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
            border-bottom: 3px solid #1e40af;
            padding-bottom: 15px;
        }

        .header h1 {
            font-size: 24pt;
            color: #1e40af;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 10pt;
            color: #666;
            margin: 3px 0;
        }

        .filter-info {
            background-color: #f3f4f6;
            border-left: 4px solid #1e40af;
            padding: 10px 12px;
            margin-bottom: 20px;
            font-size: 10pt;
            line-height: 1.6;
        }

        .filter-info label {
            font-weight: bold;
            color: #1e40af;
            width: 100px;
            display: inline-block;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        thead {
            background-color: #1e40af;
            color: white;
            font-weight: bold;
        }

        th {
            padding: 10px;
            text-align: left;
            font-size: 10pt;
            border: 1px solid #1e40af;
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
            background-color: #f0f4ff;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
            padding: 3px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9pt;
            display: inline-block;
        }

        .status-approved {
            background-color: #dbeafe;
            color: #1e40af;
            padding: 3px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9pt;
            display: inline-block;
        }

        .status-in_progress {
            background-color: #ede9fe;
            color: #6b21a8;
            padding: 3px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9pt;
            display: inline-block;
        }

        .status-completed {
            background-color: #dcfce7;
            color: #15803d;
            padding: 3px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9pt;
            display: inline-block;
        }

        .status-cancelled {
            background-color: #fee2e2;
            color: #991b1b;
            padding: 3px 6px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 9pt;
            display: inline-block;
        }

        .totals {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #1e40af;
            display: flex;
            justify-content: flex-end;
            gap: 40px;
        }

        .total-item {
            text-align: right;
        }

        .total-item label {
            font-weight: bold;
            color: #1e40af;
            display: block;
            margin-bottom: 3px;
            font-size: 10pt;
        }

        .total-item value {
            font-size: 14pt;
            font-weight: bold;
            color: #1e40af;
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
    </style>
</head>
<body>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>JOB ORDERS REPORT</h1>
            <p>Generated on {{ now()->format('F d, Y \a\t H:i A') }}</p>
        </div>

        <!-- Filter Information -->
        @if(isset($filters) && ($filters['customer'] !== 'All' || $filters['status'] !== 'All' || $filters['date_from'] !== 'Start' || $filters['date_to'] !== 'End'))
        <div class="filter-info">
            <div><label>Customer:</label> <span>{{ $filters['customer'] ?? 'All' }}</span></div>
            <div><label>Status:</label> <span>{{ ucfirst($filters['status'] ?? 'All') }}</span></div>
            <div><label>Period:</label> <span>{{ $filters['date_from'] ?? 'Start' }} to {{ $filters['date_to'] ?? 'End' }}</span></div>
        </div>
        @endif

        <!-- Data Table -->
        @if($jobOrders->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">JO Number</th>
                    <th style="width: 10%;">PO Number</th>
                    <th style="width: 18%;">Product / Model</th>
                    <th style="width: 15%;">Customer</th>
                    <th style="width: 8%; text-align: right;">Qty</th>
                    <th style="width: 6%;">UoM</th>
                    <th style="width: 12%;">Date Needed</th>
                    <th style="width: 12%;">Status</th>
                    <th style="width: 12%; text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jobOrders as $jo)
                <tr>
                    <td>{{ $jo->jo_number }}</td>
                    <td>{{ $jo->po_number ?? '—' }}</td>
                    <td>{{ $jo->product->model_name ?? $jo->product->product_code ?? '—' }}</td>
                    <td>{{ $jo->product->customer ?? '—' }}</td>
                    <td class="text-right">{{ number_format($jo->qty) }}</td>
                    <td>{{ $jo->uom }}</td>
                    <td>{{ $jo->date_needed?->format('M d, Y') ?? '—' }}</td>
                    <td class="text-center">
                        <span class="status-{{ $jo->status }}">{{ ucfirst(str_replace('_', ' ', $jo->status)) }}</span>
                    </td>
                    <td class="text-right">₱{{ number_format($jo->qty * $jo->product->selling_price, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Section -->
        <div class="totals">
            <div class="total-item">
                <label>Total Quantity:</label>
                <value>{{ number_format($totalQty) }} units</value>
            </div>
            <div class="total-item">
                <label>Total Amount:</label>
                <value>₱{{ number_format($totalAmount, 2) }}</value>
            </div>
        </div>

        @else
        <div class="no-data">
            No job orders found matching the selected criteria.
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