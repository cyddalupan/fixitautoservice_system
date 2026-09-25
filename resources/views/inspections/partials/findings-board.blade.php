{{-- Shared Findings board (quick-add bar + grouped cards). Used by Repair Order show + edit pages. Source: extracted from inspections/show.blade.php --}}
{{-- $quotationMode = true (Repair Quotation page): the "add" bar stays available so users can
     add new quotation items. Per-finding delete is limited to items added on the quotation itself
     ($finding->is_quotation_added); Repair-Order findings are locked. --}}
@php
    $quotationMode = $quotationMode ?? false;
    // Locked RO (promoted from an approved Repair Quotation): the approved findings are frozen,
    // but the shop may still ADD a new finding discovered during the repair.
    $findingsLocked = $findingsLocked ?? false;
    // Which findings this board shows: 'all' | 'locked' | 'unlocked'.
    // Default follows the lock behaviour (on a locked RO, the Findings tab shows only
    // the during-repair items). The Repair Quotation page passes an explicit scope so a
    // during-repair re-quote shows only ITS findings, not the original quotation's.
    $findingScope = $findingScope ?? ($findingsLocked ? 'unlocked' : 'all');
    $findingInScope = function ($f) use ($findingScope, $inspection) {
        if ($findingScope === 'all') {
            return true;
        }
        $locked = $inspection->findingIsLocked($f);

        return $findingScope === 'locked' ? $locked : ! $locked;
    };
@endphp
@if($findingsLocked)
<div class="alert d-flex flex-wrap align-items-center gap-2 mb-3" style="background:#eff6ff;border:1px solid #93c5fd;color:#1e40af;">
    <i class="fas fa-lock"></i>
    <div class="flex-grow-1">
        <strong>Bagong findings ito (during repair).</strong>
        Nasa <strong>From Quotation</strong> tab ang approved na items mula sa quotation — doon naka-lock ang presyo. Dito mo ilalagay ang mga <strong>bagong makita</strong> sa sasakyan habang ginagawa.
    </div>
</div>
@endif
            <!-- Quick Add Bar -->
            <div class="findings-quick-add py-3 px-3 mb-3 rounded-3" style="background: linear-gradient(135deg, rgba(26,35,126,0.05) 0%, rgba(13,71,161,0.1) 100%); border: 1px solid rgba(26,35,126,0.15); position: sticky; top: 0; z-index: 10;">
                <div class="row g-2 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="small fw-semibold text-muted mb-1"><i class="fas fa-tag me-1"></i>Category</label>
                        <select id="quick-category" class="form-select form-select-sm">
                            <option value="Engine">Engine</option>
                            <option value="Brakes">Brakes</option>
                            <option value="Suspension">Suspension</option>
                            <option value="Electrical">Electrical</option>
                            <option value="Cooling">Cooling</option>
                            <option value="Transmission">Transmission</option>
                            <option value="Tires">Tires</option>
                            <option value="Aircon">Aircon</option>
                            <option value="Steering">Steering</option>
                            <option value="Body / Exterior">Body / Exterior</option>
                            <option value="Safety">Safety</option>
                            <option value="Maintenance">Maintenance</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="small fw-semibold text-muted mb-1"><i class="fas fa-cog me-1"></i>Parts</label>
                        <input type="text" id="quick-parts" class="form-control form-control-sm" placeholder="Part needed (e.g. Tie Rod End)" autocomplete="off">
                    </div>
                    <div class="col-12 col-md-2">
                        <label class="small fw-semibold text-muted mb-1"><i class="fas fa-circle-info me-1"></i>Remarks</label>
                        <input type="text" id="quick-remarks" class="form-control form-control-sm" placeholder="worn out / damage / leak" autocomplete="off">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="small fw-semibold text-muted mb-1"><i class="fas fa-clock me-1"></i>Urgency</label>
                        <div class="d-flex urgency-group">
                            <button type="button" class="btn btn-sm btn-outline-secondary urgency-btn" data-value="routine">Rtn</button>
                            <button type="button" class="btn btn-sm btn-outline-info urgency-btn active" data-value="soon">Soon</button>
                            <button type="button" class="btn btn-sm btn-outline-warning urgency-btn" data-value="urgent">Urg</button>
                            <button type="button" class="btn btn-sm btn-outline-danger urgency-btn" data-value="immediate">Imm</button>
                        </div>
                    </div>
                    <div class="col-3 col-md-1">
                        <label class="small fw-semibold text-muted mb-1"><i class="fas fa-hashtag me-1"></i>Qty</label>
                        <input type="number" id="quick-quantity" class="form-control form-control-sm" min="0" step="0.01" value="1">
                    </div>
                    <div class="col-3 col-md-1">
                        <label class="small fw-semibold text-muted mb-1"><i class="fas fa-money-bill me-1"></i>Price</label>
                        <input type="number" id="quick-price" class="form-control form-control-sm" min="0" step="0.01" placeholder="0.00">
                    </div>
                    <div class="col-6 col-md-1">
                        <button id="btn-quick-add" class="btn btn-primary btn-sm w-100" style="background: linear-gradient(135deg, #1a237e, #283593);" title="Add quotation item">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Findings Groups Board (drag a card into a group to share one labor cost) -->
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-2">
                <div class="small text-muted">
                    @if($findingsLocked)
                        <i class="fas fa-lock me-1"></i>Ang approved na items ay nasa <strong>From Quotation</strong> tab. Mga bagong finding dito — pwede mo pa ring i-group kung magkapareho ng labor.
                    @else
                        <i class="fas fa-hand-pointer me-1"></i>Drag a card into a group to share one labor cost.
                    @endif
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="createFindingGroup()">
                    <i class="fas fa-layer-group me-1"></i>New Group
                </button>
            </div>

            <div id="findings-board">
                @if($quotationMode)
                {{-- "Not Pursued" zone: items the customer did NOT push through with.
                     Placed at the TOP of the board. Dragging a card here separates it from the
                     quotation (excluded from totals). --}}
                @php $declinedFindings = $inspection->inspectionFindings->where('is_declined', true)->filter($findingInScope); @endphp
                <div class="finding-group mb-3 declined-zone" data-declined="1">
                    <div class="finding-group-head d-flex flex-wrap align-items-center gap-2 px-3 py-2"
                         style="background:#fef2f2;border:1px dashed #fca5a5;border-bottom:none;border-radius:10px 10px 0 0;color:#991b1b;">
                        <i class="fas fa-ban"></i>
                        <span class="fw-semibold">Not Pursued</span>
                        <span class="badge" style="background:#fee2e2;color:#991b1b;">Customer did not push through</span>
                        <span class="badge bg-danger ms-1 declined-count">{{ $declinedFindings->count() }}</span>
                        <span class="ms-auto small text-muted"><i class="fas fa-hand-pointer me-1"></i>Drag a card here to separate it from the quotation.</span>
                    </div>
                    <div class="finding-dropzone p-2" data-declined="1"
                         style="border:1px dashed #fca5a5;border-top:none;border-radius:0 0 10px 10px;min-height:56px;background:#fff;">
                        <div class="finding-headrow"><span></span><span>Category</span><span>Parts</span><span>Remarks</span><span class="text-center">Urgency</span><span class="text-center">Qty</span><span class="text-end">Price</span><span class="text-end">Total</span><span></span></div>
                        @forelse($declinedFindings as $finding)
                            @include('inspections.partials.finding-card', ['finding' => $finding, 'quotationMode' => $quotationMode])
                        @empty
                            <p class="text-muted small mb-0 py-2 text-center finding-empty-hint">{{ $quotationMode ? 'Drag quotation items here if the customer did not pursue them.' : 'Drag findings here if the customer did not pursue them.' }}</p>
                        @endforelse
                    </div>
                </div>
                @endif

                <!-- Ungrouped -->
                <div class="finding-group mb-3" data-group-id="">
                    <div class="finding-group-head d-flex flex-wrap align-items-center gap-2 px-3 py-2"
                         style="background:#f1f5f9;border:1px dashed #cbd5e1;border-radius:10px 10px 0 0;">
                        <span class="fw-semibold"><i class="fas fa-inbox me-1"></i>{{ $quotationMode ? 'Ungrouped Quotation Items' : ($findingsLocked ? 'New Findings' : 'Ungrouped Findings') }}</span>
                        <span class="badge bg-secondary group-count">0</span>
                    </div>
                    <div class="finding-dropzone p-2" data-group-id=""
                         style="border:1px dashed #cbd5e1;border-top:none;border-radius:0 0 10px 10px;min-height:56px;background:#fff;">
                        <div class="finding-headrow"><span></span><span>Category</span><span>Parts</span><span>Remarks</span><span class="text-center">Urgency</span><span class="text-center">Qty</span><span class="text-end">Price</span><span class="text-end">Total</span><span></span></div>
                        @php
                            // On a locked RO the approved items live in the "From Quotation" tab,
                            // so the board holds only the findings within this page's scope.
                            $allFindings = $inspection->inspectionFindings->filter($findingInScope);
                            $ungrouped = $allFindings->whereNull('group_id')->where('is_declined', false);
                        @endphp
                        @forelse($ungrouped as $finding)
                            @php $cardLocked = $findingsLocked && $inspection->findingIsLocked($finding); @endphp
                            @include('inspections.partials.finding-card', ['finding' => $finding, 'quotationMode' => $quotationMode, 'showLabor' => true, 'locked' => $cardLocked])
                        @empty
                            <p class="text-muted small mb-0 py-2 text-center finding-empty-hint">{{ $quotationMode ? 'No ungrouped quotation items.' : ($findingsLocked ? 'Wala pang bagong finding. Gamitin ang bar sa itaas kung may makita kang bago habang ginagawa.' : 'No ungrouped findings. Drag cards here or add with the bar above.') }}</p>
                        @endforelse
                    </div>
                </div>

                @foreach($inspection->findingGroups as $group)
                @php
                    // An "approved" group holds findings fixed from the Repair Quotation;
                    // those belong in the From Quotation tab, not on the Findings board.
                    // Skip it there — unless this page explicitly scopes to the locked
                    // findings (the Repair Quotation edit page), where they ARE the items.
                    $groupLocked = $findingsLocked && $findingScope !== 'locked' && $group->findings->contains(function ($f) use ($inspection) {
                        return $inspection->findingIsLocked($f);
                    });
                    $groupFindings = $group->findings->where('is_declined', false)->filter($findingInScope);
                @endphp
                @continue($groupLocked || $groupFindings->isEmpty())
                <div class="finding-group mb-3" data-group-id="{{ $group->id }}" data-auto-name="{{ $group->auto_name ? '1' : '0' }}" data-labor-cost="{{ (float) $group->labor_cost }}">
                    <div class="finding-group-head d-flex flex-wrap align-items-center gap-2 px-3 py-2"
                         style="background:linear-gradient(135deg,#1a237e,#283593);color:#fff;border-radius:10px 10px 0 0;">
                        <i class="fas fa-layer-group"></i>
                        @if($groupLocked)
                        <span class="fw-semibold">{{ $group->name }}</span>
                        <div class="ms-auto d-flex align-items-center gap-2">
                            <span class="small"><i class="fas fa-tools me-1"></i>Labor ₱{{ number_format((float) $group->labor_cost, 2) }}</span>
                            <span class="badge bg-light text-dark group-total-badge">₱0.00</span>
                            <span class="badge" style="background:rgba(255,255,255,.2);color:#fff;"><i class="fas fa-lock me-1"></i>Locked</span>
                        </div>
                        @else
                        <input type="text" class="form-control form-control-sm group-name-input" value="{{ $group->name }}"
                               style="max-width:170px;background:rgba(255,255,255,.15);border:none;color:#fff;"
                               onchange="manualGroupName({{ $group->id }}, this.value)" oninput="this.closest('.finding-group').dataset.autoName='0'">
                        <div class="ms-auto d-flex align-items-center gap-2">
                            <label class="small mb-0 text-white-50"><i class="fas fa-tools me-1"></i>Labor ₱</label>
                            <input type="number" min="0" step="0.01" class="form-control form-control-sm group-labor-input"
                                   value="{{ number_format((float) $group->labor_cost, 2, '.', '') }}" style="width:110px;"
                                   onchange="saveGroupField({{ $group->id }}, 'labor_cost', this.value)">
                            <span class="badge bg-light text-dark group-total-badge">₱0.00</span>
                            <button type="button" class="btn btn-sm btn-outline-light" onclick="deleteFindingGroup({{ $group->id }})" title="Delete group (keeps cards)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                        @endif
                    </div>
                    <div class="finding-dropzone p-2" data-group-id="{{ $group->id }}"
                         style="border:1px solid #dbe3f0;border-top:none;border-radius:0 0 10px 10px;min-height:56px;background:#fff;">
                        <div class="finding-headrow"><span></span><span>Category</span><span>Parts</span><span>Remarks</span><span class="text-center">Urgency</span><span class="text-center">Qty</span><span class="text-end">Price</span><span class="text-end">Total</span><span></span></div>
                        @forelse($groupFindings as $finding)
                            @php $cardLocked = $findingsLocked && $inspection->findingIsLocked($finding); @endphp
                            @include('inspections.partials.finding-card', ['finding' => $finding, 'quotationMode' => $quotationMode, 'locked' => $cardLocked])
                        @empty
                            <p class="text-muted small mb-0 py-2 text-center finding-empty-hint">Drop cards here to share this labor cost.</p>
                        @endforelse
                    </div>
                </div>
                @endforeach
            </div>
