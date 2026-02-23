@extends('layouts.app')

@section('title', 'Billing & Invoices')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col">
            <h1 class="h2 mb-0">Billing & Invoices</h1>
            <p class="text-muted mb-0">View and manage your invoices and payments</p>
        </div>
        <div class="col-auto">
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#paymentModal">
                    <i class="bi bi-credit-card me-1"></i> Make Payment
                </button>
                <a href="{{ route('portal.billing.payment-methods') }}" class="btn btn-primary">
                    <i class="bi bi-wallet2 me-1"></i> Payment Methods
                </a>
            </div>
        </div>
    </div>

    <!-- Billing Summary -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-1">Balance Due</h6>
                            <div class="display-4 fw-bold text-primary">${{ number_format($balanceDue, 2) }}</div>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-primary fs-6 mb-2">Due Now</div>
                            @if($overdueAmount > 0)
                                <p class="text-danger small mb-0">${{ number_format($overdueAmount, 2) }} overdue</p>
                            @else
                                <p class="text-success small mb-0">No overdue payments</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-1">Total Paid</h6>
                            <div class="display-4 fw-bold text-success">${{ number_format($totalPaid, 2) }}</div>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-success fs-6 mb-2">This Year</div>
                            <p class="text-muted small mb-0">{{ $invoicesPaid }} invoices paid</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-1">Upcoming</h6>
                            <div class="display-4 fw-bold text-warning">${{ number_format($upcomingAmount, 2) }}</div>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-warning text-dark fs-6 mb-2">Next 30 Days</div>
                            <p class="text-muted small mb-0">{{ $upcomingInvoices }} invoices</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title text-muted mb-1">Credit Balance</h6>
                            <div class="display-4 fw-bold text-info">${{ number_format($creditBalance, 2) }}</div>
                        </div>
                        <div class="text-end">
                            <div class="badge bg-info fs-6 mb-2">Available</div>
                            <p class="text-muted small mb-0">Can be applied to future invoices</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Advanced Filters -->
    <div class="row mb-4">
        <div class="col">
            <div class="card">
                <div class="card-header bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-funnel me-2"></i> Advanced Filters
                        </h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="true">
                                <i class="bi bi-chevron-up"></i>
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="bi bi-bookmark me-1"></i> Presets
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#" onclick="applyPreset('today')">Today's Invoices</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="applyPreset('this_week')">This Week</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="applyPreset('this_month')">This Month</a></li>
                                    <li><a class="dropdown-item" href="#" onclick="applyPreset('overdue')">Overdue Only</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#savePresetModal">Save Current Preset</a></li>
                                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#loadPresetModal">Load Saved Preset</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="collapse show" id="filterCollapse">
                    <div class="card-body">
                        <!-- Filter Summary -->
                        <div class="alert alert-info mb-3" id="filterSummary" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-info-circle me-2"></i>
                                    <span id="summaryText">Active filters will appear here</span>
                                </div>
                                <button type="button" class="btn-close" onclick="clearFilterSummary()"></button>
                            </div>
                        </div>
                        
                        <form method="GET" action="{{ route('portal.billing.index') }}" id="filterForm" class="row g-3">
                            <!-- Row 1: Basic Filters -->
                            <div class="col-md-3">
                                <label for="status" class="form-label">Payment Status</label>
                                <select class="form-select" id="status" name="status" onchange="updateFilterSummary()">
                                    <option value="">All Statuses</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                                    <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partially Paid</option>
                                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="type" class="form-label">Invoice Type</label>
                                <select class="form-select" id="type" name="type" onchange="updateFilterSummary()">
                                    <option value="">All Types</option>
                                    <option value="service" {{ request('type') === 'service' ? 'selected' : '' }}>Service</option>
                                    <option value="parts" {{ request('type') === 'parts' ? 'selected' : '' }}>Parts</option>
                                    <option value="membership" {{ request('type') === 'membership' ? 'selected' : '' }}>Membership</option>
                                    <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="date_range" class="form-label">Date Range</label>
                                <select class="form-select" id="date_range" name="date_range" onchange="toggleCustomDateRange()">
                                    <option value="">All Time</option>
                                    <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                                    <option value="this_week" {{ request('date_range') == 'this_week' ? 'selected' : '' }}>This Week</option>
                                    <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>This Month</option>
                                    <option value="last_30_days" {{ request('date_range') == 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                                    <option value="last_90_days" {{ request('date_range') == 'last_90_days' ? 'selected' : '' }}>Last 90 Days</option>
                                    <option value="custom" {{ request('date_range') == 'custom' ? 'selected' : '' }}>Custom Range</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select class="form-select" id="payment_method" name="payment_method" onchange="updateFilterSummary()">
                                    <option value="">All Methods</option>
                                    <option value="credit_card" {{ request('payment_method') === 'credit_card' ? 'selected' : '' }}>Credit Card</option>
                                    <option value="debit_card" {{ request('payment_method') === 'debit_card' ? 'selected' : '' }}>Debit Card</option>
                                    <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                                    <option value="check" {{ request('payment_method') === 'check' ? 'selected' : '' }}>Check</option>
                                </select>
                            </div>
                            
                            <!-- Row 2: Custom Date Range (Hidden by Default) -->
                            <div class="row g-3 mt-2" id="customDateRange" style="display: none;">
                                <div class="col-md-6">
                                    <label for="date_from" class="form-label">From Date</label>
                                    <input type="date" class="form-control" id="date_from" name="date_from" 
                                           value="{{ request('date_from') }}" onchange="updateFilterSummary()">
                                </div>
                                <div class="col-md-6">
                                    <label for="date_to" class="form-label">To Date</label>
                                    <input type="date" class="form-control" id="date_to" name="date_to" 
                                           value="{{ request('date_to') }}" onchange="updateFilterSummary()">
                                </div>
                            </div>
                            
                            <!-- Row 3: Amount Range -->
                            <div class="col-md-6">
                                <label for="amount_min" class="form-label">Amount Range</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="amount_min" name="amount_min" 
                                           placeholder="Min" value="{{ request('amount_min') }}" min="0" step="0.01" onchange="updateFilterSummary()">
                                    <span class="input-group-text">to</span>
                                    <input type="number" class="form-control" id="amount_max" name="amount_max" 
                                           placeholder="Max" value="{{ request('amount_max') }}" min="0" step="0.01" onchange="updateFilterSummary()">
                                </div>
                            </div>
                            
                            <!-- Row 3: Search -->
                            <div class="col-md-6">
                                <label for="search" class="form-label">Search</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="search" name="search" 
                                           placeholder="Search invoice #, description, or customer..." 
                                           value="{{ request('search') }}" onchange="updateFilterSummary()">
                                    <button class="btn btn-outline-primary" type="submit">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Row 4: Action Buttons -->
                            <div class="col-12">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-funnel me-1"></i> Apply Filters
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="resetFilters()">
                                        <i class="bi bi-x-circle me-1"></i> Clear All Filters
                                    </button>
                                    <div class="ms-auto d-flex gap-2">
                                        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#savePresetModal">
                                            <i class="bi bi-bookmark-plus me-1"></i> Save Preset
                                        </button>
                                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
                                            <i class="bi bi-download me-1"></i> Export
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="row">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Invoices</h5>
                </div>
                <div class="card-body">
                    @if($invoices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAll">
                                            </div>
                                        </th>
                                        <th>Invoice #</th>
                                        <th>Date</th>
                                        <th>Due Date</th>
                                        <th>Description</th>
                                        <th class="text-end">Amount</th>
                                        <th class="text-end">Paid</th>
                                        <th class="text-end">Balance</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                    <tr class="{{ $invoice->status === 'overdue' ? 'table-danger' : ($invoice->status === 'pending' ? 'table-warning' : '') }}">
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input invoice-checkbox" type="checkbox" 
                                                       value="{{ $invoice->id }}"
                                                       {{ $invoice->balance <= 0 ? 'disabled' : '' }}>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('portal.billing.show', $invoice->id) }}" class="fw-bold">
                                                {{ $invoice->invoice_number }}
                                            </a>
                                            @if($invoice->is_recurring)
                                                <span class="badge bg-info ms-1">Recurring</span>
                                            @endif
                                        </td>
                                        <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                                        <td>
                                            {{ $invoice->due_date->format('M d, Y') }}
                                            @if($invoice->is_overdue)
                                                <span class="badge bg-danger ms-1">Overdue</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($invoice->service_record)
                                                    <i class="bi bi-car-front text-muted me-2"></i>
                                                    <div>
                                                        <div>{{ $invoice->service_record->vehicle->make }} {{ $invoice->service_record->vehicle->model }}</div>
                                                        <small class="text-muted">{{ $invoice->service_record->service_type }}</small>
                                                    </div>
                                                @else
                                                    <i class="bi bi-receipt text-muted me-2"></i>
                                                    <div>{{ $invoice->description ?: 'General Invoice' }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-end">${{ number_format($invoice->total_amount, 2) }}</td>
                                        <td class="text-end text-success">${{ number_format($invoice->paid_amount, 2) }}</td>
                                        <td class="text-end fw-bold {{ $invoice->balance > 0 ? 'text-danger' : 'text-success' }}">
                                            ${{ number_format($invoice->balance, 2) }}
                                        </td>
                                        <td>
                                            @if($invoice->status === 'paid')
                                                <span class="badge bg-success">Paid</span>
                                            @elseif($invoice->status === 'pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @elseif($invoice->status === 'overdue')
                                                <span class="badge bg-danger">Overdue</span>
                                            @elseif($invoice->status === 'partial')
                                                <span class="badge bg-info">Partial</span>
                                            @elseif($invoice->status === 'cancelled')
                                                <span class="badge bg-secondary">Cancelled</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" 
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="bi bi-three-dots-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('portal.billing.show', $invoice->id) }}">
                                                            <i class="bi bi-eye me-2"></i> View Details
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('portal.billing.download', $invoice->id) }}" target="_blank">
                                                            <i class="bi bi-download me-2"></i> Download PDF
                                                        </a>
                                                    </li>
                                                    @if($invoice->balance > 0)
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-primary" href="#" 
                                                           data-bs-toggle="modal" 
                                                           data-bs-target="#paymentModal"
                                                           data-invoice-id="{{ $invoice->id }}"
                                                           data-invoice-number="{{ $invoice->invoice_number }}"
                                                           data-balance="{{ $invoice->balance }}">
                                                            <i class="bi bi-credit-card me-2"></i> Pay Now
                                                        </a>
                                                    </li>
                                                    @endif
                                                    @if($invoice->status === 'pending' || $invoice->status === 'overdue')
                                                    <li>
                                                        <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#disputeModal" data-invoice-id="{{ $invoice->id }}">
                                                            <i class="bi bi-flag me-2"></i> Dispute Charge
                                                        </a>
                                                    </li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <td colspan="5" class="text-end fw-bold">Totals:</td>
                                        <td class="text-end fw-bold">${{ number_format($invoices->sum('total_amount'), 2) }}</td>
                                        <td class="text-end fw-bold text-success">${{ number_format($invoices->sum('paid_amount'), 2) }}</td>
                                        <td class="text-end fw-bold text-danger">${{ number_format($invoices->sum('balance'), 2) }}</td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <!-- Bulk Actions -->
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="d-flex gap-2">
                                <select class="form-select form-select-sm" style="width: auto;" id="bulkAction">
                                    <option value="">Bulk Actions</option>
                                    <option value="pay">Pay Selected</option>
                                    <option value="download">Download PDFs</option>
                                    <option value="export">Export Selected</option>
                                    <option value="dispute">Dispute Selected</option>
                                </select>
                                <button type="button" class="btn btn-sm btn-primary" id="applyBulkAction" disabled>
                                    Apply
                                </button>
                            </div>
                            
                            <!-- Pagination -->
                            @if($invoices->hasPages())
                            <div>
                                {{ $invoices->links() }}
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="bi bi-receipt display-1 text-muted"></i>
                            </div>
                            <h4 class="text-muted mb-2">No invoices found</h4>
                            <p class="text-muted mb-4">Your invoices will appear here</p>
                            <a href="{{ route('portal.dashboard') }}" class="btn btn-primary">
                                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Payment History -->
    <div class="row mt-4">
        <div class="col">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Recent Payments</h5>
                </div>
                <div class="card-body">
                    @if($recentPayments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Payment #</th>
                                        <th>Method</th>
                                        <th>Invoice #</th>
                                        <th class="text-end">Amount</th>
                                        <th>Status</th>
                                        <th class="text-end">Receipt</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentPayments as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                        <td>{{ $payment->payment_number }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($payment->payment_method === 'credit_card')
                                                    <i class="bi bi-credit-card text-primary me-2"></i>
                                                    <span>Credit Card</span>
                                                @elseif($payment->payment_method === 'bank_transfer')
                                                    <i class="bi bi-bank text-success me-2"></i>
                                                    <span>Bank Transfer</span>
                                                @elseif($payment->payment_method === 'cash')
                                                    <i class="bi bi-cash text-warning me-2"></i>
                                                    <span>Cash</span>
                                                @else
                                                    <i class="bi bi-wallet2 text-info me-2"></i>
                                                    <span>{{ ucfirst($payment->payment_method) }}</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($payment->invoice)
                                                <a href="{{ route('portal.billing.show', $payment->invoice->id) }}" class="text-decoration-none">
                                                    {{ $payment->invoice->invoice_number }}
                                                </a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td class="text-end text-success">${{ number_format($payment->amount, 2) }}</td>
                                        <td>
                                            @if($payment->status === 'completed')
                                                <span class="badge bg-success">Completed</span>
                                            @elseif($payment->status === 'pending')
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            @elseif($payment->status === 'failed')
                                                <span class="badge bg-danger">Failed</span>
                                            @elseif($payment->status === 'refunded')
                                                <span class="badge bg-info">Refunded</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('portal.billing.receipt', $payment->id) }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="bi bi-receipt me-1"></i> Receipt
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($recentPayments->count() >= 5)
                        <div class="text-center mt-3">
                            <a href="{{ route('portal.billing.payments') }}" class="btn btn-sm btn-outline-primary">
                                View All Payments
                            </a>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-credit-card display-1 text-muted mb-3"></i>
                            <h6 class="text-muted mb-2">No payment history</h6>
                            <p class="text-muted small mb-0">Your payment history will appear here</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Make Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="paymentForm" method="POST" action="{{ route('portal.billing.pay') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="invoice_id" id="invoiceId">
                    
                    <!-- Invoice Selection -->
                    <div class="mb-4" id="invoiceSelection">
                        <h6 class="mb-3">Select Invoices to Pay</h6>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i>
                            Select invoices from the table above or enter amounts manually
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-sm" id="selectedInvoicesTable">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Due Date</th>
                                        <th class="text-end">Balance</th>
                                        <th class="text-end">Amount to Pay</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="selectedInvoicesBody">
                                    <!-- Rows will be added dynamically -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Total:</td>
                                        <td class="text-end fw-bold" id="totalPaymentAmount">$0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="text-end">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="clearSelection">
                                Clear Selection
                            </button>
                        </div>
                    </div>
                    
                    <!-- Payment Amount -->
                    <div class="mb-4">
                        <h6 class="mb-3">Payment Amount</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="payment_amount" class="form-label">Amount to Pay</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="payment_amount" name="amount" 
                                           min="0.01" step="0.01" required>
                                </div>
                                <div class="form-text">Minimum payment: $1.00</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="payment_type" class="form-label">Payment Type</label>
                                <select class="form-select" id="payment_type" name="payment_type" required>
                                    <option value="">Select type</option>
                                    <option value="full">Full Payment</option>
                                    <option value="partial">Partial Payment</option>
                                    <option value="minimum">Minimum Payment</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="mb-4">
                        <h6 class="mb-3">Payment Method</h6>
                        @if($paymentMethods->count() > 0)
                            <div class="list-group mb-3">
                                @foreach($paymentMethods as $method)
                                <label class="list-group-item">
                                    <div class="form-check d-flex align-items-center">
                                        <input class="form-check-input me-3" type="radio" 
                                               name="payment_method_id" 
                                               value="{{ $method->id }}"
                                               {{ $loop->first ? 'checked' : '' }}>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>
                                                        @if($method->type === 'credit_card')
                                                            {{ $method->card_type }} •••• {{ $method->last_four }}
                                                        @elseif($method->type === 'bank_account')
                                                            Bank Account •••• {{ $method->last_four }}
                                                        @else
                                                            {{ ucfirst($method->type) }}
                                                        @endif
                                                    </strong>
                                                </div>
                                                <div>
                                                    @if($method->is_default)
                                                        <span class="badge bg-primary">Default</span>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="text-muted small">
                                                Expires: {{ $method->expiry_date ? $method->expiry_date->format('m/Y') : 'N/A' }}
                                            </div>
                                        </div>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                            
                            <div class="text-end">
                                <a href="{{ route('portal.billing.payment-methods') }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-plus-circle me-1"></i> Add New Method
                                </a>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                No payment methods saved. Please add a payment method first.
                            </div>
                            <div class="text-center">
                                <a href="{{ route('portal.billing.payment-methods') }}" class="btn btn-primary">
                                    <i class="bi bi-plus-circle me-1"></i> Add Payment Method
                                </a>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Payment Confirmation -->
                    <div class="alert alert-light border">
                        <h6 class="alert-heading mb-2"><i class="bi bi-shield-check me-2"></i>Secure Payment</h6>
                        <ul class="mb-0">
                            <li>All payments are processed securely</li>
                            <li>You will receive a receipt via email</li>
                            <li>Payments are applied immediately</li>
                            <li>24/7 payment processing available</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitPayment" {{ $paymentMethods->count() === 0 ? 'disabled' : '' }}>
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
                <h5 class="modal-title" id="disputeModalLabel">Dispute Charge</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('portal.billing.dispute') }}">
                @csrf
                <input type="hidden" name="invoice_id" id="disputeInvoiceId">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Please provide details about why you are disputing this charge.
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

<!-- Save Preset Modal -->
<div class="modal fade" id="savePresetModal" tabindex="-1" aria-labelledby="savePresetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('portal.billing.save-filter-preset') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="savePresetModalLabel">Save Filter Preset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="presetName" class="form-label">Preset Name</label>
                        <input type="text" class="form-control" id="presetName" name="name" required 
                               placeholder="e.g., Overdue Invoices This Month">
                        <div class="form-text">Give your filter preset a descriptive name.</div>
                    </div>
                    <div class="mb-3">
                        <label for="presetDescription" class="form-label">Description (Optional)</label>
                        <textarea class="form-control" id="presetDescription" name="description" rows="2" 
                                  placeholder="Describe what this filter preset does..."></textarea>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="makeDefault" name="make_default">
                        <label class="form-check-label" for="makeDefault">
                            Set as default filter for this page
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-bookmark-check me-1"></i> Save Preset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Load Preset Modal -->
<div class="modal fade" id="loadPresetModal" tabindex="-1" aria-labelledby="loadPresetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loadPresetModalLabel">Load Saved Filter Preset</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if(isset($filterPresets) && count($filterPresets) > 0)
                    <div class="row">
                        @foreach($filterPresets as $preset)
                            <div class="col-md-6 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title">{{ $preset->name }}</h6>
                                        @if($preset->description)
                                            <p class="card-text text-muted small">{{ $preset->description }}</p>
                                        @endif
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <small class="text-muted">
                                                Created: {{ $preset->created_at->format('M d, Y') }}
                                            </small>
                                            <div class="btn-group">
                                                <a href="{{ route('portal.billing.index', json_decode($preset->filters, true)) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-play-circle me-1"></i> Apply
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="deletePreset({{ $preset->id }})">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="bi bi-bookmark-x display-4 text-muted mb-3"></i>
                        <h5>No saved presets</h5>
                        <p class="text-muted">Save your current filter settings to create your first preset.</p>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Export Invoices</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('portal.billing.export') }}">
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
                            <option value="all">All Invoices</option>
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
                            <input class="form-check-input" type="checkbox" id="include_details" name="include[]" value="details" checked>
                            <label class="form-check-label" for="include_details">
                                Invoice Details
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_payments" name="include[]" value="payments" checked>
                            <label class="form-check-label" for="include_payments">
                                Payment History
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="include_tax" name="include[]" value="tax" checked>
                            <label class="form-check-label" for="include_tax">
                                Tax Breakdown
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-download me-1"></i> Export
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Invoice selection
        const selectAll = document.getElementById('selectAll');
        const invoiceCheckboxes = document.querySelectorAll('.invoice-checkbox');
        const selectedInvoicesBody = document.getElementById('selectedInvoicesBody');
        const totalPaymentAmount = document.getElementById('totalPaymentAmount');
        const paymentAmountInput = document.getElementById('payment_amount');
        const clearSelectionBtn = document.getElementById('clearSelection');
        
        // Store selected invoices
        let selectedInvoices = {};
        
        // Select all functionality
        if (selectAll) {
            selectAll.addEventListener('change', function() {
                const isChecked = this.checked;
                invoiceCheckboxes.forEach(checkbox => {
                    if (!checkbox.disabled) {
                        checkbox.checked = isChecked;
                        updateSelectedInvoice(checkbox);
                    }
                });
            });
        }
        
        // Individual checkbox handling
        invoiceCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectedInvoice(this);
            });
        });
        
        function updateSelectedInvoice(checkbox) {
            const invoiceId = checkbox.value;
            const row = checkbox.closest('tr');
            
            if (checkbox.checked && !checkbox.disabled) {
                const invoiceNumber = row.cells[1].querySelector('a').textContent.trim();
                const dueDate = row.cells[3].textContent.trim();
                const balance = parseFloat(row.cells[7].textContent.replace('$', '').replace(',', ''));
                
                selectedInvoices[invoiceId] = {
                    invoiceNumber,
                    dueDate,
                    balance,
                    amountToPay: balance
                };
            } else {
                delete selectedInvoices[invoiceId];
            }
            
            updateSelectedInvoicesTable();
            updateSelectAllCheckbox();
        }
        
        function updateSelectedInvoicesTable() {
            if (!selectedInvoicesBody) return;
            
            selectedInvoicesBody.innerHTML = '';
            let total = 0;
            
            Object.entries(selectedInvoices).forEach(([id, invoice]) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${invoice.invoiceNumber}</td>
                    <td>${invoice.dueDate}</td>
                    <td class="text-end">$${invoice.balance.toFixed(2)}</td>
                    <td class="text-end">
                        <input type="number" class="form-control form-control-sm text-end" 
                               value="${invoice.amountToPay.toFixed(2)}" 
                               min="0.01" max="${invoice.balance.toFixed(2)}" step="0.01"
                               data-invoice-id="${id}"
                               onchange="updateInvoiceAmount(this)">
                    </td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeInvoice('${id}')">
                            <i class="bi bi-x"></i>
                        </button>
                    </td>
                `;
                selectedInvoicesBody.appendChild(row);
                total += invoice.amountToPay;
            });
            
            totalPaymentAmount.textContent = `$${total.toFixed(2)}`;
            
            // Update payment amount input
            if (paymentAmountInput) {
                paymentAmountInput.value = total.toFixed(2);
                paymentAmountInput.min = total > 0 ? '0.01' : '1.00';
            }
        }
        
        function updateSelectAllCheckbox() {
            if (!selectAll) return;
            
            const enabledCheckboxes = Array.from(invoiceCheckboxes).filter(cb => !cb.disabled);
            const checkedCount = enabledCheckboxes.filter(cb => cb.checked).length;
            
            selectAll.checked = checkedCount > 0 && checkedCount === enabledCheckboxes.length;
            selectAll.indeterminate = checkedCount > 0 && checkedCount < enabledCheckboxes.length;
        }
        
        // Global functions for inline event handlers
        window.updateInvoiceAmount = function(input) {
            const invoiceId = input.getAttribute('data-invoice-id');
            const amount = parseFloat(input.value) || 0;
            const maxAmount = selectedInvoices[invoiceId].balance;
            
            if (amount > maxAmount) {
                input.value = maxAmount.toFixed(2);
                selectedInvoices[invoiceId].amountToPay = maxAmount;
            } else if (amount < 0.01) {
                input.value = '0.01';
                selectedInvoices[invoiceId].amountToPay = 0.01;
            } else {
                selectedInvoices[invoiceId].amountToPay = amount;
            }
            
            updateSelectedInvoicesTable();
        };
        
        window.removeInvoice = function(invoiceId) {
            delete selectedInvoices[invoiceId];
            const checkbox = document.querySelector(`.invoice-checkbox[value="${invoiceId}"]`);
            if (checkbox) {
                checkbox.checked = false;
            }
            updateSelectedInvoicesTable();
            updateSelectAllCheckbox();
        };
        
        // Clear selection
        if (clearSelectionBtn) {
            clearSelectionBtn.addEventListener('click', function() {
                selectedInvoices = {};
                invoiceCheckboxes.forEach(checkbox => {
                    checkbox.checked = false;
                });
                updateSelectedInvoicesTable();
                updateSelectAllCheckbox();
            });
        }
        
        // Payment modal setup
        const paymentModal = document.getElementById('paymentModal');
        if (paymentModal) {
            paymentModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                
                if (button.hasAttribute('data-invoice-id')) {
                    // Single invoice payment
                    const invoiceId = button.getAttribute('data-invoice-id');
                    const invoiceNumber = button.getAttribute('data-invoice-number');
                    const balance = parseFloat(button.getAttribute('data-balance'));
                    
                    document.getElementById('invoiceId').value = invoiceId;
                    
                    // Clear and add single invoice
                    selectedInvoices = {};
                    selectedInvoices[invoiceId] = {
                        invoiceNumber,
                        dueDate: button.closest('tr').cells[3].textContent.trim(),
                        balance,
                        amountToPay: balance
                    };
                    
                    updateSelectedInvoicesTable();
                    
                    // Update payment amount
                    if (paymentAmountInput) {
                        paymentAmountInput.value = balance.toFixed(2);
                    }
                } else {
                    // Multiple invoice payment - use current selection
                    const invoiceIds = Object.keys(selectedInvoices);
                    if (invoiceIds.length > 0) {
                        document.getElementById('invoiceId').value = invoiceIds.join(',');
                    }
                }
            });
        }
        
        // Dispute modal setup
        const disputeModal = document.getElementById('disputeModal');
        if (disputeModal) {
            disputeModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const invoiceId = button.getAttribute('data-invoice-id');
                document.getElementById('disputeInvoiceId').value = invoiceId;
            });
        }
        
        // Export modal setup
        const exportRange = document.getElementById('export_range');
        const exportCustomRange = document.getElementById('exportCustomRange');
        
        if (exportRange && exportCustomRange) {
            exportRange.addEventListener('change', function() {
                if (this.value === 'custom') {
                    exportCustomRange.classList.remove('d-none');
                } else {
                    exportCustomRange.classList.add('d-none');
                }
            });
        }
        
        // Bulk actions
        const bulkActionSelect = document.getElementById('bulkAction');
        const applyBulkActionBtn = document.getElementById('applyBulkAction');
        
        if (bulkActionSelect && applyBulkActionBtn) {
            bulkActionSelect.addEventListener('change', function() {
                applyBulkActionBtn.disabled = !this.value;
            });
            
            applyBulkActionBtn.addEventListener('click', function() {
                const action = bulkActionSelect.value;
                const selectedIds = Array.from(document.querySelectorAll('.invoice-checkbox:checked'))
                    .map(cb => cb.value);
                
                if (selectedIds.length === 0) {
                    alert('Please select at least one invoice.');
                    return;
                }
                
                switch (action) {
                    case 'pay':
                        // Prepare for payment
                        selectedInvoices = {};
                        selectedIds.forEach(id => {
                            const checkbox = document.querySelector(`.invoice-checkbox[value="${id}"]`);
                            const row = checkbox.closest('tr');
                            const invoiceNumber = row.cells[1].querySelector('a').textContent.trim();
                            const dueDate = row.cells[3].textContent.trim();
                            const balance = parseFloat(row.cells[7].textContent.replace('$', '').replace(',', ''));
                            
                            selectedInvoices[id] = {
                                invoiceNumber,
                                dueDate,
                                balance,
                                amountToPay: balance
                            };
                        });
                        
                        // Show payment modal
                        const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
                        paymentModal.show();
                        break;
                        
                    case 'download':
                        // Download PDFs
                        selectedIds.forEach(id => {
                            window.open(`/portal/billing/${id}/download`, '_blank');
                        });
                        break;
                        
                    case 'export':
                        // Export selected
                        alert(`Exporting ${selectedIds.length} invoices...`);
                        // In a real implementation, this would make an API call
                        break;
                        
                    case 'dispute':
                        // Dispute selected
                        if (selectedIds.length > 1) {
                            alert('Please dispute invoices one at a time.');
                        } else {
                            const invoiceId = selectedIds[0];
                            const disputeBtn = document.querySelector(`[data-invoice-id="${invoiceId}"]`);
                            if (disputeBtn) {
                                disputeBtn.click();
                            }
                        }
                        break;
                }
            });
        }
        
        // Auto-update payment amount when type changes
        const paymentTypeSelect = document.getElementById('payment_type');
        if (paymentTypeSelect && paymentAmountInput) {
            paymentTypeSelect.addEventListener('change', function() {
                const total = parseFloat(totalPaymentAmount.textContent.replace('$', '')) || 0;
                
                switch (this.value) {
                    case 'full':
                        paymentAmountInput.value = total.toFixed(2);
                        break;
                    case 'minimum':
                        const minimum = Math.max(1.00, total * 0.1); // 10% or $1 minimum
                        paymentAmountInput.value = minimum.toFixed(2);
                        break;
                    case 'partial':
                        // Keep current value
                        break;
                }
            });
        }
        
        // Form validation
        const paymentForm = document.getElementById('paymentForm');
        if (paymentForm) {
            paymentForm.addEventListener('submit', function(e) {
                const amount = parseFloat(paymentAmountInput.value) || 0;
                const total = parseFloat(totalPaymentAmount.textContent.replace('$', '')) || 0;
                
                if (amount < 0.01) {
                    e.preventDefault();
                    alert('Payment amount must be at least $0.01.');
                    return false;
                }
                
                if (amount > total) {
                    e.preventDefault();
                    alert('Payment amount cannot exceed total balance.');
                    return false;
                }
                
                // Show loading state
                const submitBtn = this.querySelector('#submitPayment');
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
        
        // Advanced Filtering Functions
        function toggleCustomDateRange() {
            const dateRangeSelect = document.getElementById('date_range');
            const customDateRange = document.getElementById('customDateRange');
            
            if (dateRangeSelect.value === 'custom') {
                customDateRange.style.display = 'flex';
            } else {
                customDateRange.style.display = 'none';
            }
            updateFilterSummary();
        }
        
        function updateFilterSummary() {
            const filterSummary = document.getElementById('filterSummary');
            const summaryText = document.getElementById('summaryText');
            const form = document.getElementById('filterForm');
            const formData = new FormData(form);
            const activeFilters = [];
            
            // Check each filter
            if (formData.get('status')) {
                activeFilters.push(`Status: ${formData.get('status').replace('_', ' ')}`);
            }
            
            if (formData.get('type')) {
                activeFilters.push(`Type: ${formData.get('type')}`);
            }
            
            if (formData.get('date_range')) {
                if (formData.get('date_range') === 'custom') {
                    if (formData.get('date_from') || formData.get('date_to')) {
                        const from = formData.get('date_from') || 'Any';
                        const to = formData.get('date_to') || 'Any';
                        activeFilters.push(`Date: ${from} to ${to}`);
                    }
                } else {
                    activeFilters.push(`Date: ${formData.get('date_range').replace('_', ' ')}`);
                }
            }
            
            if (formData.get('payment_method')) {
                activeFilters.push(`Method: ${formData.get('payment_method').replace('_', ' ')}`);
            }
            
            if (formData.get('amount_min') || formData.get('amount_max')) {
                const min = formData.get('amount_min') || 'Any';
                const max = formData.get('amount_max') || 'Any';
                activeFilters.push(`Amount: $${min} to $${max}`);
            }
            
            if (formData.get('search')) {
                activeFilters.push(`Search: "${formData.get('search')}"`);
            }
            
            // Update summary
            if (activeFilters.length > 0) {
                filterSummary.style.display = 'block';
                summaryText.textContent = `Active filters: ${activeFilters.join(', ')}`;
            } else {
                filterSummary.style.display = 'none';
            }
        }
        
        function resetFilters() {
            const form = document.getElementById('filterForm');
            form.reset();
            document.getElementById('customDateRange').style.display = 'none';
            updateFilterSummary();
            form.submit();
        }
        
        function clearFilterSummary() {
            document.getElementById('filterSummary').style.display = 'none';
        }
        
        function applyPreset(preset) {
            const form = document.getElementById('filterForm');
            
            // Reset form first
            form.reset();
            
            // Apply preset based on type
            switch(preset) {
                case 'today':
                    form.date_range.value = 'today';
                    break;
                case 'this_week':
                    form.date_range.value = 'this_week';
                    break;
                case 'this_month':
                    form.date_range.value = 'this_month';
                    break;
                case 'overdue':
                    form.status.value = 'overdue';
                    break;
            }
            
            // Update UI and submit
            toggleCustomDateRange();
            updateFilterSummary();
            form.submit();
        }
        
        // Initialize filter summary on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Check if custom date range should be shown
            const dateRangeSelect = document.getElementById('date_range');
            if (dateRangeSelect && dateRangeSelect.value === 'custom') {
                document.getElementById('customDateRange').style.display = 'flex';
            }
            
            // Update filter summary
            updateFilterSummary();
            
            // Add event listeners for filter changes
            const filterInputs = document.querySelectorAll('#filterForm input, #filterForm select');
            filterInputs.forEach(input => {
                if (!input.hasAttribute('onchange')) {
                    input.addEventListener('change', updateFilterSummary);
                }
            });
            
            // Add debounced search
            const searchInput = document.getElementById('search');
            if (searchInput) {
                let searchTimeout;
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => {
                        updateFilterSummary();
                    }, 500);
                });
            }
        });
        
        // Save Preset Modal functionality
        document.addEventListener('DOMContentLoaded', function() {
            const savePresetModal = document.getElementById('savePresetModal');
            if (savePresetModal) {
                savePresetModal.addEventListener('show.bs.modal', function() {
                    const form = document.getElementById('filterForm');
                    const formData = new FormData(form);
                    const presetNameInput = document.getElementById('presetName');
                    
                    // Generate a default name based on active filters
                    let defaultName = 'My Filter Preset';
                    const activeFilters = [];
                    
                    if (formData.get('status')) {
                        activeFilters.push(formData.get('status'));
                    }
                    if (formData.get('date_range')) {
                        activeFilters.push(formData.get('date_range'));
                    }
                    
                    if (activeFilters.length > 0) {
                        defaultName = activeFilters.join('_').replace('_', ' ') + ' Preset';
                    }
                    
                    if (presetNameInput) {
                        presetNameInput.value = defaultName;
                    }
                });
            }
        });
        
        // Export modal date range toggle
        document.addEventListener('DOMContentLoaded', function() {
            const exportRangeSelect = document.getElementById('export_range');
            const exportCustomRange = document.getElementById('exportCustomRange');
            
            if (exportRangeSelect && exportCustomRange) {
                exportRangeSelect.addEventListener('change', function() {
                    if (this.value === 'custom') {
                        exportCustomRange.classList.remove('d-none');
                    } else {
                        exportCustomRange.classList.add('d-none');
                    }
                });
                
                // Initialize on page load
                if (exportRangeSelect.value === 'custom') {
                    exportCustomRange.classList.remove('d-none');
                }
            }
        });
    });
</script>
@endpush
@endsection