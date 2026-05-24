@extends('layouts.app')

@section('title', 'Invoices - Fix-It Auto Services')

@push('styles')
<style>
.module-invoices { --module-primary: #10b981; --module-primary-dark: #059669; --module-primary-light: #d1fae5; --module-primary-subtle: #ecfdf5; }
</style>
@endpush

@section('content')
<div class="container-fluid module-invoices">
    <!-- Page Header -->
    <div class="page-module-header">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <span class="module-badge"><i class="fas fa-file-invoice"></i></span>
                <div>
                    <h1 class="module-title">Invoices</h1>
                    <p class="module-subtitle">Manage customer invoices and payments</p>
                </div>
            </div>
            <div class="d-flex gap-2 mt-2 mt-sm-0">
                <a href="{{ route('invoices.create') }}" class="btn-create">
                    <i class="fas fa-plus"></i> New Invoice
                </a>
                <a href="{{ route('invoices.statistics') }}" class="btn-secondary-action">
                    <i class="fas fa-chart-bar"></i> Statistics
                </a>
                <div class="dropdown">
                    <button class="btn-secondary-action dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-print"></i> Print List</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-download"></i> Export</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('payments.index') }}"><i class="fas fa-credit-card"></i> View Payments</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-card-body">
                <div class="stat-card-icon"><i class="fas fa-file-invoice"></i></div>
                <div class="stat-card-info">
                    <h3>{{ $invoices->total() }}</h3>
                    <p>Total Invoices</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color: #10b981;">
            <div class="stat-card-body">
                <div class="stat-card-icon" style="background:#d1fae5;color:#059669;">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-card-info">
                    <h3>₱0.00</h3>
                    <p>Total Amount</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color: #f59e0b;">
            <div class="stat-card-body">
                <div class="stat-card-icon" style="background:#fef3c7;color:#d97706;">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-card-info">
                    <h3>₱0.00</h3>
                    <p>Total Due</p>
                </div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color: #ef4444;">
            <div class="stat-card-body">
                <div class="stat-card-icon" style="background:#fee2e2;color:#dc2626;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-card-info">
                    <h3>0</h3>
                    <p>Overdue</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="filter-bar">
        <form method="GET" action="{{ route('invoices.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="draft">Draft</option>
                    <option value="sent">Sent</option>
                    <option value="partial">Partial</option>
                    <option value="paid">Paid</option>
                    <option value="overdue">Overdue</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="customer" class="form-label">Customer</label>
                <input type="text" class="form-control" id="customer" name="customer" placeholder="Search customer...">
            </div>
            <div class="col-md-2">
                <label for="date_from" class="form-label">From Date</label>
                <input type="date" class="form-control" id="date_from" name="date_from">
            </div>
            <div class="col-md-2">
                <label for="date_to" class="form-label">To Date</label>
                <input type="date" class="form-control" id="date_to" name="date_to">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn-filter-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <a href="{{ route('invoices.index') }}" class="btn-filter-outline">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Invoices Table -->
    <div class="main-card">
        <div class="main-card-body">
            @if($invoices->isNotEmpty())
                <div class="table-responsive">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <span class="text-muted">{{ $invoices->count() }} invoices</span>
                        </div>
                        @if($invoices->where('viewed_at', null)->count() > 0)
                            <button class="btn-mark-all-read" id="markAllReadBtn" onclick="markAllAsRead('invoices', this)">
                                <i class="fas fa-check-double"></i> Mark All as Read
                            </button>
                        @endif
                    </div>
                    <table class="table-fixit" id="invoicesTable">
                        <thead>
                            <tr>
                                <th style="width:30px;"></th>
                                <th>Invoice #</th>
                                <th>Customer</th>
                                <th>Work Order</th>
                                <th>Issue Date</th>
                                <th>Due Date</th>
                                <th>Total Amount</th>
                                <th>Balance Due</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoices as $invoice)
                            <tr class="{{ $invoice->viewed_at === null ? 'tr-unread' : '' }}" data-id="{{ $invoice->id }}">
                                <td>
                                    @if($invoice->viewed_at === null)
                                        <span class="unread-dot" title="New"></span>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $invoice->viewed_at === null ? 'unread-primary-text' : '' }}">
                                        <strong>{{ $invoice->invoice_number }}</strong>
                                        @if($invoice->viewed_at === null)
                                            <span class="badge-new-record">NEW</span>
                                        @endif
                                    </span>
                                    <br>
                                    <small style="color:#94a3b8;font-size:0.75rem;">Created: {{ $invoice->created_at->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    {{ $invoice->customer->name ?? 'N/A' }}
                                    <br>
                                    <small style="color:#94a3b8;font-size:0.75rem;">{{ $invoice->customer->phone ?? '' }}</small>
                                </td>
                                <td>
                                    @if($invoice->workOrder)
                                        <a href="{{ route('work-orders.show', $invoice->workOrder->id) }}">
                                            {{ $invoice->workOrder->work_order_number }}
                                        </a>
                                    @else
                                        <span style="color:#94a3b8;">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $invoice->invoice_date->format('M d, Y') }}</td>
                                <td>
                                    {{ $invoice->due_date->format('M d, Y') }}
                                    @if($invoice->due_date < now() && $invoice->status !== 'paid')
                                        <br><span class="status-badge status-badge-danger" style="margin-top:4px;">Overdue</span>
                                    @endif
                                </td>
                                <td style="text-align:right;">
                                    <strong>₱{{ number_format($invoice->total_amount, 2) }}</strong>
                                </td>
                                <td style="text-align:right;">
                                    @if(($invoice->balance_due ?? $invoice->total_amount - ($invoice->paid_amount ?? 0)) > 0)
                                        <strong style="color:#dc2626;">₱{{ number_format($invoice->balance_due ?? $invoice->total_amount - ($invoice->paid_amount ?? 0), 2) }}</strong>
                                    @else
                                        <span style="color:#059669;">₱0.00</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'sent' => 'info',
                                            'partial' => 'warning',
                                            'paid' => 'success',
                                            'overdue' => 'danger',
                                            'cancelled' => 'danger',
                                        ];
                                    @endphp
                                    <span class="status-badge status-badge-{{ $statusColors[$invoice->status] ?? 'secondary' }}">
                                        <i class="fas fa-circle" style="font-size:0.5rem;"></i>
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <a href="{{ route('invoices.show', $invoice->id) }}" class="btn-action" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn-action" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($invoice->status === 'draft')
                                            <a href="{{ route('invoices.send', $invoice->id) }}" class="btn-action" style="color:#6366f1;" title="Send to Customer">
                                                <i class="fas fa-paper-plane"></i>
                                            </a>
                                        @endif
                                        @if(($invoice->balance_due ?? $invoice->total_amount - ($invoice->paid_amount ?? 0)) > 0)
                                            <a href="{{ route('invoices.record-payment', $invoice->id) }}" class="btn-action" style="color:#059669;" title="Record Payment">
                                                <i class="fas fa-credit-card"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('invoices.print', $invoice->id) }}" class="btn-action" title="Print" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        @if(auth()->user() && in_array(auth()->user()->role, ['super_admin', 'admin', 'office_staff', 'technician']))
                                        <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to move this invoice to archive?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action" style="border-color:#fde68a;color:#d97706;" title="Archive">
                                                <i class="fas fa-archive"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center px-3 py-3 border-top">
                    <div class="pagination-info">
                        Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} entries
                    </div>
                    <div>
                        {{ $invoices->links() }}
                    </div>
                </div>
            @else
                <div class="empty-state-module">
                    <div class="empty-state-icon">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <h4>No invoices found</h4>
                    <p>Create your first invoice to start tracking customer payments.</p>
                    <a href="{{ route('invoices.create') }}" class="btn-create">
                        <i class="fas fa-plus"></i> Create Invoice
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        if (status) {
            $('#status').val(status);
        }

        // Unread system initialization
        const table = document.getElementById('invoicesTable');
        if (table) {
            initUnreadSystem(table, 'invoices', { dataAttr: 'data-id', markAllBtnId: 'markAllReadBtn' });
        }
    });
</script>
@endsection
