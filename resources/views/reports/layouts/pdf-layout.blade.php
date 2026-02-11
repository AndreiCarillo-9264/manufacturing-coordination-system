<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('report_title') - {{ now()->format('Y-m-d') }}</title>
    <style>
        @page { 
            size: A4 landscape; 
            margin: 8mm 8mm;
            @bottom-center {
                content: "Page " counter(page) " of " counter(pages);
                font-size: 8pt;
                color: #999;
            }
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', 'Helvetica Neue', -apple-system, sans-serif;
            font-size: 10pt;
            color: #1f2937;
            line-height: 1.6;
            background: #fff;
        }
        .container {
            width: 100%;
            max-width: 285mm;
            margin: 0 auto;
        }
        
        /* =========================== HEADER SECTION =========================== */
        .pdf-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 3px solid #1e40af;
        }
        .company-info {
            flex: 1;
        }
        .company-name {
            font-size: 16pt;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 3px;
        }
        .company-tagline {
            font-size: 9pt;
            color: #666;
        }
        .report-title-section {
            text-align: center;
            flex: 1;
        }
        .report-title {
            font-size: 20pt;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 4px;
        }
        .report-period {
            font-size: 10pt;
            color: #666;
        }
        .report-meta {
            text-align: right;
            flex: 1;
        }
        .meta-item {
            font-size: 9pt;
            margin-bottom: 3px;
            color: #555;
        }
        .meta-label {
            font-weight: 600;
            color: #1f2937;
        }
        
        /* =========================== EXECUTIVE SUMMARY =========================== */
        .executive-summary {
            background: linear-gradient(135deg, #f0f9ff 0%, #e0e7ff 100%);
            border-left: 4px solid #1e40af;
            padding: 12px 14px;
            margin-bottom: 16px;
            border-radius: 4px;
        }
        .summary-title {
            font-size: 11pt;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .summary-metrics {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        .metric-item {
            flex: 1;
            min-width: 120px;
        }
        .metric-label {
            font-size: 8.5pt;
            color: #666;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.3px;
            margin-bottom: 3px;
        }
        .metric-value {
            font-size: 14pt;
            font-weight: 700;
            color: #1e40af;
        }
        
        /* =========================== FILTERS INFO =========================== */
        .filters-section {
            background: #fafafa;
            border: 1px solid #e5e7eb;
            padding: 10px 12px;
            margin-bottom: 14px;
            border-radius: 4px;
            font-size: 9pt;
        }
        .filters-title {
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 4px;
            text-transform: uppercase;
            font-size: 8.5pt;
        }
        .filter-badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .filter-badge {
            background: #fff;
            border: 1px solid #d1d5db;
            padding: 2px 6px;
            border-radius: 12px;
            font-size: 8.5pt;
        }
        .filter-badge strong {
            color: #1e40af;
            font-weight: 600;
        }
        
        /* =========================== DATA TABLE =========================== */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 9pt;
        }
        thead {
            display: table-header-group;
        }
        thead th {
            background: linear-gradient(180deg, #1e40af 0%, #1e3a8a 100%);
            color: #fff;
            padding: 8px 10px;
            text-align: left;
            font-weight: 700;
            font-size: 9pt;
            border: 1px solid #1e3a8a;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        tbody td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        tbody tr {
            page-break-inside: avoid;
        }
        tbody tr:nth-child(even) {
            background: #f9fafb;
        }
        tbody tr:hover {
            background: #f3f4f6;
        }
        
        /* =========================== STATUS BADGES =========================== */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: 600;
            font-size: 8pt;
            text-align: center;
        }
        .badge-approved {
            background: #fef08a;
            color: #854d0e;
            border: 1px solid #fde047;
        }
        .badge-in-progress {
            background: #bfdbfe;
            color: #1e3a8a;
            border: 1px solid #93c5fd;
        }
        .badge-pending {
            background: #fed7aa;
            color: #9a3412;
            border: 1px solid #fed7aa;
        }
        .badge-completed {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .badge-delayed {
            background: #fecaca;
            color: #7f1d1d;
            border: 1px solid #fca5a5;
        }
        
        /* =========================== SUMMARY SECTION =========================== */
        .summary-footer {
            margin-top: 20px;
            padding-top: 14px;
            border-top: 2px solid #1e40af;
        }
        .totals-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 16px;
            margin-bottom: 14px;
        }
        .total-box {
            background: #f0f9ff;
            border: 1px solid #bfdbfe;
            border-left: 3px solid #1e40af;
            padding: 10px 12px;
            border-radius: 3px;
        }
        .total-label {
            font-size: 8pt;
            font-weight: 700;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 4px;
            letter-spacing: 0.3px;
        }
        .total-value {
            font-size: 14pt;
            font-weight: 700;
            color: #1e40af;
        }
        
        /* =========================== FOOTER =========================== */
        .pdf-footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 8pt;
            color: #999;
            text-align: center;
        }
        .footer-note {
            margin-bottom: 6px;
            font-size: 8pt;
        }
        
        /* =========================== UTILITIES =========================== */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: 700; }
        .text-sm { font-size: 8.5pt; }
        .text-xs { font-size: 7.5pt; }
        .mt-1 { margin-top: 4px; }
        .mb-1 { margin-bottom: 4px; }
        
        /* =========================== BREAK HANDLING =========================== */
        .page-break { page-break-after: always; }
        tr, td { page-break-inside: avoid; }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="pdf-header">
            <div class="company-info">
                <div class="company-name">CPC Nexboard</div>
                <div class="company-tagline">Manufacturing Coordination System</div>
            </div>
            <div class="report-title-section">
                <div class="report-title">@yield('report_title')</div>
                <div class="report-period">@yield('report_period')</div>
            </div>
            <div class="report-meta">
                <div class="meta-item"><span class="meta-label">Generated:</span> {{ now()->format('M d, Y H:i A') }}</div>
                <div class="meta-item"><span class="meta-label">User:</span> {{ Auth::user()->name ?? 'System' }}</div>
                <div class="meta-item"><span class="meta-label">Report ID:</span> {{ 'RPT-' . now()->format('YmdHis') }}</div>
            </div>
        </div>
        
        {{-- Executive Summary --}}
        @yield('executive_summary')
        
        {{-- Filters Applied --}}
        @yield('filters_section')
        
        {{-- Main Content --}}
        @yield('content')
        
        {{-- Summary Footer --}}
        @yield('summary_footer')
        
        {{-- Footer --}}
        <div class="pdf-footer">
            <div class="footer-note">This is an automatically generated report from CPC Nexboard. For discrepancies or questions, please contact the relevant department.</div>
            <div class="footer-note">Confidential - For Internal Use Only</div>
        </div>
    </div>
</body>
</html>
