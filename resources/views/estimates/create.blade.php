@extends('layouts.app')

@section('title', 'Create Premium Estimate - Fix-It Auto Services')

@push('styles')
<style>
:root {--ep:#2563eb;--es:#059669;--ew:#d97706;--ed:#dc2626;--ebg:#f8fafc;--ec:#fff;--eb:#e2e8f0;--et:#1e293b;--em:#94a3b8;--er:12px;}
body{background:var(--ebg)}
.estimate-header{background:linear-gradient(135deg,#1e293b 0%,#334155 100%);border-radius:var(--er);padding:24px 28px;margin-bottom:24px;color:#fff;position:relative;overflow:hidden}
.estimate-header::before{content:'';position:absolute;top:-50%;right:-20%;width:400px;height:400px;background:radial-gradient(circle,rgba(37,99,235,.15) 0%,transparent 70%);border-radius:50%;pointer-events:none}
.estimate-header h1{font-size:1.5rem;font-weight:700;margin:0;position:relative;z-index:1}
.estimate-header p{color:#94a3b8;font-size:.875rem;margin:4px 0 0;position:relative;z-index:1}
.sc{background:var(--ec);border:1px solid var(--eb);border-radius:var(--er);overflow:hidden;margin-bottom:24px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.sc .ch{background:linear-gradient(135deg,#f1f5f9 0%,#e2e8f0 100%);border-bottom:1px solid var(--eb);padding:14px 20px;display:flex;align-items:center;gap:10px}
.sc .ch h6{margin:0;font-weight:600;color:var(--et);font-size:.875rem}
.sc .cb{padding:20px}
.sc .cn{font-size:1.1rem;font-weight:700;color:var(--et)}
.sc .ig{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:8px 20px;margin-top:12px}
.sc .ii{display:flex;flex-direction:column;gap:2px}
.sc .il{font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;color:var(--em);font-weight:600}
.sc .iv{font-size:.875rem;color:var(--et);font-weight:500}
.sn{display:flex;gap:4px;background:#f1f5f9;padding:4px;border-radius:10px;margin-bottom:24px;overflow-x:auto}
.sn .p{padding:8px 16px;border-radius:8px;border:none;background:transparent;color:#64748b;font-size:.8rem;font-weight:500;cursor:pointer;white-space:nowrap;transition:all .2s;display:flex;align-items:center;gap:6px}
.sn .p:hover{color:var(--et);background:rgba(255,255,255,.7)}
.sn .p.active{background:#fff;color:var(--ep);box-shadow:0 1px 3px rgba(0,0,0,.1)}
.fs{background:var(--ec);border:1px solid var(--eb);border-radius:var(--er);margin-bottom:16px;box-shadow:0 1px 2px rgba(0,0,0,.04)}
.fs .fh{padding:14px 20px;background:#fafbfc;border-bottom:1px solid var(--eb);display:flex;align-items:center;justify-content:space-between;cursor:pointer;user-select:none}
.fs .fh h6{margin:0;font-size:.85rem;font-weight:600;color:var(--et);display:flex;align-items:center;gap:8px}
.fs .fh h6 i{width:20px;color:var(--ep);text-align:center}
.fs .fb{padding:20px}
.fs.collapsed .fb{display:none}
.igh{display:grid;grid-template-columns:30px 1.5fr 100px 70px 100px 70px 70px 100px 30px;gap:8px;padding:8px 12px;background:#f8fafc;border-radius:8px;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--em);margin-bottom:4px}
.ir{display:grid;grid-template-columns:30px 1.5fr 100px 70px 100px 70px 70px 100px 30px;gap:8px;padding:6px 12px;align-items:center;border-bottom:1px solid #f1f5f9;transition:background .15s}
.ir:hover{background:#fafbfc}
.ir .dh{cursor:grab;color:var(--em);font-size:.85rem;text-align:center}
.ir .dh:active{cursor:grabbing}
.ir .rm{color:var(--ed);cursor:pointer;opacity:.4;transition:opacity .15s;text-align:center;border:none;background:none;font-size:1rem}
.ir .rm:hover{opacity:1}
.ir input,.ir select{border:1px solid #e2e8f0;border-radius:6px;padding:6px 8px;font-size:.8rem;background:#fff;transition:border-color .15s;width:100%}
.ir input:focus,.ir select:focus{border-color:var(--ep);outline:none;box-shadow:0 0 0 3px rgba(37,99,235,.1)}
.ir .is{font-weight:600;font-size:.85rem;color:var(--et);text-align:right}

/* Autosuggest styles */
.position-relative{position:relative!important}
.autocomplete-dropdown{position:absolute;top:100%;left:0;right:0;margin-top:2px;z-index:1050;display:none;max-height:300px;overflow-y:auto;background:#fff;border:1px solid #dee2e6;border-radius:0.375rem;box-shadow:0 0.5rem 1rem rgba(0,0,0,.15)}
.autocomplete-dropdown .list-group-item{border:none;border-radius:0;padding:0.5rem 1rem;cursor:pointer;font-size:.8rem}
.autocomplete-dropdown .list-group-item:hover{background:#f8f9fa}
.autocomplete-dropdown .list-group-item-secondary{background:#e9ecef;font-size:.75rem;padding:0.25rem 1rem;cursor:default;font-weight:600}
.autocomplete-dropdown .list-group-item-secondary:hover{background:#e9ecef}
.description-display{display:block;margin-top:0.25rem;color:#6c757d;font-style:italic;min-height:0;height:auto}.description-display:empty{display:none}
.ab{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border:2px dashed #cbd5e1;border-radius:8px;background:transparent;color:var(--em);font-size:.8rem;font-weight:500;cursor:pointer;transition:all .15s;margin-top:8px}
.ab:hover{border-color:var(--ep);color:var(--ep);background:rgba(37,99,235,.05)}
.tb{background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%);border:1px solid var(--eb);border-radius:var(--er);padding:20px;margin-top:16px}
.tb h6{font-size:.8rem;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:var(--em);margin-bottom:16px}
.tr{display:flex;justify-content:space-between;align-items:center;padding:6px 0;font-size:.875rem}
.tr .tl{color:#64748b}
.tr .tv{font-weight:600;color:var(--et)}
.tr.td{border-top:1px solid var(--eb);margin-top:4px;padding-top:10px}
.tr.gt{border-top:2px solid var(--ep);margin-top:8px;padding-top:12px}
.tr.gt .tl{font-size:1rem;font-weight:700;color:var(--et)}
.tr.gt .tv{font-size:1.2rem;font-weight:800;color:var(--ep)}
.hp{background:var(--ec);border:1px solid var(--eb);border-radius:var(--er);overflow:hidden}
.hp .hi{display:flex;align-items:center;justify-content:space-between;padding:10px 16px;border-bottom:1px solid #f1f5f9;transition:background .15s}
.hp .hi:hover{background:#fafbfc}
.hp .hi:last-child{border-bottom:none}
.hp .hn{font-weight:600;font-size:.85rem;color:var(--ep)}
.hp .hm{font-size:.75rem;color:var(--em)}
.sb{position:sticky;bottom:0;z-index:1050;background:rgba(255,255,255,.95);backdrop-filter:blur(12px);border-top:1px solid var(--eb);padding:14px 24px;display:flex;align-items:center;justify-content:space-between;gap:12px;margin:24px -24px -24px -24px;box-shadow:0 -4px 12px rgba(0,0,0,.05)}
.sb .si{font-size:.8rem;color:var(--em)}
.sb .bg{display:flex;gap:8px}
.sb .btn{display:inline-flex;align-items:center;gap:6px;padding:8px 18px;border-radius:8px;font-size:.8rem;font-weight:600;transition:all .15s}
@media(max-width:768px){.igh{display:none}.ir{grid-template-columns:1fr;gap:6px;padding:12px;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:8px;position:relative}.ir .dh{display:none}.ir .rm{position:absolute;top:8px;right:8px}.sc .ig{grid-template-columns:1fr}.sb{flex-direction:column}.sb .bg{width:100%}.sb .bg .btn{flex:1}}
</style>
@endpush

@section('content')
@include('partials.customer-process-assets')

<div class="container-fluid py-3">
<form method="POST" action="{{ route('estimates.store') }}" id="estimateForm" onsubmit="return serializeItems()">
    @csrf
    <input type="hidden" name="items_json" id="items_json">

    <div class="estimate-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1><i class="fas fa-file-invoice-dollar me-2"></i> New Estimate</h1>
                <p>Create a professional quotation for your customer</p>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-flex gap-2 justify-content-md-end" style="position:relative;z-index:1">
                    <span class="badge bg-dark bg-opacity-50 px-3 py-2 rounded-pill"><i class="far fa-calendar me-1"></i> {{ now()->format('M d, Y') }}</span>
                    <span class="badge bg-dark bg-opacity-50 px-3 py-2 rounded-pill" id="EstNumberDisplay"><i class="fas fa-hashtag me-1"></i> {{ old('estimate_number', 'EST-' . now()->format('Ymd') . '-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT)) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="sn">
        <button type="button" class="p active" onclick="scrollSec('section-customer')"><i class="fas fa-user"></i> Customer</button>
        <button type="button" class="p" onclick="scrollSec('section-items')"><i class="fas fa-list"></i> Items</button>
        <button type="button" class="p" onclick="scrollSec('section-details')"><i class="fas fa-cog"></i> Details</button>
        <button type="button" class="p" onclick="scrollSec('section-totals')"><i class="fas fa-calculator"></i> Totals</button>
        <button type="button" class="p" onclick="scrollSec('section-terms')"><i class="fas fa-file-contract"></i> Terms</button>
    </div>

    <div class="row">
        <div class="col-lg-8">

            {{-- CUSTOMER --}}
            <div class="fs" id="section-customer">
                <div class="fh" onclick="this.parentElement.classList.toggle('collapsed')">
                    <h6><i class="fas fa-user"></i> Customer &amp; Vehicle</h6>
                    <i class="fas fa-chevron-down" style="color:var(--em);font-size:.75rem"></i>
                </div>
                <div class="fb">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label style="font-size:.8rem;font-weight:600;margin-bottom:4px">Customer <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id" class="form-select form-select-sm" required onchange="loadCustomer(this.value)">
                                <option value="">Select Customer...</option>
                                @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ ($selectedCustomer && $selectedCustomer->id === $customer->id) ? 'selected' : '' }}
                                    data-phone="{{ $customer->phone ?? '' }}" data-email="{{ $customer->email ?? '' }}"
                                    data-address="{{ $customer->address ?? '' }}" data-balance="{{ $customer->balance ?? 0 }}"
                                    data-notes="{{ $customer->notes ?? '' }}">
                                    {{ $customer->first_name }} {{ $customer->last_name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label style="font-size:.8rem;font-weight:600;margin-bottom:4px">Vehicle <span class="text-danger">*</span></label>
                            <select name="vehicle_id" id="vehicle_id" class="form-select form-select-sm" required onchange="updateVeh()">
                                <option value="">-- Select Vehicle --</option>
                                @foreach($customerVehicles as $vehicle)
                                <option value="{{ $vehicle->id }}" {{ ($selectedVehicle && $selectedVehicle->id === $vehicle->id) ? 'selected' : '' }}
                                    data-make="{{ $vehicle->make }}" data-model="{{ $vehicle->model }}"
                                    data-year="{{ $vehicle->year }}" data-plate="{{ $vehicle->license_plate ?? '' }}"
                                    data-miles="{{ $vehicle->odometer ?? '' }}">
                                    {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }} @if($vehicle->license_plate)({{ $vehicle->license_plate }})@endif
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div id="custSumm" class="sc mt-3" style="display:none">
                        <div class="ch"><h6><i class="fas fa-id-card"></i> Customer Summary</h6><span class="badge bg-success rounded-pill" style="font-size:.65rem">Loaded</span></div>
                        <div class="cb">
                            <div class="d-flex align-items-start justify-content-between">
                                <div><div class="cn" id="sName">--</div><div style="font-size:.8rem;color:var(--em)" id="sEmail">--</div></div>
                                <div class="text-end"><div class="il">Balance</div><div style="font-size:1rem;font-weight:700;color:var(--ed)" id="sBal">&#8369;0.00</div></div>
                            </div>
                            <div class="ig">
                                <div class="ii"><span class="il">Phone</span><span class="iv" id="sPhone">--</span></div>
                                <div class="ii"><span class="il">Address</span><span class="iv" id="sAddr">--</span></div>
                                <div class="ii"><span class="il">Make / Model</span><span class="iv" id="sMM">--</span></div>
                                <div class="ii"><span class="il">Year</span><span class="iv" id="sYr">--</span></div>
                                <div class="ii"><span class="il">Plate #</span><span class="iv" id="sPlt">--</span></div>
                                <div class="ii"><span class="il">Notes</span><span class="iv" id="sNt">--</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- INSPECTION FINDINGS --}}
            <div class="fs" id="section-findings" style="display:none;">
                <div class="fh" onclick="this.parentElement.classList.toggle('collapsed')">
                    <h6><i class="fas fa-clipboard-list"></i> Inspection Findings Available</h6>
                    <span class="badge bg-warning text-dark rounded-pill" id="fcnt" style="font-size:.65rem">0 findings</span>
                </div>
                <div class="fb">
                    <div id="findingsC" class="mb-2"></div>
                    <button type="button" class="ab" id="importFindingsBtn" onclick="importSelectedFindings()"><i class="fas fa-file-import"></i> Import Selected to Items</button>
                </div>
            </div>

            {{-- ITEMS --}}
            <div class="fs" id="section-items">
                <div class="fh" onclick="this.parentElement.classList.toggle('collapsed')">
                    <h6><i class="fas fa-list"></i> Estimate Items</h6>
                    <span class="badge bg-primary rounded-pill" id="icnt" style="font-size:.65rem">0 items</span>
                </div>
                <div class="fb">
                    <div class="igh"><span></span><span>Description</span><span>Category</span><span>Qty</span><span>Price</span><span>Disc%</span><span>Tax%</span><span>Subtotal</span><span></span></div>
                    <div id="itemsC"></div>
                    <button type="button" class="ab" onclick="addItem();recalc()"><i class="fas fa-plus"></i> Add Item</button>
                </div>
            </div>

            {{-- DETAILS --}}
            <div class="fs" id="section-details">
                <div class="fh" onclick="this.parentElement.classList.toggle('collapsed')">
                    <h6><i class="fas fa-cog"></i> Details</h6><i class="fas fa-chevron-down" style="color:var(--em);font-size:.75rem"></i>
                </div>
                <div class="fb">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label style="font-size:.8rem;font-weight:600;margin-bottom:4px">Estimate #</label>
                            <input type="text" class="form-control form-control-sm" readonly value="{{ old('estimate_number', 'EST-' . now()->format('Ymd') . '-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT)) }}">
                            <input type="hidden" name="estimate_number" value="{{ old('estimate_number', 'EST-' . now()->format('Ymd') . '-' . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT)) }}">
                        </div>
                        <div class="col-md-4"><label style="font-size:.8rem;font-weight:600;margin-bottom:4px">Issue Date</label><input type="date" name="issue_date" class="form-control form-control-sm" value="{{ old('issue_date', now()->format('Y-m-d')) }}"></div>
                        <div class="col-md-4"><label style="font-size:.8rem;font-weight:600;margin-bottom:4px">Valid Until</label><input type="date" name="expiry_date" class="form-control form-control-sm" value="{{ old('expiry_date', now()->addDays(14)->format('Y-m-d')) }}"></div>
                        <div class="col-md-4">
                            @include('partials.service-type-selector', [
                                'selected' => old('service_type', $estimate->service_type ?? ''),
                                'name' => 'service_type',
                                'label' => 'SERVICE TYPE',
                                'required' => true,
                                'showIcons' => true,
                                'multiple' => true,
                                'placeholder' => 'Select Service Type',
                                'module' => 'estimates',
                            ])
                        </div>
                        <div class="col-md-4"><label style="font-size:.8rem;font-weight:600;margin-bottom:4px">Mileage (km)</label><input type="number" name="mileage" class="form-control form-control-sm" id="estMileage" min="0" value="{{ $selectedVehicle->odometer ?? '' }}"></div>
                        <div class="col-md-4"><label style="font-size:.8rem;font-weight:600;margin-bottom:4px">Service Advisor</label><select name="service_advisor_id" class="form-select form-select-sm"><option value="">-- Select --</option>@foreach($advisors ?? [] as $advisor)<option value="{{ $advisor->id }}">{{ $advisor->name }}</option>@endforeach</select></div>
                        <div class="col-12"><label style="font-size:.8rem;font-weight:600;margin-bottom:4px">Customer Notes</label><textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Customer concerns..."></textarea></div>
                        <div class="col-12"><label style="font-size:.8rem;font-weight:600;margin-bottom:4px">Internal Notes <span class="text-muted">(staff)</span></label><textarea name="internal_notes" class="form-control form-control-sm" rows="2" placeholder="Internal notes..."></textarea></div>
                    </div>
                </div>
            </div>

            {{-- TERMS --}}
            <div class="fs" id="section-terms">
                <div class="fh" onclick="this.parentElement.classList.toggle('collapsed')">
                    <h6><i class="fas fa-file-contract"></i> Terms</h6><i class="fas fa-chevron-down" style="color:var(--em);font-size:.75rem"></i>
                </div>
                <div class="fb">
                    <textarea name="terms" class="form-control form-control-sm" rows="6" style="font-size:.8rem">Payment Terms:
- Deposit required before work begins
- Balance due upon completion
- Valid for 14 days from issue date
- All prices include applicable taxes

Labor Warranty: 6 months or 10,000 km
Parts Warranty: As per manufacturer</textarea>
                </div>
            </div>

            {{-- STICKY BAR --}}
            <div class="sb">
                <div class="si"><i class="far fa-clock me-1"></i> <span id="saveStatus">Ready to save</span></div>
                <div class="bg">
                    <button type="button" class="btn btn-outline-secondary" onclick="addItem();recalc()"><i class="fas fa-plus"></i> Add Item</button>
                    <button type="button" class="btn btn-outline-primary" onclick="save('draft')"><i class="far fa-save"></i> Save Draft</button>
                    <button type="button" class="btn btn-primary" onclick="save('pending')"><i class="fas fa-paper-plane"></i> Send</button>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- TOTALS --}}
            <div class="fs" id="section-totals">
                <div class="fh"><h6><i class="fas fa-calculator"></i> Totals</h6></div>
                <div class="fb">
                    <div class="tb">
                        <h6>Estimate Totals</h6>
                        <div class="tr"><span class="tl">Subtotal</span><span class="tv" id="tSub">&#8369;0.00</span></div>
                        <div class="tr"><span class="tl">Parts &amp; Materials</span><span class="tv" id="tParts">&#8369;0.00</span></div>
                        <div class="tr"><span class="tl">Labor &amp; Services</span><span class="tv" id="tLabor">&#8369;0.00</span></div>
                        <div class="tr td"></div>
                        <div class="tr"><span class="tl">Discounts</span><span class="tv text-danger" id="tDisc">&#8369;0.00</span></div>
                        <div class="row g-2 mt-2 mb-2">
                            <div class="col-5"><select id="disc_type" name="discount_type" class="form-select form-select-sm" onchange="recalc()"><option value="">None</option><option value="percentage">%</option><option value="fixed">&#8369; Fixed</option></select></div>
                            <div class="col-7"><input type="number" name="discount_value" id="disc_val" class="form-control form-control-sm" min="0" step="0.01" value="0" placeholder="0" oninput="recalc()"></div>
                        </div>
                        <div class="tr"><span class="tl">Tax</span><span class="tv" id="tTax">&#8369;0.00</span></div>
                        <div class="tr td"></div>
                        <div class="tr gt"><span class="tl">Grand Total</span><span class="tv" id="tGrand">&#8369;0.00</span></div>
                        <hr>
                        <div class="tr"><span class="tl">Status</span><select name="status" id="statusDD" class="form-select form-select-sm" style="width:auto;display:inline-block;font-size:.75rem"><option value="draft">Draft</option><option value="pending">Pending</option><option value="approved">Approved</option></select></div>
                        <hr>
                        <div class="tr"><span class="tl">Deposit</span><span class="tv text-warning" id="tDep">&#8369;0.00</span></div>
                        <input type="number" name="deposit_required" id="dep_req" class="form-control form-control-sm mb-2" min="0" step="0.01" value="0" placeholder="Deposit amount" oninput="recalc()">
                        <div class="tr"><span class="tl">Balance</span><span class="tv" id="tBal">&#8369;0.00</span></div>
                    </div>
                </div>
            </div>

            {{-- HISTORY --}}
            @if(isset($customerHistory) && $customerHistory->count() > 0)
            <div class="hp mt-3">
                <div class="fh"><h6><i class="fas fa-history"></i> Previous</h6><span class="badge bg-secondary rounded-pill" style="font-size:.65rem">{{ $customerHistory->count() }}</span></div>
                <div>
                    @foreach($customerHistory as $hist)
                    <div class="hi">
                        <div><div class="hn">#{{ $hist->estimate_number }}</div><div class="hm">{{ $hist->created_at->format('M d, Y') }} <span class="badge {{ $hist->status_badge_class }}">{{ $hist->status_label }}</span></div></div>
                        <div class="fw-bold" style="font-size:.85rem">&#8369;{{ number_format($hist->total_amount, 2) }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</form>
</div>
@endsection

@push('scripts')
<script>
const FMT = new Intl.NumberFormat('en-PH',{style:'currency',currency:'PHP',minimumFractionDigits:2});
let idx = 0;
function fmt(n){return FMT.format(n)}
function scrollSec(id){document.getElementById(id).scrollIntoView({behavior:'smooth',block:'start'})}
function loadCustomer(id){
    var s=document.getElementById('custSumm');
    if(!id){s.style.display='none';return}
    var o=document.querySelector('#customer_id option[value="'+id+'"]');
    if(!o)return;
    s.style.display='block';
    document.getElementById('sName').textContent=o.textContent;
    document.getElementById('sPhone').textContent=o.dataset.phone||'--';
    document.getElementById('sEmail').textContent=o.dataset.email||'--';
    document.getElementById('sAddr').textContent=o.dataset.address||'--';
    document.getElementById('sNt').textContent=o.dataset.notes||'--';
    var b=parseFloat(o.dataset.balance)||0;
    document.getElementById('sBal').innerHTML=fmt(b);
    document.getElementById('sBal').style.color=b>0?'var(--ed)':'var(--es)';
    loadInspectionFindings(id);
}

// ====================== INSPECTION FINDINGS ======================
var inspectionFindings = [];
var selectedFindings = {};

function loadInspectionFindings(customerId) {
    var section = document.getElementById('section-findings');
    var container = document.getElementById('findingsC');
    container.innerHTML = '<div class="text-center py-2"><span class="spinner-border spinner-border-sm me-2"></span>Loading findings...</div>';
    section.style.display = 'block';

    fetch('/inspections/by-customer/' + customerId + '/findings')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            inspectionFindings = data.findings || [];
            renderFindings();
        })
        .catch(function(err) {
            container.innerHTML = '<div class="text-muted small py-2">No inspection findings available.</div>';
            document.getElementById('fcnt').textContent = '0 findings';
        });
}

function renderFindings() {
    var container = document.getElementById('findingsC');
    var countEl = document.getElementById('fcnt');

    if (!inspectionFindings.length) {
        container.innerHTML = '<div class="text-muted small py-2">No inspection findings found for this customer.</div>';
        countEl.textContent = '0 findings';
        return;
    }

    countEl.textContent = inspectionFindings.length + ' findings';
    container.innerHTML = '';

    inspectionFindings.forEach(function(f) {
        var severityBadge = f.severity === 'low' ? 'bg-info' : f.severity === 'medium' ? 'bg-warning text-dark' : f.severity === 'high' ? 'bg-danger' : 'bg-dark';
        var urgencyBadge = f.estimated_urgency === 'routine' ? 'bg-secondary' : f.estimated_urgency === 'soon' ? 'bg-info' : f.estimated_urgency === 'urgent' ? 'bg-warning text-dark' : 'bg-danger';
        var costStr = f.estimated_cost !== null && f.estimated_cost !== undefined ? '₱' + parseFloat(f.estimated_cost).toFixed(2) : '-';
        var fid = 'f_' + f.id;

        var row = document.createElement('div');
        row.className = 'finding-row d-flex align-items-center p-2 mb-2 rounded-3';
        row.style.cssText = 'background:#f8f9fa;border:1px solid #e2e8f0;transition:all .15s;cursor:pointer;';
        row.dataset.findingId = f.id;
        row.innerHTML = '<div class="form-check me-2">'
            + '<input class="form-check-input finding-checkbox" type="checkbox" id="' + fid + '" value="' + f.id + '">'
            + '</div>'
            + '<div class="flex-grow-1">'
            + '<div class="small fw-semibold">' + f.issue_title + '</div>'
            + '<div class="d-flex flex-wrap gap-1 mt-1">'
            + '<span class="badge bg-secondary-subtle text-secondary" style="font-size:9px;">' + (f.category || 'Other') + '</span>'
            + '<span class="badge ' + severityBadge + '" style="font-size:9px;">' + ucfirst(f.severity) + '</span>'
            + '<span class="badge ' + urgencyBadge + '" style="font-size:9px;">' + ucfirst(f.estimated_urgency) + '</span>'
            + '<span class="fw-semibold" style="font-size:11px;color:#1a237e;">' + costStr + '</span>'
            + '</div>'
            + '</div>';

        row.addEventListener('click', function(e) {
            if (e.target.type !== 'checkbox') {
                var cb = this.querySelector('.finding-checkbox');
                cb.checked = !cb.checked;
                this.style.background = cb.checked ? '#e8f5e9' : '#f8f9fa';
                this.style.borderColor = cb.checked ? '#4caf50' : '#e2e8f0';
            }
        });

        container.appendChild(row);
    });
}

function importSelectedFindings() {
    var checkboxes = document.querySelectorAll('.finding-checkbox:checked');
    if (!checkboxes.length) {
        alert('Select at least one finding to import.');
        return;
    }

    var count = 0;
    checkboxes.forEach(function(cb) {
        var finding = inspectionFindings.find(function(f) { return f.id == cb.value; });
        if (!finding) return;
        addItem({
            desc: finding.issue_title,
            unit_price: finding.estimated_cost || 0,
            category: 'service',
            quantity: 1
        });
        count++;
        // Mark as imported
        cb.checked = false;
        cb.closest('.finding-row').style.background = '#e8f5e9';
        cb.closest('.finding-row').style.borderColor = '#4caf50';
        cb.closest('.finding-row').style.opacity = '0.6';
        cb.closest('.finding-row').querySelector('.form-check').innerHTML = '<i class="fas fa-check text-success"></i>';
    });

    // Scroll to items section
    setTimeout(function() {
        document.getElementById('section-items').scrollIntoView({behavior:'smooth', block:'start'});
    }, 300);
}

function ucfirst(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1);
}
function updateVeh(){
    var v=document.getElementById('vehicle_id');
    var o=v.options[v.selectedIndex];
    if(!o||!o.value)return;
    document.getElementById('sMM').textContent=(o.dataset.make||'')+' '+(o.dataset.model||'');
    document.getElementById('sYr').textContent=o.dataset.year||'--';
    document.getElementById('sPlt').textContent=o.dataset.plate||'--';
    if(o.dataset.miles) document.getElementById('estMileage').value=o.dataset.miles;
}
function addItem(d){
    d=d||{};var i=++idx;
    var c=d.category||'parts';
    var h='<div class="ir" data-idx="'+i+'">'+
        '<div class="dh"><i class="fas fa-grip-lines"></i></div>'+
        '<div class="position-relative"><input type="text" name="it['+i+'][desc]" placeholder="Search inventory or service..." value="'+(d.item_name||d.desc||'')+'" autocomplete="off" onfocus="showSuggestions(this)" oninput="showSuggestions(this)" onkeydown="suggestKeyDown(event,this)"><input type="hidden" name="it['+i+'][inventory_id]" class="inv-id-input" value="'+(d.inventory_id||'')+'"><div class="description-display"></div><div class="autocomplete-dropdown" style="display:none"><div class="list-group"></div></div></div>'+
        '<div><select name="it['+i+'][cat]" onchange="recalc()">'+
            '<option value="parts"'+(c==='parts'?' selected':'')+'>Parts</option>'+
            '<option value="labor"'+(c==='labor'?' selected':'')+'>Labor</option>'+
            '<option value="service"'+(c==='service'?' selected':'')+'>Service</option>'+
            '<option value="materials"'+(c==='materials'?' selected':'')+'>Materials</option>'+
            '<option value="other"'+(c==='other'?' selected':'')+'>Other</option>'+
        '</select></div>'+
        '<div><input type="number" name="it['+i+'][qty]" min="0.01" step="0.01" value="'+(d.quantity||1)+'" oninput="recalc()"></div>'+
        '<div><input type="number" name="it['+i+'][price]" min="0" step="0.01" value="'+(d.unit_price||0)+'" oninput="recalc()"></div>'+
        '<div><input type="number" name="it['+i+'][disc]" min="0" step="0.01" value="'+(d.discount||0)+'" oninput="recalc()" placeholder="0"></div>'+
        '<div><input type="number" name="it['+i+'][tax]" min="0" max="100" step="0.01" value="'+(d.tax_rate||0)+'" oninput="recalc()" placeholder="0"></div>'+
        '<div class="is" id="s'+i+'">&#8369;0.00</div>'+
        '<div><button type="button" class="rm" onclick="rmItem(this)"><i class="fas fa-times"></i></button></div>'+
    '</div>';
    document.getElementById('itemsC').insertAdjacentHTML('beforeend',h);
    recalc();
}
function rmItem(b){
    if(document.querySelectorAll('.ir').length<=1){alert('Need at least one item');return}
    b.closest('.ir').remove();recalc()
}
function recalc(){
    var sub=0,pt=0,lt=0,dt=0,tx=0;
    var rs=document.querySelectorAll('.ir');
    document.getElementById('icnt').textContent=rs.length+' item'+(rs.length!==1?'s':'');
    rs.forEach(function(r){
        var q=parseFloat(r.querySelector('[name*="[qty]"]').value)||0;
        var p=parseFloat(r.querySelector('[name*="[price]"]').value)||0;
        var d=parseFloat(r.querySelector('[name*="[disc]"]').value)||0;
        var t=parseFloat(r.querySelector('[name*="[tax]"]').value)||0;
        var c=r.querySelector('[name*="[cat]"]').value;
        var l=q*p, da=d>0?l*(Math.min(d,100)/100):0, ad=l-da, ta=t>0?ad*(t/100):0, is_=ad+ta;
        sub+=ad;dt+=da;tx+=ta;
        if(c==='parts'||c==='materials')pt+=is_;else lt+=is_;
        r.querySelector('.is').textContent=fmt(is_);
    });
    var dt2=document.getElementById('disc_type').value;
    var dv=parseFloat(document.getElementById('disc_val').value)||0;
    var eda=0;
    if(dt2==='percentage'&&dv>0)eda=sub*(Math.min(dv,100)/100);
    else if(dt2==='fixed'&&dv>0)eda=Math.min(dv,sub);
    var total=sub-eda+tx;
    document.getElementById('tSub').textContent=fmt(sub);
    document.getElementById('tParts').textContent=fmt(pt);
    document.getElementById('tLabor').textContent=fmt(lt);
    document.getElementById('tDisc').textContent=fmt(dt+eda);
    document.getElementById('tTax').textContent=fmt(tx);
    document.getElementById('tGrand').textContent=fmt(total);
    var dep=parseFloat(document.getElementById('dep_req').value)||0;
    document.getElementById('tDep').textContent=fmt(dep);
    document.getElementById('tBal').textContent=fmt(Math.max(0,total-dep));
}

// Inventory items for autosuggest
const inventoryItems = @json($inventoryItemsJson);
window.inventoryItems = inventoryItems;
const commonServices = [
    {id:'oil_change',name:'Oil Change',desc:'Full synthetic oil change with filter',price:89.99},
    {id:'tire_rotation',name:'Tire Rotation',desc:'Rotate all four tires',price:39.99},
    {id:'brake_inspection',name:'Brake Inspection',desc:'Complete brake system inspection',price:49.99},
    {id:'alignment',name:'Wheel Alignment',desc:'Four-wheel alignment',price:129.99},
    {id:'battery_test',name:'Battery Test & Replace',desc:'Complete battery and charging system test',price:29.99},
    {id:'ac_service',name:'A/C Service',desc:'A/C system recharge and leak test',price:149.99},
    {id:'trans_service',name:'Transmission Service',desc:'Automatic transmission fluid change',price:199.99},
    {id:'coolant_flush',name:'Coolant Flush',desc:'Complete cooling system flush and refill',price:89.99},
    {id:'spark_plugs',name:'Spark Plugs Replacement',desc:'Replace all spark plugs',price:159.99},
    {id:'air_filter',name:'Air Filter Replacement',desc:'Replace engine air filter',price:29.99},
];

function showSuggestions(inp){
    var row = inp.closest('.ir');
    var dd = row.querySelector('.autocomplete-dropdown');
    var ul = dd.querySelector('.list-group');
    var st = inp.value.toLowerCase().trim();
    ul.innerHTML='';
    var fi = (window.inventoryItems||[]).filter(function(it){
        return st.length<2 || (it.name||'').toLowerCase().includes(st)||(it.part_number||'').toLowerCase().includes(st);
    });
    var fs = commonServices.filter(function(s){
        return st.length<2 || s.name.toLowerCase().includes(st)||s.desc.toLowerCase().includes(st);
    });
    if(st.length<2&&!fi.length&&!fs.length){dd.style.display='none';return;}
    if(fi.length){
        var h1=document.createElement('div');
        h1.className='list-group-item list-group-item-secondary';h1.textContent='Inventory Items';
        ul.appendChild(h1);
        fi.forEach(function(it){
            var o=document.createElement('button');
            o.type='button';o.className='list-group-item list-group-item-action';
            o.innerHTML='<div class="d-flex justify-content-between"><div><strong>'+(it.name||'')+'</strong><br><small class="text-muted">'+(it.part_number||'')+'</small></div><div class="text-end"><span class="badge bg-success">\u20B1'+(parseFloat(it.retail_price||0)).toFixed(2)+'</span><br><small class="text-muted">Stock: '+(it.quantity||0)+'</small></div></div>';
            o.addEventListener('click',function(){selItem(row,it);});
            ul.appendChild(o);
        });
    }
    if(fs.length){
        var h2=document.createElement('div');
        h2.className='list-group-item list-group-item-secondary';h2.textContent='Common Services';
        ul.appendChild(h2);
        fs.forEach(function(s){
            var o=document.createElement('button');
            o.type='button';o.className='list-group-item list-group-item-action';
            o.innerHTML='<div class="d-flex justify-content-between"><div><strong>'+s.name+'</strong><br><small class="text-muted">'+s.desc+'</small></div><div class="text-end"><span class="badge bg-primary">\u20B1'+s.price.toFixed(2)+'</span></div></div>';
            o.addEventListener('click',function(){selService(row,s);});
            ul.appendChild(o);
        });
    }
    dd.style.display=(fi.length||fs.length)?'block':'none';
}
function selItem(row,it){
    row.querySelector('[name*="[desc]"]').value=it.name||'';
    row.querySelector('.inv-id-input').value=it.id||'';
    row.querySelector('.description-display').textContent=it.description||'';
    var pp = row.querySelector('[name*="[price]"]');
    if(pp) pp.value=parseFloat(it.retail_price||0).toFixed(2);
    row.querySelector('.autocomplete-dropdown').style.display='none';
    recalc();
}
function selService(row,s){
    row.querySelector('[name*="[desc]"]').value=s.name;
    row.querySelector('.inv-id-input').value='';
    row.querySelector('.description-display').textContent=s.desc;
    var pp = row.querySelector('[name*="[price]"]');
    if(pp) pp.value=s.price.toFixed(2);
    row.querySelector('.autocomplete-dropdown').style.display='none';
    recalc();
}
function suggestKeyDown(e,inp){
    if(e.key==='Escape'){inp.closest('.ir').querySelector('.autocomplete-dropdown').style.display='none';}
}
// Close autocomplete on outside click
document.addEventListener('click',function(e){
    document.querySelectorAll('.autocomplete-dropdown').forEach(function(dd){
        if(!e.target.closest('.position-relative'))dd.style.display='none';
    });
});

function serializeItems(){
    var items=[];
    document.querySelectorAll('.ir').forEach(function(r,i){
        items.push({
            description: r.querySelector('[name*="[desc]"]').value,
            category: r.querySelector('[name*="[cat]"]').value,
            quantity: parseFloat(r.querySelector('[name*="[qty]"]').value)||0,
            unit_price: parseFloat(r.querySelector('[name*="[price]"]').value)||0,
            discount: parseFloat(r.querySelector('[name*="[disc]"]').value)||0,
            tax_rate: parseFloat(r.querySelector('[name*="[tax]"]').value)||0,
            sort_order: i
        });
    });
    document.getElementById('items_json').value=JSON.stringify(items);
    document.getElementById('saveStatus').textContent='Saving...';
    return true;
}
function save(s){
    document.getElementById('statusDD').value=s;
    document.getElementById('estimateForm').submit();
}
document.addEventListener('DOMContentLoaded',function(){
    var cid=document.getElementById('customer_id');
    if(cid.value)loadCustomer(cid.value);
    setTimeout(function(){
        if(document.querySelectorAll('.ir').length===0)addItem();
        recalc();
    },100);
});
</script>
@endpush
