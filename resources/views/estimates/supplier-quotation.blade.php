<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Supplier Quotation — {{ $estimate->estimate_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #0f172a;
        }
        .bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            max-width: 720px;
            margin: 20px auto 0;
            padding: 0 4px;
        }
        .bar h1 { font-size: 1rem; margin: 0; font-weight: 600; color: #334155; }
        .bar .actions { display: flex; gap: 8px; }
        .bar button, .bar a {
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #334155;
            padding: 8px 14px;
            border-radius: 8px;
            font-size: .85rem;
            cursor: pointer;
            text-decoration: none;
        }
        .bar button.primary { background: #2563eb; border-color: #2563eb; color: #fff; }
        .bar button:hover, .bar a:hover { filter: brightness(.97); }
        .sheet {
            max-width: 720px;
            margin: 12px auto 40px;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 28px 32px;
        }
        pre {
            margin: 0;
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
            font-size: .92rem;
            line-height: 1.55;
            white-space: pre-wrap;
            word-break: break-word;
            color: #0f172a;
        }
        .hint { max-width: 720px; margin: 0 auto; padding: 0 4px 8px; font-size: .78rem; color: #64748b; }
        @media print {
            body { background: #fff; }
            .bar, .hint { display: none !important; }
            .sheet { border: 0; margin: 0; padding: 0; max-width: none; }
        }
    </style>
</head>
<body>
    @php
        $vehicle = $estimate->vehicle;
        $vehicleInfo = $vehicle
            ? strtoupper(trim(implode(' ', array_filter([$vehicle->make, $vehicle->model, $vehicle->year, $vehicle->fuel_type]))))
            : '';
        $lines = [];
        $lines[] = ($estimate->customer->name ?? '');
        $lines[] = $vehicleInfo;
        $lines[] = ($vehicle->vin ?? '');
        $lines[] = '';
        foreach ($ordered as $group) {
            $lines[] = $group['label'];
            $n = 0;
            foreach ($group['items'] as $itemName) {
                $n++;
                $lines[] = $n . '. ' . $itemName;
            }
            $lines[] = '';
        }
        $text = rtrim(implode("\n", $lines));
    @endphp

    <div class="bar">
        <h1>Supplier Quotation — {{ $estimate->estimate_number }}</h1>
        <div class="actions">
            <button type="button" class="primary" onclick="copySQ()">Copy</button>
            <button type="button" onclick="window.print()">Print</button>
            <a href="{{ url()->previous() }}">Back</a>
        </div>
    </div>
    <div class="hint">Copy this and paste it straight into Messenger / SMS to your supplier.</div>

    <div class="sheet">
        <pre id="sqText">{{ $text }}</pre>
    </div>

    <script>
        function copySQ() {
            var el = document.getElementById('sqText');
            var text = el.innerText;
            function done(btn) {
                btn.textContent = 'Copied!';
                setTimeout(function () { btn.textContent = 'Copy'; }, 1500);
            }
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function () {
                    done(event.target);
                }).catch(function () { fallback(); });
            } else {
                fallback();
            }
            function fallback() {
                var ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                try { document.execCommand('copy'); done(event.target); } catch (e) {}
                document.body.removeChild(ta);
            }
        }
    </script>
</body>
</html>
