{{-- Compact column-layout finding card (grouped findings board) --}}
@php
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
    $catBg = $catColors[$finding->category] ?? 'linear-gradient(135deg,#546e7a,#607d8b)';
    $sevBg = ['low' => '#0dcaf0', 'medium' => '#ffc107', 'high' => '#dc3545', 'critical' => '#212529'][$finding->severity] ?? '#6c757d';
    $urgBg = ['routine' => '#6c757d', 'soon' => '#0dcaf0', 'urgent' => '#ffc107', 'immediate' => '#dc3545'][$finding->estimated_urgency] ?? '#6c757d';
    $urgColor = in_array($finding->estimated_urgency, ['routine', 'soon']) ? '#fff' : '#000';
    $lineTotal = (float) $finding->quantity * (float) ($finding->unit_price ?? 0);
    $showLabor = $showLabor ?? false;
    // Ungrouped findings carry their own labor (estimated_cost); grouped ones share the group's labor.
    if ($showLabor) { $lineTotal += (float) ($finding->estimated_cost ?? 0); }
    $laborStr = ($finding->estimated_cost !== null && (float) $finding->estimated_cost > 0)
        ? number_format((float) $finding->estimated_cost, 2, '.', '') : '';
    $qtyStr = rtrim(rtrim(number_format((float) $finding->quantity, 2, '.', ''), '0'), '.');
@endphp
<div class="finding-card mb-1" data-id="{{ $finding->id }}"
     data-quotation-added="{{ $finding->is_quotation_added ? '1' : '0' }}"
     data-category="{{ $finding->category }}"
     data-issue-title="{{ $finding->issue_title }}"
     data-part-name="{{ $finding->part_name }}"
     data-remarks="{{ $finding->remarks }}"
     data-quantity="{{ (float) $finding->quantity }}"
     data-unit-price="{{ $finding->unit_price !== null ? (float) $finding->unit_price : '' }}"
     data-severity="{{ $finding->severity }}"
     data-urgency="{{ $finding->estimated_urgency }}"
     data-notes="{{ $finding->detailed_notes }}"
     data-action="{{ $finding->recommended_action }}"
     data-cost="{{ $finding->estimated_cost }}">
    <div class="card border-0 shadow-sm" style="background:#fff;">
        <div class="finding-grid">
            <span class="drag-handle text-muted" style="cursor:grab;padding:0 4px;" title="Drag to move"><i class="fas fa-grip-vertical"></i></span>
            <span class="fcell"><span class="badge category-badge" style="background:{{ $catBg }};color:#fff;padding:2px 8px;border-radius:10px;font-size:10.5px;white-space:normal;"><i class="fas fa-tag me-1"></i>{{ $finding->category }}</span></span>
            <span class="fcell fc-parts">
                <span class="sev-dot" title="{{ ucfirst($finding->severity) }}" style="display:inline-block;width:9px;height:9px;border-radius:50%;background:{{ $sevBg }};flex:0 0 auto;"></span>
                <strong class="finding-title" title="{{ $finding->issue_title }}">{{ $finding->issue_title }}</strong>
            </span>
            <span class="fcell"><span class="finding-remarks" title="{{ $finding->remarks }}{{ $finding->detailed_notes ? ' — '.$finding->detailed_notes : '' }}">{{ $finding->remarks ?: ($finding->part_name ?: '—') }}@if($finding->detailed_notes)<span class="finding-notes"> · {{ $finding->detailed_notes }}</span>@endif</span></span>
            <span class="fcell justify-content-center"><span class="badge urgency-badge" style="background:{{ $urgBg }};color:{{ $urgColor }};">{{ ucfirst($finding->estimated_urgency) }}</span></span>
            <span class="fcell justify-content-center finding-qty-wrap" onclick="inlineEditFinding({{ $finding->id }})" title="Tap to edit Qty / Price" style="cursor:pointer;">Qty <span class="finding-qty">{{ $qtyStr }}</span></span>
            <span class="fcell justify-content-end finding-price" onclick="inlineEditFinding({{ $finding->id }})" title="Tap to edit Qty / Price" style="cursor:pointer;">@if($finding->unit_price !== null)&#8369;{{ number_format((float) $finding->unit_price, 2) }}@else<span class="text-muted">—</span>@endif</span>
            <span class="fcell justify-content-end fw-bold finding-line-total">&#8369;{{ number_format($lineTotal, 2) }}</span>
            <span class="fcell justify-content-center gap-1 finding-actions">
                <span class="finding-action-btns d-inline-flex gap-1">
                    <button class="btn btn-sm btn-light border py-0 px-1" onclick="inlineEditFinding({{ $finding->id }})" title="Quick edit Qty / Price"><i class="fas fa-edit text-primary"></i></button>
                    @if(!($quotationMode ?? false) || $finding->is_quotation_added)
                    <button class="btn btn-sm btn-light border py-0 px-1" onclick="deleteFinding({{ $finding->id }})" title="Delete"><i class="fas fa-trash text-danger"></i></button>
                    @endif
                </span>
                <span class="badge finding-linked-badge" style="@if($finding->is_linked_to_estimate) background:#059669;color:#fff; @else display:none; @endif font-size:9px;"><i class="fas fa-link"></i></span>
            </span>
        </div>
        @if($showLabor)
        {{-- Ungrouped finding: each has its own labor price. --}}
        <div class="finding-labor-row d-flex align-items-center justify-content-end gap-2 px-3 pb-2 pt-1" style="border-top:1px dashed #e6ebf3;">
            <label class="text-muted mb-0" style="font-size:11px;"><i class="fas fa-tools me-1"></i>Labor &#8369;</label>
            <input type="number" min="0" step="0.01" inputmode="decimal"
                   class="form-control form-control-sm finding-labor-input" style="width:104px;padding:.08rem .35rem;height:auto;font-size:12.5px;"
                   value="{{ $laborStr }}" placeholder="0.00"
                   onchange="saveFindingCost({{ $finding->id }}, this.value)">
        </div>
        @endif
    </div>
</div>
