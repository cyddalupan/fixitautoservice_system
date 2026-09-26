{{--
    Printable REPAIR QUOTATION sheet.
    Data source: the Repair Order (VehicleInspection) + its Findings (grouped / ungrouped).
    Mirrors the Repair Order slip layout (partials/repair-order-slip) but:
      - title REPAIR QUOTATION
      - no preventive-maintenance / service checkbox grid
      - Job Description -> Recommendation / Quotation
      - one long table: JOB/PARTS DESCRIPTION | QTY/HM | PARTS PRICE | LABOR COST | REMARKS
      - no separate Parts/Supplies section, no Concern box
      - SECTION HEADER = CATEGORY (auto-detected, so same-category parts share one block)
      - findings that were grouped (share one labor cost) get a MERGED Labor Cost cell
        (rowspan across the group's rows) so "isang labor lang" for those parts
--}}
@php
    $customer = $inspection->customer ?? null;
    $vehicle  = $inspection->vehicle ?? null;
    $vehicleDesc = trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? ''));
    if ($vehicle && !empty($vehicle->year)) {
        $vehicleDesc = trim($vehicleDesc . ' ' . $vehicle->year);
    }

    // Odometer: the Repair Order's own reading (like the RO overview / RO slip). Falls back to
    // "N/A" when the RO never captured a mileage — do NOT show the vehicle master's possibly
    // stale odometer.
    $mileage = $inspection->vehicle_mileage ?? null;
    $odometer = ($mileage !== null && (float) $mileage > 0) ? number_format((float) $mileage) : 'N/A';

    $estimate = $estimate ?? null;

    $findings = ($inspection->inspectionFindings ?? collect())->where('is_declined', false); // ordered by sort_order; "Not Pursued" items are separated out

    $money = function ($n) { return '&#8369; ' . number_format((float) $n, 2); };

    // Present the quotation from the RO's "Findings" tab ONLY — the new things found
    // during the repair. The approved/locked items (the old quotation, shown under the
    // "From Quotation" tab) must NOT appear here.
    $quotedFindings = $findings->filter(function ($f) use ($inspection) {
        return ! $inspection->findingIsLocked($f);
    })->values();

    // Build the quotation sections (and totals) for a given set of findings.
    // Returns [sections, partsTotal, laborTotal].
    //
    // A SECTION is one finding GROUP (its own name + one shared labor cost for its
    // parts) or, for ungrouped findings, one CATEGORY. Sectioning by GROUP — not by
    // category — keeps two distinct groups that share a category (e.g. "Aircon" and
    // "Aircon - Tentative") as SEPARATE blocks, instead of merging all their parts
    // into a single block.
    $buildSections = function ($findings) use ($money) {
        $partsTotal = 0.0;
        $laborTotal = 0.0;

        // Bucket: a grouped finding follows its group; an ungrouped one follows its
        // category. First-seen order is preserved (findings arrive by sort_order).
        $buckets = [];
        foreach ($findings as $f) {
            if (! is_null($f->group_id)) {
                $key = 'g:' . $f->group_id;
                if (! isset($buckets[$key])) {
                    $name = trim((string) ($f->group->name ?? ''));
                    $buckets[$key] = ['label' => $name !== '' ? $name : 'GROUP', 'kind' => 'group', 'group' => $f->group, 'rows' => []];
                }
            } else {
                $cat = trim((string) ($f->category ?? ''));
                if ($cat === '') { $cat = 'GENERAL'; }
                $key = 'c:' . $cat;
                if (! isset($buckets[$key])) {
                    $buckets[$key] = ['label' => $cat, 'kind' => 'cat', 'group' => null, 'rows' => []];
                }
            }
            $buckets[$key]['rows'][] = $f;
        }

        $sections = [];
        foreach ($buckets as $bucket) {
            // Within a section, order by the finding's own sort order.
            $ordered = collect($bucket['rows'])->sortBy(fn ($f) => (int) $f->sort_order)->values();

            $items = [];
            if ($bucket['kind'] === 'group') {
                // The group's own labor_cost is shared across its parts — printed
                // once as a merged cell spanning the group's rows.
                $gLabor = (float) ($bucket['group']->labor_cost ?? 0);
                $runLen = $ordered->count();
                foreach ($ordered as $idx => $ff) {
                    $qty = (float) $ff->quantity;
                    $parts = $qty * (float) ($ff->unit_price ?? 0);
                    $partsTotal += $parts;
                    $isFirst = ($idx === 0);
                    $laborHere = $isFirst ? $gLabor : 0.0;
                    $laborTotal += $laborHere;
                    $items[] = [
                        'desc' => $ff->issue_title ?: ($ff->part_name ?: ''),
                        'qty' => $qty,
                        'parts' => $parts,
                        'laborTxt' => $laborHere > 0 ? $money($laborHere) : null,
                        'merge' => $runLen > 1 ? ($isFirst ? 'start' : 'cont') : 'none',
                        'rowspan' => $runLen > 1 ? ($isFirst ? $runLen : 0) : 1,
                        'remarks' => trim((string) ($ff->remarks ?? '')) ?: trim((string) ($ff->detailed_notes ?? '')),
                    ];
                }
            } else {
                // Ungrouped findings each carry their own labor.
                foreach ($ordered as $ff) {
                    $qty = (float) $ff->quantity;
                    $parts = $qty * (float) ($ff->unit_price ?? 0);
                    $labor = (float) ($ff->estimated_cost ?? 0);
                    $partsTotal += $parts; $laborTotal += $labor;
                    $items[] = [
                        'desc' => $ff->issue_title ?: ($ff->part_name ?: ''),
                        'qty' => $qty,
                        'parts' => $parts,
                        'laborTxt' => $labor > 0 ? $money($labor) : null,
                        'merge' => 'none',
                        'rowspan' => 1,
                        'remarks' => trim((string) ($ff->remarks ?? '')) ?: trim((string) ($ff->detailed_notes ?? '')),
                    ];
                }
            }

            if (!empty($items)) { $sections[] = ['label' => $bucket['label'], 'items' => $items]; }
        }

        return [$sections, $partsTotal, $laborTotal];
    };

    [$sections, $partsTotal, $laborTotal] = $buildSections($quotedFindings);

    $subtotal = $partsTotal + $laborTotal;
    $discount = 0.0;
    $grandTotal = max(0, $subtotal - $discount);

    $qtyFmt = function ($q) {
        $s = rtrim(rtrim(number_format((float) $q, 2, '.', ''), '0'), '.');
        return $s === '' ? '0' : $s;
    };

    // Reference the Repair Order's own number (fresh info) — not the old quotation's
    // estimate number.
    $docNo = ($inspection->reference_label ?? null)
        ?: ($inspection->appointment->appointment_number ?? null)
        ?: ('#'.($inspection->id ?? ''));

    $emptyRows = 4;
@endphp
<style>
    .rq { font-family: "DejaVu Sans", Arial, Helvetica, sans-serif; color: #1a1a1a; font-size: 11px; }
    .rq .head { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #e11d48; padding-bottom: 6px; margin-bottom: 8px; }
    .rq .head .brand h1 { margin: 0 0 1px; font-size: 18px; color: #e11d48; letter-spacing: 1px; }
    .rq .head .brand p { margin: 0; font-size: 10px; color: #555; }
    .rq .head .rq-title { font-size: 14px; font-weight: bold; letter-spacing: 2px; text-align: right; }
    .rq .head .rq-title small { display: block; font-weight: normal; letter-spacing: 0; font-size: 10px; color: #444; margin-top: 2px; }
    table.rq-info { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    table.rq-info td { border: 1px solid #bbb; padding: 3px 6px; font-size: 10.5px; line-height: 1.25; }
    table.rq-info td.lbl { background: #f2f2f2; font-weight: bold; color: #555; white-space: nowrap; width: 1%; }
    .rq-sec { font-size: 10.5px; font-weight: bold; text-transform: uppercase; margin: 0 0 3px; color: #e11d48; }
    table.rq-items { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    table.rq-items th { background: #e11d48; color: #fff; padding: 4px 5px; font-size: 9.5px; text-transform: uppercase; border: 1px solid #e11d48; }
    table.rq-items td { border: 1px solid #bbb; padding: 3px 5px; font-size: 10.5px; height: 16px; }
    table.rq-items td.blank { height: 16px; }
    table.rq-items tr.catrow td { background: #f7d9e0; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; font-size: 10px; color: #9a1238; }
    table.rq-items td.merge-cell { vertical-align: middle; background: #fffdf7; }
    /* Additional findings (added during repair) — amber, visually separated from the approved quotation */
    table.rq-items.rq-add th { background: #b45309; border-color: #b45309; }
    table.rq-items.rq-add tr.catrow td { background: #fde9cf; color: #92400e; }
    .rq-sec.rq-sec-add { color: #b45309; }
    .rq-right { text-align: right; }
    table.rq-totals { width: 100%; border-collapse: collapse; margin: 0; }
    table.rq-totals td { border: 1px solid #bbb; padding: 3px 7px; font-size: 10.5px; }
    table.rq-totals td.lbl { background: #f2f2f2; font-weight: bold; }
    table.rq-totals tr.grand td { background: #e11d48; color: #fff; font-weight: bold; font-size: 11.5px; }
    .rq-terms { border: 1px solid #bbb; padding: 6px 8px; font-size: 8.5px; line-height: 1.4; margin: 10px 0; }
    .rq-terms h4 { margin: 0 0 3px; font-size: 10px; text-transform: uppercase; }
    .rq-terms ol { margin: 0; padding-left: 14px; }
    table.rq-sign { width: 100%; border-collapse: collapse; margin-top: 12px; }
    table.rq-sign td { width: 33%; text-align: center; font-size: 10px; padding-top: 26px; }
    table.rq-sign td .ln { border-top: 1px solid #333; margin: 0 12px; padding-top: 3px; }
</style>

<div class="rq">
    <div class="head">
        <div class="brand">
            <h1>FIX-IT AUTO SERVICES - STA MARIA</h1>
            <p>New Bypass Bridge, San Gabriel Sitio Gulod, Santa Maria, Bulacan</p>
        </div>
        <div class="rq-title">
            REPAIR QUOTATION
            <small>No. {{ $docNo }} &bull; {{ now()->format('M d, Y') }}</small>
        </div>
    </div>

    {{-- Customer + vehicle information (from the Repair Order) --}}
    <table class="rq-info">
        <tr>
            <td class="lbl">Customer</td>
            <td>{{ $customer->full_name ?? '' }}</td>
            <td class="lbl">Mobile</td>
            <td>{{ $customer->phone ?? '' }}</td>
            <td class="lbl">Plate</td>
            <td>{{ $vehicle->license_plate ?? '' }}</td>
        </tr>
        <tr>
            <td class="lbl">Address</td>
            <td colspan="3">{{ trim(implode(', ', array_filter([$customer->address ?? null, $customer->city ?? null]))) }}</td>
            <td class="lbl">Odometer</td>
            <td>{{ $odometer }}</td>
        </tr>
        <tr>
            <td class="lbl">Vehicle</td>
            <td>{{ $vehicleDesc }}</td>
            <td class="lbl">VIN</td>
            <td>{{ $vehicle->vin ?? '' }}</td>
            <td class="lbl">Engine No.</td>
            <td>{{ $vehicle->engine_no ?? '' }}</td>
        </tr>
        <tr>
            <td class="lbl">Trans / Fuel</td>
            <td colspan="5">{{ trim(($vehicle->transmission ?? '') . ' • ' . ($vehicle->fuel_type ?? ''), ' •') }}</td>
        </tr>
    </table>

    <p class="rq-sec">Recommendation / Quotation</p>
    <table class="rq-items">
        <tr>
            <th style="text-align:left">Job / Parts Description</th>
            <th style="width:9%">Qty/HM</th>
            <th style="width:14%">Parts Price</th>
            <th style="width:14%">Labor Cost</th>
            <th style="width:24%">Remarks</th>
        </tr>
        @forelse($sections as $section)
            <tr class="catrow"><td colspan="5">{{ $section['label'] }}</td></tr>
            @foreach($section['items'] as $row)
                <tr>
                    <td>{{ $row['desc'] }}</td>
                    <td class="rq-right">{{ $qtyFmt($row['qty']) }}</td>
                    <td class="rq-right">{{ $row['parts'] > 0 ? number_format($row['parts'], 2) : '' }}</td>
                    @if($row['merge'] === 'start')
                        <td class="rq-right merge-cell" rowspan="{{ $row['rowspan'] }}">{!! $row['laborTxt'] ?: '' !!}</td>
                    @elseif($row['merge'] === 'none')
                        <td class="rq-right">{!! $row['laborTxt'] ?: '' !!}</td>
                    @endif
                    <td>{{ $row['remarks'] }}</td>
                </tr>
            @endforeach
        @empty
            @for($i = 0; $i < $emptyRows; $i++)
                <tr><td class="blank">&nbsp;</td><td class="blank"></td><td class="blank"></td><td class="blank"></td><td class="blank"></td></tr>
            @endfor
        @endforelse
    </table>

    {{-- Totals (built from the findings) --}}
    <table class="rq-totals" style="width:44%; margin:6px 0 10px auto;">
        <tr><td class="lbl">Total Parts Price</td><td class="rq-right">{!! $money($partsTotal) !!}</td></tr>
        <tr><td class="lbl">Total Labor Cost</td><td class="rq-right">{!! $money($laborTotal) !!}</td></tr>
        <tr><td class="lbl">Subtotal</td><td class="rq-right">{!! $money($subtotal) !!}</td></tr>
        <tr><td class="lbl">Discount</td><td class="rq-right">{!! $money($discount) !!}</td></tr>
        <tr class="grand"><td>GRAND TOTAL</td><td class="rq-right">{!! $money($grandTotal) !!}</td></tr>
    </table>

    <div class="rq-terms">
        <h4>Terms &amp; Conditions</h4>
        <ol>
            <li>Ang quotation na ito ay batay sa mga findings na naitala sa repair order at may bisa sa loob ng 30 araw mula sa petsa nito.</li>
            <li>Ang kabuuang halaga ay maaaring magbago kapag may nadiskubre pang sira habang ginagawa ang trabaho; ipapaalam agad sa customer bago magpatuloy.</li>
            <li>Hindi kasama ang mga pyesa/parts na hindi galing sa shop, at ang mga normal na pagkasira dahil sa gamit o kalumaan.</li>
            <li>Ang trabaho ay magsisimula lamang matapos maaprubahan ng customer ang quotation na ito.</li>
            <li>Kailangang bayaran ang buong halaga bago ilabas ang sasakyan, maliban kung may naunang kasunduan.</li>
            <li>Ang mga sasakyang hindi makukuha sa loob ng 30 araw pagkatapos maipaalam ay maaaring singilin ng storage fee.</li>
        </ol>
    </div>

    <table class="rq-sign">
        <tr>
            <td><div class="ln">Customer / Authorized Representative</div></td>
            <td><div class="ln">Service Advisor</div></td>
            <td><div class="ln">Technician</div></td>
        </tr>
    </table>
</div>
