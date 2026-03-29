@extends('layouts.app')

@section('title', 'Add New Deduction')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1><i class="fas fa-plus-circle me-2"></i>Add New Deduction</h1>
        <p class="lead">Create a new employee deduction record</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Deduction Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('hr-payroll.deductions.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="employee_id" class="form-label">Employee *</label>
                                <select class="form-select @error('employee_id') is-invalid @enderror" id="employee_id" name="employee_id" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->name }} ({{ $employee->email }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('employee_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="deduction_type" class="form-label">Deduction Type *</label>
                                <select class="form-select @error('deduction_type') is-invalid @enderror" id="deduction_type" name="deduction_type" required>
                                    <option value="">Select Type</option>
                                    @foreach($deductionTypes as $key => $label)
                                        <option value="{{ $key }}" {{ old('deduction_type') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('deduction_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">Amount *</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" step="0.01" min="0" class="form-control @error('amount') is-invalid @enderror" 
                                           id="amount" name="amount" value="{{ old('amount') }}" required placeholder="0.00">
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="date" class="form-label">Date *</label>
                                <input type="date" class="form-control @error('date') is-invalid @enderror" 
                                       id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="payroll_period_id" class="form-label">Payroll Period (Optional)</label>
                                <select class="form-select @error('payroll_period_id') is-invalid @enderror" id="payroll_period_id" name="payroll_period_id">
                                    <option value="">Not assigned to payroll</option>
                                    @foreach($payrollPeriods as $period)
                                        <option value="{{ $period->id }}" {{ old('payroll_period_id') == $period->id ? 'selected' : '' }}>
                                            {{ $period->period_name }} ({{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('payroll_period_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="notes" class="form-label">Notes (Optional)</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" 
                                          rows="1" placeholder="Enter any notes about this deduction">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Note:</strong> New deductions are created with "Pending" status. They need to be approved before being applied to payroll.
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('hr-payroll.deductions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to List
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Create Deduction
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Deduction Types Info -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Deduction Types</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <strong>SSS</strong>
                            <p class="text-muted small mb-0">Social Security System contributions</p>
                        </li>
                        <li class="mb-2">
                            <strong>Tax</strong>
                            <p class="text-muted small mb-0">Withholding tax deductions</p>
                        </li>
                        <li class="mb-2">
                            <strong>Cash Advance</strong>
                            <p class="text-muted small mb-0">Employee cash advances</p>
                        </li>
                        <li class="mb-2">
                            <strong>Late</strong>
                            <p class="text-muted small mb-0">Late arrival or absence deductions</p>
                        </li>
                        <li>
                            <strong>Other</strong>
                            <p class="text-muted small mb-0">Miscellaneous deductions</p>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Calculation Example -->
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-calculator me-2"></i>Payroll Calculation Example</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Gross Salary:</strong>
                        <div class="text-end">₱25,000.00</div>
                    </div>
                    <div class="mb-3">
                        <strong>Deductions:</strong>
                        <div class="ms-3">
                            <div>SSS: <span class="float-end">₱1,200.00</span></div>
                            <div>Tax: <span class="float-end">₱2,000.00</span></div>
                            <div>Cash Advance: <span class="float-end">₱1,500.00</span></div>
                            <div>Late: <span class="float-end">₱300.00</span></div>
                        </div>
                    </div>
                    <hr>
                    <div class="mb-0">
                        <strong>Net Salary:</strong>
                        <div class="text-end text-success"><strong>₱20,000.00</strong></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Set today's date as default if not already set
    document.addEventListener('DOMContentLoaded', function() {
        const dateField = document.getElementById('date');
        if (!dateField.value) {
            dateField.value = new Date().toISOString().split('T')[0];
        }
    });
</script>
@endsection