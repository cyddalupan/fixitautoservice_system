@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Leave Management</h1>
                    <p class="text-muted">Manage employee leave requests and balances</p>
                </div>
                <div>
                    <a href="{{ route('hr-payroll.leave.balances') }}" class="btn btn-info">
                        <i class="fas fa-balance-scale"></i> View Leave Balances
                    </a>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeaveRequestModal">
                        <i class="fas fa-plus"></i> Add Leave Request
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <form method="GET" action="{{ route('hr-payroll.leave') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="employeeFilter">Employee:</label>
                                <select name="employee_id" id="employeeFilter" class="form-control">
                                    <option value="">All Employees</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="leaveTypeFilter">Leave Type:</label>
                                <select name="leave_type" id="leaveTypeFilter" class="form-control">
                                    <option value="">All Types</option>
                                    @foreach($leaveTypes as $type)
                                        <option value="{{ $type }}" {{ request('leave_type') == $type ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="statusFilter">Status:</label>
                                <select name="status" id="statusFilter" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="dateRange">Date Range:</label>
                                <input type="text" name="date_range" id="dateRange" class="form-control" 
                                       placeholder="Select date range" value="{{ request('date_range') }}">
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Apply Filters
                                </button>
                                <a href="{{ route('hr-payroll.leave') }}" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Pending Requests
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $leaveRequests->where('status', 'pending')->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Approved This Month
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $leaveRequests->where('status', 'approved')
                                                  ->whereBetween('start_date', [now()->startOfMonth(), now()->endOfMonth()])
                                                  ->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                On Leave Today
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $leaveRequests->where('status', 'approved')
                                                  ->filter(function($request) {
                                                      return \Carbon\Carbon::parse($request->start_date)->lte(today()) &&
                                                             \Carbon\Carbon::parse($request->end_date)->gte(today());
                                                  })
                                                  ->count() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Leave Days
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $leaveRequests->where('status', 'approved')->sum(function($request) {
                                    return \Carbon\Carbon::parse($request->start_date)->diffInDays($request->end_date) + 1;
                                }) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Requests Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Leave Requests</h6>
                    <div class="btn-group">
                        <button type="button" class="btn btn-light btn-sm" onclick="approveSelected()">
                            <i class="fas fa-check"></i> Approve Selected
                        </button>
                        <button type="button" class="btn btn-light btn-sm" onclick="rejectSelected()">
                            <i class="fas fa-times"></i> Reject Selected
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="leaveRequestsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th width="50">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Date Range</th>
                                    <th>Duration</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Submitted On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaveRequests as $request)
                                    <tr>
                                        <td>
                                            @if($request->status === 'pending')
                                                <input type="checkbox" class="request-checkbox" value="{{ $request->id }}">
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="mr-3">
                                                    <img src="{{ $request->employee->profile_photo_url ?? asset('images/default-avatar.png') }}" 
                                                         alt="{{ $request->employee->name }}" 
                                                         class="rounded-circle" 
                                                         width="40" 
                                                         height="40">
                                                </div>
                                                <div>
                                                    <strong>{{ $request->employee->name }}</strong>
                                                    <div class="text-muted small">{{ $request->employee->hrDetail->position ?? 'Employee' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $request->leave_type === 'vacation' ? 'info' : ($request->leave_type === 'sick' ? 'warning' : 'secondary') }} p-2">
                                                {{ ucfirst(str_replace('_', ' ', $request->leave_type)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ \Carbon\Carbon::parse($request->start_date)->format('M d, Y') }}</strong>
                                            <div class="text-muted small">to</div>
                                            <strong>{{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ \Carbon\Carbon::parse($request->start_date)->diffInDays($request->end_date) + 1 }} days</strong>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $request->reason }}">
                                                {{ $request->reason }}
                                            </div>
                                        </td>
                                        <td>
                                            @if($request->status === 'pending')
                                                <span class="badge badge-warning p-2">Pending</span>
                                            @elseif($request->status === 'approved')
                                                <span class="badge badge-success p-2">Approved</span>
                                            @elseif($request->status === 'rejected')
                                                <span class="badge badge-danger p-2">Rejected</span>
                                            @elseif($request->status === 'cancelled')
                                                <span class="badge badge-secondary p-2">Cancelled</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($request->created_at)->format('M d, Y') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" 
                                                        class="btn btn-sm btn-info" 
                                                        title="View Details"
                                                        onclick="viewRequest({{ $request->id }})">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                
                                                @if($request->status === 'pending')
                                                    <button type="button" 
                                                            class="btn btn-sm btn-success" 
                                                            title="Approve"
                                                            onclick="approveRequest({{ $request->id }})">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="Reject"
                                                            onclick="rejectRequest({{ $request->id }})">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @endif
                                                
                                                @if(in_array($request->status, ['pending', 'approved']))
                                                    <button type="button" 
                                                            class="btn btn-sm btn-warning" 
                                                            title="Edit"
                                                            onclick="editRequest({{ $request->id }})">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="fas fa-calendar-alt fa-2x mb-3"></i>
                                            <p>No leave requests found.</p>
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeaveRequestModal">
                                                <i class="fas fa-plus"></i> Add Leave Request
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($leaveRequests->hasPages())
                        <div class="mt-3">
                            {{ $leaveRequests->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Leave Calendar -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-info text-white">
                    <h6 class="m-0 font-weight-bold">Upcoming Approved Leave (Next 30 Days)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="thead-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Duration</th>
                                    <th>Coverage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $upcomingLeave = $leaveRequests->where('status', 'approved')
                                                                   ->where('start_date', '>=', today())
                                                                   ->where('start_date', '<=', now()->addDays(30))
                                                                   ->sortBy('start_date')
                                                                   ->take(10);
                                @endphp
                                
                                @forelse($upcomingLeave as $request)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="mr-3">
                                                    <img src="{{ $request->employee->profile_photo_url ?? asset('images/default-avatar.png') }}" 
                                                         alt="{{ $request->employee->name }}" 
                                                         class="rounded-circle" 
                                                         width="30" 
                                                         height="30">
                                                </div>
                                                <div>
                                                    {{ $request->employee->name }}
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $request->leave_type === 'vacation' ? 'info' : ($request->leave_type === 'sick' ? 'warning' : 'secondary') }}">
                                                {{ ucfirst(str_replace('_', ' ', $request->leave_type)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ \Carbon\Carbon::parse($request->start_date)->format('M d, Y') }}</strong>
                                            <div class="text-muted small">
                                                {{ \Carbon\Carbon::parse($request->start_date)->diffForHumans() }}
                                            </div>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}</td>
                                        <td class="text-center">
                                            <strong>{{ \Carbon\Carbon::parse($request->start_date)->diffInDays($request->end_date) + 1 }} days</strong>
                                        </td>
                                        <td>
                                            @if($request->employee->hrDetail->reports_to_name)
                                                <span class="badge badge-light">
                                                    {{ $request->employee->hrDetail->reports_to_name }}
                                                </span>
                                            @else
                                                <span class="text-muted">No coverage assigned</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">
                                            No upcoming leave in the next 30 days.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Leave Request Modal -->
<div class="modal fade" id="addLeaveRequestModal" tabindex="-1" role="dialog" aria-labelledby="addLeaveRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addLeaveRequestModalLabel">Add Leave Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addLeaveRequestForm">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="modal_employee_id">Employee *</label>
                                <select name="employee_id" id="modal_employee_id" class="form-control" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="modal_leave_type">Leave Type *</label>
                                <select name="leave_type" id="modal_leave_type" class="form-control" required>
                                    <option value="">Select Type</option>
                                    @foreach($leaveTypes as $type)
                                        <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="modal_start_date">Start Date *</label>
                                <input type="date" name="start_date" id="modal_start_date" class="form-control" 
                                       value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="modal_end_date">End Date *</label>
                                <input type="date" name="end_date" id="modal_end_date" class="form-control" 
                                       value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="modal_reason">Reason *</label>
                                <textarea name="reason" id="modal_reason" class="form-control" rows="3" 
                                          placeholder="Please provide a reason for this leave request" required></textarea>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="modal_notes">Additional Notes</label>
                                <textarea name="notes" id="modal_notes" class="form-control" rows="2" 
                                          placeholder="Optional additional notes"></textarea>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitLeaveRequest()">Submit Leave Request</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize DataTables with a small delay to ensure DOM is ready
        setTimeout(function() {
            $('#leaveRequestsTable').DataTable({
                pageLength: 25,
                order: [[7, 'desc']],
                initComplete: function(settings, json) {
                    console.log('DataTables initialized successfully');
                },
                error: function(settings, techNote, message) {
                    console.error('DataTables error:', message);
                }
            });
        }, 100);

        });

        // Date range picker
        $('#dateRange').daterangepicker({
            autoUpdateInput: false,
            locale: {
                cancelLabel: 'Clear'
            }
        });

        $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('MM/DD/YYYY') + ' - ' + picker.endDate.format('MM/DD/YYYY'));
        });

        $('#dateRange').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });

        // Select all checkbox
        $('#selectAll').click(function() {
            $('.request-checkbox').prop('checked', this.checked);
        });
    });

    function viewRequest(requestId) {
        // In a real implementation, this would open a modal with request details
        alert('Viewing leave request ID: ' + requestId);
    }

    function editRequest(requestId) {
        // In a real implementation, this would open an edit modal
        alert('Editing leave request ID: ' + requestId);
    }

    function approveRequest(requestId) {
        if (confirm('Approve this leave request?')) {
            // In a real implementation, this would submit an approval request
            alert('Approving leave request ID: ' + requestId);
            // window.location.href = "/hr-payroll/leave/" + requestId + "/approve";
        }
    }

    function rejectRequest(requestId) {
        if (confirm('Reject this leave request?')) {
            // In a real implementation, this would submit a rejection request
            alert('Rejecting leave request ID: ' + requestId);
            // window.location.href = "/hr-payroll/leave/" + requestId + "/reject";
        }
    }

    function approveSelected() {
        const selectedIds = [];
        $('.request-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            alert('Please select at least one pending request to approve.');
            return;
        }

        if (confirm('Approve ' + selectedIds.length + ' selected leave requests?')) {
            // In a real implementation, this would submit a batch approval
            alert('Approving requests: ' + selectedIds.join(', '));
        }
    }

    function rejectSelected() {
        const selectedIds = [];
        $('.request-checkbox:checked').each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            alert('Please select at least one pending request to reject.');
            return;
        }

        if (confirm('Reject ' + selectedIds.length + ' selected leave requests?')) {
            // In a real implementation, this would submit a batch rejection
            alert('Rejecting requests: ' + selectedIds.join(', '));
        }
    }

    function submitLeaveRequest() {
        // In a real implementation, this would submit the form via AJAX
        alert('Leave request form would be submitted here.');
        $('#addLeaveRequestModal').modal('hide');
    }
</script>
@endpush