@extends('layouts.app')

@section('title', 'Deductions Management')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1><i class="fas fa-money-bill-wave me-2"></i>Deductions Management</h1>
        <p class="lead">Manage employee deductions for payroll processing</p>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-primary text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase mb-0">Total Deductions</h6>
                            <h2 class="mb-0">{{ $totalDeductions }}</h2>
                            <small>All time</small>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-list"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-warning text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase mb-0">Pending</h6>
                            <h2 class="mb-0">{{ $pendingDeductions }}</h2>
                            <small>Awaiting approval</small>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-success text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase mb-0">Total Amount</h6>
                            <h2 class="mb-0">₱{{ number_format($totalAmount, 2) }}</h2>
                            <small>Approved deductions</small>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card bg-info text-white h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-uppercase mb-0">Quick Actions</h6>
                            <div class="mt-2">
                                <a href="{{ route('hr-payroll.deductions.create') }}" class="btn btn-light btn-sm">
                                    <i class="fas fa-plus me-1"></i> Add Deduction
                                </a>
                            </div>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Deductions Table -->
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i>All Deductions</h5>
            <div class="d-flex gap-2">
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['status' => '']) }}">All</a></li>
                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['status' => 'pending']) }}">Pending</a></li>
                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['status' => 'approved']) }}">Approved</a></li>
                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['status' => 'applied']) }}">Applied</a></li>
                    </ul>
                </div>
                <a href="{{ route('hr-payroll.deductions.create') }}" class="btn btn-sm btn-primary me-2">
                    <i class="fas fa-plus me-1"></i> Add New
                </a>
                <a href="{{ route('hr-payroll.deductions.bulk-create') }}" class="btn btn-sm btn-success">
                    <i class="fas fa-users me-1"></i> Bulk Create
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Employee</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Payroll Period</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deductions as $deduction)
                        <tr>
                            <td>
                                <strong>{{ $deduction->employee->name ?? 'N/A' }}</strong>
                                <br>
                                <small class="text-muted">ID: {{ $deduction->employee_id }}</small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $deduction->deduction_type }}</span>
                            </td>
                            <td class="text-danger">
                                <strong>₱{{ number_format($deduction->amount, 2) }}</strong>
                            </td>
                            <td>{{ $deduction->date->format('M d, Y') }}</td>
                            <td>
                                @if($deduction->payrollPeriod)
                                    <span class="badge bg-info">{{ $deduction->payrollPeriod->period_name }}</span>
                                @else
                                    <span class="text-muted">Not assigned</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $deduction->status_color }}">
                                    {{ ucfirst($deduction->status) }}
                                </span>
                            </td>
                            <td>{{ $deduction->creator->name ?? 'System' }}</td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('hr-payroll.deductions.show', $deduction) }}" class="btn btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('hr-payroll.deductions.edit', $deduction) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($deduction->status === 'pending')
                                    <form action="{{ route('hr-payroll.deductions.approve', $deduction) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-success" onclick="return confirm('Approve this deduction?')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('hr-payroll.deductions.reject', $deduction) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Reject this deduction?')">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                    @endif
                                    <form action="{{ route('hr-payroll.deductions.destroy', $deduction) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Delete this deduction?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-info-circle me-2"></i>No deductions found
                                <br>
                                <div class="mt-2">
                                    <a href="{{ route('hr-payroll.deductions.create') }}" class="btn btn-sm btn-primary me-2">
                                        <i class="fas fa-plus me-1"></i> Create First Deduction
                                    </a>
                                    <a href="{{ route('hr-payroll.deductions.bulk-create') }}" class="btn btn-sm btn-success">
                                        <i class="fas fa-users me-1"></i> Bulk Create
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    Showing {{ $deductions->firstItem() ?? 0 }} to {{ $deductions->lastItem() ?? 0 }} of {{ $deductions->total() }} entries
                </div>
                <div>
                    {{ $deductions->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Auto-refresh every 30 seconds
    setTimeout(function() {
        window.location.reload();
    }, 30000);
</script>
@endsection