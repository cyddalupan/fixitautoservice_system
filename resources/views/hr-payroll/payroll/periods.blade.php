@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Payroll Periods</h1>
                    <p class="text-muted">Manage and process payroll periods</p>
                </div>
                <div>
                    <a href="{{ route('hr-payroll.payroll.periods.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create New Period
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Year Filter -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="yearFilter">Filter by Year:</label>
                            <select id="yearFilter" class="form-control">
                                <option value="all">All Years</option>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                                        {{ $year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="statusFilter">Filter by Status:</label>
                            <select id="statusFilter" class="form-control">
                                <option value="all">All Status</option>
                                <option value="draft">Draft</option>
                                <option value="processing">Processing</option>
                                <option value="completed">Completed</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                        <div class="col-md-6 text-right pt-4">
                            <a href="{{ route('hr-payroll.payroll.process') }}" class="btn btn-success">
                                <i class="fas fa-calculator"></i> Process Payroll
                            </a>
                            <a href="{{ route('hr-payroll.reports') }}" class="btn btn-info">
                                <i class="fas fa-chart-bar"></i> Payroll Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payroll Periods Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h6 class="m-0 font-weight-bold">Payroll Periods</h6>
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
                        <table class="table table-bordered table-hover" id="payrollPeriodsTable">
                            <thead class="thead-light">
                                <tr>
                                    <th>Period</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Pay Date</th>
                                    <th>Employees</th>
                                    <th>Total Gross</th>
                                    <th>Total Net</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($periods as $period)
                                    <tr>
                                        <td>
                                            <strong>{{ $period->period_name }}</strong>
                                            <div class="text-muted small">{{ ucfirst($period->period_type) }}</div>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($period->start_date)->format('M d, Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($period->end_date)->format('M d, Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($period->pay_date)->format('M d, Y') }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-primary p-2">
                                                {{ $period->payrollRecords->count() }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>₱{{ number_format($period->payrollRecords->sum('total_gross'), 2) }}</strong>
                                        </td>
                                        <td>
                                            <strong class="text-success">₱{{ number_format($period->payrollRecords->sum('total_net'), 2) }}</strong>
                                        </td>
                                        <td>
                                            @if($period->status === 'draft')
                                                <span class="badge badge-secondary p-2">Draft</span>
                                            @elseif($period->status === 'processing')
                                                <span class="badge badge-warning p-2">Processing</span>
                                            @elseif($period->status === 'completed')
                                                <span class="badge badge-info p-2">Completed</span>
                                            @elseif($period->status === 'paid')
                                                <span class="badge badge-success p-2">Paid</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('hr-payroll.payroll.periods.show', $period->id) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                @if($period->status === 'draft')
                                                    <a href="{{ route('hr-payroll.payroll.periods.edit', $period->id) }}" 
                                                       class="btn btn-sm btn-warning" 
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" 
                                                            class="btn btn-sm btn-success" 
                                                            title="Process"
                                                            onclick="processPeriod({{ $period->id }})">
                                                        <i class="fas fa-calculator"></i>
                                                    </button>
                                                @endif
                                                
                                                @if($period->status === 'completed')
                                                    <button type="button" 
                                                            class="btn btn-sm btn-primary" 
                                                            title="Mark as Paid"
                                                            onclick="markAsPaid({{ $period->id }})">
                                                        <i class="fas fa-check-circle"></i>
                                                    </button>
                                                @endif
                                                
                                                @if(in_array($period->status, ['draft', 'processing']))
                                                    <button type="button" 
                                                            class="btn btn-sm btn-danger" 
                                                            title="Delete"
                                                            onclick="confirmDelete({{ $period->id }})">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="fas fa-calendar-alt fa-2x mb-3"></i>
                                            <p>No payroll periods found. Create your first payroll period to get started.</p>
                                            <a href="{{ route('hr-payroll.payroll.periods.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Create Payroll Period
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($periods->hasPages())
                        <div class="mt-3">
                            {{ $periods->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Periods
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $periods->total() }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
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
                                Total Paid
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₱{{ number_format($periods->where('status', 'paid')->sum(function($period) {
                                    return $period->payrollRecords->sum('total_net');
                                }), 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
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
                                Pending Processing
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $periods->where('status', 'processing')->count() }}
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
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Avg. Payroll/Period
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ₱{{ number_format($periods->where('status', 'paid')->avg(function($period) {
                                    return $period->payrollRecords->sum('total_net');
                                }) ?? 0, 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#payrollPeriodsTable').DataTable({
            pageLength: 25,
            order: [[1, 'desc']]
        });

        // Year filter
        $('#yearFilter').change(function() {
            const year = $(this).val();
            if (year === 'all') {
                window.location.href = "{{ route('hr-payroll.payroll.periods') }}";
            } else {
                window.location.href = "{{ route('hr-payroll.payroll.periods') }}?year=" + year;
            }
        });

        // Status filter
        $('#statusFilter').change(function() {
            const status = $(this).val();
            if (status !== 'all') {
                window.location.href = "{{ route('hr-payroll.payroll.periods') }}?status=" + status;
            } else {
                window.location.href = "{{ route('hr-payroll.payroll.periods') }}";
            }
        });
    });

    function processPeriod(periodId) {
        if (confirm('Are you sure you want to process this payroll period? This will calculate payroll for all employees.')) {
            // In a real implementation, this would submit a form to process the payroll
            alert('Processing payroll period ID: ' + periodId);
            // window.location.href = "/hr-payroll/payroll/" + periodId + "/process";
        }
    }

    function markAsPaid(periodId) {
        if (confirm('Mark this payroll period as paid? This will update all records and generate payment reports.')) {
            // In a real implementation, this would submit a form to mark as paid
            alert('Marking payroll period ID: ' + periodId + ' as paid');
            // window.location.href = "/hr-payroll/payroll/" + periodId + "/mark-paid";
        }
    }

    function confirmDelete(periodId) {
        if (confirm('Are you sure you want to delete this payroll period? This action cannot be undone.')) {
            // In a real implementation, this would submit a delete form
            alert('Delete functionality would be implemented here for period ID: ' + periodId);
        }
    }
</script>
@endpush
@endsection