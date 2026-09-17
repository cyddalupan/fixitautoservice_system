<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Job Order {{ $jobOrder->job_order_number }} — Tech</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1a1a1a; margin: 0; }
        .header { text-align: center; border-bottom: 2px solid #334155; padding-bottom: 8px; margin-bottom: 14px; }
        .header h1 { margin: 0 0 2px; font-size: 20px; color: #334155; }
        .header p { margin: 0; font-size: 11px; color: #555; }
        table.meta { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        table.meta td { border: 1px solid #ccc; padding: 6px 8px; font-size: 11px; }
        table.items { width: 100%; border-collapse: collapse; }
        table.items th { background: #334155; color: #fff; padding: 7px; text-align: left; font-size: 11px; text-transform: uppercase; }
        table.items td { border: 1px solid #ccc; padding: 7px; font-size: 12px; }
        .footer { margin-top: 20px; font-size: 10px; color: #888; text-align: center; border-top: 1px solid #ccc; padding-top: 6px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>FIXIT AUTO SERVICES</h1>
        <p>Job Order — Technician Copy</p>
    </div>

    <table class="meta">
        <tr>
            <td><strong>Job Order No.</strong> {{ $jobOrder->job_order_number }}</td>
            <td><strong>Date:</strong> {{ optional($jobOrder->job_order_date)->format('M d, Y') }}</td>
        </tr>
        <tr>
            <td><strong>Vehicle:</strong> {{ $jobOrder->vehicle ? $jobOrder->vehicle->year . ' ' . $jobOrder->vehicle->make . ' ' . $jobOrder->vehicle->model : 'N/A' }} ({{ $jobOrder->vehicle?->license_plate ?? 'no plate' }})</td>
            <td><strong>Technician:</strong> {{ $jobOrder->technician?->name ?? 'Unassigned' }}</td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr><th>Services to Perform</th></tr>
        </thead>
        <tbody>
            @forelse ($jobOrder->items as $item)
                <tr><td>{{ $item->description }}</td></tr>
            @empty
                <tr><td>No services listed.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Technician copy — customer billing information intentionally omitted.
    </div>
</body>
</html>
