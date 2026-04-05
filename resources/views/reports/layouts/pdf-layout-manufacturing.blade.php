<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('report_title') - {{ now()->format('Y-m-d') }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 10mm 12mm 10mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            font-size: 9pt;
            color: #000;
            line-height: 1.3;
        }
        
        /* ===== HEADER ===== */
        .report-header {
            border: 2px solid #000;
            padding: 8px 10px;
            margin-bottom: 10px;
            background: #fff;
        }
        
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .company-section {
            flex: 1;
        }
        
        .company-name {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .company-details {
            font-size: 7.5pt;
            line-height: 1.4;
        }
        
        .report-section {
            flex: 1;
            text-align: center;
        }
        
        .report-title {
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        
        .report-subtitle {
            font-size: 8.5pt;
            color: #333;
        }
        
        .meta-section {
            flex: 1;
            text-align: right;
        }
        
        .meta-item {
            font-size: 8pt;
            margin-bottom: 2px;
        }
        
        .meta-label {
            font-weight: bold;
        }
        
        /* ===== REPORT PARAMETERS ===== */
        .report-parameters {
            background: #f0f0f0;
            border: 1px solid #666;
            padding: 6px 8px;
            margin-bottom: 8px;
        }
        
        .param-title {
            font-size: 8.5pt;
            font-weight: bold;
            margin-bottom: 3px;
        }
        
        .param-table {
            width: 100%;
            font-size: 8pt;
        }
        
        .param-table td {
            padding: 2px 5px;
        }
        
        /* ===== SUMMARY SECTION ===== */
        .summary-section {
            margin-bottom: 10px;
        }
        
        .summary-title {
            background: #000;
            color: #fff;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 5px;
        }
        
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .summary-box {
            border: 1px solid #333;
            padding: 8px;
            text-align: center;
            background: #f9f9f9;
        }
        
        .summary-label {
            font-size: 7.5pt;
            font-weight: bold;
            color: #555;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        
        .summary-value {
            font-size: 12pt;
            font-weight: bold;
            color: #000;
        }
        
        /* ===== DATA SECTION ===== */
        .data-section {
            margin-bottom: 10px;
        }
        
        .data-title {
            background: #000;
            color: #fff;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 5px;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        
        .data-table thead th {
            background: #333;
            color: #fff;
            padding: 5px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #000;
            font-size: 8pt;
        }
        
        .data-table tbody td {
            padding: 4px;
            border: 1px solid #999;
            vertical-align: middle;
        }
        
        .data-table tbody tr:nth-child(even) {
            background: #f5f5f5;
        }
        
        .data-table tfoot td {
            background: #e0e0e0;
            padding: 5px 4px;
            border: 1px solid #000;
            font-weight: bold;
        }
        
        /* ===== STATUS BADGES ===== */
        .status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 2px;
            font-weight: bold;
            font-size: 7pt;
            text-align: center;
            white-space: nowrap;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffc107;
        }
        
        .status-approved {
            background: #cfe2ff;
            color: #084298;
            border: 1px solid #0d6efd;
        }
        
        .status-in-progress {
            background: #e7d4ff;
            color: #5a1a9b;
            border: 1px solid #9b59b6;
        }
        
        .status-completed {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #28a745;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #842029;
            border: 1px solid #dc3545;
        }
        
        .status-delivered {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #28a745;
        }
        
        .status-backlog {
            background: #f8d7da;
            color: #842029;
            border: 1px solid #dc3545;
        }
        
        .status-scheduled {
            background: #cfe2ff;
            color: #084298;
            border: 1px solid #0d6efd;
        }
        
        /* ===== BREAKDOWN SECTION ===== */
        .breakdown-section {
            margin-top: 10px;
            page-break-inside: avoid;
        }
        
        .breakdown-title {
            background: #666;
            color: #fff;
            padding: 4px 8px;
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 5px;
        }
        
        .breakdown-table {
            width: 50%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        
        .breakdown-table th {
            background: #d0d0d0;
            padding: 4px 6px;
            border: 1px solid #666;
            font-weight: bold;
            text-align: left;
        }
        
        .breakdown-table td {
            padding: 4px 6px;
            border: 1px solid #999;
        }
        
        .breakdown-table tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        /* ===== FOOTER ===== */
        .report-footer {
            margin-top: 15px;
            page-break-inside: avoid;
        }
        
        .footer-notes {
            font-size: 7.5pt;
            padding: 6px 8px;
            background: #f9f9f9;
            border: 1px solid #ccc;
            margin-bottom: 8px;
        }
        
        .footer-notes strong {
            display: block;
            margin-bottom: 3px;
            font-size: 8pt;
        }
        
        .footer-notes ul {
            margin-left: 15px;
        }
        
        .footer-notes li {
            margin-bottom: 2px;
        }
        
        .footer-signature {
            margin-top: 10px;
        }
        
        .signature-block {
            text-align: center;
            padding: 5px;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            margin-bottom: 3px;
            min-height: 40px;
        }
        
        .signature-label {
            font-weight: bold;
            font-size: 8.5pt;
        }
        
        .signature-date {
            font-size: 7.5pt;
            color: #555;
        }
        
        /* ===== UTILITIES ===== */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .text-sm { font-size: 8pt; }
        .text-xs { font-size: 7pt; }
        
        .no-data {
            text-align: center;
            padding: 30px;
            background: #f5f5f5;
            border: 1px dashed #999;
            color: #666;
            font-size: 9pt;
        }
        
        /* ===== PAGE BREAKS ===== */
        .page-break { page-break-after: always; }
        tr { page-break-inside: avoid; }
        
        /* ===== DOCUMENT FOOTER ===== */
        .document-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7pt;
            color: #666;
            padding: 5px 0;
            border-top: 1px solid #ccc;
        }
    </style>
</head>
<body>
    {{-- HEADER --}}
    <div class="report-header">
        <div class="header-row">
            <div class="company-section">
                <div class="company-name">CPC NEXBOARD</div>
                <div class="company-details">
                    Manufacturing Coordination System<br>
                    {{ config('app.company_address', 'Carmelray Industrial Park II, Calamba City, Laguna, Philippines') }}<br>
                    Tel: {{ config('app.company_phone', '+639693148752') }} | Email: {{ config('app.company_email', 'darkandrei@gmail.com') }}
                </div>
            </div>
            <div class="report-section">
                <div class="report-title">@yield('report_title')</div>
                <div class="report-subtitle">@yield('report_period')</div>
            </div>
            <div class="meta-section">
                <div class="meta-item"><span class="meta-label">Report Code:</span> @yield('report_code')</div>
                <div class="meta-item"><span class="meta-label">Generated:</span> {{ now()->format('m/d/Y h:i A') }}</div>
                <div class="meta-item"><span class="meta-label">Prepared By:</span> {{ Auth::user()->name ?? 'System' }}</div>
                <div class="meta-item"><span class="meta-label">Page:</span> 1 of 1</div>
            </div>
        </div>
    </div>
    
    {{-- MAIN CONTENT --}}
    @yield('content')
    
    {{-- DOCUMENT FOOTER --}}
    <div style="margin-top: 10px; text-align: center; font-size: 7pt; color: #666; border-top: 1px solid #ccc; padding-top: 5px;">
        <strong>CONFIDENTIAL - FOR INTERNAL USE ONLY</strong> | This document is system-generated and valid without signature unless otherwise stated.
    </div>
</body>
</html>