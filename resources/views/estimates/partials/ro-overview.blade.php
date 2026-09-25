{{-- Read-only Repair Order Overview for Repair Quotation pages.
     Mirrors the Repair Order "Overview" tab look (Customer / Vehicle / Concern),
     but intentionally EXCLUDES "Services & Job Description" and "Assigned Team" —
     a Repair Quotation is already priced/tracked on the Repair Order.
     Expects: $inspection (App\Models\VehicleInspection). --}}
@php
    $cust = $inspection->customer ?? null;
    $veh  = $inspection->vehicle ?? null;
    $custName = $cust ? ($cust->full_name ?? trim(($cust->first_name ?? '').' '.($cust->last_name ?? ''))) : null;
    $vehName = $veh ? trim(($veh->year ?? '').' '.($veh->make ?? '').' '.($veh->model ?? '')) : null;
@endphp
<div class="row g-3">
    <div class="col-lg-6">
        <div class="form-section">
            <div class="form-section-header no-collapse">
                <h6><i class="fas fa-user"></i>Customer Information</h6>
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small text-uppercase">Name</label>
                        <p class="fw-semibold mb-0">{{ $custName ?: 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small text-uppercase">Mobile No.</label>
                        <p class="fw-semibold mb-0">{{ $cust->phone ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small text-uppercase">Email</label>
                        <p class="fw-semibold mb-0">{{ $cust->email ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small text-uppercase">Address</label>
                        <p class="fw-semibold mb-0">{{ ($cust->address ?? null) ?: 'N/A' }}{{ ($cust->city ?? null) ? ', '.$cust->city : '' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="form-section">
            <div class="form-section-header no-collapse">
                <h6><i class="fas fa-car"></i>Vehicle Information</h6>
            </div>
            <div class="form-section-body">
                @if($veh)
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">Vehicle</label>
                            <p class="fw-semibold mb-0">{{ $vehName ?: 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">License Plate</label>
                            <p class="fw-semibold mb-0">{{ $veh->license_plate ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">VIN</label>
                            <p class="fw-semibold mb-0 text-monospace">{{ $veh->vin ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">Odometer</label>
                            @php
                                // Show THIS Repair Order's own intake reading (vehicle_mileage). A fresh
                                // (quotation-promoted) RO is reset to 0, so don't fall through to the
                                // vehicle master's older reading — same rule as the Repair Order page/slip.
                                $ovm = $inspection->vehicle_mileage;
                                if ($ovm !== null) {
                                    $odoText = ((float) $ovm) > 0 ? number_format((float) $ovm) : 'N/A';
                                } else {
                                    $odoText = $veh->odometer ? number_format((float) $veh->odometer) : 'N/A';
                                }
                            @endphp
                            <p class="fw-semibold mb-0">{{ $odoText }}</p>
                        </div>
                    </div>
                @else
                    <p class="text-muted mb-0"><i class="fas fa-exclamation-circle me-1"></i>No vehicle assigned</p>
                @endif
            </div>
        </div>
    </div>

    @if($inspection->customer_concerns)
    <div class="col-12">
        <div class="form-section mb-0">
            <div class="form-section-header no-collapse">
                <h6><i class="fas fa-comment-dots"></i>Concern / Request</h6>
            </div>
            <div class="form-section-body">
                <p class="mb-0" style="white-space: pre-wrap;">{{ $inspection->customer_concerns }}</p>
            </div>
        </div>
    </div>
    @endif
</div>
