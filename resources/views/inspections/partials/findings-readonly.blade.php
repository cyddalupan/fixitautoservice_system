{{-- READ-ONLY Findings board.
     Used on the Repair Quotation Edit page: findings are created/edited on the
     Repair Order, so here we only DISPLAY what was saved there (no add/edit/delete). --}}
@php
    $allFindings = $inspection->inspectionFindings;
    $ungrouped = $allFindings->whereNull('group_id');
    $catColors = [
        'Engine' => 'linear-gradient(135deg,#1a237e,#283593)',
        'Brakes' => 'linear-gradient(135deg,#c62828,#d32f2f)',
        'Suspension' => 'linear-gradient(135deg,#e65100,#ef6c00)',
        'Electrical' => 'linear-gradient(135deg,#00695c,#00897b)',
        'Cooling' => 'linear-gradient(135deg,#4a148c,#6a1b9a)',
        'Transmission' => 'linear-gradient(135deg,#37474f,#455a64)',
        'Tires' => 'linear-gradient(135deg,#1b5e20,#2e7d32)',
        'Aircon' => 'linear-gradient(135deg,#01579b,#0277bd)',
        'Steering' => 'linear-gradient(135deg,#3e2723,#4e342e)',
        'Body / Exterior' => 'linear-gradient(135deg,#827717,#9e9d24)',
    ];
    $grand = 0;
    foreach ($allFindings as $f) { $grand += (float) $f->quantity * (float) ($f->unit_price ?? 0); }
    foreach ($inspection->findingGroups as $g) { $grand += (float) $g->labor_cost; }
@endphp

<div class="ro-findings">
    <style>
        .ro-findings .ro-grid{display:grid;grid-template-columns:110px minmax(0,1.7fr) minmax(0,1.2fr) 90px 58px 100px 106px;align-items:center;}
        .ro-findings .ro-headrow{padding:5px 8px;font-size:10.5px;letter-spacing:.4px;text-transform:uppercase;color:#94a3b8;font-weight:600;border-bottom:1px solid #e6ebf3;background:#f8fafc;}
        .ro-findings .ro-headrow span{padding:0 6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
        .ro-findings .ro-row{font-size:13px;line-height:1.5;border-bottom:1px solid #eef2f7;}
        .ro-findings .ro-cell{padding:6px 8px;min-width:0;display:flex;align-items:center;white-space:nowrap;overflow:hidden;}
        .ro-findings .ro-title{font-size:14px;font-weight:700;color:#1e293b;overflow:hidden;text-overflow:ellipsis;}
        .ro-findings .ro-remarks{font-size:12.5px;color:#607d8b;overflow:hidden;text-overflow:ellipsis;}
        .ro-findings .ro-total{font-size:14px;font-weight:700;color:#1a237e;}
        .ro-findings .ro-group{border:1px solid #dbe3f0;border-radius:10px;overflow:hidden;margin-bottom:14px;background:#fff;}
        .ro-findings .ro-group-head{display:flex;flex-wrap:wrap;align-items:center;gap:10px;padding:9px 12px;background:linear-gradient(135deg,#1a237e,#283593);color:#fff;}
        .ro-findings .ro-group-head.is-ungrouped{background:#f1f5f9;color:#334155;border-bottom:1px dashed #cbd5e1;}
        .ro-findings .ro-empty{color:#94a3b8;font-size:12.5px;padding:14px 8px;text-align:center;}
    </style>

    {{-- Ungrouped --}}
    @if($ungrouped->count())
    <div class="ro-group">
        <div class="ro-group-head is-ungrouped">
            <i class="fas fa-inbox"></i>
            <span class="fw-semibold">Ungrouped Findings</span>
            <span class="badge bg-secondary ms-1">{{ $ungrouped->count() }}</span>
        </div>
        <div class="ro-headrow ro-grid">
            <span>Category</span><span>Finding</span><span>Remarks</span>
            <span class="text-center">Urgency</span><span class="text-center">Qty</span>
            <span class="text-end">Price</span><span class="text-end">Total</span>
        </div>
        @foreach($ungrouped as $finding)
            @php
                $catBg = $catColors[$finding->category] ?? 'linear-gradient(135deg,#546e7a,#607d8b)';
                $urgBg = ['routine' => '#6c757d', 'soon' => '#0dcaf0', 'urgent' => '#ffc107', 'immediate' => '#dc3545'][$finding->estimated_urgency] ?? '#6c757d';
                $urgColor = in_array($finding->estimated_urgency, ['routine', 'soon']) ? '#fff' : '#000';
                $lineTotal = (float) $finding->quantity * (float) ($finding->unit_price ?? 0);
                $qtyStr = rtrim(rtrim(number_format((float) $finding->quantity, 2, '.', ''), '0'), '.');
            @endphp
            <div class="ro-row ro-grid">
                <span class="ro-cell"><span class="badge" style="background:{{ $catBg }};color:#fff;padding:2px 8px;border-radius:10px;font-size:10.5px;white-space:normal;">{{ $finding->category }}</span></span>
                <span class="ro-cell"><strong class="ro-title" title="{{ $finding->issue_title }}">{{ $finding->issue_title }}</strong></span>
                <span class="ro-cell"><span class="ro-remarks" title="{{ $finding->remarks }}{{ $finding->detailed_notes ? ' — '.$finding->detailed_notes : '' }}">{{ $finding->remarks ?: ($finding->part_name ?: '—') }}@if($finding->detailed_notes)<span class="finding-notes"> · {{ $finding->detailed_notes }}</span>@endif</span></span>
                <span class="ro-cell justify-content-center"><span class="badge" style="background:{{ $urgBg }};color:{{ $urgColor }};font-size:11px;padding:3px 9px;border-radius:10px;">{{ ucfirst($finding->estimated_urgency) }}</span></span>
                <span class="ro-cell justify-content-center">{{ $qtyStr }}</span>
                <span class="ro-cell justify-content-end">@if($finding->unit_price !== null)&#8369;{{ number_format((float) $finding->unit_price, 2) }}@else<span class="text-muted">—</span>@endif</span>
                <span class="ro-cell justify-content-end ro-total">&#8369;{{ number_format($lineTotal, 2) }}</span>
            </div>
        @endforeach
    </div>
    @endif

    {{-- Groups --}}
    @foreach($inspection->findingGroups as $group)
        @php
            $groupPartsTotal = 0;
            foreach ($group->findings as $gf) { $groupPartsTotal += (float) $gf->quantity * (float) ($gf->unit_price ?? 0); }
            $groupTotal = $groupPartsTotal + (float) $group->labor_cost;
        @endphp
        <div class="ro-group">
            <div class="ro-group-head">
                <i class="fas fa-layer-group"></i>
                <span class="fw-semibold">{{ $group->name }}</span>
                <span class="badge bg-light text-dark">{{ $group->findings->count() }} item(s)</span>
                <div class="ms-auto d-flex align-items-center gap-3 small">
                    <span><i class="fas fa-tools me-1"></i>Labor &#8369;{{ number_format((float) $group->labor_cost, 2) }}</span>
                    <span class="badge bg-light text-dark">Group total &#8369;{{ number_format($groupTotal, 2) }}</span>
                </div>
            </div>
            <div class="ro-headrow ro-grid">
                <span>Category</span><span>Finding</span><span>Remarks</span>
                <span class="text-center">Urgency</span><span class="text-center">Qty</span>
                <span class="text-end">Price</span><span class="text-end">Total</span>
            </div>
            @forelse($group->findings as $finding)
                @php
                    $catBg = $catColors[$finding->category] ?? 'linear-gradient(135deg,#546e7a,#607d8b)';
                    $urgBg = ['routine' => '#6c757d', 'soon' => '#0dcaf0', 'urgent' => '#ffc107', 'immediate' => '#dc3545'][$finding->estimated_urgency] ?? '#6c757d';
                    $urgColor = in_array($finding->estimated_urgency, ['routine', 'soon']) ? '#fff' : '#000';
                    $lineTotal = (float) $finding->quantity * (float) ($finding->unit_price ?? 0);
                    $qtyStr = rtrim(rtrim(number_format((float) $finding->quantity, 2, '.', ''), '0'), '.');
                @endphp
                <div class="ro-row ro-grid">
                    <span class="ro-cell"><span class="badge" style="background:{{ $catBg }};color:#fff;padding:2px 8px;border-radius:10px;font-size:10.5px;white-space:normal;">{{ $finding->category }}</span></span>
                    <span class="ro-cell"><strong class="ro-title" title="{{ $finding->issue_title }}">{{ $finding->issue_title }}</strong></span>
                    <span class="ro-cell"><span class="ro-remarks" title="{{ $finding->remarks }}{{ $finding->detailed_notes ? ' — '.$finding->detailed_notes : '' }}">{{ $finding->remarks ?: ($finding->part_name ?: '—') }}@if($finding->detailed_notes)<span class="finding-notes"> · {{ $finding->detailed_notes }}</span>@endif</span></span>
                    <span class="ro-cell justify-content-center"><span class="badge" style="background:{{ $urgBg }};color:{{ $urgColor }};font-size:11px;padding:3px 9px;border-radius:10px;">{{ ucfirst($finding->estimated_urgency) }}</span></span>
                    <span class="ro-cell justify-content-center">{{ $qtyStr }}</span>
                    <span class="ro-cell justify-content-end">@if($finding->unit_price !== null)&#8369;{{ number_format((float) $finding->unit_price, 2) }}@else<span class="text-muted">—</span>@endif</span>
                    <span class="ro-cell justify-content-end ro-total">&#8369;{{ number_format($lineTotal, 2) }}</span>
                </div>
            @empty
                <div class="ro-empty">No items in this group.</div>
            @endforelse
        </div>
    @endforeach

    @if($allFindings->count() === 0)
        <div class="ro-empty py-4">
            <i class="fas fa-clipboard-list fa-lg d-block mb-2 text-muted"></i>
            No findings saved on this Repair Order yet.
        </div>
    @endif

    <div class="d-flex justify-content-end align-items-center gap-2 mt-2">
        <span class="text-muted small">Grand total (parts + labor)</span>
        <span class="fw-bold" style="font-size:1.05rem;color:#1a237e;">&#8369;{{ number_format($grand, 2) }}</span>
    </div>
</div>
