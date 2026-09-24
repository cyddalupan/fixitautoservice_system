<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Repair Order Slip — {{ $slipReference }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; background: #f1f5f9; font-family: Arial, Helvetica, sans-serif; }
        .toolbar { max-width: 820px; margin: 16px auto 0; display: flex; justify-content: space-between; align-items: center; gap: 10px; flex-wrap: wrap; }
        .toolbar .actions { display: flex; gap: 8px; }
        .btn { display: inline-block; text-decoration: none; border: 0; padding: 10px 18px; border-radius: 6px; font-size: 14px; font-weight: bold; cursor: pointer; }
        .btn-print { background: #0d6efd; color: #fff; }
        .btn-pdf { background: #e11d48; color: #fff; }
        .btn-back { background: #e2e8f0; color: #1a1a1a; }
        .sheet { max-width: 820px; margin: 16px auto 40px; background: #fff; padding: 32px; border-radius: 8px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); }
        @media print {
            body { background: #fff; }
            .toolbar { display: none !important; }
            .sheet { box-shadow: none; margin: 0; max-width: none; border-radius: 0; padding: 14px; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <a href="{{ route('inspections.show', $inspection) }}" class="btn btn-back">&larr; Back to Repair Order</a>
        <div class="actions">
            <a href="{{ route('inspections.repair-order-pdf', $inspection) }}" class="btn btn-pdf">&#11015; Download PDF</a>
            <button type="button" class="btn btn-print" onclick="window.print()">&#128424; Print</button>
        </div>
    </div>

    <div class="sheet">
        @include('partials.repair-order-slip')
    </div>
</body>
</html>
