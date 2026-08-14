<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Job Order {{ $jobOrder->job_order_number }} — Print</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 13px; color: #1a1a1a; margin: 0; background: #f1f5f9; }
        .sheet { max-width: 820px; margin: 24px auto; background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        .toolbar { text-align: right; margin-bottom: 16px; }
        .toolbar button { background: #e11d48; color: #fff; border: 0; padding: 10px 22px; border-radius: 6px; font-size: 14px; font-weight: bold; cursor: pointer; }
        .toolbar button:hover { background: #be123c; }
        .header { text-align: center; border-bottom: 3px solid #e11d48; padding-bottom: 12px; margin-bottom: 18px; }
        .header h1 { margin: 0 0 4px; font-size: 26px; color: #e11d48; letter-spacing: 1px; }
        .header p { margin: 2px 0; font-size: 12px; color: #444; }
        .title-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
        .title-row h2 { margin: 0; font-size: 18px; }
        .badge { display: inline-block; background: #e11d48; color: #fff; padding: 4px 12px; border-radius: 4px; font-size: 12px; text-transform: uppercase; font-weight: bold; }
        table.meta { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.meta td { border: 1px solid #ddd; padding: 7px 9px; font-size: 12px; }
        table.meta td.label { background: #f8fafc; font-weight: bold; width: 22%; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.items th { background: #e11d48; color: #fff; padding: 8px; text-align: left; font-size: 12px; text-transform: uppercase; }
        table.items td { border: 1px solid #ddd; padding: 8px; font-size: 13px; }
        .right { text-align: right; }
        .notes { border: 1px solid #ddd; border-left: 4px solid #e11d48; background: #fafafa; padding: 12px 14px; margin-bottom: 14px; font-size: 12.5px; line-height: 1.55; white-space: pre-wrap; }
        .notes h4 { margin: 0 0 6px; font-size: 13px; color: #b91c1c; text-transform: uppercase; }
        .totals { width: 45%; margin-left: auto; border-collapse: collapse; }
        .totals td { padding: 6px 9px; font-size: 12.5px; border: 1px solid #ddd; }
        .totals td.label { background: #f8fafc; font-weight: bold; }
        .totals .grand td { background: #e11d48; color: #fff; font-weight: bold; font-size: 14px; }
        .signatures { display: flex; justify-content: space-between; margin-top: 40px; }
        .signatures div { width: 45%; }
        .signatures .line { border-top: 1px solid #333; margin-top: 44px; padding-top: 6px; font-size: 11.5px; text-align: center; }
        .footer { margin-top: 24px; font-size: 10.5px; color: #888; text-align: center; border-top: 1px solid #ddd; padding-top: 8px; }
        @media print {
            body { background: #fff; }
            .sheet { box-shadow: none; margin: 0; max-width: none; border-radius: 0; padding: 20px; }
            .toolbar { display: none; }
        }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="toolbar">
            <button type="button" onclick="window.print()"><i>🖨</i> Print / Save as PDF</button>
        </div>

        <div class="header">
            <h1>FIXIT AUTO SERVICES</h1>
            <p>123 A. Bonifacio Ave, Quezon City, Philippines</p>
            <p>Tel: (02) 8123-4567 &bull; fixitautoservices.com</p>
        </div>

        <div class="title-row">
            <h2>Job Order / Work Order</h2>
            <span class="badge">{{ $jobOrder->job_order_status ?? 'draft' }}</span>
        </div>

        <table class="meta">
            <tr>
                <td class="label">Job Order No.</td>
                <td>{{ $jobOrder->job_order_number }}</td>
                <td class="label">Date</td>
                <td>{{ optional($jobOrder->job_order_date)->format('M d, Y') }}</td>
            </tr>
            <tr>
                <td class="label">Customer</td>
                <td>{{ $jobOrder->customer?->full_name ?? 'N/A' }}</td>
                <td class="label">Phone</td>
                <td>{{ $jobOrder->customer?->phone ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Vehicle</td>
                <td>{{ $jobOrder->vehicle ? $jobOrder->vehicle->year . ' ' . $jobOrder->vehicle->make . ' ' . $jobOrder->vehicle->model : 'N/A' }}</td>
                <td class="label">Plate No.</td>
                <td>{{ $jobOrder->vehicle?->license_plate ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Technician</td>
                <td>{{ $jobOrder->technician?->name ?? 'N/A' }}</td>
                <td class="label">Priority</td>
                <td>{{ ucfirst($jobOrder->priority ?? 'normal') }}</td>
            </tr>
            @if($jobOrder->odometer_in || $jobOrder->fuel_level)
            <tr>
                <td class="label">Odometer In</td>
                <td>{{ $jobOrder->odometer_in ?? '—' }}</td>
                <td class="label">Fuel Level</td>
                <td>{{ $jobOrder->fuel_level ?? '—' }}</td>
            </tr>
            @endif
        </table>

        @if($jobOrder->customer_concerns || $jobOrder->customer_complaints)
        <div class="notes">
            <h4>Customer Concerns / Complaints</h4>
            {{ $jobOrder->customer_concerns ?? $jobOrder->customer_complaints }}
        </div>
        @endif

        <table class="items">
            <thead>
                <tr>
                    <th style="width:55%;">Service / Item</th>
                    <th class="right">Unit Price</th>
                    <th class="right">Qty</th>
                    <th class="right">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jobOrder->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="right">₱ {{ number_format($item->unit_cost, 2) }}</td>
                        <td class="right">{{ rtrim(rtrim(number_format($item->quantity, 2), '0'), '.') }}</td>
                        <td class="right">₱ {{ number_format($item->final_amount ?? $item->total_cost, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="text-align:center;">No services listed.</td></tr>
                @endforelse
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td class="label">Total</td>
                <td class="right">₱ {{ number_format($jobOrder->final_amount ?? 0, 2) }}</td>
            </tr>
            @if($jobOrder->amount_paid)
            <tr>
                <td class="label">Amount Paid</td>
                <td class="right">₱ {{ number_format($jobOrder->amount_paid, 2) }}</td>
            </tr>
            @endif
            <tr class="grand">
                <td>Balance Due</td>
                <td class="right">₱ {{ number_format($jobOrder->balance_due ?? ($jobOrder->final_amount ?? 0), 2) }}</td>
            </tr>
        </table>

        @if($jobOrder->additional_notes || $jobOrder->technician_diagnosis || $jobOrder->recommended_services)
        <div class="notes" style="margin-top:14px;">
            <h4>Notes / Instructions</h4>
            {{ $jobOrder->additional_notes ?? ($jobOrder->technician_diagnosis ?? $jobOrder->recommended_services) }}
        </div>
        @endif

        <div class="signatures">
            <div class="line">Customer Signature</div>
            <div class="line">Technician Signature</div>
        </div>

        <div class="footer">
            Thank you for choosing FixIt Auto Services. Please keep this job order for your records.
        </div>
    </div>
</body>
</html>
