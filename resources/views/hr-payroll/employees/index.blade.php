@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h3 mb-0 text-gray-800">Employee Management</h1>
            <p class="text-muted">Manage all employees in the system</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">All Employees</h6>
                    <a href="{{ route('hr-payroll.employees.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus"></i> Add New Employee
                    </a>
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
                        <table class="table table-bordered table-hover" id="employeesTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Employment Type</th>
                                    <th>Status</th>
                                    <th>Hire Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    <tr>
                                        <td>{{ $employee->employee_id ?? 'N/A' }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="mr-3">
                                                    <img src="{{ $employee->profile_photo_url ?? asset('images/default-avatar.png') }}" 
                                                         alt="{{ $employee->name }}" 
                                                         class="rounded-circle" 
                                                         width="40" 
                                                         height="40">
                                                </div>
                                                <div>
                                                    <strong>{{ $employee->name }}</strong>
                                                    <div class="text-muted small">{{ $employee->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $employee->hrDetail->department ?? 'Not Set' }}</td>
                                        <td>{{ $employee->hrDetail->position ?? 'Not Set' }}</td>
                                        <td>
                                            @php
                                                // Debug: Check what employment_type value we have
                                                $empType = $employee->employment_type ?? null;
                                                $badgeColor = 'light';
                                                $displayText = 'Not Set';
                                                
                                                // DEBUG: Log the value
                                                // {{-- DEBUG: employment_type = {{ $empType }} --}}
                                                
                                                if ($empType && $empType !== '') {
                                                    // Simple color mapping
                                                    $badgeColor = match($empType) {
                                                        'full_time' => 'success',
                                                        'part_time' => 'info',
                                                        'contract' => 'warning',
                                                        'temporary' => 'secondary',
                                                        default => 'light',
                                                    };
                                                    $displayText = ucfirst(str_replace('_', ' ', $empType));
                                                }
                                            @endphp
                                            <span class="badge bg-{{ $badgeColor }} @if(in_array($badgeColor, ['light', 'secondary'])) text-dark @endif" title="Type: {{ $empType ?? 'null' }}, Color: {{ $badgeColor }}">
                                                {{ $displayText }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                // Debug: Check what employment_status value we have
                                                $empStatus = $employee->hrDetail?->employment_status ?? 'active';
                                                $statusBadgeColor = 'light';
                                                $statusDisplay = 'Not Set';
                                                
                                                // DEBUG: Log the value
                                                // {{-- DEBUG: employment_status = {{ $empStatus }} --}}
                                                
                                                // Simple color mapping
                                                $statusBadgeColor = match($empStatus) {
                                                    'active' => 'success',
                                                    'on_leave' => 'warning',
                                                    'suspended' => 'danger',
                                                    'terminated' => 'danger',
                                                    'full_time' => 'success',
                                                    'part_time' => 'info',
                                                    'contract' => 'warning',
                                                    'temporary' => 'secondary',
                                                    'intern' => 'light',
                                                    default => 'light',
                                                };
                                                
                                                $statusDisplay = match($empStatus) {
                                                    'active' => 'Active',
                                                    'on_leave' => 'On Leave',
                                                    'suspended' => 'Suspended',
                                                    'terminated' => 'Terminated',
                                                    'full_time' => 'Full Time',
                                                    'part_time' => 'Part Time',
                                                    'contract' => 'Contract',
                                                    'temporary' => 'Temporary',
                                                    'intern' => 'Intern',
                                                    default => ucfirst(str_replace('_', ' ', $empStatus)),
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $statusBadgeColor }} @if(in_array($statusBadgeColor, ['light', 'secondary'])) text-dark @endif" title="Status: {{ $empStatus }}, Color: {{ $statusBadgeColor }}">
                                                {{ $statusDisplay }}
                                            </span>
                                        </td>
                                        <td>{{ $employee->hrDetail && $employee->hrDetail->hire_date ? \Carbon\Carbon::parse($employee->hrDetail->hire_date)->format('M d, Y') : 'Not Set' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('hr-payroll.employees.show', $employee->id) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('hr-payroll.employees.edit', $employee->id) }}" 
                                                   class="btn btn-sm btn-warning" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" 
                                                        class="btn btn-sm btn-danger" 
                                                        title="Delete"
                                                        onclick="confirmDelete({{ $employee->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="fas fa-users fa-2x mb-3"></i>
                                            <p>No employees found. Add your first employee to get started.</p>
                                            <a href="{{ route('hr-payroll.employees.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Add Employee
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($employees->hasPages())
                        <div class="mt-3">
                            {{ $employees->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#employeesTable').DataTable({
            pageLength: 25,
            order: [[1, 'asc']]
        });
    });

    function confirmDelete(employeeId) {
        if (confirm('Are you sure you want to delete this employee? This action cannot be undone.')) {
            // In a real implementation, this would submit a delete form
            alert('Delete functionality would be implemented here for employee ID: ' + employeeId);
        }
    }
</script>
@endpush
@endsection