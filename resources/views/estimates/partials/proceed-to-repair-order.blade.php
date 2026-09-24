{{--
    "Proceed to Repair Order" — promotes a Repair Quotation back into the
    workshop as a Repair Order.

    Requires: $estimate (App\Models\Estimate). Optional: $inspection (linked RO).

    Opens a review modal (customer / vehicle / total / payments / linked RO) then
    POSTs to estimates.convert-to-repair-order. The reviewer can pick the initial
    Repair Status (e.g. "Awaiting Parts" when the car is already in the shop).
--}}
@php
    $inspection = $inspection ?? ($estimate->inspection_id
        ? \App\Models\VehicleInspection::with('repairOrderPayments')->find($estimate->inspection_id)
        : null);
    $payments = $inspection ? $inspection->repairOrderPayments : collect();
    $verifiedPaid = $payments->where('status', 'verified')->sum('amount');
    $pendingCount = $payments->where('status', 'pending')->count();
    $quotationTotal = (float) ($estimate->quotation_total ?? 0);
    $balance = max(0, $quotationTotal - (float) $verifiedPaid);
    $statuses = \App\Models\VehicleInspection::REPAIR_STATUSES;
    $alreadyConverted = in_array($estimate->status, ['converted_to_repair_order', 'converted_to_job_order', 'converted'], true);
    $canConvert = ! $alreadyConverted && $estimate->status !== 'rejected';
@endphp

@if($canConvert)
    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#proceedRoModal">
        <i class="fas fa-arrow-right-to-bracket me-1"></i>Proceed to Repair Order
    </button>

    <div class="modal fade" id="proceedRoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('estimates.convert-to-repair-order', $estimate) }}">
                    @csrf
                    <div class="modal-header" style="background:#065f46;color:#fff;">
                        <h5 class="modal-title">
                            <i class="fas fa-clipboard-check me-2"></i>Move Quotation to Repair Order
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3" style="font-size:.875rem;">
                            Review the details below. Once confirmed, the job moves into the workshop as a
                            <strong>Repair Order</strong>. Payments already recorded stay on the same ledger.
                        </p>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="text-uppercase text-muted fw-bold mb-2" style="font-size:.7rem;letter-spacing:.5px;">Customer</div>
                                    <div class="fw-semibold">{{ $estimate->customer->full_name ?? 'N/A' }}</div>
                                    <div class="text-muted" style="font-size:.8rem;">{{ $estimate->customer->phone ?? '—' }}</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="text-uppercase text-muted fw-bold mb-2" style="font-size:.7rem;letter-spacing:.5px;">Vehicle</div>
                                    @if($estimate->vehicle)
                                        <div class="fw-semibold">{{ $estimate->vehicle->year }} {{ $estimate->vehicle->make }} {{ $estimate->vehicle->model }}</div>
                                        <div class="text-muted" style="font-size:.8rem;">{{ $estimate->vehicle->license_plate }}</div>
                                    @else
                                        <span class="text-muted">No vehicle</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="text-uppercase text-muted fw-bold mb-2" style="font-size:.7rem;letter-spacing:.5px;">Quotation Total</div>
                                    <div class="fw-bold" style="font-size:1.1rem;color:#065f46;">₱{{ number_format($quotationTotal, 2) }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="text-uppercase text-muted fw-bold mb-2" style="font-size:.7rem;letter-spacing:.5px;">Verified Paid</div>
                                    <div class="fw-bold" style="font-size:1.1rem;">₱{{ number_format($verifiedPaid, 2) }}</div>
                                    @if($pendingCount)
                                        <div class="text-warning fw-semibold" style="font-size:.75rem;">{{ $pendingCount }} pending</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="text-uppercase text-muted fw-bold mb-2" style="font-size:.7rem;letter-spacing:.5px;">Balance</div>
                                    <div class="fw-bold" style="font-size:1.1rem;color:{{ $balance > 0 ? '#dc2626' : '#16a34a' }};">₱{{ number_format($balance, 2) }}</div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="border rounded-3 p-3">
                                    <div class="text-uppercase text-muted fw-bold mb-2" style="font-size:.7rem;letter-spacing:.5px;">Repair Order</div>
                                    @if($inspection)
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-link text-success"></i>
                                            <span>Linked to existing Repair Order <strong>#{{ $inspection->id }}</strong>
                                                @if($inspection->reference_number) ({{ $inspection->reference_label }})@endif
                                            </span>
                                        </div>
                                        <div class="text-muted mt-1" style="font-size:.78rem;">
                                            This quotation was created from that Repair Order, so it will simply be re-activated.
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-plus-circle text-success"></i>
                                            <span>A new Repair Order will be created and a reference number issued.</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-semibold" style="font-size:.85rem;">
                                    <i class="fas fa-screwdriver-wrench me-1"></i>Initial Repair Status
                                </label>
                                <select name="repair_status" class="form-select">
                                    @foreach($statuses as $key => $meta)
                                        <option value="{{ $key }}" {{ $key === 'received' ? 'selected' : '' }}>{{ $meta['label'] }}</option>
                                    @endforeach
                                </select>
                                <div class="text-muted mt-1" style="font-size:.75rem;">
                                    Tip: choose <strong>Awaiting Parts</strong> if the car is already in the shop while waiting for parts.
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i>Confirm — Move to Repair Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
