@extends('layouts.app')

@section('title', 'Estimates')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-invoice-dollar text-primary"></i> Estimates
        </h1>
        <div class="btn-group">
            <a href="{{ route('estimates.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Estimate
            </a>
            <button type="button" class="btn btn-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                <span class="visually-hidden">Toggle Dropdown</span>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#"><i class="fas fa-print"></i> Print List</a></li>
                <li><a class="dropdown-item" href="#"><i class="fas fa-download"></i> Export</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('estimates.statistics') }}"><i class="fas fa-chart-bar"></i> Statistics</a></li>
            </ul>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('estimates.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Status</option>
                        <option value="draft">Draft</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="expired">Expired</option>
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
                    <a href="{{ route('estimates.index') }}" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Estimates Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Estimates List
                <span class="badge bg-secondary ms-2">{{ $estimates->total() }} total</span>
            </h6>
        </div>
        <div class="card-body">
            @if($estimates->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice-dollar fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">No estimates found</h5>
                    <p class="text-gray-500">Create your first estimate to get started</p>
                    <a href="{{ route('estimates.create') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus"></i> Create Estimate
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Estimate #</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Issue Date</th>
                                <th>Expiry Date</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($estimates as $estimate)
                            <tr>
                                <td>
                                    <strong>{{ $estimate->estimate_number }}</strong>
                                    <br>
                                    <small class="text-gray-600">Created: {{ $estimate->created_at->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    {{ $estimate->customer->name ?? 'N/A' }}
                                    <br>
                                    <small class="text-gray-600">{{ $estimate->customer->phone ?? '' }}</small>
                                </td>
                                <td>
                                    {{ $estimate->vehicle->make ?? 'N/A' }} {{ $estimate->vehicle->model ?? '' }}
                                    <br>
                                    <small class="text-gray-600">{{ $estimate->vehicle->license_plate ?? '' }}</small>
                                </td>
                                <td>{{ $estimate->issue_date->format('M d, Y') }}</td>
                                <td>
                                    {{ $estimate->expiry_date->format('M d, Y') }}
                                    @if($estimate->expiry_date < now())
                                        <br><span class="badge bg-danger">Expired</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <strong>₱{{ number_format($estimate->total_amount, 2) }}</strong>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'draft' => 'secondary',
                                            'pending' => 'warning',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'expired' => 'dark',
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$estimate->status] ?? 'secondary' }}">
                                        {{ ucfirst($estimate->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('estimates.show', $estimate->id) }}" class="btn btn-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('estimates.edit', $estimate->id) }}" class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        
                                        <!-- Form for POST actions (hidden) -->
                                        <form id="estimate-action-form-{{ $estimate->id }}" method="POST" style="display: none;">
                                            @csrf
                                        </form>
                                        
                                        <!-- Draft Status: Send for Approval -->
                                        @if($estimate->status === 'draft')
                                            <button type="submit" 
                                                    form="estimate-action-form-{{ $estimate->id }}"
                                                    formaction="{{ route('estimates.send', $estimate->id) }}"
                                                    class="btn btn-primary" 
                                                    title="Send for Approval">
                                                📤 Send for Approval
                                            </button>
                                        @endif
                                        
                                        <!-- Pending Status: Resend, Approve, Reject -->
                                        @if($estimate->status === 'pending')
                                            <button type="submit" 
                                                    form="estimate-action-form-{{ $estimate->id }}"
                                                    formaction="{{ route('estimates.send', $estimate->id) }}"
                                                    class="btn btn-secondary" 
                                                    title="Resend Approval">
                                                Resend
                                            </button>
                                            <button type="submit" 
                                                    form="estimate-action-form-{{ $estimate->id }}"
                                                    formaction="{{ route('estimates.approve', $estimate->id) }}"
                                                    class="btn btn-success" 
                                                    title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="submit" 
                                                    form="estimate-action-form-{{ $estimate->id }}"
                                                    formaction="{{ route('estimates.reject', $estimate->id) }}"
                                                    class="btn btn-danger" 
                                                    title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        
                                        <!-- Approved Status: Convert to Work Order -->
                                        @if($estimate->status === 'approved')
                                            <a href="{{ route('estimates.convert-to-work-order', $estimate->id) }}" class="btn btn-primary" title="Convert to Work Order">
                                                <i class="fas fa-wrench"></i>
                                            </a>
                                        @endif
                                        
                                        <!-- Print Button (Always Visible) -->
                                        <a href="{{ route('estimates.print', $estimate->id) }}" class="btn btn-secondary" title="Print" target="_blank">
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
                        Showing {{ $estimates->firstItem() }} to {{ $estimates->lastItem() }} of {{ $estimates->total() }} entries
                    </div>
                    <div>
                        {{ $estimates->links() }}
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
                                Total Estimates
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $estimates->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice-dollar fa-2x text-gray-500"></i>
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
                                Approved Estimates
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                0
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-500"></i>
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
                                Pending Approval
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
                                Total Value
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