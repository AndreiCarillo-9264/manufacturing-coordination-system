@extends('reports.layouts.pdf-layout-delivery')

@section('document_title', 'DELIVERY SCHEDULE / DRIVER ROUTE SHEET')

@php
    // Support both a single `$delivery` or a collection/array `$deliveries` passed from the controller.
    if (!isset($delivery) && isset($deliveries)) {
        if (is_countable($deliveries) && count($deliveries) > 0) {
            // If it's a Collection, use first(); otherwise fall back to index 0
            $delivery = is_object($deliveries) && method_exists($deliveries, 'first') ? $deliveries->first() : $deliveries[0];
        } else {
            $delivery = null;
        }
    }
@endphp

@section('document_number', $delivery->ds_code ?? 'DS-' . now()->format('Ymd-His'))
@section('document_date', isset($delivery) && $delivery->delivery_date ? $delivery->delivery_date->format('F d, Y') : now()->format('F d, Y'))

@section('content')

{{-- SECTION 1: DELIVERY INFORMATION --}}
<div class="info-section">
    <div class="section-header">DELIVERY INFORMATION</div>
    <table class="info-table">
        <tr>
            <td class="label">Delivery Schedule No.:</td>
            <td class="value">{{ $delivery->ds_code ?? 'N/A' }}</td>
            <td class="label">Job Order No.:</td>
            <td class="value">{{ $delivery->jobOrder?->jo_number ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Delivery Date:</td>
            <td class="value">{{ $delivery->delivery_date?->format('F d, Y') ?? 'N/A' }}</td>
            <td class="label">Scheduled Time:</td>
            <td class="value">{{ $delivery->scheduled_time ?? '08:00 AM' }}</td>
        </tr>
        <tr>
            <td class="label">Status:</td>
            <td class="value"><strong>{{ strtoupper($delivery->ds_status) }}</strong></td>
            <td class="label">Priority:</td>
            <td class="value">{{ $delivery->priority ?? 'NORMAL' }}</td>
        </tr>
    </table>
</div>

{{-- SECTION 2: CUSTOMER & DELIVERY ADDRESS --}}
<div class="info-section">
    <div class="section-header">CUSTOMER & DELIVERY ADDRESS</div>
    <table class="info-table">
        <tr>
            <td class="label" style="width: 20%;">Customer Name:</td>
            <td class="value" colspan="3"><strong>{{ $delivery->customer_name ?? $delivery->product->customer ?? 'N/A' }}</strong></td>
        </tr>
        <tr>
            <td class="label">Delivery Address:</td>
            <td class="value" colspan="3">{{ $delivery->delivery_address ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Contact Person:</td>
            <td class="value">{{ $delivery->contact_person ?? 'N/A' }}</td>
            <td class="label">Contact Number:</td>
            <td class="value">{{ $delivery->contact_number ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Special Instructions:</td>
            <td class="value" colspan="3">{{ $delivery->special_instructions ?? 'None' }}</td>
        </tr>
    </table>
</div>

{{-- SECTION 3: PRODUCT DETAILS --}}
<div class="info-section">
    <div class="section-header">PRODUCT DETAILS</div>
    <table class="product-table">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">Product Code / Model</th>
                <th style="width: 30%;">Description</th>
                <th style="width: 12%;">Quantity</th>
                <th style="width: 8%;">UOM</th>
                <th style="width: 20%;">Packaging Type</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td><strong>{{ $delivery->product->product_code ?? 'N/A' }}</strong></td>
                <td>{{ $delivery->product->model_name ?? $delivery->product->description ?? 'N/A' }}</td>
                <td class="text-right"><strong>{{ number_format($delivery->quantity) }}</strong></td>
                <td class="text-center">{{ $delivery->uom ?? 'PCS' }}</td>
                <td>{{ $delivery->packaging_type ?? 'Standard Box' }}</td>
            </tr>
        </tbody>
    </table>
    
    <table class="info-table" style="margin-top: 10px;">
        <tr>
            <td class="label" style="width: 20%;">Batch/Lot Number:</td>
            <td class="value" style="width: 30%;">{{ $delivery->batch_number ?? 'N/A' }}</td>
            <td class="label" style="width: 20%;">Total Weight:</td>
            <td class="value" style="width: 30%;">{{ $delivery->total_weight ?? 'N/A' }} kg</td>
        </tr>
        <tr>
            <td class="label">Number of Packages:</td>
            <td class="value">{{ $delivery->package_count ?? ceil($delivery->quantity / 100) }}</td>
            <td class="label">Total Volume:</td>
            <td class="value">{{ $delivery->total_volume ?? 'N/A' }} m³</td>
        </tr>
    </table>
</div>

{{-- SECTION 4: VEHICLE & DRIVER ASSIGNMENT --}}
<div class="info-section">
    <div class="section-header">VEHICLE & DRIVER ASSIGNMENT</div>
    <table class="info-table">
        <tr>
            <td class="label" style="width: 20%;">Driver Name:</td>
            <td class="value" style="width: 30%;">{{ $delivery->driver_name ?? '___________________________' }}</td>
            <td class="label" style="width: 20%;">Driver Contact:</td>
            <td class="value" style="width: 30%;">{{ $delivery->driver_contact ?? '___________________________' }}</td>
        </tr>
        <tr>
            <td class="label">Vehicle Type:</td>
            <td class="value">{{ $delivery->vehicle_type ?? '___________________________' }}</td>
            <td class="label">Plate Number:</td>
            <td class="value">{{ $delivery->plate_number ?? '___________________________' }}</td>
        </tr>
        <tr>
            <td class="label">Departure Time:</td>
            <td class="value">_______________ AM/PM</td>
            <td class="label">Arrival Time:</td>
            <td class="value">_______________ AM/PM</td>
        </tr>
    </table>
</div>

{{-- SECTION 5: LOADING VERIFICATION --}}
<div class="info-section">
    <div class="section-header">LOADING VERIFICATION</div>
    <table class="verification-table">
        <thead>
            <tr>
                <th style="width: 50%;">Checkpoint</th>
                <th style="width: 15%;">Status</th>
                <th style="width: 35%;">Checked By / Time</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Products loaded and secured properly</td>
                <td class="checkbox">☐ Yes ☐ No</td>
                <td>_______________________</td>
            </tr>
            <tr>
                <td>Quantity verified against delivery schedule</td>
                <td class="checkbox">☐ Yes ☐ No</td>
                <td>_______________________</td>
            </tr>
            <tr>
                <td>Packaging condition checked (no damage)</td>
                <td class="checkbox">☐ Yes ☐ No</td>
                <td>_______________________</td>
            </tr>
            <tr>
                <td>Documentation complete (DO, Invoice, etc.)</td>
                <td class="checkbox">☐ Yes ☐ No</td>
                <td>_______________________</td>
            </tr>
        </tbody>
    </table>
</div>

{{-- SECTION 6: DELIVERY CONFIRMATION --}}
<div class="info-section">
    <div class="section-header">DELIVERY CONFIRMATION</div>
    <table class="info-table">
        <tr>
            <td class="label" style="width: 25%;">Actual Delivery Date & Time:</td>
            <td class="value" style="width: 75%;">_____________________________________________</td>
        </tr>
        <tr>
            <td class="label">Received Quantity:</td>
            <td class="value">_____________________________________________</td>
        </tr>
        <tr>
            <td class="label">Condition Upon Delivery:</td>
            <td class="value">☐ Good Condition  ☐ With Damage (Specify): _______________________</td>
        </tr>
        <tr>
            <td class="label" style="vertical-align: top;">Remarks/Notes:</td>
            <td class="value">
                _______________________________________________________________<br>
                _______________________________________________________________<br>
                _______________________________________________________________
            </td>
        </tr>
    </table>
</div>

{{-- SECTION 7: SIGNATURES --}}
<div class="signatures-section">
    <table class="signatures-table">
        <tr>
            <td style="width: 33%;">
                <div class="signature-block">
                    <div class="signature-line">_________________________</div>
                    <div class="signature-label">Prepared By</div>
                    <div class="signature-sublabel">Warehouse Staff</div>
                    <div class="signature-sublabel">Date: {{ now()->format('M d, Y') }}</div>
                </div>
            </td>
            <td style="width: 34%;">
                <div class="signature-block">
                    <div class="signature-line">_________________________</div>
                    <div class="signature-label">Driver's Signature</div>
                    <div class="signature-sublabel">{{ $delivery->driver_name ?? 'Driver Name' }}</div>
                    <div class="signature-sublabel">Date: ______________</div>
                </div>
            </td>
            <td style="width: 33%;">
                <div class="signature-block">
                    <div class="signature-line">_________________________</div>
                    <div class="signature-label">Received By</div>
                    <div class="signature-sublabel">Customer Representative</div>
                    <div class="signature-sublabel">Date: ______________</div>
                </div>
            </td>
        </tr>
    </table>
</div>

{{-- FOOTER NOTES --}}
<div class="footer-notes">
    <strong>IMPORTANT NOTES:</strong>
    <ul>
        <li>Driver must ensure proper vehicle inspection before departure</li>
        <li>Contact warehouse immediately if any discrepancy is found during loading</li>
        <li>Customer signature is required upon successful delivery</li>
        <li>Report any delivery issues to logistics coordinator immediately</li>
        <li>Return this document to warehouse with customer signature within 24 hours</li>
    </ul>
</div>

@endsection