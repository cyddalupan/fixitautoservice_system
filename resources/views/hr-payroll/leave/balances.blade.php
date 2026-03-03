@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Leave Balances</h3>
                    <div class="card-tools">
                        <a href="{{ route('hr-payroll.leave') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Leave Management
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Filters</h4>
                                </div>
                                <div class="card-body">
                                    <form method="GET" action="{{ route('hr-payroll.leave.balances') }}">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="employee_id">Employee</label>
                                                    <select class="form-control" id="employee_id" name="employee_id">
                                                        <option value="">All Employees</option>
                                                        @foreach($employees ?? [] as $employee)
                                                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                                                {{ $employee->user->name ?? 'Unknown' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="leave_type">Leave Type</label>
                                                    <select class="form-control" id="leave_type" name="leave_type">
                                                        <option value="">All Types</option>
                                                        @foreach($leaveTypes ?? [] as $type)
                                                            <option value="{{ $type }}" {{ request('leave_type') == $type ? 'selected' : '' }}>
                                                                {{ ucfirst(str_replace('_', ' ', $type)) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <div>
                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fas fa-filter"></i> Apply Filters
                                                        </button>
                                                        <a href="{{ route('hr-payroll.leave.balances') }}" class="btn btn-secondary">
                                                            <i class="fas fa-times"></i> Clear Filters
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Leave Balances Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Total Entitled</th>
                                    <th>Used</th>
                                    <th>Remaining</th>
                                    <th>Expires On</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($balances as $balance)
                                    <tr>
                                        <td>
                                            <strong>{{ $balance->employee->user->name ?? 'Unknown' }}</strong><br>
                                            <small class="text-muted">{{ $balance->employee->employee_id ?? 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">
                                                {{ ucfirst(str_replace('_', ' ', $balance->leave_type)) }}
                                            </span>
                                        </td>
                                        <td>{{ $balance->total_entitled }} days</td>
                                        <td>{{ $balance->used }} days</td>
                                        <td>
                                            <strong>{{ $balance->remaining }} days</strong>
                                            @if($balance->remaining <= 5)
                                                <span class="badge badge-warning float-right">Low</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($balance->expires_at)
                                                {{ \Carbon\Carbon::parse($balance->expires_at)->format('M d, Y') }}
                                                @if(\Carbon\Carbon::parse($balance->expires_at)->isPast())
                                                    <span class="badge badge-danger">Expired</span>
                                                @elseif(\Carbon\Carbon::parse($balance->expires_at)->diffInDays(now()) <= 30)
                                                    <span class="badge badge-warning">Soon</span>
                                                @endif
                                            @else
                                                <span class="text-muted">No expiration</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($balance->remaining > 0)
                                                <span class="badge badge-success">Available</span>
                                            @else
                                                <span class="badge badge-danger">Exhausted</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-toggle="modal" data-target="#balanceDetails{{ $balance->id }}">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <a href="#" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Adjust
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i> No leave balances found.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($balances->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $balances->links() }}
                        </div>
                    @endif

                    <!-- Summary Statistics -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title">Leave Balance Summary</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="info-box bg-info">
                                                <span class="info-box-icon"><i class="fas fa-users"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Employees</span>
                                                    <span class="info-box-number">{{ $balances->total() }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-success">
                                                <span class="info-box-icon"><i class="fas fa-calendar-check"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Total Leave Days</span>
                                                    <span class="info-box-number">
                                                        {{ $balances->sum('total_entitled') }} days
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-warning">
                                                <span class="info-box-icon"><i class="fas fa-calendar-minus"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Used Leave Days</span>
                                                    <span class="info-box-number">
                                                        {{ $balances->sum('used') }} days
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="info-box bg-primary">
                                                <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text">Remaining Leave Days</span>
                                                    <span class="info-box-number">
                                                        {{ $balances->sum('remaining') }} days
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modals for Balance Details -->
@foreach($balances as $balance)
<div class="modal fade" id="balanceDetails{{ $balance->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Leave Balance Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Employee Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <th>Name:</th>
                                <td>{{ $balance->employee->user->name ?? 'Unknown' }}</td>
                            </tr>
                            <tr>
                                <th>Employee ID:</th>
                                <td>{{ $balance->employee->employee_id ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Department:</th>
                                <td>{{ $balance->employee->department ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Position:</th>
                                <td>{{ $balance->employee->position ?? 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Leave Balance Information</h6>
                        <table class="table table-sm">
                            <tr>
                                <th>Leave Type:</th>
                                <td>
                                    <span class="badge badge-info">
                                        {{ ucfirst(str_replace('_', ' ', $balance->leave_type)) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Total Entitled:</th>
                                <td>{{ $balance->total_entitled }} days</td>
                            </tr>
                            <tr>
                                <th>Used:</th>
                                <td>{{ $balance->used }} days</td>
                            </tr>
                            <tr>
                                <th>Remaining:</th>
                                <td>
                                    <strong>{{ $balance->remaining }} days</strong>
                                    @if($balance->remaining <= 5)
                                        <span class="badge badge-warning">Low Balance</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Expires On:</th>
                                <td>
                                    @if($balance->expires_at)
                                        {{ \Carbon\Carbon::parse($balance->expires_at)->format('M d, Y') }}
                                        @if(\Carbon\Carbon::parse($balance->expires_at)->isPast())
                                            <span class="badge badge-danger">Expired</span>
                                        @elseif(\Carbon\Carbon::parse($balance->expires_at)->diffInDays(now()) <= 30)
                                            <span class="badge badge-warning">Expires in {{ \Carbon\Carbon::parse($balance->expires_at)->diffInDays(now()) }} days</span>
                                        @endif
                                    @else
                                        <span class="text-muted">No expiration</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status:</th>
                                <td>
                                    @if($balance->remaining > 0)
                                        <span class="badge badge-success">Available</span>
                                    @else
                                        <span class="badge badge-danger">Exhausted</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-12">
                        <h6>Leave Usage History</h6>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            Leave usage history would be displayed here. This feature requires integration with the leave requests system.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a href="#" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Adjust Balance
                </a>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTable if needed
        $('.table').DataTable({
            "pageLength": 25,
            "responsive": true,
            "order": [[0, 'asc']]
        });
        
        // Export functionality
        $('#exportBtn').click(function() {
            // Implement export functionality here
            alert('Export functionality would be implemented here.');
        });
    });
</script>
@endsection