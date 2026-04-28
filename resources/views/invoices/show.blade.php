@extends('layouts.app')

@section('title', 'Invoice ' . $invoice->invoice_number . ' - Fix-It Auto Services')

@section('content')
<!-- Service Progress Bar -->
@if($invoice->serviceProgress)
    <div class="row mb-4">
        <div class="col-12">
            @include('components.service-progress-bar', [
                'progress' => $invoice->serviceProgress,
                'currentStage' => 'invoice'
            ])
        </div>
    </div>
@endif

<!-- Invoice Header with Status -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h1 class="h2 mb-1">
                            <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
                            Invoice #{{ $invoice->invoice_number }}
                        </h1>
                        <p class="text-muted mb-2">
                            <i class="fas fa-user me-1"></i>
                            {{ $invoice->customer->first_name }} {{ $invoice->customer->last_name }}
                            • 
                            <i class="fas fa-calendar me-1"></i>
                            {{ $invoice->invoice_date->format('F d, Y') }}
                        </p>
                        
                        <!-- Status Badges -->
                        <div class="d-flex gap-2 mb-3">
                            <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : ($invoice->status === 'partial' ? 'warning' : ($invoice->status === 'overdue' ? 'danger' : 'secondary')) }} fs-6 px-3 py-2">
                                <i class="fas fa-{{ $invoice->status === 'paid' ? 'check-circle' : ($invoice->status === 'overdue' ? 'exclamation-triangle' : 'clock') }} me-1"></i>
                                {{ ucfirst($invoice->status) }}
                            </span>
                            
                            <span class="badge bg-{{ $invoice->payment_status === 'paid' ? 'success' : ($invoice->payment_status === 'partial' ? 'warning' : 'danger') }} fs-6 px-3 py-2">
                                <i class="fas fa-{{ $invoice->payment_status === 'paid' ? 'money-bill-wave' : ($invoice->payment_status === 'partial' ? 'money-bill' : 'exclamation-circle') }} me-1"></i>
                                {{ ucfirst($invoice->payment_status) }}
                            </span>
                            
                            @if($invoice->due_date && $invoice->due_date->isPast() && $invoice->balance_due > 0)
                                <span class="badge bg-danger fs-6 px-3 py-2">
                                    <i class="fas fa-calendar-times me-1"></i>
                                    Overdue: {{ $invoice->due_date->diffForHumans() }}
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="btn-group">
                        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back
                        </a>
                        
                        @if($invoice->status === 'draft')
                            <a href="{{ route('invoices.edit', $invoice) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                        @endif
                        
                        <div class="btn-group">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-cog me-1"></i> Actions
                            </button>
                            <div class="dropdown-menu">
                                <a href="{{ route('invoices.pdf', $invoice) }}" class="dropdown-item" target="_blank">
                                    <i class="fas fa-file-pdf me-2"></i> Download PDF
                                </a>
                                <a href="{{ route('invoices.print', $invoice) }}" class="dropdown-item" target="_blank">
                                    <i class="fas fa-print me-2"></i> Print Invoice
                                </a>
                                <div class="dropdown-divider"></div>
                                @if($invoice->status === 'sent')
                                    <a href="{{ route('invoices.send', $invoice) }}" class="dropdown-item">
                                        <i class="fas fa-paper-plane me-2"></i> Send to Customer
                                    </a>
                                @endif
                                @if($invoice->status !== 'cancelled')
                                    <a href="#" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#cancelInvoiceModal">
                                        <i class="fas fa-times-circle me-2"></i> Cancel Invoice
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Financial Summary -->
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Total Amount</h6>
                                <h3 class="mb-0 text-dark">₱{{ number_format($invoice->total_amount, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Amount Paid</h6>
                                <h3 class="mb-0 text-success">₱{{ number_format($invoice->amount_paid, 2) }}</h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Balance Due</h6>
                                <h3 class="mb-0 {{ $invoice->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                                    ₱{{ number_format($invoice->balance_due, 2) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Payment Progress</h6>
                                @php
                                    $progress = $invoice->total_amount > 0 ? ($invoice->amount_paid / $invoice->total_amount) * 100 : 0;
                                @endphp
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $progress >= 100 ? 'success' : ($progress >= 50 ? 'warning' : 'danger') }}" 
                                         role="progressbar" 
                                         style="width: {{ $progress }}%"
                                         aria-valuenow="{{ $progress }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                    </div>
                                </div>
                                <small class="text-muted mt-1">{{ number_format($progress, 1) }}% Paid</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content: Invoice Details + Payments -->
<div class="row">
    <!-- Left Column: Invoice Details -->
    <div class="col-lg-8 mb-4">
        <!-- Customer & Vehicle Info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-user-circle me-2"></i>
                    Customer & Vehicle Information
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Customer Details</h6>
                        <p class="mb-1">
                            <strong>{{ $invoice->customer->first_name }} {{ $invoice->customer->last_name }}</strong>
                        </p>
                        <p class="mb-1">
                            <i class="fas fa-phone me-1 text-muted"></i>
                            {{ $invoice->customer->phone }}
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-envelope me-1 text-muted"></i>
                            {{ $invoice->customer->email }}
                        </p>
                    </div>
                    
                    @if($invoice->vehicle)
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Vehicle Details</h6>
                        <p class="mb-1">
                            <strong>{{ $invoice->vehicle->make }} {{ $invoice->vehicle->model }} ({{ $invoice->vehicle->year }})</strong>
                        </p>
                        <p class="mb-1">
                            <i class="fas fa-car me-1 text-muted"></i>
                            {{ $invoice->vehicle->license_plate }}
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-barcode me-1 text-muted"></i>
                            VIN: {{ $invoice->vehicle->vin }}
                        </p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-list-alt me-2"></i>
                    Invoice Items
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0">Description</th>
                                <th class="border-0 text-end">Quantity</th>
                                <th class="border-0 text-end">Unit Price</th>
                                <th class="border-0 text-end">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                            <tr>
                                <td class="border-0">
                                    <strong>{{ $item->description }}</strong>
                                    @if($item->part_number)
                                        <br><small class="text-muted">Part #: {{ $item->part_number }}</small>
                                    @endif
                                </td>
                                <td class="border-0 text-end">{{ number_format($item->quantity, 2) }}</td>
                                <td class="border-0 text-end">₱{{ number_format($item->unit_price, 2) }}</td>
                                <td class="border-0 text-end">₱{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Invoice Summary -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-calculator me-2"></i>
                    Invoice Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 offset-md-6">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="border-0 text-muted">Subtotal</td>
                                    <td class="border-0 text-end">₱{{ number_format($invoice->subtotal, 2) }}</td>
                                </tr>
                                @if($invoice->tax_amount > 0)
                                <tr>
                                    <td class="border-0 text-muted">Tax ({{ $invoice->tax_rate }}%)</td>
                                    <td class="border-0 text-end">₱{{ number_format($invoice->tax_amount, 2) }}</td>
                                </tr>
                                @endif
                                @if($invoice->discount_amount > 0)
                                <tr>
                                    <td class="border-0 text-muted">Discount</td>
                                    <td class="border-0 text-end text-danger">-₱{{ number_format($invoice->discount_amount, 2) }}</td>
                                </tr>
                                @endif
                                @if($invoice->shipping_amount > 0)
                                <tr>
                                    <td class="border-0 text-muted">Shipping</td>
                                    <td class="border-0 text-end">₱{{ number_format($invoice->shipping_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr class="table-light">
                                    <td class="border-0"><strong>Total Amount</strong></td>
                                    <td class="border-0 text-end"><strong>₱{{ number_format($invoice->total_amount, 2) }}</strong></td>
                                </tr>
                                <tr class="table-success">
                                    <td class="border-0"><strong>Amount Paid</strong></td>
                                    <td class="border-0 text-end"><strong>₱{{ number_format($invoice->amount_paid, 2) }}</strong></td>
                                </tr>
                                <tr class="table-{{ $invoice->balance_due > 0 ? 'danger' : 'success' }}">
                                    <td class="border-0"><strong>Balance Due</strong></td>
                                    <td class="border-0 text-end"><strong>₱{{ number_format($invoice->balance_due, 2) }}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Column: Payments Section -->
    <div class="col-lg-4 mb-4">
        <!-- Quick Payment Form -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-credit-card me-2"></i>
                    Record Payment
                </h5>
            </div>
            <div class="card-body">
                @if($invoice->balance_due > 0)
                    <form action="{{ route('invoices.record-payment', $invoice) }}" method="POST" id="paymentForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="payment_amount" class="form-label">Payment Amount *</label>
                            <div class="input-group">
                                <span class="input-group-text">₱</span>
                                <input type="number" 
                                       class="form-control" 
                                       id="payment_amount" 
                                       name="amount" 
                                       step="0.01" 
                                       min="0.01" 
                                       max="{{ $invoice->balance_due }}"
                                       value="{{ $invoice->balance_due }}"
                                       required>
                            </div>
                            <small class="text-muted">Balance due: ₱{{ number_format($invoice->balance_due, 2) }}</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_method" class="form-label">Payment Method *</label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                <option value="">Select Method</option>
                                <option value="cash">Cash</option>
                                <option value="check">Check</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="gcash">GCash</option>
                                <option value="paymaya">PayMaya</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="reference_number" class="form-label">Reference Number</label>
                            <input type="text" class="form-control" id="reference_number" name="reference_number" placeholder="Check #, Transaction ID, etc.">
                        </div>
                        
                        <div class="mb-3">
                            <label for="payment_date" class="form-label">Payment Date *</label>
                            <input type="date" class="form-control" id="payment_date" name="payment_date" value="{{ date('Y-m-d') }}" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Additional payment notes..."></textarea>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-check-circle me-1"></i>
                                Record Payment
                            </button>
                            
                            @if($invoice->balance_due > 0)
                                <button type="button" class="btn btn-outline-primary" id="markAsPaidBtn">
                                    <i class="fas fa-money-bill-wave me-1"></i>
                                    Mark as Fully Paid
                                </button>
                            @endif
                        </div>
                    </form>
                @else
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <i class="fas fa-check-circle fa-3x text-success"></i>
                        </div>
                        <h5 class="text-success">Invoice Fully Paid</h5>
                        <p class="text-muted">No balance due for this invoice.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Payment History -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>
                    Payment History
                </h5>
            </div>
            <div class="card-body p-0">
                @if($invoice->payments && $invoice->payments->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($invoice->payments->sortByDesc('payment_date') as $payment)
                            <div class="list-group-item border-0">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div>
                                        <h6 class="mb-0">
                                            <i class="fas fa-{{ $payment->payment_method === 'cash' ? 'money-bill' : ($payment->payment_method === 'credit_card' ? 'credit-card' : 'university') }} me-1"></i>
                                            {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}
                                        </h6>
                                        <small class="text-muted">
                                            {{ $payment->payment_date->format('M d, Y') }}
                                            @if($payment->reference_number)
                                                • Ref: {{ $payment->reference_number }}
                                            @endif
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-success fs-6">₱{{ number_format($payment->amount, 2) }}</span>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-circle text-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }} me-1"></i>
                                            {{ ucfirst($payment->status) }}
                                        </small>
                                    </div>
                                </div>
                                @if($payment->notes)
                                    <p class="mb-0 mt-2 small text-muted">
                                        <i class="fas fa-sticky-note me-1"></i>
                                        {{ $payment->notes }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-history fa-2x text-muted mb-3"></i>
                        <p class="text-muted mb-0">No payment history found.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Payment Statistics -->
        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    Payment Summary
                </h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h6 class="text-muted mb-1">Total Payments</h6>
                        <h4 class="mb-0">₱{{ number_format($invoice->amount_paid, 2) }}</h4>
                    </div>
                    <div class="col-6">
                        <h6 class="text-muted mb-1">Remaining Balance</h6>
                        <h4 class="mb-0 {{ $invoice->balance_due > 0 ? 'text-danger' : 'text-success' }}">
                            ₱{{ number_format($invoice->balance_due, 2) }}
                        </h4>
                    </div>
                </div>
                
                @if($invoice->payments && $invoice->payments->count() > 0)
                    <div class="mt-3">
                        <h6 class="text-muted mb-2">Payment Methods Used</h6>
                        @php
                            $paymentMethods = $invoice->payments->groupBy('payment_method');
                        @endphp
                        @foreach($paymentMethods as $method => $payments)
                            @php
                                $total = $payments->sum('amount');
                                $percentage = $invoice->amount_paid > 0 ? ($total / $invoice->amount_paid) * 100 : 0;
                            @endphp
                            <div class="mb-2">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small">
                                        <i class="fas fa-{{ $method === 'cash' ? 'money-bill' : ($method === 'credit_card' ? 'credit-card' : 'university') }} me-1"></i>
                                        {{ ucfirst(str_replace('_', ' ', $method)) }}
                                    </span>
                                    <span class="small">₱{{ number_format($total, 2) }} ({{ number_format($percentage, 1) }}%)</span>
                                </div>
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Payment Timeline (Bottom Section) -->
@if($invoice->payments && $invoice->payments->count() > 0)
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-stream me-2"></i>
                    Payment Timeline
                </h5>
            </div>
            <div class="card-body">
                <div class="timeline">
                    @foreach($invoice->payments->sortBy('payment_date') as $payment)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}"></div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-0">
                                            {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }} Payment
                                            @if($payment->reference_number)
                                                <small class="text-muted">({{ $payment->reference_number }})</small>
                                            @endif
                                        </h6>
                                        <p class="text-muted mb-0">
                                            {{ $payment->payment_date->format('F d, Y - h:i A') }}
                                        </p>
                                    </div>
                                    <div class="text-end">
                                        <h5 class="mb-0 text-success">₱{{ number_format($payment->amount, 2) }}</h5>
                                        <span class="badge bg-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </div>
                                </div>
                                @if($payment->notes)
                                    <p class="mt-2 mb-0 small">
                                        <i class="fas fa-sticky-note me-1"></i>
                                        {{ $payment->notes }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- JavaScript for Payment Actions -->
@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mark as Fully Paid button
        const markAsPaidBtn = document.getElementById('markAsPaidBtn');
        if (markAsPaidBtn) {
            markAsPaidBtn.addEventListener('click', function() {
                const paymentAmount = document.getElementById('payment_amount');
                const paymentMethod = document.getElementById('payment_method');
                const paymentDate = document.getElementById('payment_date');
                
                // Set values for full payment
                paymentAmount.value = {{ $invoice->balance_due }};
                paymentMethod.value = 'cash'; // Default to cash
                paymentDate.value = '{{ date('Y-m-d') }}';
                
                // Submit the form
                document.getElementById('paymentForm').submit();
            });
        }
        
        // Auto-fill reference number based on payment method
        const paymentMethodSelect = document.getElementById('payment_method');
        const referenceNumberInput = document.getElementById('reference_number');
        
        if (paymentMethodSelect && referenceNumberInput) {
            paymentMethodSelect.addEventListener('change', function() {
                const method = this.value;
                const today = new Date().toISOString().slice(0, 10).replace(/-/g, '');
                
                switch(method) {
                    case 'check':
                        referenceNumberInput.placeholder = 'Check number (e.g., CHK-' + today + ')';
                        break;
                    case 'credit_card':
                        referenceNumberInput.placeholder = 'Transaction ID (e.g., TXN-' + today + ')';
                        break;
                    case 'bank_transfer':
                        referenceNumberInput.placeholder = 'Bank reference number';
                        break;
                    case 'gcash':
                        referenceNumberInput.placeholder = 'GCash reference number';
                        break;
                    case 'paymaya':
                        referenceNumberInput.placeholder = 'PayMaya reference number';
                        break;
                    default:
                        referenceNumberInput.placeholder = 'Reference number';
                }
            });
        }
        
        // Validate payment amount
        const paymentAmountInput = document.getElementById('payment_amount');
        if (paymentAmountInput) {
            paymentAmountInput.addEventListener('input', function() {
                const maxAmount = parseFloat(this.max);
                const currentAmount = parseFloat(this.value);
                
                if (currentAmount > maxAmount) {
                    this.value = maxAmount;
                    alert('Payment amount cannot exceed balance due of ₱' + maxAmount.toFixed(2));
                }
            });
        }
    });
</script>

<style>
    /* Timeline Styles */
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    
    .timeline-item {
        position: relative;
        padding-bottom: 20px;
    }
    
    .timeline-marker {
        position: absolute;
        left: -30px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 3px solid white;
        box-shadow: 0 0 0 3px #e9ecef;
    }
    
    .timeline-content {
        padding: 10px 15px;
        background: #f8f9fa;
        border-radius: 6px;
        border-left: 3px solid #dee2e6;
    }
    
    .timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: -21px;
        top: 20px;
        bottom: 0;
        width: 2px;
        background: #dee2e6;
    }
    
    /* Progress bar colors */
    .progress-bar {
        background-color: #0d6efd;
    }
    
    .bg-success .progress-bar {
        background-color: #198754;
    }
    
    .bg-warning .progress-bar {
        background-color: #ffc107;
    }
    
    .bg-danger .progress-bar {
        background-color: #dc3545;
    }
</style>
@endsection

<!-- Cancel Invoice Modal -->
<div class="modal fade" id="cancelInvoiceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-archive me-2"></i>Archive Invoice</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('invoices.destroy', $invoice) }}" method="POST" id="cancelInvoiceForm">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <i class="fas fa-archive me-2"></i>Are you sure you want to move this invoice to archive?
                    </div>
                    <p class="mb-0 text-muted small">The record will be preserved in the archive and can be restored later.</p>
                    <div class="mb-3 mt-3">
                        <label for="cancellation_reason" class="form-label">Reason for Cancellation</label>
                        <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-archive me-1"></i> Move to Archive
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection