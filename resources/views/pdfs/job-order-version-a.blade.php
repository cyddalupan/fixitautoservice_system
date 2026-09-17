<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Job Order {{ $jobOrder->job_order_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; margin: 0; }
        .header { text-align: center; border-bottom: 3px solid #e11d48; padding-bottom: 10px; margin-bottom: 16px; }
        .header h1 { margin: 0 0 4px; font-size: 24px; color: #e11d48; letter-spacing: 1px; }
        .header p { margin: 2px 0; font-size: 11px; color: #444; }
        .title-row { display: flex; justify-content: space-between; margin-bottom: 14px; }
        .title-row h2 { margin: 0; font-size: 18px; }
        .badge { display: inline-block; background: #e11d48; color: #fff; padding: 3px 10px; border-radius: 4px; font-size: 11px; text-transform: uppercase; }
        table.meta { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        table.meta td { border: 1px solid #ddd; padding: 6px 8px; font-size: 11px; }
        table.meta td.label { background: #f8fafc; font-weight: bold; width: 22%; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.items th { background: #e11d48; color: #fff; padding: 8px; text-align: left; font-size: 11px; text-transform: uppercase; }
        table.items td { border: 1px solid #ddd; padding: 8px; font-size: 12px; }
        .right { text-align: right; }
        .totals { width: 40%; margin-left: auto; border-collapse: collapse; }
        .totals td { padding: 5px 8px; font-size: 12px; border: 1px solid #ddd; }
        .totals td.label { background: #f8fafc; font-weight: bold; }
        .totals .grand td { background: #e11d48; color: #fff; font-weight: bold; font-size: 13px; }
        .footer { margin-top: 24px; font-size: 10px; color: #888; text-align: center; border-top: 1px solid #ddd; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>FIXIT AUTO SERVICES</h1>
        <p>123 A. Bonifacio Ave, Quezon City, Philippines</p>
        <p>Tel: (02) 8123-4567 &bull; fixitautoservices.com</p>
    </div>

    <div class="title-row">
        <h2>Job Order</h2>
        <span class="badge">{{ $jobOrder->job_order_status }}</span>
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
            <td class="label"></td>
            <td></td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width:55%;">Service</th>
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
                    <td class="right">₱ {{ number_format($item->final_amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="right" style="text-align:center;">No services listed.</td></tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label">Total</td>
            <td class="right">₱ {{ number_format($jobOrder->final_amount, 2) }}</td>
        </tr>
        <tr class="grand">
            <td>Balance Due</td>
            <td class="right">₱ {{ number_format($jobOrder->balance_due ?? $jobOrder->final_amount, 2) }}</td>
        </tr>
    </table>

    <div class="footer">
        Thank you for choosing FixIt Auto Services. Please keep this job order for your records.
    </div>
</body>
</html>
