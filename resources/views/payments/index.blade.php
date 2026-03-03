@extends('layouts.app')

@section('title', 'Payments')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-credit-card text-primary"></i> Payments
        </h1>
        <div class="btn-group">
            <a href="{{ route('payments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Payment
            </a>
            <a href="{{ route('payments.statistics') }}" class="btn btn-info">
                <i class="fas fa-chart-bar"></i> Statistics
            </a>
            <a href="{{ route('payments.by-date-range') }}" class="btn btn-warning">
                <i class="fas fa-calendar-alt"></i> Date Range
            </a>
            <button type="button" class="btn btn-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="visually-hidden">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#"><i class="fas fa-print"></i> Print List</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-download"></i> Export</a></li>
            </ul>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('payments.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="payment_method" class="form-label">Payment Method</label>
                    <select class="form-select" id="payment_method" name="payment_method">
                        <option value="">All Methods</option>
                        <option value="cash">Cash</option>
                        <option value="check">Check</option>
                        <option value="credit_card">Credit Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="gcash">GCash</option>
                        <option value="paymaya">PayMaya</option>
                    </select>
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
                    <a href="{{ route('payments.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Payments List
                <span class="badge bg-secondary ms-2">{{ $payments->total() }} total</span>
            </h6>
        </div>
        <div class="card-body">
            @if($payments->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-credit-card fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">No payments found</h5>
                    <p class="text-gray-500">Record your first payment to get started</p>
                    <a href="{{ route('payments.create') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus"></i> Record Payment
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Payment Date</th>
                                <th>Invoice/Customer</th>
                                <th>Payment Method</th>
                                <th>Amount</th>
                                <th>Reference</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                            <tr>
                                <td>
                                    <strong>{{ $payment->payment_date->format('M d, Y') }}</strong>
                                    <br>
                                    <small class="text-gray-600">{{ $payment->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    @if($payment->invoice)
                                        <a href="{{ route('invoices.show', $payment->invoice->id) }}">
                                            Invoice: {{ $payment->invoice->invoice_number }}
                                        </a>
                                        <br>
                                        <small class="text-gray-600">{{ $payment->invoice->customer->name ?? 'N/A' }}</small>
                                    @elseif($payment->customer)
                                        <a href="#">
                                            {{ $payment->customer->name }}
                                        </a>
                                        <br>
                                        <small class="text-gray-600">Direct Payment</small>
                                    @else
                                        <span class="text-gray-600">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $payment->payment_method_badge_class }}">
                                        {{ $payment->payment_method_display }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <strong>{{ $payment->formatted_amount }}</strong>
                                </td>
                                <td>
                                    @if($payment->reference_number)
                                        <code>{{ $payment->reference_number }}</code>
                                    @else
                                        <span class="text-gray-600">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $payment->status_badge_class }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('payments.show', $payment->id) }}" class="btn btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('payments.edit', $payment->id) }}" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($payment->status === 'pending')
                                            <a href="{{ route('payments.mark-as-completed', $payment->id) }}" class="btn btn-success" title="Mark as Completed">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="{{ route('payments.mark-as-failed', $payment->id) }}" class="btn btn-danger" title="Mark as Failed">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        @endif
                                        @if($payment->status === 'completed')
                                            <a href="{{ route('payments.refund', $payment->id) }}" class="btn btn-warning" title="Refund">
                                                <i class="fas fa-undo"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('payments.print', $payment->id) }}" class="btn btn-secondary" title="Print Receipt" target="_blank">
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
                        Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} entries
                    </div>
                    <div>
                        {{ $payments->links() }}
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
                                Total Payments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $payments->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-credit-card fa-2x text-gray-500"></i>
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
                                Pending Payments
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

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Top Method
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                N/A
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-credit-card fa-2x text-gray-500"></i>
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
        
        const method = urlParams.get('payment_method');
        if (method) {
            $('#payment_method').val(method);
        }
    });
</script>
@endsection