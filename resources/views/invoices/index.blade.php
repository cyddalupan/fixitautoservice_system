@extends('layouts.app')

@section('title', 'Invoices')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-invoice text-primary"></i> Invoices
        </h1>
        <div class="btn-group">
            <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Invoice
            </a>
            <a href="{{ route('invoices.statistics') }}" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> Statistics
            </a>
            <button type="button" class="btn btn-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="visually-hidden">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#"><i class="fas fa-print"></i> Print List</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-download"></i> Export</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('payments.by-date-range') }}"><i class="fas fa-calendar-alt"></i> Date Range Report</a></li>
            </ul>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('invoices.index') }}" class="row g-3">
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
                <div class="col-md-3">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" class="form-control" id="date_from" name="date_from">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" class="form-control" id="date_to" name="date_to">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Invoices List
                <span class="badge bg-secondary ms-2">{{ $invoices->total() }} total</span>
            </h6>
        </div>
        <div class="card-body">
            @if($invoices->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">No invoices found</h5>
                    <p class="text-gray-500">Create your first invoice to get started</p>
                    <a href="{{ route('invoices.create') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus"></i> Create Invoice
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
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
                            <tr>
                                <td>
                                    <strong>{{ $invoice->invoice_number }}</strong>
                                    <br>
                                    <small class="text-gray-600">Created: {{ $invoice->created_at->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    {{ $invoice->customer->name ?? 'N/A' }}
                                    <br>
                                    <small class="text-gray-600">{{ $invoice->customer->phone ?? '' }}</small>
                                </td>
                                <td>
                                    @if($invoice->workOrder)
                                        <a href="{{ route('work-orders.show', $invoice->workOrder->id) }}">
                                            {{ $invoice->workOrder->work_order_number }}
                                        </a>
                                    @else
                                        <span class="text-gray-600">N/A</span>
                                    @endif
                                </td>
                                <td>{{ $invoice->issue_date->format('M d, Y') }}</td>
                                <td>
                                    {{ $invoice->due_date->format('M d, Y') }}
                                    @if($invoice->due_date < now() && $invoice->status !== 'paid')
                                        <br><span class="badge bg-danger">Overdue</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <strong>₱{{ number_format($invoice->total_amount, 2) }}</strong>
                                </td>
                                <td class="text-end">
                                    @if($invoice->balance_due > 0)
                                        <strong class="text-danger">₱{{ number_format($invoice->balance_due, 2) }}</strong>
                                    @else
                                        <span class="text-success">₱0.00</span>
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
                                            'cancelled' => 'dark',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$invoice->status] ?? 'secondary' }}">
                                        {{ ucfirst($invoice->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('invoices.show', $invoice->id) }}" class="btn btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($invoice->status === 'draft')
                                            <a href="{{ route('invoices.send', $invoice->id) }}" class="btn btn-primary" title="Send to Customer">
                                                <i class="fas fa-paper-plane"></i>
                                            </a>
                                        @endif
                                        @if($invoice->balance_due > 0)
                                            <a href="{{ route('invoices.record-payment', $invoice->id) }}" class="btn btn-success" title="Record Payment">
                                                <i class="fas fa-credit-card"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('invoices.print', $invoice->id) }}" class="btn btn-secondary" title="Print" target="_blank">
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
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-gray-600">
                        Showing {{ $invoices->firstItem() }} to {{ $invoices->lastItem() }} of {{ $invoices->total() }} entries
                    </div>
                    <div>
                        {{ $invoices->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Invoices
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $invoices->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice fa-2x text-gray-500"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Amount
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₱0.00
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-500"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Total Due
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₱0.00
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-500"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Overdue Invoices
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                0
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-500"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTables
        if ($.fn.DataTable) {
            $('table').DataTable({
                pageLength: 10,
                responsive: true,
                order: [[0, 'desc']]
            });
        }

        // Status filter
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        if (status) {
            $('#status').val(status);
        }
    });
</script>
@endsection