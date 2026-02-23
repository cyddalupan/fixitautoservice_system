@extends('layouts.app')

@section('title', 'Invoice Details')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Invoice Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('portal.billing.index') }}">Billing</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Invoice #{{ $invoice->invoice_number }}</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('portal.billing.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Billing
            </a>
            <div class="dropdown">
                <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-gear me-1"></i> Actions
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="{{ route('portal.billing.download', $invoice->id) }}" target="_blank">
                            <i class="bi bi-download me-2"></i> Download PDF
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('portal.billing.print', $invoice->id) }}" target="_blank">
                            <i class="bi bi-printer me-2"></i> Print Invoice
                        </a>
                    </li>
                    @if($invoice->balance > 0)
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item text-success" href="#" data-bs-toggle="modal" data-bs-target="#paymentModal">
                            <i class="bi bi-credit-card me-2"></i> Make Payment
                        </a>
                    </li>
                    @endif
                    @if($invoice->status === 'paid')
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('portal.billing.receipt', $invoice->id) }}" target="_blank">
                            <i class="bi bi-receipt me-2"></i> View Receipt
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <!-- Invoice Status Alert -->
    @if($invoice->status === 'overdue')
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>This invoice is overdue!</strong> Please make payment as soon as possible to avoid late fees.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @elseif($invoice->status === 'pending')
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-clock me-2"></i>
        <strong>This invoice is pending payment.</strong> Due date: {{ $invoice->due_date->format('F j, Y') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @elseif($invoice->status === 'paid')
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <strong>This invoice has been paid in full.</strong> Thank you for your payment.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="row">
        <!-- Left Column: Invoice Details -->
        <div class="col-lg-8">
            <!-- Invoice Header -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h5 class="text-muted mb-2">Bill To:</h5>
                                <address class="mb-0">
                                    <strong>{{ $invoice->customer->name }}</strong><br>
                                    @if($invoice->customer->address)
                                    {{ $invoice->customer->address }}<br>
                                    @endif
                                    @if($invoice->customer->city && $invoice->customer->state)
                                    {{ $invoice->customer->city }}, {{ $invoice->customer->state }} {{ $invoice->customer->zip_code }}<br>
                                    @endif
                                    @if($invoice->customer->phone)
                                    <i class="bi bi-telephone me-1"></i> {{ $invoice->customer->phone }}<br>
                                    @endif
                                    @if($invoice->customer->email)
                                    <i class="bi bi-envelope me-1"></i> {{ $invoice->customer->email }}
                                    @endif
                                </address>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-md-end">
                                <h5 class="text-muted mb-2">Invoice Details:</h5>
                                <table class="table table-sm table-borderless">
                                    <tr>
                                        <th class="text-start">Invoice #:</th>
                                        <td class="text-end">{{ $invoice->invoice_number }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-start">Issue Date:</th>
                                        <td class="text-end">{{ $invoice->issue_date->format('F j, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-start">Due Date:</th>
                                        <td class="text-end">
                                            @if($invoice->due_date->isPast() && $invoice->status !== 'paid')
                                            <span class="text-danger">{{ $invoice->due_date->format('F j, Y') }}</span>
                                            @else
                                            {{ $invoice->due_date->format('F j, Y') }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-start">Status:</th>
                                        <td class="text-end">
                                            @if($invoice->status === 'paid')
                                            <span class="badge bg-success">Paid</span>
                                            @elseif($invoice->status === 'pending')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                            @elseif($invoice->status === 'overdue')
                                            <span class="badge bg-danger">Overdue</span>
                                            @elseif($invoice->status === 'draft')
                                            <span class="badge bg-secondary">Draft</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @if($invoice->work_order)
                                    <tr>
                                        <th class="text-start">Work Order:</th>
                                        <td class="text-end">
                                            <a href="{{ route('portal.service-requests.show', $invoice->work_order->id) }}" class="text-decoration-none">
                                                #{{ $invoice->work_order->work_order_number }}
                                            </a>
                                        </td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Line Items -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Invoice Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Description</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end">Tax</th>
                                    <th class="text-end pe-4">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $item)
                                <tr>
                                    <td class="ps-4">
                                        <div>
                                            <strong>{{ $item->description }}</strong>
                                            @if($item->details)
                                            <div class="text-muted small mt-1">{{ $item->details }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">{{ $item->quantity }}</td>
                                    <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end">
                                        @if($item->tax_rate > 0)
                                        ${{ number_format($item->tax_amount, 2) }}
                                        <div class="text-muted small">({{ $item->tax_rate }}%)</div>
                                        @else
                                        <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">${{ number_format($item->total_amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            @if($invoice->notes)
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Notes</h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $invoice->notes }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Column: Summary & Actions -->
        <div class="col-lg-4">
            <!-- Summary Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Invoice Summary</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th class="text-start">Subtotal:</th>
                            <td class="text-end">${{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <th class="text-start">Tax:</th>
                            <td class="text-end">${{ number_format($invoice->tax_amount, 2) }}</td>
                        </tr>
                        @if($invoice->discount_amount > 0)
                        <tr>
                            <th class="text-start">Discount:</th>
                            <td class="text-end text-success">-${{ number_format($invoice->discount_amount, 2) }}</td>
                        </tr>
                        @endif
                        @if($invoice->shipping_amount > 0)
                        <tr>
                            <th class="text-start">Shipping:</th>
                            <td class="text-end">${{ number_format($invoice->shipping_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="border-top">
                            <th class="text-start"><strong>Total:</strong></th>
                            <td class="text-end"><strong>${{ number_format($invoice->total_amount, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <th class="text-start">Paid:</th>
                            <td class="text-end text-success">${{ number_format($invoice->paid_amount, 2) }}</td>
                        </tr>
                        <tr class="border-top">
                            <th class="text-start"><strong>Balance Due:</strong></th>
                            <td class="text-end">
                                <strong class="{{ $invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                                    ${{ number_format($invoice->balance, 2) }}
                                </strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Payment History -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Payment History</h5>
                </div>
                <div class="card-body">
                    @if($invoice->payments->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($invoice->payments as $payment)
                        <div class="list-group-item px-0 py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-medium">{{ $payment->payment_method }}</div>
                                    <div class="text-muted small">{{ $payment->payment_date->format('M j, Y') }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-medium text-success">${{ number_format($payment->amount, 2) }}</div>
                                    <div class="text-muted small">
                                        @if($payment->status === 'completed')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Completed</span>
                                        @elseif($payment->status === 'pending')
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">Pending</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @if($payment->transaction_id)
                            <div class="text-muted small mt-1">
                                <i class="bi bi-hash me-1"></i> {{ $payment->transaction_id }}
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-3">
                        <i class="bi bi-credit-card display-1 text-muted mb-3"></i>
                        <p class="text-muted mb-0">No payments recorded</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($invoice->balance > 0)
                        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#paymentModal">
                            <i class="bi bi-credit-card me-1"></i> Pay Invoice
                        </button>
                        @endif
                        <a href="{{ route('portal.billing.download', $invoice->id) }}" class="btn btn-outline-primary" target="_blank">
                            <i class="bi bi-download me-1"></i> Download PDF
                        </a>
                        <a href="{{ route('portal.billing.print', $invoice->id) }}" class="btn btn-outline-secondary" target="_blank">
                            <i class="bi bi-printer me-1"></i> Print Invoice
                        </a>
                        @if($invoice->status !== 'paid')
                        <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#disputeModal">
                            <i class="bi bi-flag me-1"></i> Dispute Charge
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Pay Invoice #{{ $invoice->invoice_number }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('portal.billing.pay') }}">
                @csrf
                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        You are about to make a payment for Invoice #{{ $invoice->invoice_number }}
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_amount" class="form-label">Payment Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="payment_amount" name="amount" 
                                   value="{{ number_format($invoice->balance, 2, '.', '') }}" 
                                   min="0.01" max="{{ number_format($invoice->balance, 2, '.', '') }}" 
                                   step="0.01" required>
                        </div>
                        <div class="form-text">Balance due: ${{ number_format($invoice->balance, 2) }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select class="form-select" id="payment_method" name="payment_method_id" required>
                            <option value="">Select payment method</option>
                            @foreach($paymentMethods as $method)
                            <option value="{{ $method->id }}">
                                @if($method->type === 'credit_card')
                                {{ $method->card_type }} •••• {{ $method->last_four }}
                                @elseif($method->type === 'bank_account')
                                Bank Account •••• {{ $method->last_four }}
                                @else
                                {{ ucfirst($method->type) }}
                                @endif
                                @if($method->is_default)
                                (Default)
                                @endif
                            </option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            <a href="{{ route('portal.billing.payment-methods') }}" class="text-decoration-none">
                                <i class="bi bi-plus-circle me-1"></i> Add new payment method
                            </a>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="payment_notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="payment_notes" name="notes" rows="2" placeholder="Add any notes about this payment..."></textarea>
                    </div>
                    
                    <div class="alert alert-light border">
                        <h6 class="alert-heading mb-2"><i class="bi bi-shield-check me-2"></i>Secure Payment</h6>
                        <ul class="mb-0 small">
                            <li>All payments are processed securely</li>
                            <li>You will receive a receipt via email</li>
                            <li>Payment will be applied immediately</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-credit-card me-1"></i> Process Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Dispute Modal -->
<div class="modal fade" id="disputeModal" tabindex="-1" aria-labelledby="disputeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="disputeModalLabel">Dispute Invoice #{{ $invoice->invoice_number }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('portal.billing.dispute') }}">
                @csrf
                <input type="hidden" name="invoice_id" value="{{ $invoice->id }}">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Please provide details about why you are disputing this invoice.
                    </div>
                    
                    <div class="mb-3">
                        <label for="dispute_reason" class="form-label">Reason for Dispute</label>
                        <select class="form-select" id="dispute_reason" name="reason" required>
                            <option value="">Select reason</option>
                            <option value="incorrect_amount">Incorrect Amount</option>
                            <option value="duplicate_charge">Duplicate Charge</option>
                            <option value="service_not_received">Service Not Received</option>
                            <option value="quality_issue">Quality Issue</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="dispute_details" class="form-label">Details</label>
                        <textarea class="form-control" id="dispute_details" name="details" 
                                  rows="4" placeholder="Please provide specific details about your dispute..." required></textarea>
                        <div class="form-text">Please be as specific as possible</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="dispute_contact" class="form-label">Preferred Contact Method</label>
                        <select class="form-select" id="dispute_contact" name="contact_method">
                            <option value="email">Email</option>
                            <option value="phone">Phone</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-flag me-1"></i> Submit Dispute
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Payment amount validation
        const paymentAmountInput = document.getElementById('payment_amount');
        if (paymentAmountInput) {
            paymentAmountInput.addEventListener('change', function() {
                const maxAmount = parseFloat(this.getAttribute('max'));
                const currentAmount = parseFloat(this.value) || 0;
                
                if (currentAmount > maxAmount) {
                    this.value = maxAmount.toFixed(2);
                    alert('Payment amount cannot exceed balance due.');
                } else if (currentAmount < 0.01) {
                    this.value = '0.01';
                    alert('Payment amount must be at least $0.01.');
                }
            });
        }
        
        // Payment form validation
        const paymentForm = document.querySelector('#paymentModal form');
        if (paymentForm) {
            paymentForm.addEventListener('submit', function(e) {
                const amount = parseFloat(paymentAmountInput.value) || 0;
                const maxAmount = parseFloat(paymentAmountInput.getAttribute('max'));
                
                if (amount < 0.01) {
                    e.preventDefault();
                    alert('Payment amount must be at least $0.01.');
                    return false;
                }
                
                if (amount > maxAmount) {
                    e.preventDefault();
                    alert('Payment amount cannot exceed balance due.');
                    return false;
                }
                
                // Show loading state
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Processing...';
                submitBtn.disabled = true;
                
                // Re-enable after 10 seconds if still on page
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 10000);
            });
        }
    });
</script>
@endpush
@endsection