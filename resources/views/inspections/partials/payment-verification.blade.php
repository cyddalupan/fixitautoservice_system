{{--
    Payment Verification panel (Repair Order right sidebar).

    Lets staff upload a payment (down payment / full payment) with a proof
    (receipt / screenshot / PDF) directly on the repair order page. Each entry
    is "pending" until verified, so the system can track whether the repair
    order is unpaid, partially paid (down payment) or fully paid.

    Expects: $inspection (VehicleInspection) with ->repairOrderPayments loaded.
--}}
@php
    // $payments may be passed explicitly (e.g. a Repair Quotation page passes only the
    // payments that belong to THAT quotation). Defaults to all of the RO's payments.
    $payments = $payments ?? ($inspection->repairOrderPayments ?? collect());
    $payments = collect($payments)->sortByDesc('created_at');

    $verified = $payments->where('status', 'verified');
    $verifiedTotal = (float) $verified->sum('amount');
    $hasFull = $verified->contains(fn ($p) => $p->payment_type === 'full_payment');
    $pendingCount = $payments->where('status', 'pending')->count();

    $isFullyPaid = $hasFull;
    $isDownPaid = !$isFullyPaid && $verifiedTotal > 0;
    $overallLabel = $isFullyPaid ? 'FULLY PAID' : ($isDownPaid ? 'DOWN PAYMENT' : 'UNPAID');
    $overallBadge = $isFullyPaid ? 'success' : ($isDownPaid ? 'info' : 'secondary');

    // The total this panel is paid against. On the Repair Order page it's the RO's
    // repair total; on a Repair Quotation page the caller passes paymentTotal /
    // paymentTotalFormatted (parts + labor from the linked RO's findings, excluding
    // "Not Pursued" — same amount the quotations list shows).
    $repairTotal = (float) ($paymentTotal ?? $inspection->repair_total);
    $repairTotalFormatted = $paymentTotalFormatted ?? $inspection->repair_total_formatted;
    $totalLabel = $totalLabel ?? 'Repair total';
    $balanceDue = max(0, $repairTotal - $verifiedTotal);
    $pendingAmount = (float) $payments->where('status', 'pending')->sum('amount');
@endphp

<div class="form-section" id="payment-verification-card">
    <div class="form-section-header no-collapse d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-money-check-dollar"></i>Payment Verification</h6>
        <span class="badge bg-{{ $overallBadge }}" id="payment-overall-badge">{{ $overallLabel }}</span>
    </div>
    <div class="form-section-body">

        {{-- Summary / balance --}}
        <div class="d-flex justify-content-between align-items-center mb-1">
            <small class="text-muted">{{ $totalLabel }}</small>
            <strong>{{ $repairTotalFormatted }}</strong>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-1">
            <small class="text-muted">Verified payments</small>
            <strong id="payment-verified-total">₱{{ number_format($verifiedTotal, 2) }}</strong>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-1 border-top pt-2 mt-1">
            <small class="fw-semibold">Balance due</small>
            <strong id="payment-balance" class="{{ $balanceDue > 0 ? 'text-danger' : 'text-success' }}">
                ₱{{ number_format($balanceDue, 2) }}
            </strong>
        </div>
        @if($pendingAmount > 0)
            <div class="d-flex justify-content-between align-items-center mb-1">
                <small class="text-muted">Pending (unverified)&nbsp;<span class="badge bg-warning text-dark">{{ $pendingCount }}</span></small>
                <span class="text-muted" style="font-size:.8rem;">₱{{ number_format($pendingAmount, 2) }}</span>
            </div>
        @endif

        {{-- Toggle upload form --}}
        <button class="btn btn-sm btn-primary w-100 mt-2" type="button"
                data-bs-toggle="collapse" data-bs-target="#paymentUploadForm"
                aria-expanded="false" aria-controls="paymentUploadForm">
            <i class="fas fa-upload me-1"></i>Upload Payment / Proof
        </button>

        <div class="collapse mt-3" id="paymentUploadForm">
            <form action="{{ route('inspections.payments.store', $inspection) }}"
                  method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-2">
                    <label class="form-label small mb-1">Amount <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0.01" name="amount"
                           class="form-control form-control-sm" placeholder="0.00"
                           data-balance="{{ number_format($balanceDue, 2, '.', '') }}" required>
                    <small class="text-muted d-block" id="payment-remaining-hint">
                        Balance due: ₱{{ number_format($balanceDue, 2) }}
                    </small>
                </div>
                <div class="mb-2">
                    <label class="form-label small mb-1">Payment Type <span class="text-danger">*</span></label>
                    <select name="payment_type" class="form-select form-select-sm" required>
                        <option value="down_payment">Down Payment (partial)</option>
                        <option value="full_payment">Full Payment</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small mb-1">Payment Method</label>
                    <select name="payment_method" class="form-select form-select-sm">
                        <option value="">— Select —</option>
                        <option value="cash">Cash</option>
                        <option value="gcash">GCash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="cheque">Cheque</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small mb-1">Reference No.</label>
                    <input type="text" name="reference_number" class="form-control form-control-sm"
                           placeholder="e.g. GCash ref / OR no.">
                </div>
                <div class="mb-2">
                    <label class="form-label small mb-1">Proof <span class="text-danger">*</span></label>
                    <input type="file" name="proof" class="form-control form-control-sm"
                           accept="image/*,application/pdf" required>
                    <small class="text-muted">Receipt / screenshot / PDF — max 8 MB.</small>
                </div>
                <div class="mb-2">
                    <label class="form-label small mb-1">Notes</label>
                    <textarea name="notes" rows="2" class="form-control form-control-sm"
                              placeholder="Optional"></textarea>
                </div>
                <button type="submit" class="btn btn-sm btn-success w-100">
                    <i class="fas fa-check me-1"></i>Submit for Verification
                </button>
            </form>
        </div>

        {{-- History --}}
        <hr class="my-3">
        <small class="text-muted d-block mb-2">Payment records ({{ $payments->count() }})</small>

        @forelse($payments as $p)
            <div class="border rounded p-2 mb-2" style="font-size:0.82rem;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>₱{{ number_format((float) $p->amount, 2) }}</strong>
                        <span class="badge bg-light text-dark border ms-1">{{ $p->type_label }}</span>
                    </div>
                    <span class="badge bg-{{ $p->status_badge }}">{{ $p->status_label }}</span>
                </div>
                <div class="text-muted mt-1">
                    {{ $p->created_at ? $p->created_at->format('M j, Y g:i A') : '' }}
                    @if($p->payment_method) &middot; {{ ucfirst(str_replace('_', ' ', $p->payment_method)) }} @endif
                    @if($p->reference_number) &middot; Ref: {{ $p->reference_number }} @endif
                </div>
                @if($p->uploadedBy)
                    <div class="text-muted"><i class="fas fa-user me-1"></i>{{ $p->uploadedBy->name }}</div>
                @endif
                @if($p->notes)
                    <div class="text-muted fst-italic">{{ $p->notes }}</div>
                @endif

                @if($p->proof_url)
                    @php
                        $proofExt = strtolower(pathinfo((string) $p->proof_path, PATHINFO_EXTENSION));
                        $proofIsImage = in_array($proofExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
                    @endphp
                    <button type="button" class="btn btn-link p-0 mt-1 d-inline-block align-baseline"
                            data-bs-toggle="modal" data-bs-target="#paymentProofModal"
                            data-proof-url="{{ $p->proof_url }}"
                            data-proof-type="{{ $proofIsImage ? 'image' : 'file' }}"
                            data-proof-label="₱{{ number_format((float) $p->amount, 2) }} &middot; {{ $p->type_label }}">
                        <i class="fas fa-paperclip me-1"></i>View proof
                    </button>
                @endif

                <div class="d-flex gap-1 mt-2 flex-wrap">
                    @if($p->status !== 'verified')
                        <form action="{{ route('inspections.payments.verify', $p) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-xs btn-outline-success btn-sm py-0 px-2" title="Mark verified">
                                <i class="fas fa-check"></i> Verify
                            </button>
                        </form>
                    @endif
                    @if($p->status !== 'rejected')
                        <form action="{{ route('inspections.payments.reject', $p) }}" method="POST" class="d-inline">
                            @csrf
                            <button class="btn btn-xs btn-outline-danger btn-sm py-0 px-2" title="Mark rejected">
                                <i class="fas fa-times"></i> Reject
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('inspections.payments.destroy', $p) }}" method="POST" class="d-inline"
                          onsubmit="return confirm('Remove this payment record?');">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm py-0 px-2 text-muted" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-muted small mb-0">No payments recorded yet.</p>
        @endforelse
    </div>
</div>

@once
<div class="modal fade" id="paymentProofModal" tabindex="-1" aria-labelledby="paymentProofModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="paymentProofModalLabel">
                    <i class="fas fa-receipt me-1"></i>Payment proof
                    <span class="text-muted fw-normal" id="paymentProofSubtitle"></span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center d-flex align-items-center justify-content-center" style="min-height:220px;">
                <img id="paymentProofImage" src="" alt="Payment proof" class="img-fluid rounded d-none" style="max-height:72vh;">
                <iframe id="paymentProofFrame" src="" class="d-none w-100" style="height:72vh;border:0;" title="Payment proof"></iframe>
                <p id="paymentProofEmpty" class="text-muted small mb-0 d-none">Preview not available.</p>
            </div>
            <div class="modal-footer justify-content-between">
                <a href="#" id="paymentProofOpenNew" class="btn btn-sm btn-outline-secondary" target="_blank" rel="noopener">
                    <i class="fas fa-external-link-alt me-1"></i>Open in new tab
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    document.addEventListener('show.bs.modal', function (ev) {
        var modal = ev.target;
        if (modal.id !== 'paymentProofModal') return;

        var trigger = ev.relatedTarget;
        if (!trigger) return;

        var url   = trigger.getAttribute('data-proof-url') || '';
        var type  = trigger.getAttribute('data-proof-type') || 'file';
        var label = trigger.getAttribute('data-proof-label') || '';

        var img   = modal.querySelector('#paymentProofImage');
        var frame = modal.querySelector('#paymentProofFrame');
        var empty = modal.querySelector('#paymentProofEmpty');

        modal.querySelector('#paymentProofSubtitle').innerHTML = label ? ' &mdash; ' + label : '';
        modal.querySelector('#paymentProofOpenNew').href = url;

        img.classList.add('d-none');   img.src = '';
        frame.classList.add('d-none'); frame.src = '';
        empty.classList.add('d-none');

        if (!url) {
            empty.classList.remove('d-none');
        } else if (type === 'image') {
            img.src = url;
            img.classList.remove('d-none');
            img.onerror = function () { img.classList.add('d-none'); empty.classList.remove('d-none'); };
        } else {
            // PDF / other — embed in an iframe (browser PDF viewer).
            frame.src = url;
            frame.classList.remove('d-none');
        }
    });

    document.addEventListener('hidden.bs.modal', function (ev) {
        if (ev.target.id !== 'paymentProofModal') return;
        ev.target.querySelector('#paymentProofImage').src = '';
        ev.target.querySelector('#paymentProofFrame').src = '';
    });
})();
</script>
@endonce

@once
<script>
(function () {
    var inp  = document.querySelector('#paymentUploadForm input[name="amount"]');
    var hint = document.getElementById('payment-remaining-hint');
    if (!inp || !hint) return;

    var balance = parseFloat(inp.dataset.balance || '0') || 0;
    var peso = function (n) {
        return '₱' + n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    function update() {
        var amt = parseFloat(inp.value || '0');
        if (isNaN(amt) || amt <= 0) {
            hint.textContent = 'Balance due: ' + peso(balance);
            hint.classList.add('text-muted');
            return;
        }
        var rem = balance - amt;
        if (rem > 0) {
            hint.textContent = 'Remaining after this payment: ' + peso(rem);
        } else if (rem < 0) {
            hint.textContent = 'Overpayment of ' + peso(Math.abs(rem));
        } else {
            hint.textContent = 'Fully paid — balance will be ₱0.00';
        }
        hint.classList.add('text-muted');
    }

    inp.addEventListener('input', update);
    update();
})();
</script>
@endonce
