@extends('layouts.app')

@section('title', 'Payment History')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Payment History</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('portal.billing.index') }}">Billing</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Payment History</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('portal.billing.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Billing
            </a>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
                <i class="bi bi-download me-1"></i> Export
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Payments</h6>
                            <h3 class="mb-0">${{ number_format($totalPayments, 2) }}</h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bi bi-credit-card display-6 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Completed</h6>
                            <h3 class="mb-0">{{ $completedCount }}</h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bi bi-check-circle display-6 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Pending</h6>
                            <h3 class="mb-0">{{ $pendingCount }}</h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bi bi-clock display-6 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">This Month</h6>
                            <h3 class="mb-0">${{ number_format($thisMonthTotal, 2) }}</h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bi bi-calendar-month display-6 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('portal.billing.payments') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="">All Statuses</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                            <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="method" class="form-label">Payment Method</label>
                        <select class="form-select" id="method" name="method">
                            <option value="">All Methods</option>
                            @foreach($paymentMethods as $method)
                            <option value="{{ $method->id }}" {{ request('method') == $method->id ? 'selected' : '' }}>
                                @if($method->type === 'credit_card')
                                {{ $method->card_type }}
                                @elseif($method->type === 'bank_account')
                                Bank Account
                                @else
                                {{ ucfirst($method->type) }}
                                @endif
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="date_from" class="form-label">From Date</label>
                        <input type="date" class="form-control" id="date_from" name="date_from" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="date_to" class="form-label">To Date</label>
                        <input type="date" class="form-control" id="date_to" name="date_to" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" 
                               placeholder="Search by invoice #, transaction ID, or notes..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-end h-100 gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-funnel me-1"></i> Apply Filters
                            </button>
                            <a href="{{ route('portal.billing.payments') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> Clear
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Payment Records</h5>
                <div class="text-muted">
                    Showing {{ $payments->firstItem() ?? 0 }}-{{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }} payments
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($payments->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Invoice</th>
                            <th>Method</th>
                            <th class="text-end">Amount</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-medium">{{ $payment->payment_date->format('M j, Y') }}</div>
                                <div class="text-muted small">{{ $payment->payment_date->format('g:i A') }}</div>
                            </td>
                            <td>
                                @if($payment->invoice)
                                <a href="{{ route('portal.billing.show', $payment->invoice->id) }}" class="text-decoration-none">
                                    #{{ $payment->invoice->invoice_number }}
                                </a>
                                <div class="text-muted small">
                                    {{ $payment->invoice->customer->name }}
                                </div>
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($payment->payment_method === 'credit_card')
                                    <i class="bi bi-credit-card me-2 text-primary"></i>
                                    <span>Credit Card</span>
                                    @elseif($payment->payment_method === 'bank_transfer')
                                    <i class="bi bi-bank me-2 text-success"></i>
                                    <span>Bank Transfer</span>
                                    @elseif($payment->payment_method === 'cash')
                                    <i class="bi bi-cash-coin me-2 text-warning"></i>
                                    <span>Cash</span>
                                    @elseif($payment->payment_method === 'check')
                                    <i class="bi bi-file-text me-2 text-info"></i>
                                    <span>Check</span>
                                    @else
                                    <i class="bi bi-wallet me-2 text-secondary"></i>
                                    <span>{{ ucfirst($payment->payment_method) }}</span>
                                    @endif
                                </div>
                                @if($payment->last_four)
                                <div class="text-muted small">•••• {{ $payment->last_four }}</div>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="fw-medium text-success">${{ number_format($payment->amount, 2) }}</div>
                                @if($payment->fee_amount > 0)
                                <div class="text-muted small">Fee: ${{ number_format($payment->fee_amount, 2) }}</div>
                                @endif
                            </td>
                            <td>
                                @if($payment->status === 'completed')
                                <span class="badge bg-success">Completed</span>
                                @elseif($payment->status === 'pending')
                                <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($payment->status === 'failed')
                                <span class="badge bg-danger">Failed</span>
                                @elseif($payment->status === 'refunded')
                                <span class="badge bg-info">Refunded</span>
                                @elseif($payment->status === 'cancelled')
                                <span class="badge bg-secondary">Cancelled</span>
                                @endif
                                @if($payment->is_refund)
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 ms-1">Refund</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('portal.billing.receipt', $payment->id) }}" target="_blank">
                                                <i class="bi bi-receipt me-2"></i> View Receipt
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('portal.billing.download-receipt', $payment->id) }}" target="_blank">
                                                <i class="bi bi-download me-2"></i> Download Receipt
                                            </a>
                                        </li>
                                        @if($payment->invoice)
                                        <li>
                                            <a class="dropdown-item" href="{{ route('portal.billing.show', $payment->invoice->id) }}">
                                                <i class="bi bi-file-text me-2"></i> View Invoice
                                            </a>
                                        </li>
                                        @endif
                                        @if($payment->status === 'completed' && !$payment->is_refund)
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item text-warning" href="#" data-bs-toggle="modal" data-bs-target="#refundModal" data-payment-id="{{ $payment->id }}" data-payment-amount="{{ $payment->amount }}">
                                                <i class="bi bi-arrow-counterclockwise me-2"></i> Request Refund
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($payments->hasPages())
            <div class="card-footer">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Page {{ $payments->currentPage() }} of {{ $payments->lastPage() }}
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            {{ $payments->withQueryString()->links() }}
                        </ul>
                    </nav>
                </div>
            </div>
            @endif
            @else
            <div class="text-center py-5">
                <i class="bi bi-credit-card display-1 text-muted mb-3"></i>
                <h5 class="text-muted mb-2">No payments found</h5>
                <p class="text-muted mb-4">Your payment history will appear here</p>
                <a href="{{ route('portal.billing.index') }}" class="btn btn-primary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Billing
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Export Payment History</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('portal.billing.export-payments') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="export_format" class="form-label">Format</label>
                        <select class="form-select" id="export_format" name="format" required>
                            <option value="csv">CSV (Excel)</option>
                            <option value="pdf">PDF Document</option>
                            <option value="excel">Excel File</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="export_range" class="form-label">Date Range</label>
                        <select class="form-select" id="export_range" name="range" required>
                            <option value="all">All Payments</option>
                            <option value="year">Last 12 Months</option>
                            <option value="quarter">Last 3 Months</option>
                            <option value="month">Last 30 Days</option>
                            <option value="custom">Custom Range</option>
                        </select>
                    </div>
                    
                    <div id="exportCustomRange" class="d-none">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="export_from" class="form-label">From Date</label>
                                <input type="date" class="form-control" id="export_from" name="from">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="export_to" class="form-label">To Date</label>
                                <input type="date" class="form-control" id="export_to" name="to">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="export_include" class="form-label">Include</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_invoice" name="include[]" value="invoice" checked>
                            <label class="form-check-label" for="include_invoice">
                                Invoice Details
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_fees" name="include[]" value="fees" checked>
                            <label class="form-check-label" for="include_fees">
                                Fee Breakdown
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_tax" name="include[]" value="tax" checked>
                            <label class="form-check-label" for="include_tax">
                                Tax Information
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_notes" name="include[]" value="notes" checked>
                            <label class="form-check-label" for="include_notes">
                                Payment Notes
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-download me-1"></i> Export
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="refundModalLabel">Request Refund</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('portal.billing.request-refund') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="refund_payment_id" name="payment_id">
                    
                    <div class="mb-3">
                        <label for="refund_amount" class="form-label">Refund Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="refund_amount" name="amount" 
                                   step="0.01" min="0.01" required>
                        </div>
                        <div class="form-text">Maximum refundable amount: $<span id="max_refund_amount">0.00</span></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="refund_reason" class="form-label">Reason for Refund</label>
                        <select class="form-select" id="refund_reason" name="reason" required>
                            <option value="">Select a reason</option>
                            <option value="duplicate_payment">Duplicate Payment</option>
                            <option value="service_not_received">Service Not Received</option>
                            <option value="incorrect_amount">Incorrect Amount</option>
                            <option value="cancelled_service">Cancelled Service</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="refund_notes" class="form-label">Additional Notes</label>
                        <textarea class="form-control" id="refund_notes" name="notes" rows="3" 
                                  placeholder="Please provide any additional details..."></textarea>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Refund requests are processed within 3-5 business days. You will receive email confirmation once processed.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Submit Refund Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Export modal range toggle
    document.getElementById('export_range').addEventListener('change', function() {
        const customRangeDiv = document.getElementById('exportCustomRange');
        if (this.value === 'custom') {
            customRangeDiv.classList.remove('d-none');
        } else {
            customRangeDiv.classList.add('d-none');
        }
    });

    // Refund modal setup
    const refundModal = document.getElementById('refundModal');
    if (refundModal) {
        refundModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const paymentId = button.getAttribute('data-payment-id');
            const paymentAmount = button.getAttribute('data-payment-amount');
            
            document.getElementById('refund_payment_id').value = paymentId;
            document.getElementById('refund_amount').value = paymentAmount;
            document.getElementById('refund_amount').max = paymentAmount;
            document.getElementById('max_refund_amount').textContent = parseFloat(paymentAmount).toFixed(2);
        });
    }

    // Filter form submission
    document.getElementById('filterForm').addEventListener('submit', function(e) {
        // Validate date range
        const dateFrom = document.getElementById('date_from').value;
        const dateTo = document.getElementById('date_to').value;
        
        if (dateFrom && dateTo && new Date(dateFrom) > new Date(dateTo)) {
            e.preventDefault();
            alert('From date cannot be after To date');
            return false;
        }
    });

    // Bulk selection
    const selectAllCheckbox = document.getElementById('selectAll');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.payment-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
    }

    // Export functionality
    document.querySelectorAll('.export-btn').forEach(button => {
        button.addEventListener('click', function() {
            const format = this.getAttribute('data-format');
            const paymentIds = Array.from(document.querySelectorAll('.payment-checkbox:checked'))
                .map(checkbox => checkbox.value);
            
            if (paymentIds.length === 0) {
                alert('Please select at least one payment to export');
                return;
            }
            
            // Submit export request
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("portal.billing.export-payments") }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            const formatInput = document.createElement('input');
            formatInput.type = 'hidden';
            formatInput.name = 'format';
            formatInput.value = format;
            form.appendChild(formatInput);
            
            paymentIds.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'payment_ids[]';
                input.value = id;
                form.appendChild(input);
            });
            
            document.body.appendChild(form);
            form.submit();
        });
    });
</script>
@endsection