@extends('layouts.app')

@section('title', 'Expenses Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Expenses Management</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Expenses</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Expense Tracking</h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('expenses.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Record New Expense
                            </a>
                            <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Stats Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-1">Total Expenses</h6>
                                            <h3 class="mb-0">₱{{ number_format($totalExpenses, 2) }}</h3>
                                        </div>
                                        <i class="fas fa-money-bill-wave fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-1">This Month</h6>
                                            <h3 class="mb-0">₱{{ number_format($monthlyExpenses, 2) }}</h3>
                                        </div>
                                        <i class="fas fa-calendar-alt fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-1">Pending Approval</h6>
                                            <h3 class="mb-0">₱{{ number_format($pendingExpenses, 2) }}</h3>
                                        </div>
                                        <i class="fas fa-clock fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="card-title mb-1">Total Records</h6>
                                            <h3 class="mb-0">{{ number_format($expenses->total()) }}</h3>
                                        </div>
                                        <i class="fas fa-receipt fa-2x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Filter Tabs -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-2">
                                <a href="{{ route('expenses.index') }}" class="btn btn-outline-primary {{ !request('status') && !request('category') ? 'active' : '' }}">
                                    All Expenses
                                </a>
                                <a href="{{ route('expenses.index', ['status' => 'pending']) }}" class="btn btn-outline-warning">
                                    Pending <span class="badge bg-warning text-dark ms-1">{{ App\Models\Expense::pending()->count() }}</span>
                                </a>
                                <a href="{{ route('expenses.index', ['status' => 'approved']) }}" class="btn btn-outline-info">
                                    Approved
                                </a>
                                <a href="{{ route('expenses.index', ['status' => 'paid']) }}" class="btn btn-outline-success">
                                    Paid
                                </a>
                                <a href="{{ route('expenses.index', ['category' => 'vehicle']) }}" class="btn btn-outline-danger">
                                    Vehicle Expenses
                                </a>
                                <a href="{{ route('expenses.index', ['month' => date('Y-m')]) }}" class="btn btn-outline-secondary">
                                    This Month
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Expenses Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Expense #</th>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Vendor</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenses as $expense)
                                    <tr>
                                        <td>
                                            <strong>{{ $expense->expense_number }}</strong>
                                            @if($expense->receipt_path)
                                                <br><small class="text-muted"><i class="fas fa-receipt"></i> Receipt attached</small>
                                            @endif
                                        </td>
                                        <td>{{ $expense->date->format('M d, Y') }}</td>
                                        <td>{!! $expense->category_badge !!}</td>
                                        <td>
                                            <div>{{ $expense->description }}</div>
                                            @if($expense->payment_method)
                                                <small class="text-muted">Paid via: {{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}</small>
                                            @endif
                                            @if($expense->reference_number)
                                                <br><small class="text-muted">Ref: {{ $expense->reference_number }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <strong class="text-danger">₱{{ number_format($expense->amount, 2) }}</strong>
                                        </td>
                                        <td>
                                            @if($expense->vendor)
                                                {{ $expense->vendor }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{!! $expense->status_badge !!}</td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('expenses.show', $expense) }}" class="btn btn-outline-primary" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-outline-secondary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($expense->status === 'pending')
                                                    <form action="{{ route('expenses.approve', $expense) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-success" title="Approve" onclick="return confirm('Approve this expense?')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                @if($expense->status === 'approved')
                                                    <form action="{{ route('expenses.markAsPaid', $expense) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-info" title="Mark as Paid" onclick="return confirm('Mark this expense as paid?')">
                                                            <i class="fas fa-money-check"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No expenses found</h5>
                                            <p class="text-muted">
                                                @if(request('search') || request('status') || request('category'))
                                                    Try adjusting your search or filter criteria
                                                @else
                                                    No expenses have been recorded yet
                                                @endif
                                            </p>
                                            <a href="{{ route('expenses.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus me-1"></i> Record First Expense
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($expenses->hasPages())
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div class="text-muted">
                                Showing {{ $expenses->firstItem() }} to {{ $expenses->lastItem() }} of {{ $expenses->total() }} expenses
                            </div>
                            <div>
                                {{ $expenses->links() }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">Filter Expenses</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET" action="{{ route('expenses.index') }}">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="search" class="form-label">Search</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" 
                               placeholder="Search by description, vendor, reference...">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">Category</label>
                            <select class="form-select" id="category" name="category">
                                <option value="all">All Categories</option>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="all">All Status</option>
                                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="month" class="form-label">Or select month</label>
                        <input type="month" class="form-control" id="month" name="month" value="{{ request('month') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                    @if(request('search') || request('status') || request('category') || request('start_date') || request('end_date') || request('month'))
                        <a href="{{ route('expenses.index') }}" class="btn btn-outline-danger">Clear All</a>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-submit search on enter
    document.querySelector('input[name="search"]')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            this.form.submit();
        }
    });
    
    // Set today's date as default for end date in filter
    document.addEventListener('DOMContentLoaded', function() {
        const endDateInput = document.getElementById('end_date');
        if (endDateInput && !endDateInput.value) {
            endDateInput.value = new Date().toISOString().split('T')[0];
        }
        
        // Set start date to first day of current month if not set
        const startDateInput = document.getElementById('start_date');
        if (startDateInput && !startDateInput.value) {
            const now = new Date();
            const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
            startDateInput.value = firstDay.toISOString().split('T')[0];
        }
    });
</script>
@endpush