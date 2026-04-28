@extends('layouts.app')

@section('title', 'Expenses Management')

@section('body-class', 'page-expenses')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-1" style="font-weight: 700; color: #0f172a;">Expenses Management</h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0" style="background: none; padding: 0;">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" style="color: #64748b; text-decoration: none;">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page" style="color: #0f172a;">Expenses</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('expenses.create') }}" class="btn" style="background: #059669; color: #fff; border: none;">
                        <i class="fas fa-plus me-1"></i> Record New Expense
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card-gradient stat-gradient-teal p-3" style="border-radius: 12px; height: 100%;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1" style="font-size: 0.8rem; opacity: 0.85; font-weight: 500;">Total This Month</p>
                        <h3 class="mb-0" style="font-weight: 700;">₱{{ number_format($monthlyExpenses, 2) }}</h3>
                    </div>
                    <div class="stat-icon" style="opacity: 0.3;">
                        <i class="fas fa-calendar-alt" style="font-size: 2.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-gradient stat-gradient-amber p-3" style="border-radius: 12px; height: 100%;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1" style="font-size: 0.8rem; opacity: 0.85; font-weight: 500;">Pending Expenses</p>
                        <h3 class="mb-0" style="font-weight: 700;">₱{{ number_format($pendingExpenses, 2) }}</h3>
                    </div>
                    <div class="stat-icon" style="opacity: 0.3;">
                        <i class="fas fa-clock" style="font-size: 2.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-gradient stat-gradient-green p-3" style="border-radius: 12px; height: 100%;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1" style="font-size: 0.8rem; opacity: 0.85; font-weight: 500;">Paid Expenses</p>
                        <h3 class="mb-0" style="font-weight: 700;">—</h3>
                    </div>
                    <div class="stat-icon" style="opacity: 0.3;">
                        <i class="fas fa-check-circle" style="font-size: 2.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-gradient stat-gradient-purple p-3" style="border-radius: 12px; height: 100%;">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1" style="font-size: 0.8rem; opacity: 0.85; font-weight: 500;">Total Records</p>
                        <h3 class="mb-0" style="font-weight: 700;">{{ number_format($expenses->total()) }}</h3>
                    </div>
                    <div class="stat-icon" style="opacity: 0.3;">
                        <i class="fas fa-receipt" style="font-size: 2.2rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Filter Tabs -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="{{ route('expenses.index') }}" class="btn btn-sm" style="border-radius: 50px; font-size: 0.8rem; {{ !request('status') && !request('category') ? 'background: #059669; color: #fff; border: 1px solid #059669;' : 'background: #fff; color: #475569; border: 1px solid #e2e8f0;' }}">
            All Expenses
        </a>
        <a href="{{ route('expenses.index', ['status' => 'pending']) }}" class="btn btn-sm" style="border-radius: 50px; font-size: 0.8rem; {{ request('status') === 'pending' ? 'background: #d97706; color: #fff; border: 1px solid #d97706;' : 'background: #fff; color: #475569; border: 1px solid #e2e8f0;' }}">
            <i class="fas fa-clock me-1" style="font-size: 0.7rem;"></i> Pending
            <span class="badge ms-1" style="background: {{ request('status') === 'pending' ? 'rgba(255,255,255,0.25)' : '#fef3c7' }}; color: {{ request('status') === 'pending' ? '#fff' : '#92400e' }}; font-size: 0.65rem;">{{ App\Models\Expense::pending()->count() }}</span>
        </a>
        <a href="{{ route('expenses.index', ['status' => 'approved']) }}" class="btn btn-sm" style="border-radius: 50px; font-size: 0.8rem; {{ request('status') === 'approved' ? 'background: #6366f1; color: #fff; border: 1px solid #6366f1;' : 'background: #fff; color: #475569; border: 1px solid #e2e8f0;' }}">
            Approved
        </a>
        <a href="{{ route('expenses.index', ['status' => 'paid']) }}" class="btn btn-sm" style="border-radius: 50px; font-size: 0.8rem; {{ request('status') === 'paid' ? 'background: #16a34a; color: #fff; border: 1px solid #16a34a;' : 'background: #fff; color: #475569; border: 1px solid #e2e8f0;' }}">
            Paid
        </a>
        <a href="{{ route('expenses.index', ['category' => 'vehicle']) }}" class="btn btn-sm" style="border-radius: 50px; font-size: 0.8rem; {{ request('category') === 'vehicle' ? 'background: #dc2626; color: #fff; border: 1px solid #dc2626;' : 'background: #fff; color: #475569; border: 1px solid #e2e8f0;' }}">
            Vehicle Expenses
        </a>
        <a href="{{ route('expenses.index', ['month' => date('Y-m')]) }}" class="btn btn-sm" style="border-radius: 50px; font-size: 0.8rem; background: #fff; color: #475569; border: 1px solid #e2e8f0;">
            This Month
        </a>
    </div>

    <!-- Main Card -->
    <div class="card" style="border: none; border-radius: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.1);">
        <div class="card-body p-4">
            <!-- Filter Bar -->
            <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                <form method="GET" action="{{ route('expenses.index') }}" class="d-flex flex-wrap align-items-center gap-2" style="flex: 1;">
                    <div style="position: relative; flex: 1; min-width: 180px;">
                        <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.85rem;"></i>
                        <input type="text" class="form-control" name="search" 
                               placeholder="Search expenses..." 
                               value="{{ request('search') }}"
                               style="padding-left: 36px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.85rem;">
                    </div>
                    <select class="form-select" name="category" style="border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.85rem; width: auto; min-width: 140px;">
                        <option value="">All Categories</option>
                        @foreach($categories as $key => $label)
                            <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select class="form-select" name="status" style="border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.85rem; width: auto; min-width: 130px;">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}" style="border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.85rem; width: auto;">
                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}" style="border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.85rem; width: auto;">
                    <button type="submit" class="btn" style="background: #059669; color: #fff; border: none; border-radius: 8px; font-size: 0.85rem;">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    @if(request('search') || request('status') || request('category') || request('date_from') || request('date_to'))
                        <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary" style="border-radius: 8px; font-size: 0.85rem;">
                            <i class="fas fa-times me-1"></i> Clear
                        </a>
                    @endif
                </form>
                <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#filterModal" style="border-radius: 8px; font-size: 0.85rem;">
                    <i class="fas fa-sliders-h me-1"></i> Advanced
                </button>
            </div>

            <!-- Expenses Table -->
            <div class="table-responsive">
                <table class="table" style="margin-bottom: 0;">
                    <thead>
                        <tr style="border-bottom: 2px solid #f1f5f9;">
                            <th style="padding: 0.75rem; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Date</th>
                            <th style="padding: 0.75rem; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Category</th>
                            <th style="padding: 0.75rem; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Description</th>
                            <th style="padding: 0.75rem; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Amount</th>
                            <th style="padding: 0.75rem; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Vendor</th>
                            <th style="padding: 0.75rem; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Status</th>
                            <th style="padding: 0.75rem; font-size: 0.75rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; width: 120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 0.75rem; vertical-align: middle;">
                                    <span style="font-weight: 500; font-size: 0.85rem; color: #0f172a;">{{ $expense->date->format('M d, Y') }}</span>
                                </td>
                                <td style="padding: 0.75rem; vertical-align: middle;">
                                    {!! str_replace(['class="','badge'], ['style="display:inline-block;padding:0.25rem 0.6rem;border-radius:50px;font-size:0.7rem;font-weight:500;','badge'], $expense->category_badge) !!}
                                </td>
                                <td style="padding: 0.75rem; vertical-align: middle;">
                                    <div style="font-weight: 500; font-size: 0.85rem; color: #0f172a;">{{ $expense->description }}</div>
                                    @if($expense->payment_method)
                                        <div style="font-size: 0.75rem; color: #94a3b8;">
                                            <i class="fas fa-credit-card me-1" style="font-size: 0.7rem;"></i> Paid via: {{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}
                                        </div>
                                    @endif
                                    @if($expense->reference_number)
                                        <div style="font-size: 0.75rem; color: #94a3b8;">
                                            <i class="fas fa-hashtag me-1" style="font-size: 0.7rem;"></i> Ref: {{ $expense->reference_number }}
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem; vertical-align: middle;">
                                    <span style="font-weight: 700; font-size: 0.9rem; color: #dc2626;">₱{{ number_format($expense->amount, 2) }}</span>
                                </td>
                                <td style="padding: 0.75rem; vertical-align: middle;">
                                    @if($expense->vendor)
                                        <span style="font-size: 0.85rem; color: #334155;">{{ $expense->vendor }}</span>
                                    @else
                                        <span style="font-size: 0.8rem; color: #94a3b8;">—</span>
                                    @endif
                                </td>
                                <td style="padding: 0.75rem; vertical-align: middle;">
                                    @if($expense->status === 'pending')
                                        <span style="display: inline-block; padding: 0.25rem 0.6rem; border-radius: 50px; font-size: 0.7rem; font-weight: 500; background: #fef3c7; color: #92400e;">Pending</span>
                                    @elseif($expense->status === 'approved')
                                        <span style="display: inline-block; padding: 0.25rem 0.6rem; border-radius: 50px; font-size: 0.7rem; font-weight: 500; background: #dbeafe; color: #1d40af;">Approved</span>
                                    @elseif($expense->status === 'paid')
                                        <span style="display: inline-block; padding: 0.25rem 0.6rem; border-radius: 50px; font-size: 0.7rem; font-weight: 500; background: #d1fae5; color: #065f46;">Paid</span>
                                    @elseif($expense->status === 'rejected')
                                        <span style="display: inline-block; padding: 0.25rem 0.6rem; border-radius: 50px; font-size: 0.7rem; font-weight: 500; background: #fee2e2; color: #991b1b;">Rejected</span>
                                    @else
                                        {!! $expense->status_badge !!}
                                    @endif
                                </td>
                                <td style="padding: 0.75rem; vertical-align: middle;">
                                    <div class="dropdown">
                                        <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" 
                                                style="border: 1px solid #e2e8f0; border-radius: 6px; background: #fff; padding: 0.25rem 0.5rem;"
                                                aria-expanded="false">
                                            <i class="fas fa-ellipsis-v" style="color: #64748b; font-size: 0.8rem;"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end" style="border: none; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 0.4rem;">
                                            <li>
                                                <a href="{{ route('expenses.show', $expense) }}" class="dropdown-item" style="font-size: 0.85rem; padding: 0.5rem 1rem; border-radius: 6px;">
                                                    <i class="fas fa-eye me-2" style="color: #64748b; width: 16px;"></i> View
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('expenses.edit', $expense) }}" class="dropdown-item" style="font-size: 0.85rem; padding: 0.5rem 1rem; border-radius: 6px;">
                                                    <i class="fas fa-edit me-2" style="color: #64748b; width: 16px;"></i> Edit
                                                </a>
                                            </li>
                                            @if($expense->status === 'pending')
                                                <li><hr class="dropdown-divider" style="margin: 0.25rem 0;"></li>
                                                <li>
                                                    <form action="{{ route('expenses.approve', $expense) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item" style="font-size: 0.85rem; padding: 0.5rem 1rem; border-radius: 6px; background: none; border: none; width: 100%; text-align: left;" onclick="return confirm('Approve this expense?')">
                                                            <i class="fas fa-check me-2" style="color: #16a34a; width: 16px;"></i> Approve
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                            @if($expense->status === 'approved')
                                                <li><hr class="dropdown-divider" style="margin: 0.25rem 0;"></li>
                                                <li>
                                                    <form action="{{ route('expenses.markAsPaid', $expense) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item" style="font-size: 0.85rem; padding: 0.5rem 1rem; border-radius: 6px; background: none; border: none; width: 100%; text-align: left;" onclick="return confirm('Mark this expense as paid?')">
                                                            <i class="fas fa-money-check me-2" style="color: #2563eb; width: 16px;"></i> Mark as Paid
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="padding: 3rem 2rem; text-align: center;">
                                    <i class="fas fa-receipt" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem; display: block;"></i>
                                    <h5 style="font-weight: 600; color: #0f172a; margin-bottom: 0.5rem;">No expenses found</h5>
                                    <p style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 1.25rem;">
                                        @if(request('search') || request('status') || request('category'))
                                            Try adjusting your search or filter criteria
                                        @else
                                            No expenses have been recorded yet
                                        @endif
                                    </p>
                                    <a href="{{ route('expenses.create') }}" class="btn" style="background: #059669; color: #fff; border: none; border-radius: 8px;">
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
                <div class="d-flex justify-content-between align-items-center mt-3 pt-3" style="border-top: 1px solid #f1f5f9;">
                    <div style="font-size: 0.8rem; color: #64748b;">
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

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border: none; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,0.12);">
            <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; padding: 1.25rem 1.5rem;">
                <h5 class="modal-title" id="filterModalLabel" style="font-weight: 600; color: #0f172a;">
                    <i class="fas fa-sliders-h me-2" style="color: #059669;"></i> Filter Expenses
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="GET" action="{{ route('expenses.index') }}">
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="mb-3">
                        <label for="search" class="form-label" style="font-weight: 500; font-size: 0.85rem; color: #334155;">Search</label>
                        <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" 
                               placeholder="Search by description, vendor, reference..."
                               style="border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.85rem;">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label" style="font-weight: 500; font-size: 0.85rem; color: #334155;">Category</label>
                            <select class="form-select" id="category" name="category" style="border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.85rem;">
                                <option value="all">All Categories</option>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label" style="font-weight: 500; font-size: 0.85rem; color: #334155;">Status</label>
                            <select class="form-select" id="status" name="status" style="border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.85rem;">
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
                            <label for="start_date" class="form-label" style="font-weight: 500; font-size: 0.85rem; color: #334155;">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}"
                                   style="border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.85rem;">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label" style="font-weight: 500; font-size: 0.85rem; color: #334155;">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}"
                                   style="border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.85rem;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="month" class="form-label" style="font-weight: 500; font-size: 0.85rem; color: #334155;">Or select month</label>
                        <input type="month" class="form-control" id="month" name="month" value="{{ request('month') }}"
                               style="border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.85rem;">
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e2e8f0; padding: 1rem 1.5rem;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px; font-size: 0.85rem;">Cancel</button>
                    <button type="submit" class="btn" style="background: #059669; color: #fff; border: none; border-radius: 8px; font-size: 0.85rem;">Apply Filters</button>
                    @if(request('search') || request('status') || request('category') || request('start_date') || request('end_date') || request('month'))
                        <a href="{{ route('expenses.index') }}" class="btn btn-outline-danger" style="border-radius: 8px; font-size: 0.85rem;">Clear All</a>
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
