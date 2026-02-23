@extends('layouts.app')

@section('title', 'Invoices - Point of Sale')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-file-invoice-dollar me-2"></i>Invoices
            </h1>
            <p class="text-muted mb-0">Manage customer invoices and billing</p>
        </div>
        <div>
            <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create Invoice
            </a>
            <a href="{{ route('invoices.statistics') }}" class="btn btn-outline-info">
                <i class="fas fa-chart-bar me-1"></i> Statistics
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-0">Total Invoices</h6>
                        <h3 class="mb-0">{{ $invoices->total() }}</h3>
                    </div>
                    <i class="fas fa-file-invoice fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-0">Paid</h6>
                        <h3 class="mb-0">{{ $invoices->where('payment_status', 'paid')->count() }}</h3>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-0">Pending</h6>
                        <h3 class="mb-0">{{ $invoices->where('payment_status', 'pending')->count() }}</h3>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-0">Overdue</h6>
                        <h3 class="mb-0">{{ $invoices->where('status', 'overdue')->count() }}</h3>
                    </div>
                    <i class="fas fa-exclamation-triangle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('invoices.index') }}">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Payment Status</label>
                    <select class="form-select" name="payment_status">
                        <option value="">All Payment Statuses</option>
                        <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>Partial</option>
                        <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label class="form-label">End Date</label>
                    <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" 
                           placeholder="Invoice number, customer name, email, phone...">
                </div>
                
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Apply Filters
                    </button>
                </div>
                
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-times me-1"></i> Clear Filters
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Invoices Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-list me-2"></i>Invoices ({{ $invoices->total() }})
        </h6>
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print
            </button>
            <button class="btn btn-sm btn-outline-secondary" onclick="exportToExcel()">
                <i class="fas fa-file-excel me-1"></i> Export
            </button>
        </div>
    </div>
    
    <div class="card-body">
        @if($invoices->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Customer</th>
                            <th>Vehicle</th>
                            <th>Date</th>
                            <th>Due Date</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                            <tr>
                                <td>
                                    <strong>{{ $invoice->invoice_number }}</strong>
                                    @if($invoice->is_overdue)
                                        <span class="badge bg-danger ms-1">Overdue</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle-sm me-2">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div>
                                            <strong>{{ $invoice->customer->first_name }} {{ $invoice->customer->last_name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $invoice->customer->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($invoice->vehicle)
                                        {{ $invoice->vehicle->year }} {{ $invoice->vehicle->make }} {{ $invoice->vehicle->model }}
                                        <br>
                                        <small class="text-muted">{{ $invoice->vehicle->license_plate ?? 'No Plate' }}</small>
                                    @else
                                        <span class="text-muted">No Vehicle</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $invoice->invoice_date->format('M d, Y') }}
                                    <br>
                                    <small class="text-muted">{{ $invoice->invoice_date->format('g:i A') }}</small>
                                </td>
                                <td>
                                    @if($invoice->due_date)
                                        {{ $invoice->due_date->format('M d, Y') }}
                                        @if($invoice->is_overdue)
                                            <br>
                                            <small class="text-danger">
                                                {{ $invoice->due_date->diffForHumans() }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="text-muted">No due date</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $invoice->formatted_total }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        Balance: {{ $invoice->formatted_balance_due }}
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $invoice->status === 'paid' ? 'success' : 
                                                              ($invoice->status === 'sent' ? 'info' : 
                                                              ($invoice->status === 'overdue' ? 'danger' : 
                                                              ($invoice->status === 'draft' ? 'secondary' : 'warning'))) }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $invoice->payment_status === 'paid' ? 'success' : 
                                                              ($invoice->payment_status === 'partial' ? 'warning' : 'secondary') }}">
                                        {{ ucfirst($invoice->payment_status) }}
                                    </span>
                                    @if($invoice->payment_status === 'partial')
                                        <br>
                                        <small class="text-muted">
                                            Paid: ₱{{ number_format($invoice->amount_paid, 2) }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('invoices.show', $invoice) }}" 
                                           class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($invoice->status === 'draft')
                                            <a href="{{ route('invoices.edit', $invoice)" 
                                               class="btn btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        
                                        @if($invoice->status === 'sent' && $invoice->payment_status !== 'paid')
                                            <a href="{{ route('payments.create', ['invoice_id' => $invoice->id]) }}" 
                                               class="btn btn-outline-success" title="Receive Payment">
                                                <i class="fas fa-money-bill-wave"></i>
                                            </a>
                                        @endif
                                        
                                        <a href="{{ route('invoices.print', $invoice)" 
                                           class="btn btn-outline-info" title="Print">
                                            <i class="fas fa-print"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="mt-4">
                {{ $invoices->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-file-invoice fa-4x text-muted mb-4"></i>
                <h4 class="text-muted mb-3">No Invoices Found</h4>
                <p class="text-muted mb-4">
                    @if(request()->hasAny(['status', 'payment_status', 'search', 'start_date', 'end_date']))
                        Try adjusting your filters
                    @else
                        Get started by creating your first invoice
                    @endif
                </p>
                <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Create First Invoice
                </a>
            </div>
        @endif
    </div>
</div>

<style>
    .avatar-circle-sm {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 14px;
    }
</style>

<script>
    function exportToExcel() {
        // TODO: Implement Excel export
        alert('Excel export feature coming soon!');
    }
</script>
@endsection