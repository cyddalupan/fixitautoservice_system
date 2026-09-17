@extends('layouts.app')

@section('title', 'Update Information - Fix-It Auto Services')

@section('content')
<div class="page-header mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-edit me-2"></i>Update Information
            </h1>
            <p class="text-muted mb-0">
                Appointment <strong>{{ $appointment->appointment_number }}</strong>
                @if($appointment->customer)
                    &middot; {{ $appointment->customer->full_name }}
                @endif
            </p>
        </div>
        <div>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <strong>Please check the following:</strong>
        <ul class="mb-0 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('appointments.update-info', $appointment) }}" method="POST">
    @csrf

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-calendar-check me-2"></i>Receipt</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Date Received</label>
                    <input type="date" class="form-control" name="date_received"
                           value="{{ old('date_received', $appointment->date_received ? $appointment->date_received->format('Y-m-d') : ($appointment->checked_in_at ? $appointment->checked_in_at->format('Y-m-d') : ($appointment->appointment_date ? $appointment->appointment_date->format('Y-m-d') : ''))) }}">
                    <div class="form-text">Petsa na natanggap ang sasakyan. Ito ang ma-re-record sa Repair Order pag na-Start.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-user me-2"></i>Customer</h5>
        </div>
        <div class="card-body">
            <p class="text-muted small">Punan ang mga kulang na detalye. Ang iiwan na blangko ay hindi mababago.</p>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">First Name</label>
                    <input type="text" class="form-control" name="first_name"
                           value="{{ old('first_name', $appointment->customer->first_name ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Last Name</label>
                    <input type="text" class="form-control" name="last_name"
                           value="{{ old('last_name', $appointment->customer->last_name ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mobile No.</label>
                    <input type="text" class="form-control" name="phone"
                           value="{{ old('phone', $appointment->customer->phone ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email"
                           value="{{ old('email', $appointment->customer->email ?? '') }}">
                </div>
                <div class="col-md-8">
                    <label class="form-label">Address</label>
                    <input type="text" class="form-control" name="address"
                           value="{{ old('address', $appointment->customer->address ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">City</label>
                    <input type="text" class="form-control" name="city"
                           value="{{ old('city', $appointment->customer->city ?? '') }}">
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-car me-2"></i>Vehicle</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Brand / Make</label>
                    <input type="text" class="form-control" name="make"
                           value="{{ old('make', $appointment->vehicle->make ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Model</label>
                    <input type="text" class="form-control" name="model"
                           value="{{ old('model', $appointment->vehicle->model ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Year Model</label>
                    <input type="text" class="form-control" name="year"
                           value="{{ old('year', $appointment->vehicle->year ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Plate No.</label>
                    <input type="text" class="form-control" name="license_plate"
                           value="{{ old('license_plate', $appointment->vehicle->license_plate ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">VIN No.</label>
                    <input type="text" class="form-control" name="vin"
                           value="{{ old('vin', $appointment->vehicle->vin ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Engine No.</label>
                    <input type="text" class="form-control" name="engine_no"
                           value="{{ old('engine_no', $appointment->vehicle->engine_no ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Transmission</label>
                    @php $trans = old('transmission', $appointment->vehicle->transmission ?? ''); @endphp
                    <select class="form-select" name="transmission">
                        <option value="">-- piliin --</option>
                        <option value="AT" {{ $trans === 'AT' ? 'selected' : '' }}>AT (Automatic)</option>
                        <option value="MT" {{ $trans === 'MT' ? 'selected' : '' }}>MT (Manual)</option>
                        <option value="CVT" {{ $trans === 'CVT' ? 'selected' : '' }}>CVT</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Fuel</label>
                    @php $fuel = old('fuel_type', $appointment->vehicle->fuel_type ?? ''); @endphp
                    <select class="form-select" name="fuel_type">
                        <option value="">-- piliin --</option>
                        <option value="Gas" {{ $fuel === 'Gas' ? 'selected' : '' }}>Gas</option>
                        <option value="Diesel" {{ $fuel === 'Diesel' ? 'selected' : '' }}>Diesel</option>
                        <option value="Hybrid" {{ $fuel === 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                        <option value="Electric" {{ $fuel === 'Electric' ? 'selected' : '' }}>Electric</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Odometer</label>
                    <input type="text" class="form-control" name="odometer"
                           value="{{ old('odometer', $appointment->vehicle->odometer ?? '') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Color</label>
                    <input type="text" class="form-control" name="color"
                           value="{{ old('color', $appointment->vehicle->color ?? '') }}">
                </div>
            </div>
        </div>
    </div>

    @php
        $rawTypes = old('service_types', $appointment->service_types);
        if (is_string($rawTypes)) {
            $decoded = json_decode($rawTypes, true);
            $selectedTypes = is_array($decoded) ? $decoded : ($rawTypes !== '' ? [$rawTypes] : []);
        } elseif (is_array($rawTypes)) {
            $selectedTypes = $rawTypes;
        } else {
            $selectedTypes = [];
        }
    @endphp
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Services</h5>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-3">Pwedeng higit sa isa (hal. PMS + Aircon + Basic Tune Up).</p>
            <div class="row g-2">
                @foreach(config('service-types.list', []) as $svcKey => $svcLabel)
                    <div class="col-md-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="service_types[]"
                                   value="{{ $svcKey }}" id="svc_{{ $svcKey }}"
                                   {{ in_array($svcKey, $selectedTypes, true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="svc_{{ $svcKey }}">{{ $svcLabel }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @php
        $jdItems = old('job_description_items', $appointment->job_description_items ?? []);
        if (!is_array($jdItems)) { $jdItems = []; }
        $partsItems = old('parts_items', $appointment->parts_items ?? []);
        if (!is_array($partsItems)) { $partsItems = []; }
    @endphp
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Job Description</h5>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="rosAddJobRow()"><i class="fas fa-plus me-1"></i>Add row</button>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-2">Ilagay dito ang Job Description, MH, Unit Price at Labor Cost. Ang mga naka-check na Services sa itaas ay awtomatikong nadadagdag dito (pwedeng i-edit).</p>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:42%">Job Description</th>
                            <th style="width:12%">MH</th>
                            <th style="width:18%">Unit Price</th>
                            <th style="width:20%">Labor Cost</th>
                            <th style="width:8%"></th>
                        </tr>
                    </thead>
                    <tbody id="rosJobBody">
                        @foreach($jdItems as $i => $row)
                            <tr>
                                <td><input type="text" class="form-control form-control-sm" name="job_description_items[{{ $i }}][description]" value="{{ is_array($row) ? ($row['description'] ?? '') : ($row ?? '') }}"></td>
                                <td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-jd-mh" name="job_description_items[{{ $i }}][mh]" value="{{ is_array($row) ? ($row['mh'] ?? '') : '' }}"></td>
                                <td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-jd-unit" name="job_description_items[{{ $i }}][unit_price]" value="{{ is_array($row) ? ($row['unit_price'] ?? '') : '' }}"></td>
                                <td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-jd-cost" name="job_description_items[{{ $i }}][labor_cost]" value="{{ is_array($row) ? ($row['labor_cost'] ?? '') : '' }}"></td>
                                <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">&times;</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-boxes-stacked me-2"></i>Parts / Supplies</h5>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="rosAddPartRow()"><i class="fas fa-plus me-1"></i>Add row</button>
        </div>
        <div class="card-body">
            <p class="text-muted small mb-2">Ilagay ang Parts / Supplies Description, Qty, Unit Price at Cost.</p>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width:46%">Parts / Supplies Description</th>
                            <th style="width:12%">Qty</th>
                            <th style="width:18%">Unit Price</th>
                            <th style="width:16%">Cost</th>
                            <th style="width:8%"></th>
                        </tr>
                    </thead>
                    <tbody id="rosPartBody">
                        @foreach($partsItems as $i => $row)
                            <tr>
                                <td><input type="text" class="form-control form-control-sm" name="parts_items[{{ $i }}][description]" value="{{ is_array($row) ? ($row['description'] ?? '') : ($row ?? '') }}"></td>
                                <td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-pt-qty" name="parts_items[{{ $i }}][qty]" value="{{ is_array($row) ? ($row['qty'] ?? '') : '' }}"></td>
                                <td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-pt-unit" name="parts_items[{{ $i }}][unit_price]" value="{{ is_array($row) ? ($row['unit_price'] ?? '') : '' }}"></td>
                                <td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-pt-cost" name="parts_items[{{ $i }}][cost]" value="{{ is_array($row) ? ($row['cost'] ?? '') : '' }}"></td>
                                <td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()">&times;</button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Discount</h5>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Discount (&#8369;)</label>
                    <input type="number" step="0.01" min="0" class="form-control" name="discount"
                           value="{{ old('discount', $appointment->discount ?? '') }}">
                    <div class="form-text">Lalabas ito sa printed Repair Order slip.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-comment-dots me-2"></i>Concern / Request</h5>
        </div>
        <div class="card-body">
            <textarea class="form-control" name="service_request" rows="4">{{ old('service_request', $appointment->service_request ?? '') }}</textarea>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center gap-2 mb-5 flex-wrap">
        <a href="{{ route('appointments.repair-order-slip', $appointment) }}" class="btn btn-outline-dark">
            <i class="fas fa-file-invoice me-1"></i> Review Repair Order Slip
        </a>
        <div class="d-flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-1"></i> Save Information
            </button>
        </div>
    </div>
</form>

<script>
(function () {
    var jobBody = document.getElementById('rosJobBody');
    var partBody = document.getElementById('rosPartBody');
    if (!jobBody || !partBody) { return; }
    var seq = 100000; // unique index base for JS-added rows

    function num(v) { var n = parseFloat(v); return isNaN(n) ? 0 : n; }
    function money(n) { return (Math.round(n * 100) / 100).toFixed(2); }

    // --- Job description rows ---
    window.rosAddJobRow = function (description, mh, unit, cost) {
        var i = seq++;
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td><input type="text" class="form-control form-control-sm" name="job_description_items[' + i + '][description]"></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-jd-mh" name="job_description_items[' + i + '][mh]"></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-jd-unit" name="job_description_items[' + i + '][unit_price]"></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-jd-cost" name="job_description_items[' + i + '][labor_cost]"></td>' +
            '<td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'tr\').remove()">&times;</button></td>';
        jobBody.appendChild(tr);
        if (description) { tr.querySelector('input[type=text]').value = description; }
        if (mh) { tr.querySelector('.ros-jd-mh').value = mh; }
        if (unit) { tr.querySelector('.ros-jd-unit').value = unit; }
        if (cost) { tr.querySelector('.ros-jd-cost').value = cost; }
        return tr;
    };

    // --- Parts rows ---
    window.rosAddPartRow = function (description, qty, unit, cost) {
        var i = seq++;
        var tr = document.createElement('tr');
        tr.innerHTML =
            '<td><input type="text" class="form-control form-control-sm" name="parts_items[' + i + '][description]"></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-pt-qty" name="parts_items[' + i + '][qty]"></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-pt-unit" name="parts_items[' + i + '][unit_price]"></td>' +
            '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm ros-pt-cost" name="parts_items[' + i + '][cost]"></td>' +
            '<td class="text-end"><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'tr\').remove()">&times;</button></td>';
        partBody.appendChild(tr);
        if (description) { tr.querySelector('input[type=text]').value = description; }
        if (qty) { tr.querySelector('.ros-pt-qty').value = qty; }
        if (unit) { tr.querySelector('.ros-pt-unit').value = unit; }
        if (cost) { tr.querySelector('.ros-pt-cost').value = cost; }
        return tr;
    };

    // Auto-compute labor cost (MH x Unit Price) and parts cost (Qty x Unit Price).
    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('ros-jd-mh') || e.target.classList.contains('ros-jd-unit')) {
            var tr = e.target.closest('tr');
            var mh = num(tr.querySelector('.ros-jd-mh').value);
            var un = num(tr.querySelector('.ros-jd-unit').value);
            if (mh || un) { tr.querySelector('.ros-jd-cost').value = money(mh * un); }
        }
        if (e.target.classList.contains('ros-pt-qty') || e.target.classList.contains('ros-pt-unit')) {
            var ptr = e.target.closest('tr');
            var q = num(ptr.querySelector('.ros-pt-qty').value);
            var pu = num(ptr.querySelector('.ros-pt-unit').value);
            if (q || pu) { ptr.querySelector('.ros-pt-cost').value = money(q * pu); }
        }
    });

    // Seed Job Description rows from checked Services (only if not already present).
    function syncServiceRows() {
        var existing = {};
        Array.prototype.forEach.call(jobBody.querySelectorAll('input[type=text]'), function (inp) {
            var v = (inp.value || '').trim().toUpperCase();
            if (v) { existing[v] = true; }
        });
        Array.prototype.forEach.call(document.querySelectorAll('input[name="service_types[]"]:checked'), function (cb) {
            var label = (cb.closest('.form-check').querySelector('label').textContent || '').trim();
            if (label && !existing[label.toUpperCase()]) {
                window.rosAddJobRow(label);
                existing[label.toUpperCase()] = true;
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        syncServiceRows();
        Array.prototype.forEach.call(document.querySelectorAll('input[name="service_types[]"]'), function (cb) {
            cb.addEventListener('change', syncServiceRows);
        });
    });
})();
</script>
@endsection
