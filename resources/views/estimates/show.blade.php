@extends('layouts.app')

@section('title', 'Estimate #{{ $estimate->estimate_number }} - Fix-It Auto Services')

@push('styles')
<style>
:root {--ep:#2563eb;--es:#059669;--ew:#d97706;--ed:#dc2626;--ebg:#f8fafc;--ec:#fff;--eb:#e2e8f0;--et:#1e293b;--em:#94a3b8;--er:12px;}
body{background:var(--ebg)}

/* Header */
.est-header{background:linear-gradient(135deg,#1e293b 0%,#334155 100%);border-radius:var(--er);padding:24px 28px;margin-bottom:24px;color:#fff;position:relative;overflow:hidden}
.est-header::before{content:'';position:absolute;top:-50%;right:-10%;width:400px;height:400px;background:radial-gradient(circle,rgba(37,99,235,.15) 0%,transparent 70%);border-radius:50%;pointer-events:none}
.est-header h1{font-size:1.6rem;font-weight:700;margin:0;position:relative;z-index:1}
.est-header p{color:#94a3b8;font-size:.875rem;margin:4px 0 0;position:relative;z-index:1}
.est-header .badge{position:relative;z-index:1}

/* Status badges */
.status-badge{padding:6px 16px;border-radius:50px;font-size:.75rem;font-weight:600;display:inline-flex;align-items:center;gap:6px;text-transform:uppercase;letter-spacing:.5px}
.status-draft{background:#f1f5f9;color:#64748b}
.status-pending{background:#fef3c7;color:#92400e}
.status-sent{background:#dbeafe;color:#1e40af}
.status-viewed{background:#ede9fe;color:#5b21b6}
.status-approved{background:#d1fae5;color:#065f46}
.status-rejected{background:#fee2e2;color:#991b1b}
.status-expired{background:#f1f5f9;color:#475569}
.status-converted{background:#d1fae5;color:#065f46}

/* Cards */
.card-premium{background:var(--ec);border:1px solid var(--eb);border-radius:var(--er);overflow:hidden;margin-bottom:20px;box-shadow:0 1px 3px rgba(0,0,0,.05)}
.card-premium .ch{background:linear-gradient(135deg,#f1f5f9 0%,#e2e8f0 100%);border-bottom:1px solid var(--eb);padding:14px 20px;display:flex;align-items:center;justify-content:space-between}
.card-premium .ch h6{margin:0;font-weight:600;color:var(--et);font-size:.875rem;display:flex;align-items:center;gap:8px}
.card-premium .ch h6 i{color:var(--ep);width:18px;text-align:center}
.card-premium .cb{padding:20px}

/* Timeline */
.timeline{position:relative;padding-left:28px}
.timeline::before{content:'';position:absolute;left:8px;top:4px;bottom:4px;width:2px;background:var(--eb)}
.ti{position:relative;padding-bottom:20px}
.ti:last-child{padding-bottom:0}
.ti .dot{position:absolute;left:-24px;top:4px;width:16px;height:16px;border-radius:50%;border:3px solid var(--ep);background:var(--ec);z-index:1}
.ti.completed .dot{background:var(--ep)}
.ti.active .dot{background:var(--es);border-color:var(--es);box-shadow:0 0 0 4px rgba(5,150,105,.2)}
.ti.rejected .dot{background:var(--ed);border-color:var(--ed)}
.ti .tt{font-size:.85rem;font-weight:600;color:var(--et)}
.ti .ts{font-size:.75rem;color:var(--em)}

/* Action buttons grid */
.action-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px}
.action-grid .btn{display:flex;align-items:center;justify-content:center;gap:6px;padding:10px 12px;border-radius:8px;font-size:.8rem;font-weight:600;transition:all .15s}

/* Items table */
.it-tbl{width:100%;border-collapse:separate;border-spacing:0}
.it-tbl th{padding:10px 12px;font-size:.7rem;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--em);background:#f8fafc;text-align:left;border-bottom:2px solid var(--eb)}
.it-tbl td{padding:10px 12px;font-size:.85rem;color:var(--et);border-bottom:1px solid #f1f5f9}
.it-tbl tr:last-child td{border-bottom:none}
.it-tbl tr:hover td{background:#fafbfc}
.it-tbl .cat-tag{padding:2px 8px;border-radius:4px;font-size:.7rem;font-weight:600;display:inline-block}
.cat-parts{background:#dbeafe;color:#1e40af}
.cat-labor{background:#fef3c7;color:#92400e}
.cat-service{background:#ede9fe;color:#5b21b6}
.cat-materials{background:#d1fae5;color:#065f46}
.cat-other{background:#f1f5f9;color:#64748b}

/* Totals table */
.tt-tbl{width:auto;margin-left:auto}
.tt-tbl td{padding:6px 16px;font-size:.875rem}
.tt-tbl .lbl{color:#64748b;padding-right:24px}
.tt-tbl .val{font-weight:600;color:var(--et);text-align:right}
.tt-tbl .sep td{padding:0;border-top:1px solid var(--eb);height:1px}
.tt-tbl .gt .lbl{font-size:1rem;font-weight:700;color:var(--et)}
.tt-tbl .gt .val{font-size:1.2rem;font-weight:800;color:var(--ep)}

/* Info grid */
.info-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:8px 20px}
.info-grid .ig-item{display:flex;flex-direction:column;gap:2px}
.info-grid .ig-lbl{font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;color:var(--em);font-weight:600}
.info-grid .ig-val{font-size:.875rem;color:var(--et);font-weight:500}

/* Notes */
.note-box{padding:14px 16px;background:#f8fafc;border-radius:8px;border-left:3px solid var(--ep);margin-bottom:12px}

/* Approval section */
.approval-banner{border-radius:var(--er);padding:24px;text-align:center;margin-bottom:20px}
.approval-banner.approved{background:linear-gradient(135deg,#d1fae5,#a7f3d0);border:1px solid #6ee7b7}
.approval-banner.rejected{background:linear-gradient(135deg,#fee2e2,#fecaca);border:1px solid #fca5a5}
.approval-banner h3{font-size:1.3rem;font-weight:700;margin:0}
.approval-banner p{font-size:.9rem;margin:4px 0 0;opacity:.8}

/* History sidebar */
.hist-item{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f1f5f9}
.hist-item:last-child{border-bottom:none}
.hist-item .h-cir{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.85rem;font-weight:700;flex-shrink:0}
.hist-item .h-det{flex:1}
.hist-item .h-det .h-tit{font-size:.85rem;font-weight:600;color:var(--et)}
.hist-item .h-det .h-sub{font-size:.75rem;color:var(--em)}

/* Responsive */
@media print{.no-print{display:none!important}.card-premium{break-inside:avoid;box-shadow:none}.est-header{background:#1e293b!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}}
@media(max-width:768px){.action-grid{grid-template-columns:1fr 1fr}.info-grid{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="container-fluid py-3">

    {{-- HEADER --}}
    <div class="est-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1><i class="fas fa-file-invoice-dollar me-2"></i> Estimate #{{ $estimate->estimate_number }}</h1>
                <p>Created {{ $estimate->created_at->format('F d, Y \a\t g:i A') }}</p>
            </div>
            <div class="col-md-6 text-md-end">
                <span class="status-badge status-{{ $estimate->status }} no-print">
                    @switch($estimate->status)
                        @case('draft') <i class="fas fa-pen"></i> Draft @break
                        @case('pending') <i class="fas fa-clock"></i> Pending @break
                        @case('sent') <i class="fas fa-paper-plane"></i> Sent @break
                        @case('viewed') <i class="fas fa-eye"></i> Viewed @break
                        @case('approved') <i class="fas fa-check-circle"></i> Approved @break
                        @case('rejected') <i class="fas fa-times-circle"></i> Rejected @break
                        @case('expired') <i class="fas fa-hourglass-end"></i> Expired @break
                        @case('converted') <i class="fas fa-exchange-alt"></i> Converted @break
                        @default <i class="fas fa-circle"></i> {{ ucfirst($estimate->status) }}
                    @endswitch
                </span>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">

            {{-- APPROVAL STATUS BANNER --}}
            @if($estimate->status === 'approved')
            <div class="approval-banner approved no-print">
                <h3><i class="fas fa-check-circle me-2"></i> Approved</h3>
                <p>Approved by {{ $estimate->approvedBy->name ?? 'System' }} on {{ $estimate->approved_at ? $estimate->approved_at->format('F d, Y \a\t g:i A') : '--' }}</p>
            </div>
            @elseif($estimate->status === 'rejected')
            <div class="approval-banner rejected no-print">
                <h3><i class="fas fa-times-circle me-2"></i> Rejected</h3>
                <p>Rejected on {{ $estimate->rejected_at ? $estimate->rejected_at->format('F d, Y \a\t g:i A') : '--' }}</p>
            </div>
            @endif

            {{-- ACTIONS --}}
            <div class="card-premium no-print">
                <div class="ch"><h6><i class="fas fa-bolt"></i> Quick Actions</h6></div>
                <div class="cb">
                    <div class="action-grid">
                        @if($estimate->status === 'draft')
                        <a href="{{ route('estimates.edit', $estimate) }}" class="btn btn-primary"><i class="fas fa-edit"></i> Edit</a>
                        <form method="POST" action="{{ route('estimates.send', $estimate) }}" style="display:inline" class="d-grid">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary"><i class="fas fa-paper-plane"></i> Send</button>
                        </form>
                        @endif
                        @if(in_array($estimate->status, ['sent','viewed']))
                        <form method="POST" action="{{ route('estimates.approve', $estimate) }}" style="display:inline" class="d-grid">
                            @csrf
                            <button type="submit" class="btn btn-outline-success"><i class="fas fa-check"></i> Approve</button>
                        </form>
                        <form method="POST" action="{{ route('estimates.reject', $estimate) }}" style="display:inline" class="d-grid">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger"><i class="fas fa-times"></i> Reject</button>
                        </form>
                        @endif
                        <a href="javascript:window.print()" class="btn btn-outline-secondary"><i class="fas fa-print"></i> Print</a>
                        <a href="{{ route('estimates.edit', $estimate) }}" class="btn btn-outline-warning"><i class="fas fa-copy"></i> Duplicate</a>
                        @if($estimate->status === 'approved')
                        <form method="POST" action="{{ route('estimates.convert-to-work-order', $estimate) }}" style="display:inline" class="d-grid">
                            @csrf
                            <button type="submit" class="btn btn-success"><i class="fas fa-wrench"></i> Work Order</button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('estimates.destroy', $estimate) }}" style="display:inline" class="d-grid"
                            onsubmit="return confirm('Archive this estimate?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger"><i class="fas fa-archive"></i> Archive</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- CUSTOMER INFO --}}
            <div class="card-premium">
                <div class="ch"><h6><i class="fas fa-user"></i> Customer</h6></div>
                <div class="cb">
                    <div class="info-grid">
                        <div class="ig-item"><span class="ig-lbl">Name</span><span class="ig-val">{{ $estimate->customer->first_name ?? '' }} {{ $estimate->customer->last_name ?? '' }}</span></div>
                        <div class="ig-item"><span class="ig-lbl">Phone</span><span class="ig-val">{{ $estimate->customer->phone ?? '--' }}</span></div>
                        <div class="ig-item"><span class="ig-lbl">Email</span><span class="ig-val">{{ $estimate->customer->email ?? '--' }}</span></div>
                        <div class="ig-item"><span class="ig-lbl">Address</span><span class="ig-val">{{ Str::limit($estimate->customer->address ?? '--', 40) }}</span></div>
                    </div>
                </div>
            </div>

            {{-- VEHICLE INFO --}}
            @if($estimate->vehicle)
            <div class="card-premium">
                <div class="ch"><h6><i class="fas fa-car"></i> Vehicle</h6></div>
                <div class="cb">
                    <div class="info-grid">
                        <div class="ig-item"><span class="ig-lbl">Make</span><span class="ig-val">{{ $estimate->vehicle->make ?? '--' }}</span></div>
                        <div class="ig-item"><span class="ig-lbl">Model</span><span class="ig-val">{{ $estimate->vehicle->model ?? '--' }}</span></div>
                        <div class="ig-item"><span class="ig-lbl">Year</span><span class="ig-val">{{ $estimate->vehicle->year ?? '--' }}</span></div>
                        <div class="ig-item"><span class="ig-lbl">Plate #</span><span class="ig-val">{{ $estimate->vehicle->license_plate ?? '--' }}</span></div>
                        <div class="ig-item"><span class="ig-lbl">VIN</span><span class="ig-val">{{ $estimate->vehicle->vin ?? '--' }}</span></div>
                        @if($estimate->mileage)<div class="ig-item"><span class="ig-lbl">Mileage</span><span class="ig-val">{{ number_format($estimate->mileage) }} km</span></div>@endif
                    </div>
                </div>
            </div>
            @endif

            {{-- ESTIMATE ITEMS TABLE --}}
            <div class="card-premium">
                <div class="ch"><h6><i class="fas fa-list"></i> Items</h6><span class="badge bg-secondary rounded-pill" style="font-size:.65rem">{{ $estimate->items->count() }} items</span></div>
                <div class="cb" style="padding:0;overflow-x:auto">
                    <table class="it-tbl">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th style="text-align:center">Qty</th>
                                <th style="text-align:right">Price</th>
                                <th style="text-align:right">Discount</th>
                                <th style="text-align:right">Tax</th>
                                <th style="text-align:right">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($estimate->items as $i => $item)
                            <tr>
                                <td style="color:var(--em);font-weight:600">{{ $i + 1 }}</td>
                                <td style="font-weight:500">{{ $item->description ?? $item->item_name }}</td>
                                <td><span class="cat-tag cat-{{ $item->category ?? 'other' }}">{{ ucfirst($item->category ?? 'Other') }}</span></td>
                                <td style="text-align:center">{{ $item->quantity }}</td>
                                <td style="text-align:right">&#8369;{{ number_format($item->unit_price, 2) }}</td>
                                <td style="text-align:right">{{ $item->discount ? number_format($item->discount, 1).'%' : '--' }}</td>
                                <td style="text-align:right">{{ $item->tax_rate ? number_format($item->tax_rate, 1).'%' : '--' }}</td>
                                <td style="text-align:right;font-weight:700;color:var(--et)">&#8369;{{ number_format($item->subtotal ?? ($item->quantity * $item->unit_price), 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TOTALS --}}
            <div class="card-premium">
                <div class="cb">
                    <table class="tt-tbl">
                        <tr><td class="lbl">Subtotal</td><td class="val">&#8369;{{ number_format($estimate->subtotal ?? $estimate->items->sum(fn($i) => $i->quantity * $i->unit_price), 2) }}</td></tr>
                        <tr><td class="lbl">Parts &amp; Materials</td><td class="val">&#8369;{{ number_format($estimate->parts_total ?? 0, 2) }}</td></tr>
                        <tr><td class="lbl">Labor &amp; Services</td><td class="val">&#8369;{{ number_format($estimate->labor_total ?? 0, 2) }}</td></tr>
                        <tr class="sep"><td colspan="2"></td></tr>
                        <tr><td class="lbl">Discount</td><td class="val text-danger">-&#8369;{{ number_format($estimate->discount_amount ?? 0, 2) }}</td></tr>
                        <tr><td class="lbl">Tax (VAT)</td><td class="val">&#8369;{{ number_format($estimate->tax_total ?? 0, 2) }}</td></tr>
                        <tr class="sep"><td colspan="2"></td></tr>
                        <tr class="gt"><td class="lbl">Grand Total</td><td class="val">&#8369;{{ number_format($estimate->total_amount ?? 0, 2) }}</td></tr>
                        @if($estimate->deposit_required > 0)
                        <tr class="sep"><td colspan="2"></td></tr>
                        <tr><td class="lbl">Deposit Required</td><td class="val" style="color:#d97706;font-weight:700">&#8369;{{ number_format($estimate->deposit_required, 2) }}</td></tr>
                        <tr><td class="lbl">Balance Remaining</td><td class="val">&#8369;{{ number_format(max(0, ($estimate->total_amount ?? 0) - $estimate->deposit_required), 2) }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>

            {{-- NOTES --}}
            @if($estimate->customer_notes || $estimate->notes)
            <div class="card-premium">
                <div class="ch"><h6><i class="fas fa-sticky-note"></i> Customer Notes</h6></div>
                <div class="cb">
                    <div class="note-box">{{ $estimate->customer_notes ?? $estimate->notes }}</div>
                </div>
            </div>
            @endif

            @if($estimate->internal_notes)
            <div class="card-premium">
                <div class="ch"><h6><i class="fas fa-lock"></i> Internal Notes</h6></div>
                <div class="cb">
                    <div class="note-box" style="border-left-color:#d97706;background:#fffbeb">{{ $estimate->internal_notes }}</div>
                </div>
            </div>
            @endif

            {{-- TERMS --}}
            @if($estimate->terms)
            <div class="card-premium">
                <div class="ch"><h6><i class="fas fa-file-contract"></i> Terms &amp; Conditions</h6></div>
                <div class="cb">
                    <div style="font-size:.85rem;color:#475569;line-height:1.7;white-space:pre-wrap">{{ $estimate->terms }}</div>
                </div>
            </div>
            @endif

        </div>

        <div class="col-lg-4">

            {{-- TIMELINE --}}
            <div class="card-premium no-print">
                <div class="ch"><h6><i class="fas fa-clock"></i> Status Timeline</h6></div>
                <div class="cb">
                    <div class="timeline">
                        <div class="ti{{ $estimate->created_at ? ' completed' : '' }}">
                            <div class="dot"></div>
                            <div class="tt">Created</div>
                            <div class="ts">{{ $estimate->created_at->format('M d, Y g:i A') }}</div>
                        </div>
                        @if($estimate->sent_at)
                        <div class="ti completed">
                            <div class="dot"></div>
                            <div class="tt">Sent to Customer</div>
                            <div class="ts">{{ is_string($estimate->sent_at) ? date('M d, Y g:i A', strtotime($estimate->sent_at)) : $estimate->sent_at->format('M d, Y g:i A') }}</div>
                        </div>
                        @endif
                        @if($estimate->viewed_at)
                        <div class="ti completed">
                            <div class="dot"></div>
                            <div class="tt">Viewed by Customer</div>
                            <div class="ts">{{ is_string($estimate->viewed_at) ? date('M d, Y g:i A', strtotime($estimate->viewed_at)) : $estimate->viewed_at->format('M d, Y g:i A') }}</div>
                        </div>
                        @endif
                        @if($estimate->approved_at)
                        <div class="ti completed active">
                            <div class="dot"></div>
                            <div class="tt">Approved</div>
                            <div class="ts">{{ is_string($estimate->approved_at) ? date('M d, Y g:i A', strtotime($estimate->approved_at)) : $estimate->approved_at->format('M d, Y g:i A') }}</div>
                        </div>
                        @endif
                        @if($estimate->rejected_at)
                        <div class="ti rejected">
                            <div class="dot"></div>
                            <div class="tt">Rejected</div>
                            <div class="ts">{{ is_string($estimate->rejected_at) ? date('M d, Y g:i A', strtotime($estimate->rejected_at)) : $estimate->rejected_at->format('M d, Y g:i A') }}</div>
                        </div>
                        @endif
                        @if(!$estimate->sent_at && !$estimate->approved_at && !$estimate->rejected_at)
                        <div class="ti active">
                            <div class="dot"></div>
                            <div class="tt">Awaiting Action</div>
                            <div class="ts">Current state</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ESTIMATE INFO --}}
            <div class="card-premium">
                <div class="ch"><h6><i class="fas fa-info-circle"></i> Estimate Info</h6></div>
                <div class="cb">
                    <div class="info-grid" style="grid-template-columns:1fr">
                        <div class="ig-item"><span class="ig-lbl">Estimate #</span><span class="ig-val">{{ $estimate->estimate_number }}</span></div>
                        <div class="ig-item"><span class="ig-lbl">Issue Date</span><span class="ig-val">{{ $estimate->issue_date ? date('M d, Y', strtotime($estimate->issue_date)) : '--' }}</span></div>
                        <div class="ig-item"><span class="ig-lbl">Valid Until</span><span class="ig-val">{{ $estimate->expiry_date ? date('M d, Y', strtotime($estimate->expiry_date)) : '--' }}</span></div>
                        <div class="ig-item"><span class="ig-lbl">Status</span><span class="ig-val"><span class="status-badge status-{{ $estimate->status }}" style="font-size:.7rem">{{ ucfirst($estimate->status) }}</span></span></div>
                        @if($estimate->serviceAdvisor)
                        <div class="ig-item"><span class="ig-lbl">Service Advisor</span><span class="ig-val">{{ $estimate->serviceAdvisor->name ?? '--' }}</span></div>
                                <div class="ig-item"><span class="ig-lbl">Service Type</span><span class="ig-val">@php
    $st = $estimate->service_type;
    $stArr = is_string($st) && str_starts_with($st, '[') ? json_decode($st, true) : (is_array($st) ? $st : [$st]);
    $stArr = array_filter((array)$stArr);
@endphp
@if(!empty($stArr))
    @foreach($stArr as $stItem)
        <span class="badge bg-soft-primary text-primary me-1" style="font-weight:500;font-size:.75rem">{{ \App\Models\ServiceType::name($stItem) }}</span>
    @endforeach
@else
    <span class="text-muted">--</span>
@endif</span></div>
                        @endif
                        <div class="ig-item"><span class="ig-lbl">Created By</span><span class="ig-val">{{ $estimate->user->name ?? 'System' }}</span></div>
                    </div>
                </div>
            </div>

            {{-- LINKS --}}
            @if($estimate->appointment_id || $estimate->workOrder)
            <div class="card-premium">
                <div class="ch"><h6><i class="fas fa-link"></i> Related Records</h6></div>
                <div class="cb">
                    @if($estimate->appointment_id)
                    <a href="{{ route('appointments.show', $estimate->appointment_id) }}" class="btn btn-outline-primary btn-sm w-100 mb-2"><i class="fas fa-calendar-check me-1"></i> View Appointment</a>
                    @endif
                    @if($estimate->workOrder)
                    <a href="{{ route('work-orders.show', $estimate->workOrder) }}" class="btn btn-outline-success btn-sm w-100"><i class="fas fa-wrench me-1"></i> View Work Order</a>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>

</div>
@endsection
