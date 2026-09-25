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

    // Present the quotation from the Repair Order's CURRENT findings (the fresh
    // information) as ONE unified list. Items found during the repair now sit
    // alongside the rest — we no longer split "approved quotation" vs "additional".
    $quotedFindings = $findings->values();

    // Build the category sections (and totals) for a given set of findings.
    // Returns [sections, partsTotal, laborTotal].
    $buildSections = function ($findings) use ($money) {
        $partsTotal = 0.0;
        $laborTotal = 0.0;
        $groupLaborShown = []; // group_id => true (shared labor printed once)

        // Bucket by category (first-seen order preserved).
        $byCat = [];
        foreach ($findings as $f) {
            $cat = trim((string) ($f->category ?? ''));
            if ($cat === '') { $cat = 'GENERAL'; }
            $byCat[$cat][] = $f;
        }

        $sections = [];
        foreach ($byCat as $cat => $rows) {
            // Order: ungrouped first, then grouped — grouped rows kept contiguous per group
            // so their shared labor can be shown as one merged cell.
            $ordered = collect($rows)->sortBy(function ($f) {
                if (is_null($f->group_id)) { return [-1, (int) $f->sort_order]; }
                return [(int) ($f->group->sort_order ?? 0), (int) $f->sort_order];
            })->values();

            $items = [];
            $i = 0; $n = $ordered->count();
            while ($i < $n) {
                $f = $ordered[$i];

                if (is_null($f->group_id)) {
                    $qty = (float) $f->quantity;
                    $parts = $qty * (float) ($f->unit_price ?? 0);
                    $labor = (float) ($f->estimated_cost ?? 0);
                    $partsTotal += $parts; $laborTotal += $labor;
                    $items[] = [
                        'desc' => $f->issue_title ?: ($f->part_name ?: ''),
                        'qty' => $qty,
                        'parts' => $parts,
                        'laborTxt' => $labor > 0 ? $money($labor) : null,
                        'merge' => 'none',
                        'rowspan' => 1,
                        'remarks' => trim((string) ($f->remarks ?? '')) ?: trim((string) ($f->detailed_notes ?? '')),
                    ];
                    $i++;
                    continue;
                }

                // Contiguous run belonging to the same group.
                $j = $i;
                while ($j < $n && !is_null($ordered[$j]->group_id) && $ordered[$j]->group_id == $f->group_id) { $j++; }
                $runLen = $j - $i;

                $gLabor = (float) ($f->group->labor_cost ?? 0);
                $showLabor = empty($groupLaborShown[$f->group_id]); // print once per group
                $groupLaborShown[$f->group_id] = true;

                for ($k = $i; $k < $j; $k++) {
                    $ff = $ordered[$k];
                    $qty = (float) $ff->quantity;
                    $parts = $qty * (float) ($ff->unit_price ?? 0);
                    $partsTotal += $parts;
                    $isFirst = ($k === $i);
                    $laborHere = ($isFirst && $showLabor) ? $gLabor : 0.0;
                    $laborTotal += $laborHere;
                    $items[] = [
                        'desc' => $ff->issue_title ?: ($ff->part_name ?: ''),
                        'qty' => $qty,
                        'parts' => $parts,
                        'laborTxt' => $laborHere > 0 ? $money($laborHere) : null,
                        'merge' => $isFirst ? 'start' : 'cont',
                        'rowspan' => $isFirst ? $runLen : 0,
                        'remarks' => trim((string) ($ff->remarks ?? '')) ?: trim((string) ($ff->detailed_notes ?? '')),
                    ];
                }
                $i = $j;
            }

            if (!empty($items)) { $sections[] = ['label' => $cat, 'items' => $items]; }
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
            <td colspan="3">{{ $customer->address ?? '' }}</td>
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
