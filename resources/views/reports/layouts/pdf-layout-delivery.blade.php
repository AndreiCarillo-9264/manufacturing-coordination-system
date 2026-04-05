<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>@yield('document_title') - @yield('document_number')</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 10pt;
            color: #000;
            line-height: 1.4;
        }
        
        /* HEADER */
        .document-header {
            border: 2px solid #000;
            padding: 10px;
            margin-bottom: 15px;
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid #000;
        }
        
        .company-info {
            flex: 1;
        }
        
        .company-name {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 2px;
        }
        
        .company-address {
            font-size: 8pt;
            line-height: 1.3;
        }
        
        .document-info {
            text-align: right;
            flex: 1;
        }
        
        .document-title {
            font-size: 14pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .document-meta {
            font-size: 9pt;
        }
        
        .document-meta strong {
            font-weight: bold;
        }
        
        /* SECTIONS */
        .info-section {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        
        .section-header {
            background: #000;
            color: #fff;
            padding: 5px 8px;
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 5px;
        }
        
        /* TABLES */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 0;
        }
        
        .info-table td {
            padding: 4px 6px;
            border: 1px solid #333;
        }
        
        .info-table .label {
            background: #e8e8e8;
            font-weight: bold;
            width: 25%;
        }
        
        .info-table .value {
            background: #fff;
        }
        
        .product-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        
        .product-table th {
            background: #000;
            color: #fff;
            padding: 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #000;
        }
        
        .product-table td {
            padding: 5px 6px;
            border: 1px solid #333;
        }
        
        .verification-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }
        
        .verification-table th {
            background: #d4d4d4;
            padding: 5px 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #333;
        }
        
        .verification-table td {
            padding: 5px 6px;
            border: 1px solid #333;
        }
        
        .verification-table .checkbox {
            text-align: center;
            font-size: 8pt;
        }
        
        /* SIGNATURES */
        .signatures-section {
            margin-top: 20px;
            page-break-inside: avoid;
        }
        
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .signatures-table td {
            vertical-align: top;
            padding: 10px 5px;
        }
        
        .signature-block {
            text-align: center;
        }
        
        .signature-line {
            border-bottom: 1px solid #000;
            margin-bottom: 5px;
            min-height: 50px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }
        
        .signature-label {
            font-weight: bold;
            font-size: 9pt;
            margin-bottom: 2px;
        }
        
        .signature-sublabel {
            font-size: 8pt;
            color: #444;
        }
        
        /* FOOTER NOTES */
        .footer-notes {
            margin-top: 15px;
            padding: 8px;
            background: #f5f5f5;
            border: 1px solid #333;
            font-size: 8pt;
            page-break-inside: avoid;
        }
        
        .footer-notes strong {
            display: block;
            margin-bottom: 5px;
            font-size: 9pt;
        }
        
        .footer-notes ul {
            margin-left: 15px;
            margin-top: 3px;
        }
        
        .footer-notes li {
            margin-bottom: 2px;
        }
        
        /* UTILITIES */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        strong { font-weight: bold; }
    </style>
</head>
<body>
    {{-- HEADER --}}
    <div class="document-header">
        <div class="header-top">
            <div class="company-info">
                <div class="company-name">CPC NEXBOARD</div>
                <div class="company-address">
                    Manufacturing Coordination System<br>
                    {{ config('app.company_address', 'Company Address Here') }}<br>
                    Tel: {{ config('app.company_phone', 'Phone Number') }}
                </div>
            </div>
            <div class="document-info">
                <div class="document-title">@yield('document_title')</div>
                <div class="document-meta">
                    <strong>Document No.:</strong> @yield('document_number')<br>
                    <strong>Date:</strong> @yield('document_date')<br>
                    <strong>Page:</strong> 1 of 1
                </div>
            </div>
        </div>
    </div>
    
    {{-- MAIN CONTENT --}}
    @yield('content')
    
    {{-- DOCUMENT FOOTER --}}
    <div style="margin-top: 10px; text-align: center; font-size: 7pt; color: #666;">
        This is a system-generated document. For inquiries, contact the Logistics Department.
    </div>
</body>
</html>