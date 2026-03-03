@extends('layouts.app')

@section('title', 'Process Payroll')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-calculator me-2"></i>Process Payroll
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Select Payroll Period</h5>
                                </div>
                                <div class="card-body">
                                    @if($payrollPeriods->isEmpty())
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            No payroll periods available for processing. Please create a payroll period first.
                                        </div>
                                        <a href="{{ route('hr-payroll.payroll.periods.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus me-2"></i>Create Payroll Period
                                        </a>
                                    @else
                                        <form action="" method="POST" id="processPayrollForm">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="payroll_period_id" class="form-label">Select Payroll Period *</label>
                                                <select class="form-select @error('payroll_period_id') is-invalid @enderror" 
                                                        id="payroll_period_id" 
                                                        name="payroll_period_id" 
                                                        required>
                                                    <option value="">-- Select Period --</option>
                                                    @foreach($payrollPeriods as $period)
                                                        <option value="{{ $period->id }}" 
                                                                data-status="{{ $period->status }}"
                                                                data-employee-count="{{ $period->employee_count }}"
                                                                data-total-gross="{{ $period->total_gross }}"
                                                                data-total-net="{{ $period->total_net }}">
                                                            {{ $period->period_name }} 
                                                            ({{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }})
                                                            - {{ ucfirst($period->status) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('payroll_period_id')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="process_type" class="form-label">Process Type *</label>
                                                <select class="form-select @error('process_type') is-invalid @enderror" 
                                                        id="process_type" 
                                                        name="process_type" 
                                                        required>
                                                    <option value="">-- Select Process Type --</option>
                                                    <option value="calculate">Calculate Payroll Only</option>
                                                    <option value="approve">Calculate & Approve</option>
                                                    <option value="process">Full Process (Calculate, Approve & Pay)</option>
                                                </select>
                                                @error('process_type')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="mb-3">
                                                <label for="notes" class="form-label">Processing Notes (Optional)</label>
                                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                                          id="notes" 
                                                          name="notes" 
                                                          rows="3" 
                                                          placeholder="Add any notes about this payroll processing..."></textarea>
                                                @error('notes')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle me-2"></i>
                                                <strong>Processing Information:</strong>
                                                <ul class="mb-0 mt-2">
                                                    <li>Calculation will process time attendance records for the selected period</li>
                                                    <li>Approval will lock the payroll for review</li>
                                                    <li>Payment processing will generate payment records</li>
                                                    <li>This action cannot be undone once completed</li>
                                                </ul>
                                            </div>

                                            <div class="d-flex justify-content-between">
                                                <a href="{{ route('hr-payroll.payroll.periods') }}" class="btn btn-secondary">
                                                    <i class="fas fa-arrow-left me-2"></i>Back to Periods
                                                </a>
                                                <button type="submit" class="btn btn-primary" id="processBtn">
                                                    <i class="fas fa-play-circle me-2"></i>Start Processing
                                                </button>
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Period Information</h5>
                                </div>
                                <div class="card-body">
                                    <div id="periodInfo" class="text-center text-muted">
                                        <i class="fas fa-calendar-alt fa-3x mb-3"></i>
                                        <p>Select a payroll period to view details</p>
                                    </div>
                                    <div id="periodDetails" style="display: none;">
                                        <table class="table table-sm">
                                            <tr>
                                                <th>Status:</th>
                                                <td><span class="badge" id="periodStatusBadge">-</span></td>
                                            </tr>
                                            <tr>
                                                <th>Employees:</th>
                                                <td id="periodEmployeeCount">-</td>
                                            </tr>
                                            <tr>
                                                <th>Total Gross:</th>
                                                <td id="periodTotalGross">-</td>
                                            </tr>
                                            <tr>
                                                <th>Total Net:</th>
                                                <td id="periodTotalNet">-</td>
                                            </tr>
                                            <tr>
                                                <th>Pay Date:</th>
                                                <td id="periodPayDate">-</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Quick Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('hr-payroll.payroll.periods') }}" class="btn btn-outline-primary">
                                            <i class="fas fa-list me-2"></i>View All Periods
                                        </a>
                                        <a href="{{ route('hr-payroll.time-attendance') }}" class="btn btn-outline-info">
                                            <i class="fas fa-clock me-2"></i>Time & Attendance
                                        </a>
                                        <a href="{{ route('hr-payroll.reports.payroll') }}" class="btn btn-outline-success">
                                            <i class="fas fa-chart-bar me-2"></i>Payroll Reports
                                        </a>
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
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const periodSelect = document.getElementById('payroll_period_id');
    const periodInfo = document.getElementById('periodInfo');
    const periodDetails = document.getElementById('periodDetails');
    const processForm = document.getElementById('processPayrollForm');
    const processBtn = document.getElementById('processBtn');

    // Get all period options data
    const periodOptions = {};
    @foreach($payrollPeriods as $period)
        periodOptions[{{ $period->id }}] = {
            status: '{{ $period->status }}',
            employee_count: {{ $period->employee_count }},
            total_gross: {{ $period->total_gross }},
            total_net: {{ $period->total_net }},
            pay_date: '{{ $period->pay_date->format("M d, Y") }}',
            status_color: '{{ $period->status_color }}'
        };
    @endforeach

    // Update period details when selection changes
    periodSelect.addEventListener('change', function() {
        const periodId = this.value;
        
        if (periodId && periodOptions[periodId]) {
            const period = periodOptions[periodId];
            
            // Update period details
            document.getElementById('periodStatusBadge').textContent = period.status.charAt(0).toUpperCase() + period.status.slice(1);
            document.getElementById('periodStatusBadge').className = `badge bg-${period.status_color}`;
            document.getElementById('periodEmployeeCount').textContent = period.employee_count;
            document.getElementById('periodTotalGross').textContent  = '₱' + parseFloat(period.total_gross).toFixed(2);
            document.getElementById('periodTotalNet').textContent  = '₱' + parseFloat(period.total_net).toFixed(2);
            document.getElementById('periodPayDate').textContent = period.pay_date;
            
            // Show details, hide placeholder
            periodInfo.style.display = 'none';
            periodDetails.style.display = 'block';
            
            // Update button text based on status
            if (period.status === 'draft') {
                processBtn.innerHTML = '<i class="fas fa-play-circle me-2"></i>Start Processing';
            } else if (period.status === 'processing') {
                processBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Continue Processing';
            } else if (period.status === 'approved') {
                processBtn.innerHTML = '<i class="fas fa-money-bill-wave me-2"></i>Process Payments';
            } else {
                processBtn.innerHTML = '<i class="fas fa-play-circle me-2"></i>Start Processing';
            }
        } else {
            // Show placeholder, hide details
            periodInfo.style.display = 'block';
            periodDetails.style.display = 'none';
        }
    });

    // Form submission confirmation
    processForm.addEventListener('submit', function(e) {
        const periodId = periodSelect.value;
        const processType = document.getElementById('process_type').value;
        
        if (!periodId) {
            e.preventDefault();
            alert('Please select a payroll period.');
            return;
        }
        
        if (!processType) {
            e.preventDefault();
            alert('Please select a process type.');
            return;
        }
        
        const period = periodOptions[periodId];
        let message = '';
        
        switch(processType) {
            case 'calculate':
                message = `Are you sure you want to calculate payroll for "${periodSelect.options[periodSelect.selectedIndex].text}"?\n\nThis will process time attendance and calculate earnings/deductions.`;
                break;
            case 'approve':
                message = `Are you sure you want to calculate and approve payroll for "${periodSelect.options[periodSelect.selectedIndex].text}"?\n\nThis will lock the payroll for payment processing.`;
                break;
            case 'process':
                message = `Are you sure you want to fully process payroll for "${periodSelect.options[periodSelect.selectedIndex].text}"?\n\nThis will calculate, approve, and process payments. This action cannot be undone.`;
                break;
        }
        
        if (!confirm(message)) {
            e.preventDefault();
        } else {
            // Set the form action with the selected period ID
            const formAction = "{{ route('hr-payroll.payroll.periods.process', ':id') }}".replace(':id', periodId);
            processForm.action = formAction;
            
            // Add CSRF token if not already present
            if (!document.querySelector('input[name="_token"]')) {
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = "{{ csrf_token() }}";
                processForm.appendChild(csrfToken);
            }
            
            // Show loading state
            processBtn.disabled = true;
            processBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
        }
    });
});
</script>
@endpush