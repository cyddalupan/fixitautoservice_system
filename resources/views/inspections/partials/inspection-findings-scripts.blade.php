{{-- Shared Findings/Quotation scripts (include inside @push('scripts')). Needs $inspection. --}}
<script src="/js/Sortable.min.js"></script>
<script>
// ====================== ISSUE LIBRARY ======================
const issueLibrary = {
    'Engine':[
        {title:'Engine Oil Leak',action:'Inspect and repair oil leak source',avgCost:150,severity:'medium',urgency:'urgent'},
        {title:'Engine Misfire',action:'Diagnose and repair misfire',avgCost:200,severity:'high',urgency:'urgent'},
        {title:'Engine Overheating',action:'Check cooling system, thermostat, water pump',avgCost:300,severity:'critical',urgency:'immediate'},
        {title:'Timing Belt/Chain Noise',action:'Inspect and replace timing belt/chain',avgCost:500,severity:'medium',urgency:'soon'},
        {title:'Check Engine Light On',action:'Run diagnostic scan',avgCost:85,severity:'medium',urgency:'urgent'},
        {title:'Rough Idle',action:'Clean throttle body, check spark plugs',avgCost:120,severity:'low',urgency:'soon'},
        {title:'Knocking Noise',action:'Inspect internal engine components',avgCost:250,severity:'high',urgency:'urgent'},
        {title:'Low Compression',action:'Perform compression test',avgCost:150,severity:'high',urgency:'soon'},
        {title:'Valve Cover Gasket Leak',action:'Replace valve cover gasket',avgCost:180,severity:'medium',urgency:'routine'},
        {title:'Oil Sludge Build-up',action:'Perform engine flush',avgCost:100,severity:'low',urgency:'routine'}
    ],
    'Brakes':[
        {title:'Brake Pads Worn',action:'Replace brake pads',avgCost:180,severity:'high',urgency:'urgent'},
        {title:'Brake Rotors Warped',action:'Resurface or replace rotors',avgCost:300,severity:'high',urgency:'urgent'},
        {title:'Brake Fluid Low',action:'Check for leaks, top up fluid',avgCost:50,severity:'medium',urgency:'soon'},
        {title:'Brake Noise (Squealing)',action:'Inspect pads, shims, lubricate',avgCost:100,severity:'low',urgency:'routine'},
        {title:'Brake Pedal Soft/Spongy',action:'Bleed brake system',avgCost:80,severity:'critical',urgency:'immediate'},
        {title:'Parking Brake Issues',action:'Adjust parking brake cable',avgCost:90,severity:'low',urgency:'routine'},
        {title:'ABS Light On',action:'Diagnose ABS system',avgCost:120,severity:'medium',urgency:'soon'},
        {title:'Brake Line Leak',action:'Replace brake line',avgCost:250,severity:'critical',urgency:'immediate'},
        {title:'Master Cylinder Failure',action:'Replace master cylinder',avgCost:350,severity:'critical',urgency:'immediate'},
        {title:'Calipers Sticking',action:'Rebuild or replace caliper',avgCost:280,severity:'high',urgency:'urgent'}
    ],
    'Suspension':[
        {title:'Suspension Noise (Clunking)',action:'Inspect bushings, struts, links',avgCost:150,severity:'medium',urgency:'soon'},
        {title:'Shock Absorber Leaking',action:'Replace shock absorber',avgCost:350,severity:'medium',urgency:'soon'},
        {title:'Tie Rod Loose',action:'Replace tie rod end',avgCost:180,severity:'high',urgency:'urgent'},
        {title:'Ball Joint Worn',action:'Replace ball joint',avgCost:250,severity:'high',urgency:'urgent'},
        {title:'Control Arm Bushing Worn',action:'Replace control arm bushing',avgCost:200,severity:'medium',urgency:'soon'},
        {title:'Sway Bar Link Broken',action:'Replace sway bar link',avgCost:120,severity:'medium',urgency:'soon'},
        {title:'Vehicle Pulling to One Side',action:'Check alignment, suspension components',avgCost:100,severity:'medium',urgency:'soon'},
        {title:'Uneven Tire Wear',action:'Check alignment, rotate tires',avgCost:80,severity:'low',urgency:'routine'},
        {title:'Strut Mount Noise',action:'Replace strut mount',avgCost:200,severity:'medium',urgency:'routine'},
        {title:'Lowering Springs Sagged',action:'Replace springs',avgCost:400,severity:'medium',urgency:'soon'}
    ],
    'Electrical':[
        {title:'Battery Weak/Dead',action:'Test battery, replace if needed',avgCost:150,severity:'medium',urgency:'urgent'},
        {title:'Alternator Not Charging',action:'Test and replace alternator',avgCost:400,severity:'high',urgency:'urgent'},
        {title:'Starter Not Engaging',action:'Test and replace starter',avgCost:350,severity:'high',urgency:'urgent'},
        {title:'Headlight Not Working',action:'Replace bulb/assembly',avgCost:80,severity:'low',urgency:'routine'},
        {title:'Turn Signal Malfunction',action:'Diagnose and repair signal circuit',avgCost:100,severity:'low',urgency:'routine'},
        {title:'Power Window Not Working',action:'Check switch, motor, regulator',avgCost:200,severity:'low',urgency:'routine'},
        {title:'Wiring Harness Damage',action:'Repair wiring harness',avgCost:250,severity:'medium',urgency:'soon'},
        {title:'Fuse Blown Repeatedly',action:'Trace short circuit',avgCost:120,severity:'medium',urgency:'soon'},
        {title:'Central Locking Not Working',action:'Diagnose door lock system',avgCost:150,severity:'low',urgency:'routine'},
        {title:'Battery Corrosion',action:'Clean terminals, replace clamps',avgCost:40,severity:'low',urgency:'routine'}
    ],
    'Cooling':[
        {title:'Coolant Leak',action:'Pressure test, repair leak source',avgCost:200,severity:'high',urgency:'urgent'},
        {title:'Radiator Clogged/Damaged',action:'Flush or replace radiator',avgCost:350,severity:'high',urgency:'urgent'},
        {title:'Thermostat Stuck',action:'Replace thermostat',avgCost:150,severity:'medium',urgency:'soon'},
        {title:'Coolant Fan Not Working',action:'Replace fan motor/module',avgCost:250,severity:'high',urgency:'urgent'},
        {title:'Water Pump Leaking',action:'Replace water pump',avgCost:400,severity:'high',urgency:'urgent'},
        {title:'Heater Not Working',action:'Check heater core, coolant level',avgCost:180,severity:'low',urgency:'routine'},
        {title:'Coolant Contaminated',action:'Flush cooling system',avgCost:100,severity:'medium',urgency:'routine'},
        {title:'Hose Cracked/Bulging',action:'Replace radiator hose',avgCost:80,severity:'medium',urgency:'soon'},
        {title:'Reservoir Tank Leaking',action:'Replace coolant reservoir',avgCost:60,severity:'low',urgency:'routine'},
        {title:'Thermal Switch Malfunction',action:'Replace thermal switch',avgCost:120,severity:'medium',urgency:'soon'}
    ],
    'Transmission':[
        {title:'Transmission Fluid Leak',action:'Inspect and repair leak',avgCost:250,severity:'medium',urgency:'soon'},
        {title:'Transmission Slipping',action:'Diagnose transmission',avgCost:300,severity:'high',urgency:'urgent'},
        {title:'Hard Shifting',action:'Check fluid, adjust linkage',avgCost:150,severity:'medium',urgency:'soon'},
        {title:'Transmission Noises',action:'Inspect transmission internals',avgCost:200,severity:'medium',urgency:'soon'},
        {title:'Clutch Slipping',action:'Replace clutch kit',avgCost:600,severity:'high',urgency:'urgent'},
        {title:'Clutch Pedal Hard',action:'Inspect clutch cable/hydraulics',avgCost:150,severity:'medium',urgency:'routine'},
        {title:'Transmission Mount Worn',action:'Replace transmission mount',avgCost:180,severity:'low',urgency:'routine'},
        {title:'CV Axle Boot Torn',action:'Replace CV axle/boot',avgCost:250,severity:'medium',urgency:'soon'},
        {title:'Differential Noise',action:'Check differential fluid',avgCost:100,severity:'low',urgency:'routine'},
        {title:'Gear Grinding',action:'Check synchros, fluid',avgCost:200,severity:'high',urgency:'urgent'}
    ],
    'Tires':[
        {title:'Tire Pressure Low',action:'Inflate to proper pressure',avgCost:0,severity:'low',urgency:'routine'},
        {title:'Tread Depth Below Safe',action:'Replace tires',avgCost:400,severity:'high',urgency:'urgent'},
        {title:'Tire Sidewall Damage',action:'Replace damaged tire',avgCost:150,severity:'high',urgency:'urgent'},
        {title:'Tire Puncture/Cut',action:'Repair puncture',avgCost:30,severity:'medium',urgency:'routine'},
        {title:'Tire Cupping Wear',action:'Alignment check, replace tire',avgCost:150,severity:'medium',urgency:'soon'},
        {title:'Valve Stem Leaking',action:'Replace valve stem',avgCost:15,severity:'low',urgency:'routine'},
        {title:'TPMS Light On',action:'Diagnose TPMS system',avgCost:60,severity:'low',urgency:'routine'},
        {title:'Wheel Bearing Noise',action:'Replace wheel bearing',avgCost:300,severity:'medium',urgency:'soon'},
        {title:'Wheel Balance Off',action:'Balance wheels',avgCost:60,severity:'low',urgency:'routine'},
        {title:'Spare Tire Missing',action:'Replace spare tire',avgCost:100,severity:'low',urgency:'routine'}
    ],
    'Aircon':[
        {title:'A/C Not Cooling',action:'Check refrigerant level, recharge',avgCost:150,severity:'medium',urgency:'urgent'},
        {title:'A/C Weak Airflow',action:'Replace cabin filter, check blower',avgCost:80,severity:'low',urgency:'routine'},
        {title:'Strange Odors from A/C',action:'Clean evaporator, replace filter',avgCost:100,severity:'low',urgency:'routine'},
        {title:'A/C Compressor Noisy',action:'Replace compressor',avgCost:600,severity:'medium',urgency:'soon'},
        {title:'Refrigerant Leak',action:'UV dye test, repair leak',avgCost:250,severity:'medium',urgency:'soon'},
        {title:'Condenser Damaged',action:'Replace condenser',avgCost:400,severity:'medium',urgency:'soon'},
        {title:'Blower Motor Not Working',action:'Replace blower motor',avgCost:300,severity:'low',urgency:'routine'},
        {title:'Blend Door Issue',action:'Repair blend door actuator',avgCost:200,severity:'low',urgency:'routine'},
        {title:'Expansion Valve Stuck',action:'Replace expansion valve',avgCost:250,severity:'medium',urgency:'soon'},
        {title:'A/C Clutch Not Engaging',action:'Check relay, clutch coil',avgCost:150,severity:'medium',urgency:'soon'}
    ],
    'Steering':[
        {title:'Steering Wheel Vibration',action:'Check balance, suspension',avgCost:100,severity:'medium',urgency:'routine'},
        {title:'Power Steering Fluid Leak',action:'Repair leak, replace hose/pump',avgCost:300,severity:'medium',urgency:'soon'},
        {title:'Steering Rack Worn',action:'Replace steering rack',avgCost:500,severity:'high',urgency:'urgent'},
        {title:'Steering Wheel Off-Center',action:'Align steering wheel',avgCost:80,severity:'low',urgency:'routine'},
        {title:'Power Steering Pump Noisy',action:'Replace pump, flush fluid',avgCost:350,severity:'medium',urgency:'soon'},
        {title:'Steering Column Play',action:'Inspect steering column',avgCost:200,severity:'high',urgency:'urgent'},
        {title:'Steering Fluid Dark/Contaminated',action:'Flush power steering system',avgCost:100,severity:'low',urgency:'routine'},
        {title:'Steering Wheel Hard to Turn',action:'Check power steering, belt, fluid',avgCost:120,severity:'high',urgency:'urgent'},
        {title:'Knocking when Turning',action:'Check CV joints, tie rods',avgCost:150,severity:'medium',urgency:'soon'},
        {title:'Poor Return-to-Center',action:'Check alignment, steering gear',avgCost:100,severity:'low',urgency:'routine'}
    ],
    'Body / Exterior':[
        {title:'Door Hinge Squeaking',action:'Lubricate hinges',avgCost:30,severity:'low',urgency:'routine'},
        {title:'Trunk/Hood Latch Issue',action:'Adjust or replace latch',avgCost:80,severity:'low',urgency:'routine'},
        {title:'Window Seal Deteriorated',action:'Replace weatherstrip',avgCost:100,severity:'low',urgency:'routine'},
        {title:'Body Scratch / Dent',action:'Touch up paint / dent repair',avgCost:150,severity:'low',urgency:'routine'},
        {title:'Rust / Corrosion',action:'Rust treatment and panel repair',avgCost:300,severity:'medium',urgency:'soon'},
        {title:'Wiper Blades Worn',action:'Replace wiper blades',avgCost:30,severity:'low',urgency:'routine'},
        {title:'Side Mirror Damaged',action:'Replace side mirror',avgCost:150,severity:'low',urgency:'routine'},
        {title:'Tail Light Cracked',action:'Replace tail light assembly',avgCost:120,severity:'low',urgency:'routine'},
        {title:'Exhaust Leak',action:'Repair exhaust system',avgCost:200,severity:'medium',urgency:'soon'},
        {title:'Catalytic Converter Issue',action:'Diagnose and replace cat',avgCost:500,severity:'medium',urgency:'soon'}
    ]
};

// ====================== CONFIG ======================
const severityMap = {low:'info',medium:'warning',high:'danger',critical:'dark'};
const urgencyMap = {routine:'secondary',soon:'info',urgent:'warning',immediate:'danger'};
const inspectionId = {{ $inspection->id }};
const csrfToken = '{{ csrf_token() }}';
// Locked RO (promoted from an approved Repair Quotation): the existing findings are frozen.
// Newly added findings stay editable/deletable, so the shop can log a fresh discovery.
window.FINDINGS_LOCKED = {{ $inspection->isFindingsLocked() ? 'true' : 'false' }};

// ====================== INIT ======================
document.addEventListener('DOMContentLoaded', function() {
    // Severity button toggles
    document.querySelectorAll('.severity-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var parent = this.closest('.d-flex');
            parent.querySelectorAll('.severity-btn').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
        });
    });

    // Urgency button toggles
    document.querySelectorAll('.urgency-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var parent = this.closest('.d-flex');
            parent.querySelectorAll('.urgency-btn').forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
        });
    });

    // Parts input: Enter to add
    var partsInput = document.getElementById('quick-parts');
    if (partsInput) {
        partsInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') { e.preventDefault(); quickAddFinding(); }
        });
    }

    // Quick add button -> only adds on click
    var quickAddBtn = document.getElementById('btn-quick-add');
    if (quickAddBtn) {
        quickAddBtn.addEventListener('click', function() { quickAddFinding(); });
    }
});

function getSelectedSeverity() {
    var active = document.querySelector('.severity-btn.active');
    return active ? active.dataset.value : 'medium';
}

function getSelectedUrgency() {
    var active = document.querySelector('.urgency-btn.active');
    return active ? active.dataset.value : 'soon';
}

// ====================== AUTO-SUGGEST ======================
function showAutosuggest(input) {
    var dd = document.getElementById('autosuggest-results');
    var val = input.value.toLowerCase().trim();
    if (val.length < 1) { dd.style.display = 'none'; return; }

    dd.innerHTML = '';
    var results = [];

    for (var cat in issueLibrary) {
        issueLibrary[cat].forEach(function(issue) {
            if (issue.title.toLowerCase().includes(val) || (issue.action && issue.action.toLowerCase().includes(val))) {
                results.push({category: cat, issue: issue});
            }
        });
    }

    // Also search category names
    if (results.length < 3) {
        for (var catName in issueLibrary) {
            if (catName.toLowerCase().includes(val)) {
                issueLibrary[catName].forEach(function(iss) {
                    var found = results.some(function(r) { return r.issue.title === iss.title; });
                    if (!found) results.push({category: catName, issue: iss});
                });
            }
        }
    }

    if (results.length === 0) {
        dd.style.display = 'none';
        return;
    }

    // Show top 8 results
    results.slice(0, 8).forEach(function(r) {
        var item = document.createElement('button');
        item.type = 'button';
        item.className = 'list-group-item list-group-item-action';
        item.innerHTML = '<strong>' + r.issue.title + '</strong> <span class="category-hint">[' + r.category + ']</span>'
            + '<br><small class="text-muted">' + r.issue.action + ' — ₱' + r.issue.avgCost + '</small>';
        item.addEventListener('click', function() {
            selectIssue(r, input);
        });
        dd.appendChild(item);
    });

    dd.style.display = 'block';
}

function handleAutosuggestKey(e, input) {
    var dd = document.getElementById('autosuggest-results');
    if (!dd || dd.style.display === 'none') return;
    var items = dd.querySelectorAll('.list-group-item');
    var active = dd.querySelector('.active');
    var idx = Array.from(items).indexOf(active);

    if (e.key === 'ArrowDown') {
        e.preventDefault();
        var next = (idx + 1) % items.length;
        if (active) active.classList.remove('active');
        items[next].classList.add('active');
    } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        var prev = (idx - 1 + items.length) % items.length;
        if (active) active.classList.remove('active');
        items[prev].classList.add('active');
    } else if (e.key === 'Enter') {
        e.preventDefault();
        if (active) active.click();
    } else if (e.key === 'Escape') {
        dd.style.display = 'none';
    }
}

function selectIssue(result, input) {
    input.value = result.issue.title;
    document.getElementById('quick-category').value = result.category;
    // Set severity
    document.querySelectorAll('.severity-btn').forEach(function(b) {
        b.classList.toggle('active', b.dataset.value === (result.issue.severity || 'medium'));
    });
    // Set urgency
    document.querySelectorAll('.urgency-btn').forEach(function(b) {
        b.classList.toggle('active', b.dataset.value === (result.issue.urgency || 'soon'));
    });
    document.getElementById('autosuggest-results').style.display = 'none';
    document.getElementById('btn-quick-add').disabled = false;
    // Trigger add
    addFinding(result.category, result.issue.title, result.issue.severity || 'medium', result.issue.urgency || 'soon');
}

// ====================== ADD FINDING (AJAX) ======================
function addFinding(category, title, severity, urgency, skipFocus) {
    var btn = document.getElementById('btn-quick-add');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    fetch('/inspections/' + inspectionId + '/findings', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
        body: JSON.stringify({
            category: category,
            issue_title: title,
            severity: severity || 'medium',
            estimated_urgency: urgency || 'soon',
            detailed_notes: '',
            recommended_action: '',
            estimated_cost: null
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            appendFindingCard(data.finding);
            updateStats();
            document.getElementById('quick-issue-title').value = '';
            if (!skipFocus) {
                document.getElementById('quick-issue-title').focus();
            }
        }
    })
    .catch(function(err) { console.error('Add finding error:', err); })
    .finally(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plus"></i>';
    });
}

function appendFindingCard(finding) {
    // Remove empty state
    var emptyEl = document.querySelector('#findings-list-container .text-center.py-5');
    if (emptyEl) emptyEl.remove();

    var costStr = finding.estimated_cost !== null && finding.estimated_cost !== undefined
        ? '₱' + parseFloat(finding.estimated_cost).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2})
        : '';

    var cardHtml = '<div class="finding-card mb-2 finding-new" data-id="' + finding.id + '">'
        + '<div class="card border-0 shadow-sm" style="border-radius:10px;background:#fff;">'
        + '<div class="card-body p-3">'
        + '<div class="d-flex flex-wrap align-items-start gap-2">'
        + '<div class="flex-grow-1">'
        + '<div class="d-flex flex-wrap align-items-center gap-2 mb-2">'
        + '<span class="badge" style="background:linear-gradient(135deg,#1a237e,#283593);color:#fff;padding:3px 10px;border-radius:12px;font-size:11px;"><i class="fas fa-tag me-1"></i>' + finding.category + '</span>'
        + '<strong class="finding-title">' + escapeHtml(finding.issue_title) + '</strong>'
        + '</div>'
        + '</div>'
        + '<div class="text-end" style="min-width:120px;">'
        + '<div class="d-flex flex-wrap gap-1 justify-content-end mb-2">'
        + '<span class="badge px-2 py-1" style="font-size:10px;background:' + (finding.severity === 'low' ? '#0dcaf0' : finding.severity === 'medium' ? '#ffc107' : finding.severity === 'high' ? '#dc3545' : '#212529') + ';color:' + (finding.severity === 'medium' ? '#000' : '#fff') + ';"><i class="fas ' + (finding.severity === 'low' ? 'fa-chevron-down' : finding.severity === 'medium' ? 'fa-minus' : finding.severity === 'high' ? 'fa-chevron-up' : 'fa-exclamation') + ' me-1"></i>' + ucfirst(finding.severity) + '</span>'
        + '<span class="badge px-2 py-1" style="font-size:10px;background:' + (finding.estimated_urgency === 'routine' ? '#6c757d' : finding.estimated_urgency === 'soon' ? '#0dcaf0' : finding.estimated_urgency === 'urgent' ? '#ffc107' : '#dc3545') + ';color:' + (finding.estimated_urgency === 'routine' || finding.estimated_urgency === 'soon' ? '#fff' : '#000') + ';"><i class="fas ' + (finding.estimated_urgency === 'routine' ? 'fa-calendar' : finding.estimated_urgency === 'soon' ? 'fa-clock' : finding.estimated_urgency === 'urgent' ? 'fa-exclamation-circle' : 'fa-bolt') + ' me-1"></i>' + ucfirst(finding.estimated_urgency) + '</span>'
        + (costStr ? '<span class="fw-bold finding-cost" style="color:#1a237e;">' + costStr + '</span>' : '')
        + '</div>'
        + '</div>'
        + '</div>'
        + '<div class="d-flex gap-2 mt-2 pt-2 border-top">'
        + '<button class="btn btn-sm btn-outline-primary" onclick="editFinding(' + finding.id + ')"><i class="fas fa-edit"></i></button>'
        + '<button class="btn btn-sm btn-outline-danger" onclick="deleteFinding(' + finding.id + ')"><i class="fas fa-trash"></i></button>'
        + '</div>'
        + '</div>'
        + '</div>'
        + '</div>';

    var container = document.getElementById('findings-list-container');
    container.insertAdjacentHTML('beforeend', cardHtml);
}

// ====================== DELETE FINDING (AJAX) ======================
function deleteFinding(id) {
    fixitConfirm({title:'Delete this finding?', msg:'This card will be permanently removed from the repair order.', confirmText:'Delete', icon:'fa-trash-can'}).then(function(ok){ if(!ok) return;
    var el = document.querySelector('.finding-card[data-id="' + id + '"]');
    if (el) el.classList.add('finding-removing');

    fetch('/inspections/findings/' + id, {
        method: 'DELETE',
        headers: {'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            if (el) setTimeout(function() { el.remove(); }, 200);
            updateStats();
            // Show empty state if no more
            if (document.querySelectorAll('.finding-card').length === 0) {
                var container = document.getElementById('findings-list-container');
                container.innerHTML = '<div class="text-center py-5">'
                    + '<div class="mb-3"><i class="fas fa-clipboard-list" style="font-size:48px;color:#cfd8dc;"></i></div>'
                    + '<h6 class="text-muted mb-2">No Findings Recorded Yet</h6>'
                    + '<p class="text-muted small mb-0">Use the quick-add bar above to start logging findings.</p>'
                    + '</div>';
            }
        }
    })
    .catch(function(err) {
        console.error('Delete error:', err);
        if (el) el.classList.remove('finding-removing');
    });    });
}

// ====================== BULK QUICK ADD ======================
function bulkQuickAddByCategory(category) {
    var issues = issueLibrary[category];
    if (!issues) return;

    var container = document.getElementById('bulk-issues-container');
    container.innerHTML = '';

    issues.forEach(function(issue) {
        var badge = document.createElement('span');
        badge.className = 'badge bulk-add-btn px-3 py-2 me-1 mb-1';
        badge.style.cssText = 'background:#f0f4ff;color:#1a237e;border:1px solid #d0d9f0;font-size:11px;cursor:pointer;';
        badge.textContent = issue.title.length > 25 ? issue.title.substring(0, 22) + '...' : issue.title;
        badge.title = issue.title + ' — ' + issue.action + ' (₱' + issue.avgCost + ')';
        badge.addEventListener('click', function() {
            addFinding(category, issue.title, issue.severity || 'medium', issue.urgency || 'soon', true);
            this.style.background = '#059669';
            this.style.color = '#fff';
            this.style.borderColor = '#059669';
            setTimeout(function() {
                if (badge.parentNode) badge.remove();
            }, 1000);
        });
        container.appendChild(badge);
    });
}

// ====================== UTILITIES ======================
function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// ====================== INSPECTION PHOTOS (upload) ======================
function uploadInspectionPhotos() {
    var input = document.getElementById('photoUploadInput');
    var btn = document.getElementById('photoUploadBtn');
    if (!input || !input.files || input.files.length === 0) {
        showFixitToast('Pumili muna ng photo.', 'warn', 'No file selected');
        return;
    }
    var files = Array.prototype.slice.call(input.files);
    var orig = btn ? btn.innerHTML : '';
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Uploading ' + files.length + '...'; }

    // One request for all pictures
    var fd = new FormData();
    files.forEach(function (f) { fd.append('photos[]', f); });

    fetch('/repair-orders/' + inspectionId + '/upload-photos', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: fd
    })
    .then(function (r) { return r.json().catch(function () { return { success: false, message: 'Invalid server response' }; }); })
    .then(function (resp) {
        if (resp && resp.success && resp.photos) {
            resp.photos.forEach(function (p) { appendInspectionPhoto(p.path_url, ''); });
            if (input) { input.value = ''; }
            showFixitToast(resp.count + ' photo(s) uploaded', 'success', 'Photos');
        } else {
            showFixitToast((resp && resp.message) ? resp.message : 'Upload failed.', 'error', 'Photos');
        }
    })
    .catch(function (err) {
        console.error('Upload error:', err);
        showFixitToast('Upload failed.', 'error', 'Photos');
    })
    .then(function () {
        if (btn) { btn.disabled = false; btn.innerHTML = orig; }
    });
}

function appendInspectionPhoto(url, caption) {
    var gal = document.getElementById('photosGallery');
    var empty = document.getElementById('photosEmptyState');
    if (empty) { empty.style.display = 'none'; }
    if (!gal || !url) { return; }
    var idx = gal.querySelectorAll('.photo-card').length;
    var div = document.createElement('div');
    div.className = 'col-md-4 col-lg-3 photo-card';
    div.setAttribute('data-index', idx);
    div.innerHTML =
        '<div class="form-section position-relative">'
        + '<button type="button" class="btn btn-danger btn-sm position-absolute" style="top:.4rem;right:.4rem;z-index:2;" title="Delete photo" onclick="deleteInspectionPhoto(' + idx + ')"><i class="fas fa-trash"></i></button>'
        + '<div class="form-section-body p-2">'
        + '<img src="' + url + '" alt="Inspection photo" class="img-fluid rounded" '
        + 'style="width:100%;height:180px;object-fit:cover;cursor:pointer;" onclick="window.open(this.src, \'_blank\')">'
        + (caption ? '<p class="small text-muted mt-1 mb-0 px-1">' + escapeHtml(caption) + '</p>' : '')
        + '</div></div>';
    gal.appendChild(div);
}

function reindexInspectionPhotos() {
    var gal = document.getElementById('photosGallery');
    if (!gal) { return; }
    var cards = gal.querySelectorAll('.photo-card');
    cards.forEach(function (c, i) {
        c.setAttribute('data-index', i);
        var btn = c.querySelector('button[title="Delete photo"]');
        if (btn) { btn.setAttribute('onclick', 'deleteInspectionPhoto(' + i + ')'); }
    });
    var empty = document.getElementById('photosEmptyState');
    if (empty) { empty.style.display = cards.length ? 'none' : ''; }
}

function deleteInspectionPhoto(index) {
    if (!confirm('Delete this photo? Hindi na ito maibabalik.')) { return; }
    fetch('/repair-orders/' + inspectionId + '/photos/' + index, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
    })
    .then(function (r) { return r.json().catch(function () { return { success: false, message: 'Invalid server response' }; }); })
    .then(function (resp) {
        if (resp && resp.success) {
            var gal = document.getElementById('photosGallery');
            var card = gal ? gal.querySelector('.photo-card[data-index="' + index + '"]') : null;
            if (card) { card.remove(); }
            reindexInspectionPhotos();
            showFixitToast('Photo deleted', 'success', 'Photos');
        } else {
            showFixitToast((resp && resp.message) ? resp.message : 'Failed to delete photo.', 'error', 'Photos');
        }
    })
    .catch(function (err) {
        console.error('Delete error:', err);
        showFixitToast('Failed to delete photo.', 'error', 'Photos');
    });
}

function ucfirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function updateStats() {
    // Update findings count in tab badge if present
    var count = document.querySelectorAll('.finding-card').length;
    var badge = document.querySelector('[href="#findings"] .badge');
    if (badge) badge.textContent = count;
}

// ====================== EDIT FINDING (Modal) ======================
function editFinding(id) {
    var card = document.querySelector('.finding-card[data-id="' + id + '"]');
    if (!card) return;

    var title = card.querySelector('.finding-title');
    var category = card.querySelector('.badge[style*="background"]');
    var severityBadge = card.querySelector('.badge[style*="background"] + .d-flex .badge');
    var cost = card.querySelector('.finding-cost');

    // For a proper edit, we'll redirect to a simpler modal-based approach
    // Since we don't have a modal yet, open the page with the finding highlighted
    var categoryText = category ? category.textContent.replace('tag', '').trim() : '';
    var titleText = title ? title.textContent.trim() : '';

    openEditModal(id, categoryText, titleText);
}

function openEditModal(id, category, title) {
    document.getElementById('findingModalTitle').textContent = 'Edit Finding';
    document.getElementById('editFindingId').value = id;
    document.getElementById('editCategory').value = category || 'Engine';
    document.getElementById('editIssueTitle').value = title || '';
    document.getElementById('editDetailedNotes').value = '';
    document.getElementById('editEstimatedCost').value = '';
    document.getElementById('editRecommendedAction').value = '';
    var _sevEl = document.getElementById('editSeverity'); if (_sevEl) _sevEl.value = 'medium';
    document.querySelectorAll('.urgency-btn').forEach(function(b) { b.classList.remove('active'); });
    document.querySelector('.urgency-btn[data-value="soon"]').classList.add('active');

    try {
        var data = window._findingsData && window._findingsData[id];
        if (data) {
            document.getElementById('editDetailedNotes').value = data.detailed_notes || '';
            document.getElementById('editEstimatedCost').value = data.estimated_cost || '';
            document.getElementById('editRecommendedAction').value = data.recommended_action || '';
            if (data.severity) { var _se2 = document.getElementById('editSeverity'); if (_se2) _se2.value = data.severity; }
            if (data.estimated_urgency) {
                document.querySelectorAll('.urgency-btn').forEach(function(b) { b.classList.toggle('active', b.dataset.value === data.estimated_urgency); });
            }
        }
    } catch(e) {}

    var modal = new bootstrap.Modal(document.getElementById('findingEditModal'));
    modal.show();
}

function saveFindingFromModal() {
    var id = document.getElementById('editFindingId').value;
    var isNew = !id || id === '';

    var data = {
        category: document.getElementById('editCategory').value,
        issue_title: document.getElementById('editIssueTitle').value,
        detailed_notes: document.getElementById('editDetailedNotes').value,
        severity: (document.getElementById('editSeverity') || {}).value || 'medium',
        recommended_action: document.getElementById('editRecommendedAction').value,
        estimated_urgency: getSelectedUrgency(),
        estimated_cost: document.getElementById('editEstimatedCost').value || null
    };

    if (!data.issue_title.trim()) {
        showFixitToast('Please fill in the required field.', 'warn', 'Missing info'); if(false)
        return;
    }

    if (isNew) {
        // Add new finding
        fetch('/inspections/' + inspectionId + '/findings', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
            body: JSON.stringify(data)
        })
        .then(function(r) { return r.json(); })
        .then(function(resp) {
            if (resp.success) {
                appendFindingCard(resp.finding);
                updateStats();
                var modal = bootstrap.Modal.getInstance(document.getElementById('findingEditModal'));
                if (modal) modal.hide();
            }
        })
        .catch(function(err) { console.error('Save error:', err); });
    } else {
        // Update existing finding
        fetch('/inspections/findings/' + id, {
            method: 'PUT',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
            body: JSON.stringify(data)
        })
        .then(function(r) { return r.json(); })
        .then(function(resp) {
            if (resp.success) {
                // Refresh the card by removing and re-adding
                var oldCard = document.querySelector('.finding-card[data-id="' + id + '"]');
                if (oldCard) oldCard.remove();
                appendFindingCard(resp.finding);
                updateStats();
                var modal = bootstrap.Modal.getInstance(document.getElementById('findingEditModal'));
                if (modal) modal.hide();
            }
        })
        .catch(function(err) { console.error('Update error:', err); });
    }
}

// ====================== INITIALIZE BULK CATEGORY BADGES ======================
// Already initialized in DOMContentLoaded, additional UI handlers:
document.addEventListener('DOMContentLoaded', function() {
    // Ctrl+Enter inside Parts also adds
    var partsInput = document.getElementById('quick-parts');
    if (partsInput) {
        partsInput.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                quickAddFinding();
            }
        });
    }
});

// ===== GLOBAL: CATEGORY-FILTERED SUGGESTIONS ON FOCUS (must be global — called from other DOMContentLoaded blocks & inline) =====
function showAutosuggestForCategory(input, category) {
    console.log('showAutosuggestForCategory called with:', category);
    var dd = document.getElementById('autosuggest-results');
    if (!dd) { console.log('ERROR: autosuggest-results not found'); return; }
    var issues = issueLibrary[category];
    console.log('issues found:', issues ? issues.length : 0);
    if (!issues || issues.length === 0) { dd.style.display = 'none'; return; }

    dd.innerHTML = '';
    issues.slice(0, 8).forEach(function(issue) {
        var item = document.createElement('button');
        item.type = 'button';
        item.className = 'list-group-item list-group-item-action';
        item.innerHTML = '<strong>' + issue.title + '</strong> <span class="category-hint">[' + category + ']</span>'
            + '<br><small class="text-muted">' + issue.action + ' — ₱' + issue.avgCost + '</small>';
        item.addEventListener('click', function() {
            selectIssue({category: category, issue: issue}, input);
        });
        dd.appendChild(item);
    });
    dd.style.display = 'block';
}

// ===== MODAL AUTO-SUGGEST (runs on DOMContentLoaded) =====
document.addEventListener('DOMContentLoaded', function() {
    var modalTitle = document.getElementById('editIssueTitle');
    var modalCat = document.getElementById('editCategory');
    if (modalTitle && modalCat) {
        modalTitle.addEventListener('focus', function() {
            var cat = modalCat.value;
            if (this.value === '' && cat && issueLibrary[cat]) {
                var dd = document.getElementById('modal-autosuggest-results');
                if (!dd) return;
                var issues = issueLibrary[cat];
                dd.innerHTML = '';
                issues.slice(0, 8).forEach(function(issue) {
                    var item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'list-group-item list-group-item-action';
                    item.innerHTML = '<strong>' + issue.title + '</strong> <span class="category-hint">[' + cat + ']</span>'
                        + '<br><small class="text-muted">' + issue.action + ' — ₱' + issue.avgCost + '</small>';
                    item.addEventListener('click', function() {
                        modalTitle.value = issue.title;
                        dd.style.display = 'none';
                    });
                    dd.appendChild(item);
                });
                dd.style.display = 'block';
            }
        });
        // Hide on blur
        modalTitle.addEventListener('blur', function() {
            setTimeout(function() {
                var dd = document.getElementById('modal-autosuggest-results');
                if (dd) dd.style.display = 'none';
            }, 200);
        });
    }
});
/* ====================== FINDINGS GROUPS (drag & drop, shared labor) — overrides ====================== */
function fcardCategoryBg(cat){
    var m={'Engine':'linear-gradient(135deg,#1a237e,#283593)','Brakes':'linear-gradient(135deg,#c62828,#d32f2f)','Suspension':'linear-gradient(135deg,#e65100,#ef6c00)','Electrical':'linear-gradient(135deg,#00695c,#00897b)','Cooling':'linear-gradient(135deg,#4a148c,#6a1b9a)','Transmission':'linear-gradient(135deg,#37474f,#455a64)','Tires':'linear-gradient(135deg,#1b5e20,#2e7d32)','Aircon':'linear-gradient(135deg,#01579b,#0277bd)','Steering':'linear-gradient(135deg,#3e2723,#4e342e)','Body / Exterior':'linear-gradient(135deg,#827717,#9e9d24)'};
    return m[cat] || 'linear-gradient(135deg,#546e7a,#607d8b)';
}
function fnum(v){ var n=parseFloat(v); return isNaN(n)?0:n; }
function fmoney(n){ return '₱' + fnum(n).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}); }

function findingCardHtml(f, showLabor){
    var sev = f.severity || 'medium';
    var urg = f.estimated_urgency || 'soon';
    var sevBg={'low':'#0dcaf0','medium':'#ffc107','high':'#dc3545','critical':'#212529'}[sev] || '#6c757d';
    var urgBg={'routine':'#6c757d','soon':'#0dcaf0','urgent':'#ffc107','immediate':'#dc3545'}[urg] || '#6c757d';
    var urgColor = (urg==='routine'||urg==='soon') ? '#fff' : '#000';
    var qty = fnum(f.quantity || 1);
    var up = (f.unit_price===null||f.unit_price===undefined||f.unit_price==='') ? null : fnum(f.unit_price);
    var lineTotal = qty * (up||0);
    var qtyStr = (Math.round(qty*100)/100).toString();
    var remarks = f.remarks || f.part_name || '\u2014';
    // Ungrouped findings carry their own labor price (estimated_cost) — render the Labor
    // field right away so it shows up immediately on add (no page reload needed).
    var laborStr = (f.estimated_cost===null||f.estimated_cost===undefined||f.estimated_cost==='') ? '' : (Math.round(fnum(f.estimated_cost)*100)/100).toString();
    var laborRow = showLabor
        ? ('<div class="finding-labor-row d-flex align-items-center justify-content-end gap-2 px-3 pb-2 pt-1" style="border-top:1px dashed #e6ebf3;">'
            + '<label class="text-muted mb-0" style="font-size:11px;"><i class="fas fa-tools me-1"></i>Labor \u20b1</label>'
            + '<input type="number" min="0" step="0.01" inputmode="decimal" class="form-control form-control-sm finding-labor-input" style="width:104px;padding:.08rem .35rem;height:auto;font-size:12.5px;" value="'+laborStr+'" placeholder="0.00" onchange="saveFindingCost('+f.id+', this.value)">'
            + '</div>')
        : '';
    return '<div class="finding-card mb-1 finding-new" data-id="'+f.id+'"'
        + ' data-locked="'+(f.__locked?'1':'0')+'"'
        + ' data-quotation-added="'+(f.is_quotation_added?1:0)+'"'
        + ' data-category="'+escapeHtml(f.category||'')+'" data-issue-title="'+escapeHtml(f.issue_title||'')+'"'
        + ' data-part-name="'+escapeHtml(f.part_name||'')+'" data-remarks="'+escapeHtml(f.remarks||'')+'"'
        + ' data-quantity="'+qtyStr+'" data-unit-price="'+(up===null?'':up)+'"'
        + ' data-severity="'+sev+'" data-urgency="'+urg+'"'
        + ' data-notes="'+escapeHtml(f.detailed_notes||'')+'" data-action="'+escapeHtml(f.recommended_action||'')+'"'
        + ' data-cost="'+((f.estimated_cost===null||f.estimated_cost===undefined)?'':f.estimated_cost)+'">'
        + '<div class="card border-0 shadow-sm" style="background:#fff;">'
        + '<div class="finding-grid">'
        + '<span class="drag-handle text-muted" style="cursor:grab;padding:0 4px;" title="Drag to move"><i class="fas fa-grip-vertical"></i></span>'
        + '<span class="fcell"><span class="badge category-badge" style="background:'+fcardCategoryBg(f.category)+';color:#fff;padding:2px 8px;border-radius:10px;font-size:10.5px;white-space:normal;"><i class="fas fa-tag me-1"></i>'+escapeHtml(f.category||'')+'</span></span>'
        + '<span class="fcell fc-parts"><span class="sev-dot" title="'+ucfirst(sev)+'" style="display:inline-block;width:9px;height:9px;border-radius:50%;background:'+sevBg+';flex:0 0 auto;"></span>'
        + '<strong class="finding-title" title="'+escapeHtml(f.issue_title||'')+'">'+escapeHtml(f.issue_title||'')+'</strong></span>'
        + '<span class="fcell"><span class="finding-remarks">'+escapeHtml(remarks)+(f.detailed_notes?'<span class="finding-notes"> \u00b7 '+escapeHtml(f.detailed_notes)+'</span>':'')+'</span></span>'
        + '<span class="fcell justify-content-center"><span class="badge urgency-badge" style="background:'+urgBg+';color:'+urgColor+';">'+ucfirst(urg)+'</span></span>'
        + '<span class="fcell justify-content-center finding-qty-wrap" onclick="inlineEditFinding('+f.id+')" title="Tap to edit Qty / Price" style="cursor:pointer;">Qty <span class="finding-qty">'+qtyStr+'</span></span>'
        + '<span class="fcell justify-content-end finding-price" onclick="inlineEditFinding('+f.id+')" title="Tap to edit Qty / Price" style="cursor:pointer;">'+(up!==null?fmoney(up):'<span class="text-muted">\u2014</span>')+'</span>'
        + '<span class="fcell justify-content-end fw-bold finding-line-total">'+fmoney(lineTotal)+'</span>'
        + '<span class="fcell justify-content-center gap-1 finding-actions">'
        + '<span class="finding-action-btns d-inline-flex gap-1">'
        + '<button class="btn btn-sm btn-light border py-0 px-1" onclick="inlineEditFinding('+f.id+')" title="Quick edit Qty / Price"><i class="fas fa-edit text-primary"></i></button>'
        + ((!window.FINDINGS_QUOTATION_MODE) || f.is_quotation_added ? '<button class="btn btn-sm btn-light border py-0 px-1" onclick="deleteFinding('+f.id+')" title="Delete"><i class="fas fa-trash text-danger"></i></button>' : '')
        + '</span>'
        + '<span class="badge finding-linked-badge" style="'+(f.is_linked_to_estimate?'background:#059669;color:#fff;':'display:none;')+'font-size:9px;"><i class="fas fa-link"></i></span>'
        + '</span>'
        + '</div>'
        + laborRow
        + '</div></div>';
}

function dropzoneFor(groupId){
    var key = (groupId===null||groupId===undefined) ? '' : String(groupId);
    return document.querySelector('.finding-dropzone[data-group-id="'+key+'"]');
}
function clearEmptyHint(dz){ var h = dz.querySelector('.finding-empty-hint'); if(h) h.remove(); }

function refreshGroupTotals(){
    document.querySelectorAll('.finding-group').forEach(function(g){
        if(g.dataset.declined === '1') return; // "Not Pursued" zone is not part of the quotation totals
        var dz = g.querySelector('.finding-dropzone');
        var cards = dz ? dz.querySelectorAll('.finding-card') : [];
        var parts = 0;
        cards.forEach(function(c){
            var q = fnum(c.dataset.quantity || 1);
            var p = (c.dataset.unitPrice === '' || c.dataset.unitPrice === undefined) ? 0 : fnum(c.dataset.unitPrice);
            parts += q*p;
        });
        var laborEl = g.querySelector('.group-labor-input');
        var labor = laborEl ? fnum(laborEl.value) : fnum(g.dataset.laborCost || 0);
        var badge = g.querySelector('.group-total-badge');
        if(badge) badge.textContent = fmoney(parts + labor);
        var count = g.querySelector('.group-count');
        if(count) count.textContent = cards.length;
    });
}
/* Auto-name a group from the categories of the cards inside it */
function autoGroupName(g){
    if(!g || g.dataset.autoName === '0') return;
    var gid = g.dataset.groupId;
    if(!gid) return;
    var cats = [];
    g.querySelectorAll('.finding-card').forEach(function(c){
        var cat = (c.dataset.category || '').trim();
        if(cat && cats.indexOf(cat) === -1) cats.push(cat);
    });
    var name;
    if(cats.length === 0) name = 'New Group';
    else if(cats.length === 1) name = cats[0];
    else if(cats.length === 2) name = cats[0] + ' and ' + cats[1];
    else name = cats.slice(0, -1).join(', ') + ' and ' + cats[cats.length - 1];
    var input = g.querySelector('.group-name-input');
    if(input && input.value !== name){
        input.value = name;
        g.dataset.autoName = '1';
        saveGroupField(gid, 'name', name, {auto_name: 1});
    }
}
function autoNameAllGroups(){
    document.querySelectorAll('.finding-group').forEach(function(g){
        if(g.dataset.groupId !== '') autoGroupName(g);
    });
}

function updateFindingTabBadge(){
    var count = document.querySelectorAll('.finding-card').length;
    var badge = document.querySelector('[href="#findings"] .badge');
    if (badge) badge.textContent = count;
}

function addFinding(category, title, severity, urgency, skipFocus){
    var btn = document.getElementById('btn-quick-add');
    if(btn){ btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>'; }
    fetch('/repair-orders/' + inspectionId + '/findings', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
        body: JSON.stringify({category:category, issue_title:title, severity:severity||'medium', estimated_urgency:urgency||'soon', detailed_notes:'', recommended_action:'', estimated_cost:null, quantity:1, is_quotation_added:(window.FINDINGS_QUOTATION_MODE===true)?1:0})
    })
    .then(function(r){ return r.json(); })
    .then(function(data){
        if(data.success){
            appendFindingCard(data.finding);
            var inp = document.getElementById('quick-issue-title');
            if(inp){ inp.value=''; if(!skipFocus) inp.focus(); }
        }
    })
    .catch(function(err){ console.error('Add finding error:', err); })
    .finally(function(){ if(btn){ btn.disabled=false; btn.innerHTML='<i class="fas fa-plus"></i>'; } });
}

function appendFindingCard(finding){
    var dz = dropzoneFor(finding.group_id);
    if(!dz) dz = dropzoneFor('');
    if(!dz) return;
    clearEmptyHint(dz);
    // The ungrouped dropzone carries its own per-item labor field; grouped cards share the group's labor.
    var gid = dz.dataset.groupId;
    var isUngrouped = (gid === undefined || gid === null || gid === '');
    dz.insertAdjacentHTML('beforeend', findingCardHtml(finding, isUngrouped));
    refreshGroupTotals();
    updateFindingTabBadge();
}

function deleteFinding(id){
    fixitConfirm({title:'Delete this finding?', msg:'This card will be permanently removed from the repair order.', confirmText:'Delete', icon:'fa-trash-can'}).then(function(ok){ if(!ok) return;
    var el = document.querySelector('.finding-card[data-id="'+id+'"]');
    if(el) el.classList.add('finding-removing');
    fetch('/repair-orders/findings/'+id, {method:'DELETE', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}})
    .then(function(r){ return r.json(); })
    .then(function(data){
        if(data.success && el){
            var dz = el.parentNode;
            el.remove();
            if(dz && dz.classList && dz.classList.contains('finding-dropzone') && dz.querySelectorAll('.finding-card').length===0){
                dz.insertAdjacentHTML('beforeend','<p class="text-muted small mb-0 py-2 text-center finding-empty-hint">No findings here.</p>');
            }
            refreshGroupTotals(); updateFindingTabBadge();
            var pg = (dz && dz.closest) ? dz.closest('.finding-group') : null;
            if(pg){ if(!removeGroupIfEmpty(pg)) autoGroupName(pg); }
            showFixitToast('Finding deleted.', 'success', 'Deleted');
        }
    })
    .catch(function(err){ console.error('Delete error:', err); if(el) el.classList.remove('finding-removing'); showFixitToast('Could not delete the finding.', 'error', 'Delete failed'); });    });
}

function actionButtonsHtml(id, editing){
    var qm = (window.FINDINGS_QUOTATION_MODE === true);
    var _card = document.querySelector('.finding-card[data-id="'+id+'"]');
    var locked = !!window.FINDINGS_LOCKED && _card && _card.dataset.locked === '1';
    if(locked){
        return '<span class="badge" style="background:#e2e8f0;color:#475569;font-size:9px;" title="Kasama sa approved Repair Quotation — hindi na mababago."><i class="fas fa-lock"></i></span>';
    }
    if(editing){
        var html = '<button class="btn btn-sm btn-light border py-0 px-1" onclick="inlineSaveFinding('+id+')" title="Save"><i class="fas fa-check text-success"></i></button>'
            + '<button class="btn btn-sm btn-light border py-0 px-1" onclick="inlineCancelFinding('+id+')" title="Cancel"><i class="fas fa-xmark text-secondary"></i></button>';
        // Quotation page: no full "Edit Finding" (more fields) — details come from the Repair Order.
        if(!qm){
            html += '<button class="btn btn-sm btn-light border py-0 px-1" onclick="editFinding('+id+')" title="More fields…"><i class="fas fa-up-right-and-down-left-from-center text-primary"></i></button>';
        }
        return html;
    }
    var base = '<button class="btn btn-sm btn-light border py-0 px-1" onclick="inlineEditFinding('+id+')" title="Quick edit Qty / Price"><i class="fas fa-edit text-primary"></i></button>';
    // Quotation page: findings from the Repair Order are locked; only items added on the
    // quotation itself remain deletable.
    var card = document.querySelector('.finding-card[data-id="'+id+'"]');
    var quotationAdded = card && card.dataset.quotationAdded === '1';
    if(!qm || quotationAdded){
        base += '<button class="btn btn-sm btn-light border py-0 px-1" onclick="deleteFinding('+id+')" title="Delete"><i class="fas fa-trash text-danger"></i></button>';
    }
    return base;
}

var inlineEditState = {};

function inlineRecalcTotal(card){
    var qEl = card.querySelector('.inline-qty');
    var pEl = card.querySelector('.inline-price');
    var tot = card.querySelector('.finding-line-total');
    if(!tot) return;
    var q = qEl ? (parseFloat(qEl.value)||0) : (parseFloat(card.dataset.quantity)||0);
    var p = pEl ? (pEl.value==='' ? 0 : (parseFloat(pEl.value)||0)) : (card.dataset.unitPrice==='' ? 0 : (parseFloat(card.dataset.unitPrice)||0));
    var labor = card.querySelector('.finding-labor-input');
    var c = labor ? (labor.value==='' ? 0 : (parseFloat(labor.value)||0)) : 0;
    tot.textContent = fmoney(q*p + c);
}

/* Save the per-finding Labor price (ungrouped findings only). */
function saveFindingCost(id, value){
    var card = document.querySelector('.finding-card[data-id="'+id+'"]');
    var val = (value==='' || value===null || value===undefined) ? null : value;
    fetch('/repair-orders/findings/'+id+'/cost', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
        body: JSON.stringify({estimated_cost: val})
    })
    .then(function(r){ return r.json(); })
    .then(function(resp){
        if(card){
            card.dataset.cost = (val===null ? '' : val);
            var q = parseFloat(card.dataset.quantity)||0;
            var up = (card.dataset.unitPrice==='' ? 0 : (parseFloat(card.dataset.unitPrice)||0));
            var c = (val===null || val==='' ? 0 : (parseFloat(val)||0));
            var tot = card.querySelector('.finding-line-total'); if(tot) tot.textContent = fmoney(q*up + c);
        }
        showFixitToast('Labor saved.', 'success', 'Updated');
    })
    .catch(function(err){ console.error('Save finding cost error:', err); showFixitToast('Could not save labor.', 'error', 'Save failed'); });
}

function inlineEditFinding(id){
    var card = document.querySelector('.finding-card[data-id="'+id+'"]');
    if(!card || card.classList.contains('finding-inline-editing')) return;
    if(window.FINDINGS_LOCKED && card.dataset.locked === '1'){
        showFixitToast('Naka-lock ang finding na ito — kasama na sa approved quotation.', 'warn', 'Locked');
        return;
    }
    var qtyWrap = card.querySelector('.finding-qty-wrap');
    var priceCell = card.querySelector('.finding-price');
    if(!qtyWrap || !priceCell) return;
    inlineEditState[id] = { qtyHtml: qtyWrap.innerHTML, priceHtml: priceCell.innerHTML };
    var qty = card.dataset.quantity || '1';
    var up  = card.dataset.unitPrice;
    qtyWrap.innerHTML = 'Qty <input type="number" min="0" step="1" inputmode="decimal" class="inline-qty form-control form-control-sm" value="'+qty+'" style="width:66px;display:inline-block;padding:.08rem .3rem;height:auto;font-size:12.5px;">';
    priceCell.innerHTML = '<input type="number" min="0" step="0.01" inputmode="decimal" class="inline-price form-control form-control-sm" value="'+(up===undefined?'':up)+'" placeholder="0.00" style="width:88px;padding:.08rem .3rem;height:auto;font-size:12.5px;">';
    card.classList.add('finding-inline-editing');
    var btns = card.querySelector('.finding-action-btns');
    if(btns) btns.innerHTML = actionButtonsHtml(id, true);
    var qi = qtyWrap.querySelector('.inline-qty');
    if(qi){ qi.focus(); if(qi.select) qi.select(); }
    Array.prototype.forEach.call(card.querySelectorAll('.inline-qty,.inline-price'), function(inp){
        inp.addEventListener('input', function(){ inlineRecalcTotal(card); });
        inp.addEventListener('keydown', function(evt){
            if(evt.key === 'Enter'){ evt.preventDefault(); inlineSaveFinding(id); }
            else if(evt.key === 'Escape'){ evt.preventDefault(); inlineCancelFinding(id); }
        });
    });
    inlineRecalcTotal(card);
}

function inlineCancelFinding(id){
    var card = document.querySelector('.finding-card[data-id="'+id+'"]');
    if(!card) return;
    var st = inlineEditState[id];
    if(st){
        var qtyWrap = card.querySelector('.finding-qty-wrap'); if(qtyWrap && st.qtyHtml) qtyWrap.innerHTML = st.qtyHtml;
        var priceCell = card.querySelector('.finding-price'); if(priceCell && st.priceHtml) priceCell.innerHTML = st.priceHtml;
    }
    card.classList.remove('finding-inline-editing');
    var btns = card.querySelector('.finding-action-btns'); if(btns) btns.innerHTML = actionButtonsHtml(id, false);
    var q = parseFloat(card.dataset.quantity)||0;
    var up = (card.dataset.unitPrice==='' ? 0 : (parseFloat(card.dataset.unitPrice)||0));
    var tot = card.querySelector('.finding-line-total'); if(tot) tot.textContent = fmoney(q*up);
    delete inlineEditState[id];
}

function inlineSaveFinding(id){
    var card = document.querySelector('.finding-card[data-id="'+id+'"]');
    if(!card || !card.classList.contains('finding-inline-editing')) return;
    var qEl = card.querySelector('.inline-qty');
    var pEl = card.querySelector('.inline-price');
    var qty = qEl ? qEl.value : (card.dataset.quantity || '1');
    var upRaw = pEl ? pEl.value : card.dataset.unitPrice;
    var payload = {
        category: card.dataset.category || '',
        issue_title: card.dataset.issueTitle || '',
        part_name: card.dataset.partName || '',
        remarks: card.dataset.remarks || '',
        quantity: (qty === '' ? 1 : qty),
        unit_price: (upRaw === '' || upRaw === undefined ? null : upRaw),
        severity: card.dataset.severity || 'medium',
        estimated_urgency: card.dataset.urgency || 'soon',
        detailed_notes: card.dataset.notes || '',
        recommended_action: card.dataset.action || '',
        estimated_cost: (card.dataset.cost === '' || card.dataset.cost === undefined ? null : card.dataset.cost)
    };
    var btns = card.querySelector('.finding-action-btns');
    if(btns) btns.innerHTML = '<span class="badge bg-light text-secondary border"><span class="spinner-border spinner-border-sm"></span></span>';
    fetch('/repair-orders/findings/'+id, {
        method:'PUT',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
        body: JSON.stringify(payload)
    })
    .then(function(r){ return r.json(); })
    .then(function(resp){
        if(resp && resp.success){
            var f = resp.finding || {};
            var nq  = (f.quantity !== undefined && f.quantity !== null) ? parseFloat(f.quantity) : parseFloat(payload.quantity);
            var nup = (f.unit_price === null || f.unit_price === undefined || f.unit_price === '') ? null : parseFloat(f.unit_price);
            card.dataset.quantity = (Math.round(nq*100)/100).toString();
            card.dataset.unitPrice = (nup === null ? '' : nup);
            var qtyWrap = card.querySelector('.finding-qty-wrap');
            if(qtyWrap) qtyWrap.innerHTML = 'Qty <span class="finding-qty">'+(Math.round(nq*100)/100).toString()+'</span>';
            var priceCell = card.querySelector('.finding-price');
            if(priceCell) priceCell.innerHTML = (nup === null ? '<span class="text-muted">\u2014</span>' : fmoney(nup));
            var tot = card.querySelector('.finding-line-total'); if(tot) tot.textContent = fmoney(nq*(nup||0));
            card.classList.remove('finding-inline-editing');
            if(btns) btns.innerHTML = actionButtonsHtml(id, false);
            delete inlineEditState[id];
            refreshGroupTotals();
            showFixitToast('Saved.', 'success', 'Updated');
        } else {
            showFixitToast('Could not save changes.', 'error', 'Save failed');
            if(btns) btns.innerHTML = actionButtonsHtml(id, true);
        }
    })
    .catch(function(err){
        console.error('Inline save error:', err);
        showFixitToast('Could not save changes.', 'error', 'Save failed');
        if(btns) btns.innerHTML = actionButtonsHtml(id, true);
    });
}

function editFinding(id){
    var card = document.querySelector('.finding-card[data-id="'+id+'"]');
    if(!card) return;
    openEditModal(id, card.dataset);
}

function openEditModal(id, d){
    d = d || {};
    document.getElementById('findingModalTitle').textContent = id ? 'Edit Finding' : 'Add Finding';
    document.getElementById('editFindingId').value = id || '';
    document.getElementById('editCategory').value = d.category || 'Engine';
    document.getElementById('editIssueTitle').value = d.issueTitle || d.issue_title || '';
    document.getElementById('editRemarks').value = d.remarks || '';
    document.getElementById('editQuantity').value = d.quantity || 1;
    document.getElementById('editUnitPrice').value = d.unitPrice || d.unit_price || '';
    document.getElementById('editDetailedNotes').value = d.notes || d.detailed_notes || '';
    document.getElementById('editEstimatedCost').value = d.cost || d.estimated_cost || '';
    document.getElementById('editRecommendedAction').value = d.action || d.recommended_action || '';
    var sev = d.severity || 'medium', urg = d.urgency || d.estimated_urgency || 'soon';
    var _sevEl2 = document.getElementById('editSeverity'); if (_sevEl2) _sevEl2.value = sev;
    document.querySelectorAll('.urgency-btn').forEach(function(b){ b.classList.toggle('active', b.dataset.value===urg); });
    new bootstrap.Modal(document.getElementById('findingEditModal')).show();
}

function saveFindingFromModal(){
    var id = document.getElementById('editFindingId').value;
    var isNew = !id;
    var data = {
        category: document.getElementById('editCategory').value,
        issue_title: document.getElementById('editIssueTitle').value,
        remarks: document.getElementById('editRemarks').value,
        quantity: document.getElementById('editQuantity').value || 1,
        unit_price: document.getElementById('editUnitPrice').value || null,
        detailed_notes: document.getElementById('editDetailedNotes').value,
        severity: (document.getElementById('editSeverity') || {}).value || 'medium',
        recommended_action: document.getElementById('editRecommendedAction').value,
        estimated_urgency: getSelectedUrgency(),
        estimated_cost: document.getElementById('editEstimatedCost').value || null
    };
    if(!data.issue_title.trim()){ showFixitToast('Please fill in the required field.', 'warn', 'Missing info'); if(false) return; }
    var url = isNew ? ('/repair-orders/' + inspectionId + '/findings') : ('/repair-orders/findings/' + id);
    fetch(url, {
        method: isNew ? 'POST' : 'PUT',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
        body: JSON.stringify(data)
    })
    .then(function(r){ return r.json(); })
    .then(function(resp){
        if(resp.success){
            var oldCard = document.querySelector('.finding-card[data-id="'+(resp.finding.id)+'"]');
            if(oldCard) oldCard.remove();
            appendFindingCard(resp.finding);
            var m = bootstrap.Modal.getInstance(document.getElementById('findingEditModal'));
            if(m) m.hide();
            showFixitToast(isNew ? 'Finding added.' : 'Finding updated.', 'success', isNew ? 'Added' : 'Updated');
        }
    })
    .catch(function(err){ console.error('Save error:', err); });
}

function createFindingGroup(){
    fetch('/repair-orders/' + inspectionId + '/finding-groups', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
        body: JSON.stringify({name:'New Group', labor_cost:0})
    })
    .then(function(r){ return r.json(); })
    .then(function(data){ if(data.success){ insertGroupDom(data.group); refreshGroupTotals(); showFixitToast('New group created.', 'success', 'Group created'); } })
    .catch(function(err){ console.error('Create group error:', err); showFixitToast('Could not create the group.', 'error', 'Create failed'); });
}

function insertGroupDom(g){
    var board = document.getElementById('findings-board');
    var html = '<div class="finding-group mb-3" data-group-id="'+g.id+'" data-auto-name="1">'
        + '<div class="finding-group-head d-flex flex-wrap align-items-center gap-2 px-3 py-2" style="background:linear-gradient(135deg,#1a237e,#283593);color:#fff;border-radius:10px 10px 0 0;">'
        + '<i class="fas fa-layer-group"></i>'
        + '<input type="text" class="form-control form-control-sm group-name-input" value="'+escapeHtml(g.name)+'" style="max-width:170px;background:rgba(255,255,255,.15);border:none;color:#fff;" onchange="manualGroupName('+g.id+', this.value)" oninput="this.closest(\'.finding-group\').dataset.autoName=\'0\'">'
        + '<div class="ms-auto d-flex align-items-center gap-2">'
        + '<label class="small mb-0 text-white-50"><i class="fas fa-tools me-1"></i>Labor ₱</label>'
        + '<input type="number" min="0" step="0.01" class="form-control form-control-sm group-labor-input" value="0.00" style="width:110px;" onchange="saveGroupField('+g.id+', \'labor_cost\', this.value)">'
        + '<span class="badge bg-light text-dark group-total-badge">₱0.00</span>'
        + '<button type="button" class="btn btn-sm btn-outline-light" onclick="deleteFindingGroup('+g.id+')" title="Delete group (keeps cards)"><i class="fas fa-trash"></i></button>'
        + '</div></div>'
        + '<div class="finding-dropzone p-2" data-group-id="'+g.id+'" style="border:1px solid #dbe3f0;border-top:none;border-radius:0 0 10px 10px;min-height:56px;background:#fff;">'
        + '<div class="finding-headrow"><span></span><span>Category</span><span>Parts</span><span>Remarks</span><span class="text-center">Urgency</span><span class="text-center">Qty</span><span class="text-end">Price</span><span class="text-end">Total</span><span></span></div>'
        + '<p class="text-muted small mb-0 py-2 text-center finding-empty-hint">Drop cards here to share this labor cost.</p>'
        + '</div></div>';
    board.insertAdjacentHTML('beforeend', html);
    initDropzones();
}

function saveGroupField(id, field, value, extra){
    var payload = {}; payload[field] = value;
    if(extra){ for(var k in extra){ if(extra.hasOwnProperty(k)) payload[k] = extra[k]; } }
    fetch('/repair-orders/finding-groups/'+id, {
        method:'PUT',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
        body: JSON.stringify(payload)
    })
    .then(function(r){ return r.json(); })
    .then(function(){ refreshGroupTotals(); })
    .catch(function(err){ console.error('Save group error:', err); });
}

/* The user typed a group name -> it is theirs now (no more auto-naming). */
function manualGroupName(id, value){
    var g = document.querySelector('.finding-group[data-group-id="'+id+'"]');
    if(g) g.dataset.autoName = '0';
    saveGroupField(id, 'name', value, {auto_name: 0});
}

function deleteFindingGroup(id){
    fixitConfirm({title:'Delete this group?', msg:'Its cards will move back to Ungrouped. The group itself is removed.', confirmText:'Delete group', icon:'fa-trash-can'}).then(function(ok){ if(!ok) return;
    fetch('/repair-orders/finding-groups/'+id, {method:'DELETE', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}})
    .then(function(r){ return r.json(); })
    .then(function(data){
        if(data.success){
            var g = document.querySelector('.finding-group[data-group-id="'+id+'"]');
            if(g){
                var ungroup = dropzoneFor('');
                var cards = g.querySelectorAll('.finding-card');
                cards.forEach(function(card){
                    if(ungroup){ clearEmptyHint(ungroup); ungroup.appendChild(card); }
                    fetch('/repair-orders/findings/'+card.dataset.id+'/move', {method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}, body: JSON.stringify({group_id:null})});
                });
                g.remove();
            }
            if(document.querySelector('.finding-group[data-group-id=""] .finding-dropzone .finding-card')===null){
                var ud = dropzoneFor('');
                if(ud && !ud.querySelector('.finding-empty-hint')){
                    ud.insertAdjacentHTML('beforeend','<p class="text-muted small mb-0 py-2 text-center finding-empty-hint">No ungrouped findings.</p>');
                }
            }
            refreshGroupTotals(); updateFindingTabBadge();
            showFixitToast('Group deleted — its cards moved to Ungrouped.', 'success', 'Group deleted');
        }
    })
    .catch(function(err){ console.error('Delete group error:', err); showFixitToast('Could not delete the group.', 'error', 'Delete failed'); });    });
}

function removeGroupIfEmpty(g){
    if(!g) return false;
    if(g.dataset.groupId === '' || g.dataset.groupId === undefined) return false; // Ungrouped pseudo-group stays
    if(g.querySelectorAll('.finding-card').length > 0) return false;
    var id = g.dataset.groupId;
    g.remove();
    fetch('/repair-orders/finding-groups/'+id, {method:'DELETE', headers:{'X-CSRF-TOKEN':csrfToken,'Accept':'application/json'}})
        .then(function(r){ return r.json(); })
        .then(function(){ refreshGroupTotals(); updateFindingTabBadge(); })
        .catch(function(err){ console.error('Auto-remove empty group error:', err); });
    return true;
}

function refreshDeclinedCount(){
    var n = document.querySelectorAll('.finding-dropzone[data-declined="1"] .finding-card').length;
    var b = document.querySelector('.declined-count');
    if(b) b.textContent = n;
}

function emptyHintText(dz){
    var qm = (window.FINDINGS_QUOTATION_MODE === true);
    return (dz && dz.dataset && dz.dataset.declined === '1')
        ? (qm ? 'Drag quotation items here if the customer did not pursue them.' : 'Drag findings here if the customer did not pursue them.')
        : 'Drop cards here.';
}

function initDropzones(){
    if(typeof Sortable === 'undefined') return;
    document.querySelectorAll('.finding-dropzone').forEach(function(dz){
        if(dz._sortable) return;
        Sortable.create(dz, {
            group:'findings',
            animation:180,
            // Never drag locked (approved) cards — but on a locked RO the NEW findings
            // can still be re-ordered / grouped under a shared labor cost.
            draggable:'.finding-card:not([data-locked="1"])',
            filter:'.btn, button, a, input, select, textarea, .no-drag, .finding-linked-badge',
            preventOnFilter:false,
            ghostClass:'finding-ghost',
            forceFallback:true,
            fallbackOnBody:true,
            fallbackTolerance:5,
            touchStartThreshold:5,
            delay:130,
            delayOnTouchOnly:true,
            scroll:true,
            scrollSensitivity:80,
            scrollSpeed:14,
            chosenClass:'finding-chosen',
            dragClass:'finding-drag',
            onStart:function(){ document.body.classList.add('fixit-dragging'); },
            onMove:function(evt){
                document.querySelectorAll('.finding-dropzone.dz-over').forEach(function(d){ if(d!==evt.to) d.classList.remove('dz-over'); });
                document.querySelectorAll('.finding-group.group-over').forEach(function(x){ if(!evt.to || !x.contains(evt.to)) x.classList.remove('group-over'); });
                if(evt.to){ evt.to.classList.add('dz-over'); var gg=evt.to.closest('.finding-group'); if(gg) gg.classList.add('group-over'); }
                return true;
            },
            onEnd:function(evt){
                var card = evt.item;
                var toDz = evt.to;
                var toDeclined = (toDz.dataset.declined === '1');
                var fromDeclined = (evt.from && evt.from.dataset && evt.from.dataset.declined === '1');
                if(toDeclined){
                    // "Not Pursued": the customer did not push through — separate from the quotation.
                    fetch('/repair-orders/findings/'+card.dataset.id+'/decline', {
                        method:'POST',
                        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
                        body: JSON.stringify({declined: true})
                    }).then(function(r){ return r.json(); })
                      .then(function(){ refreshDeclinedCount(); showFixitToast('Separated from the quotation.', 'success', 'Not pursued'); })
                      .catch(function(err){ console.error('Decline error:', err); showFixitToast('Could not save.', 'error', 'Decline failed'); });
                } else {
                    var groupId = toDz.dataset.groupId;
                    fetch('/repair-orders/findings/'+card.dataset.id+'/move', {
                        method:'POST',
                        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
                        body: JSON.stringify({group_id: (groupId==='' ? null : parseInt(groupId,10)), sort_order: evt.newIndex})
                    }).then(function(r){ return r.json(); })
                      .then(function(){ refreshDeclinedCount(); if(fromDeclined) showFixitToast('Back in the quotation.', 'success', 'Pursued'); })
                      .catch(function(err){ console.error('Move error:', err); });
                }
                if(toDz.querySelectorAll('.finding-card').length>0) clearEmptyHint(toDz);
                if(evt.from.querySelectorAll('.finding-card').length===0 && evt.from !== toDz){
                    evt.from.insertAdjacentHTML('beforeend','<p class="text-muted small mb-0 py-2 text-center finding-empty-hint">'+emptyHintText(evt.from)+'</p>');
                }
                refreshGroupTotals();
                var gTo = toDz.closest ? toDz.closest('.finding-group') : null;
                if(gTo && gTo.dataset.declined !== '1' && gTo.dataset.groupId !== '') autoGroupName(gTo);
                var gFrom = (evt.from && evt.from.closest) ? evt.from.closest('.finding-group') : null;
                if(gFrom && gFrom !== gTo && gFrom.dataset.declined !== '1' && gFrom.dataset.groupId !== ''){ if(!removeGroupIfEmpty(gFrom)) autoGroupName(gFrom); }
                document.body.classList.remove('fixit-dragging');
                document.querySelectorAll('.finding-dropzone.dz-over').forEach(function(d){ d.classList.remove('dz-over'); });
                document.querySelectorAll('.finding-group.group-over').forEach(function(x){ x.classList.remove('group-over'); });
            }
        });
        dz._sortable = true;
    });
}

document.addEventListener('DOMContentLoaded', function(){
    initDropzones();
    refreshGroupTotals();
    autoNameAllGroups();
    updateFindingTabBadge();
});

/* ====================== QUICK ADD (only on + click) ====================== */
function quickAddFinding(){
    var cat = document.getElementById('quick-category').value;
    var parts = (document.getElementById('quick-parts').value || '').trim();
    if(!parts){
        showFixitToast('Please fill in the required field.', 'warn', 'Missing info'); if(false)
        document.getElementById('quick-parts').focus();
        return;
    }
    var remarks = document.getElementById('quick-remarks').value;
    var qty = document.getElementById('quick-quantity').value || 1;
    var price = document.getElementById('quick-price').value;
    var urgency = getSelectedUrgency();
    var btn = document.getElementById('btn-quick-add');
    if(btn){ btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>'; }
    fetch('/repair-orders/' + inspectionId + '/findings', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrfToken,'Accept':'application/json'},
        body: JSON.stringify({
            category: cat,
            issue_title: parts,
            remarks: remarks,
            quantity: qty,
            unit_price: price || null,
            detailed_notes: '',
            severity: 'medium',
            recommended_action: '',
            estimated_urgency: urgency,
            estimated_cost: null,
            is_quotation_added: (window.FINDINGS_QUOTATION_MODE===true)?1:0
        })
    })
    .then(function(r){ return r.json(); })
    .then(function(data){
        if(data.success){
            appendFindingCard(data.finding);
            document.getElementById('quick-parts').value = '';
            document.getElementById('quick-remarks').value = '';
            document.getElementById('quick-quantity').value = 1;
            document.getElementById('quick-price').value = '';
            document.querySelectorAll('.urgency-btn').forEach(function(b){ b.classList.toggle('active', b.dataset.value==='soon'); });
            document.getElementById('quick-parts').focus();
            showFixitToast('Finding added to Ungrouped.', 'success', 'Added');
        }
    })
    .catch(function(err){ console.error('Quick add error:', err); })
    .finally(function(){ if(btn){ btn.disabled=false; btn.innerHTML='<i class="fas fa-plus"></i>'; } });
}


/* ====================== Beautified confirm dialog ====================== */
function fixitConfirm(o){
    o = o || {};
    return new Promise(function(resolve){
        var danger = o.danger !== false;
        var bd = document.createElement('div');
        bd.className = 'fixit-confirm-backdrop';
        var icoBg = danger ? 'linear-gradient(135deg,#dc3545,#ef4444)' : 'linear-gradient(135deg,#1a237e,#3949ab)';
        bd.innerHTML = '<div class="fixit-confirm" role="dialog" aria-modal="true">'
            + '<div class="fc-top"><div class="fc-ico" style="background:'+icoBg+'"><i class="fas '+(o.icon||(danger?'fa-trash-can':'fa-circle-question'))+'"></i></div>'
            + '<h6>'+escapeHtml(o.title||'Are you sure?')+'</h6><p>'+escapeHtml(o.msg||'')+'</p></div>'
            + '<div class="fc-actions"><button class="fc-btn fc-cancel" type="button">'+escapeHtml(o.cancelText||'Cancel')+'</button>'
            + '<button class="fc-btn fc-ok '+(danger?'danger':'primary')+'" type="button">'+escapeHtml(o.confirmText||'Confirm')+'</button></div></div>';
        document.body.appendChild(bd);
        var done = false;
        function close(val){
            if(done) return; done = true;
            bd.style.animation = 'fixitFadeIn .16s ease reverse';
            setTimeout(function(){ if(bd.parentNode) bd.parentNode.removeChild(bd); }, 150);
            document.removeEventListener('keydown', onKey);
            resolve(val);
        }
        function onKey(e){ if(e.key === 'Escape') close(false); }
        bd.querySelector('.fc-cancel').addEventListener('click', function(){ close(false); });
        bd.querySelector('.fc-ok').addEventListener('click', function(){ close(true); });
        bd.addEventListener('click', function(e){ if(e.target === bd) close(false); });
        document.addEventListener('keydown', onKey);
        setTimeout(function(){ var b = bd.querySelector('.fc-ok'); if(b) b.focus(); }, 60);
    });
}

/* ====================== Beautified toast notifications ====================== */
function showFixitToast(msg, type, title){
    type = type || 'success';
    var stack = document.getElementById('fixit-toast-stack');
    if(!stack){ stack = document.createElement('div'); stack.id='fixit-toast-stack'; stack.className='fixit-toast-stack'; document.body.appendChild(stack); }
    var map = {
        success:{ico:'fa-check',               bg:'linear-gradient(135deg,#059669,#10b981)', bar:'#059669', t:'Success'},
        error:  {ico:'fa-xmark',               bg:'linear-gradient(135deg,#dc3545,#ef4444)', bar:'#dc3545', t:'Error'},
        warn:   {ico:'fa-triangle-exclamation',bg:'linear-gradient(135deg,#f59e0b,#fbbf24)', bar:'#f59e0b', t:'Warning'},
        info:   {ico:'fa-circle-info',         bg:'linear-gradient(135deg,#1a237e,#3949ab)', bar:'#1a237e', t:'Info'}
    };
    var conf = map[type] || map.success;
    var el = document.createElement('div');
    el.className = 'fixit-toast ft-' + type;
    el.innerHTML = '<span class="ft-ico" style="background:'+conf.bg+'"><i class="fas '+conf.ico+'"></i></span>'
        + '<div class="ft-body"><div class="ft-title">'+escapeHtml(title||conf.t)+'</div><div class="ft-msg">'+escapeHtml(msg||'')+'</div></div>'
        + '<button class="ft-x" type="button" aria-label="Close">&times;</button>'
        + '<span class="ft-bar" style="background:'+conf.bar+'"></span>';
    stack.appendChild(el);
    var kill = function(){ if(el._dead) return; el._dead = true; el.classList.add('fixit-out'); setTimeout(function(){ if(el.parentNode) el.parentNode.removeChild(el); }, 220); };
    el.querySelector('.ft-x').addEventListener('click', kill);
    var to = setTimeout(kill, 3600);
    el.addEventListener('mouseenter', function(){ clearTimeout(to); });
    return el;
}
document.addEventListener('DOMContentLoaded', function(){
    var fs = document.getElementById('fixit-flash-success');
    var fe = document.getElementById('fixit-flash-error');
    if(fs && fs.dataset.msg) showFixitToast(fs.dataset.msg, 'success', 'Success');
    if(fe && fe.dataset.msg) showFixitToast(fe.dataset.msg, 'error', 'Error');
});

</script>
