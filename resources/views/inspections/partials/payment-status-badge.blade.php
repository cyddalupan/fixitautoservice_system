{{--
    Compact "Payment" badge for list rows (Repair Quotations list + Repair Orders list).

    Derived from the Repair Order's repair_order_payments — the single ledger shared by
    the Repair Order page and the Repair Quotation page. Shows whether the job is
    UNPAID / DOWN PAYMENT / FULLY PAID, the remaining BALANCE (when a $total is given),
    plus a count of pending (unverified) uploads.

    Expects:
      $payments — Collection of App\Models\RepairOrderPayment (empty/null is fine).
      $total    — optional numeric total to compute the balance against
                  (quotation_total on the quotations list, repair_total on the RO list).
--}}
@php
    $payments = collect($payments ?? []);
    $verified = $payments->where('status', 'verified');
    $verifiedTotal = (float) $verified->sum('amount');
    $hasFull = $verified->contains(fn ($p) => $p->payment_type === 'full_payment');
    $pendingCount = $payments->where('status', 'pending')->count();
    $balance = ($total ?? null) !== null ? max(0, (float) $total - $verifiedTotal) : null;

    if ($hasFull) {
        $payLabel = 'FULLY PAID'; $payBg = '#16a34a'; $payIcon = 'fa-circle-check';
    } elseif ($verifiedTotal > 0) {
        $payLabel = 'DOWN PAYMENT'; $payBg = '#0ea5e9'; $payIcon = 'fa-hand-holding-dollar';
    } else {
        $payLabel = 'UNPAID'; $payBg = '#94a3b8'; $payIcon = 'fa-circle-xmark';
    }
@endphp
<span class="badge" style="background:{{ $payBg }};color:#fff;font-size:0.68rem;font-weight:600;letter-spacing:.3px;padding:.32em .6em;white-space:nowrap;">
    <i class="fas {{ $payIcon }} me-1"></i>{{ $payLabel }}
</span>
@if($balance !== null)
    <br>
    <small style="font-size:0.72rem;line-height:1.35;{{ $balance > 0 ? 'color:#dc2626;font-weight:600;' : 'color:#16a34a;font-weight:600;' }}">
        Balance: ₱{{ number_format($balance, 2) }}
    </small>
@endif
@if($pendingCount > 0)
    <br>
    <small style="color:#f59e0b;font-size:0.72rem;font-weight:600;">
        <i class="fas fa-clock me-1"></i>{{ $pendingCount }} pending
    </small>
@endif
