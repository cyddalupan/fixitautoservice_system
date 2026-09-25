@php
    // The slip partial is shared by the appointment slip and the walk-in
    // Repair Order slip. Parents pass these generic variables.
    $customer = $slipCustomer ?? ($appointment->customer ?? null);
    $vehicle  = $slipVehicle ?? ($appointment->vehicle ?? null);
    $selectedTypes = $selectedTypes ?? ($appointment->service_types ?? []);
    $selectedTypes = is_array($selectedTypes) ? array_values(array_filter($selectedTypes, 'is_string')) : [];
    $vehicleDesc = trim(($vehicle->make ?? '') . ' ' . ($vehicle->model ?? ''));
    if ($vehicle && !empty($vehicle->year)) {
        $vehicleDesc = trim($vehicleDesc . ' ' . $vehicle->year);
    }

    // ---- Job Description rows (fallback: the checked Services) ----
    $jdItems = $slipJdItems ?? ($appointment->job_description_items ?? []);
    if (!is_array($jdItems)) { $jdItems = []; }
    if (count($jdItems) === 0) {
        foreach ($selectedTypes as $k) {
            $jdItems[] = ['description' => $services[$k] ?? $k, 'mh' => null, 'unit_price' => null, 'labor_cost' => null];
        }
    }

    // ---- Parts / Supplies rows ----
    $partsItems = $slipPartsItems ?? ($appointment->parts_items ?? []);
    if (!is_array($partsItems)) { $partsItems = []; }

    $laborTotal = 0.0;
    foreach ($jdItems as $row) { $laborTotal += (float) ($row['labor_cost'] ?? 0); }
    $partsTotal = 0.0;
    foreach ($partsItems as $row) { $partsTotal += (float) ($row['cost'] ?? 0); }

    $discount = (float) ($slipDiscount ?? ($appointment->discount ?? 0));
    $subtotal = $laborTotal + $partsTotal;
    $grandTotal = max(0, $subtotal - $discount);

    $slipReference = $slipReference ?? ($appointment->appointment_number ?? null);
    $slipDate = $slipDate ?? ($appointment->appointment_date ?? null);
    $slipConcern = $slipConcern ?? ($appointment->service_request ?? null);

    $money = function ($n) { return '&#8369; ' . number_format((float) $n, 2); };

    // Odometer shown on the slip. Caller may pass an explicit value
    // ($slipOdometer, e.g. the Repair Order reading / "N/A"); falls back to
    // the vehicle profile for the appointment slip.
    $odometer = $slipOdometer ?? ($vehicle->odometer ?? null);

    // Repair Orders promoted from a Repair Quotation get a grouped listing
    // (each group's labor shown together with its own parts) instead of the flat
    // "Job Description" + "Parts" tables, which lump a category's labor and are
    // confusing on the customer's copy.
    $slipGroups = $slipGroups ?? [];
    if (!empty($slipGroups)) {
        $laborTotal = 0.0;
        $partsTotal = 0.0;
        foreach ($slipGroups as $g) {
            if ($g['labor'] !== null) { $laborTotal += (float) $g['labor']; }
            foreach ($g['items'] as $it) {
                $partsTotal += (float) $it['cost'];
                if ($it['labor'] !== null) { $laborTotal += (float) $it['labor']; }
            }
        }
        $subtotal = $laborTotal + $partsTotal;
        $grandTotal = max(0, $subtotal - $discount);
    }

    // Payment history (down payments / full payments) shown under Concern/Request.
    $slipPayments = $slipPayments ?? collect();
    $slipPaidTotal = $slipPayments->where('status', '!=', 'rejected')->sum(function ($p) { return (float) $p->amount; });
    $slipBalance = max(0, $grandTotal - $slipPaidTotal);

    $jdPad = max(0, 6 - count($jdItems));
    $partsPad = max(0, 6 - count($partsItems));
@endphp
<style>
    .ros { font-family: "DejaVu Sans", Arial, Helvetica, sans-serif; color: #1a1a1a; font-size: 11px; }
    .ros .head { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #e11d48; padding-bottom: 6px; margin-bottom: 8px; }
    .ros .head .brand h1 { margin: 0 0 1px; font-size: 18px; color: #e11d48; letter-spacing: 1px; }
    .ros .head .brand p { margin: 0; font-size: 10px; color: #555; }
    .ros .head .ro-title { font-size: 14px; font-weight: bold; letter-spacing: 2px; text-align: right; }
    .ros .head .ro-title small { display: block; font-weight: normal; letter-spacing: 0; font-size: 10px; color: #444; margin-top: 2px; }
    table.ros-info { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    table.ros-info td { border: 1px solid #bbb; padding: 3px 6px; font-size: 10.5px; line-height: 1.25; }
    table.ros-info td.lbl { background: #f2f2f2; font-weight: bold; color: #555; white-space: nowrap; width: 1%; }
    table.ros-svc { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
    table.ros-svc td { border: 1px solid #bbb; padding: 3px 6px; font-size: 10px; }
    .ros-sec { font-size: 10.5px; font-weight: bold; text-transform: uppercase; margin: 0 0 3px; color: #e11d48; }
    table.ros-items { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
    table.ros-items th { background: #e11d48; color: #fff; padding: 4px 5px; font-size: 9.5px; text-transform: uppercase; border: 1px solid #e11d48; }
    table.ros-items td { border: 1px solid #bbb; padding: 3px 5px; font-size: 10.5px; height: 16px; }
    table.ros-items td.blank { height: 16px; }
    .ros-right { text-align: right; }
    table.ros-totals { width: 100%; border-collapse: collapse; margin: 0; }
    table.ros-totals td { border: 1px solid #bbb; padding: 3px 7px; font-size: 10.5px; }
    table.ros-totals td.lbl { background: #f2f2f2; font-weight: bold; }
    table.ros-totals tr.grand td { background: #e11d48; color: #fff; font-weight: bold; font-size: 11.5px; }
    .ros-concern { border: 1px solid #bbb; padding: 5px 8px; font-size: 10.5px; min-height: 62px; }
    .ros-concern .ros-sec { margin-bottom: 4px; }
    .ros-concern-body { line-height: 1.4; white-space: pre-wrap; }
    .ros-terms { border: 1px solid #bbb; padding: 6px 8px; font-size: 8.5px; line-height: 1.4; margin-bottom: 10px; }
    .ros-terms h4 { margin: 0 0 3px; font-size: 10px; text-transform: uppercase; }
    .ros-terms ol { margin: 0; padding-left: 14px; }
    table.ros-sign { width: 100%; border-collapse: collapse; margin-top: 10px; }
    table.ros-sign td { width: 33%; text-align: center; font-size: 10px; padding-top: 26px; }
    table.ros-sign td .ln { border-top: 1px solid #333; margin: 0 12px; padding-top: 3px; }
</style>

<div class="ros">
    <div class="head">
        <div class="brand">
            <h1>FIX-IT AUTO SERVICES - STA MARIA</h1>
            <p>New Bypass Bridge, San Gabriel Sitio Gulod, Santa Maria, Bulacan</p>
        </div>
        <div class="ro-title">
            REPAIR ORDER
            <small>No. {{ $slipReference }} &bull; {{ $slipDate ? $slipDate->format('M d, Y') : now()->format('M d, Y') }}</small>
        </div>
    </div>

    {{-- Compressed client + vehicle information --}}
    <table class="ros-info">
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
            <td>{{ $odometer ?? '' }}</td>
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

    <table class="ros-svc">
        @foreach(array_chunk($services, 4, true) as $chunk)
            <tr>
                @foreach($chunk as $svcKey => $svcLabel)
                    <td>{!! in_array($svcKey, $selectedTypes, true) ? '&#9745;' : '&#9744;' !!} {{ $svcLabel }}</td>
                @endforeach
                @for($i = count($chunk); $i < 4; $i++)
                    <td>&nbsp;</td>
                @endfor
            </tr>
        @endforeach
    </table>

    @if(!empty($slipGroups))
    <p class="ros-sec">Job Description &amp; Parts / Supplies</p>
    <table class="ros-items">
        <tr>
            <th style="text-align:left">Description</th>
            <th style="width:8%">Qty</th>
            <th style="width:15%">Unit Price</th>
            <th style="width:15%">Parts Cost</th>
            <th style="width:15%">Labor</th>
        </tr>
        @foreach($slipGroups as $g)
            <tr>
                <td colspan="4" style="background:#fff5f7;font-weight:bold;">{{ $g['name'] }}</td>
                <td class="ros-right" style="background:#fff5f7;font-weight:bold;">{!! $g['labor'] !== null ? number_format((float) $g['labor'], 2) : '&mdash;' !!}</td>
            </tr>
            @forelse($g['items'] as $it)
                <tr>
                    <td>{{ $it['desc'] }}</td>
                    <td class="ros-right">{{ $it['qty'] !== null && $it['qty'] !== '' ? rtrim(rtrim(number_format((float) $it['qty'], 2), '0'), '.') : '' }}</td>
                    <td class="ros-right">{{ $it['unit_price'] !== null && $it['unit_price'] !== '' ? number_format((float) $it['unit_price'], 2) : '' }}</td>
                    <td class="ros-right">{{ $it['cost'] ? number_format((float) $it['cost'], 2) : '' }}</td>
                    <td class="ros-right">{!! $it['labor'] !== null ? number_format((float) $it['labor'], 2) : '' !!}</td>
                </tr>
            @empty
                <tr><td colspan="5" style="color:#888;">&mdash;</td></tr>
            @endforelse
        @endforeach
    </table>
    @else
    <p class="ros-sec">Job Description</p>
    <table class="ros-items">
        <tr>
            <th style="text-align:left">Job Description</th>
            <th style="width:7%">MH</th>
            <th style="width:13%">Unit Price</th>
            <th style="width:15%">Labor Cost</th>
        </tr>
        @foreach($jdItems as $row)
            <tr>
                <td>{{ $row['description'] ?? '' }}</td>
                <td class="ros-right">{{ $row['mh'] !== null && $row['mh'] !== '' ? rtrim(rtrim(number_format((float) $row['mh'], 2), '0'), '.') : '' }}</td>
                <td class="ros-right">{{ $row['unit_price'] !== null && $row['unit_price'] !== '' ? number_format((float) $row['unit_price'], 2) : '' }}</td>
                <td class="ros-right">{{ $row['labor_cost'] !== null && $row['labor_cost'] !== '' ? number_format((float) $row['labor_cost'], 2) : '' }}</td>
            </tr>
        @endforeach
        @for($i = 0; $i < $jdPad; $i++)
            <tr><td class="blank">&nbsp;</td><td class="blank"></td><td class="blank"></td><td class="blank"></td></tr>
        @endfor
        <tr>
            <td class="ros-right" colspan="3"><strong>TOTAL LABOR COST:</strong></td>
            <td class="ros-right"><strong>{!! $money($laborTotal) !!}</strong></td>
        </tr>
    </table>

    <p class="ros-sec" style="margin-top:8px">Parts / Supplies</p>
    <table class="ros-items">
        <tr>
            <th style="text-align:left">Parts / Supplies Description</th>
            <th style="width:8%">Qty</th>
            <th style="width:15%">Unit Price</th>
            <th style="width:17%">Parts / Supplies Cost</th>
        </tr>
        @foreach($partsItems as $row)
            <tr>
                <td>{{ $row['description'] ?? '' }}</td>
                <td class="ros-right">{{ $row['qty'] !== null && $row['qty'] !== '' ? rtrim(rtrim(number_format((float) $row['qty'], 2), '0'), '.') : '' }}</td>
                <td class="ros-right">{{ $row['unit_price'] !== null && $row['unit_price'] !== '' ? number_format((float) $row['unit_price'], 2) : '' }}</td>
                <td class="ros-right">{{ $row['cost'] !== null && $row['cost'] !== '' ? number_format((float) $row['cost'], 2) : '' }}</td>
            </tr>
        @endforeach
        @for($i = 0; $i < $partsPad; $i++)
            <tr><td class="blank">&nbsp;</td><td class="blank"></td><td class="blank"></td><td class="blank"></td></tr>
        @endfor
        <tr>
            <td class="ros-right" colspan="3"><strong>TOTAL PARTS / SUPPLIES:</strong></td>
            <td class="ros-right"><strong>{!! $money($partsTotal) !!}</strong></td>
        </tr>
    </table>
    @endif

    {{-- Concern/Request + Totals --}}
    <table style="width:100%; border-collapse:collapse; margin:6px 0 10px;">
        <tr>
            <td style="width:56%; vertical-align:top; border:0; padding:0 10px 0 0;">
                @if($slipPayments->count())
                <div class="ros-concern">
                    <p class="ros-sec">Payments / Bayad</p>
                    <table class="ros-items" style="margin-bottom:0;">
                        <tr>
                            <th style="text-align:left">Date</th>
                            <th style="text-align:left">Type</th>
                            <th style="text-align:left">Payment</th>
                            <th style="width:22%">Amount</th>
                        </tr>
                        @foreach($slipPayments as $p)
                            <tr>
                                <td>{{ optional($p->created_at)->format('M d, Y') }}</td>
                                <td>{{ $p->type_label }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', (string) ($p->payment_method ?: '&mdash;'))) }}@if($p->reference_number) &middot; Ref {{ $p->reference_number }}@endif</td>
                                <td class="ros-right">{{ number_format((float) $p->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                @endif
                <div class="ros-concern" style="margin-top:6px;">
                    <p class="ros-sec">Concern / Request</p>
                    <div class="ros-concern-body">{{ $slipConcern ?? '' }}</div>
                </div>
            </td>
            <td style="width:44%; vertical-align:top; border:0; padding:0;">
                <table class="ros-totals">
                    <tr><td class="lbl">Total Labor Cost</td><td class="ros-right">{!! $money($laborTotal) !!}</td></tr>
                    <tr><td class="lbl">Total Parts / Supplies</td><td class="ros-right">{!! $money($partsTotal) !!}</td></tr>
                    <tr><td class="lbl">Subtotal</td><td class="ros-right">{!! $money($subtotal) !!}</td></tr>
                    <tr><td class="lbl">Discount</td><td class="ros-right">{!! $money($discount) !!}</td></tr>
                    <tr class="grand"><td>GRAND TOTAL</td><td class="ros-right">{!! $money($grandTotal) !!}</td></tr>
                    @php $slipPaidList = $slipPayments->where('status', '!=', 'rejected'); @endphp
                    @if($slipPaidList->count())
                        <tr><td class="lbl" style="font-weight:normal;" colspan="2">&mdash; Down Payments &mdash;</td></tr>
                        @foreach($slipPaidList as $p)
                        <tr>
                            <td class="lbl" style="font-weight:normal;">{{ $p->type_label }} &middot; {{ ucfirst(str_replace('_', ' ', (string) ($p->payment_method ?: '&mdash;'))) }}{{ $p->created_at ? ' (' . $p->created_at->format('M d') . ')' : '' }}</td>
                            <td class="ros-right">-&#8369; {{ number_format((float) $p->amount, 2) }}</td>
                        </tr>
                        @endforeach
                        <tr class="grand" style="background:#0f766e;"><td>BALANCE</td><td class="ros-right">{!! $money($slipBalance) !!}</td></tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <div class="ros-terms">
        <h4>Terms &amp; Conditions</h4>
        <ol>
            <li>Ang repair order na ito ay awtorisasyon ng customer para sa mga gawaing nakalista sa itaas.</li>
            <li>Ang estimate ay maaaring magbago kapag may nadiskubre pang sira habang ginagawa ang trabaho; ipapaalam agad sa customer bago magpatuloy.</li>
            <li>Hindi kasama sa warranty ang mga pyesa/parts na hindi galing sa shop, at ang mga normal na pagkasira dahil sa gamit o kalumaan.</li>
            <li>Hindi sagot ng shop ang mga personal na gamit na naiwan sa loob ng sasakyan.</li>
            <li>Kailangang bayaran ang buong halaga bago ilabas ang sasakyan, maliban kung may naunang kasunduan.</li>
            <li>Ang mga sasakyang hindi makukuha sa loob ng 30 araw pagkatapos maipaalam ay maaaring singilin ng storage fee.</li>
        </ol>
    </div>

    <table class="ros-sign">
        <tr>
            <td><div class="ln">Customer / Authorized Representative</div></td>
            <td><div class="ln">Service Advisor</div></td>
            <td><div class="ln">Technician</div></td>
        </tr>
    </table>
</div>
