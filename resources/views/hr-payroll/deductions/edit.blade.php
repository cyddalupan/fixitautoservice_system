@extends('layouts.app')

@section('title', 'Edit Deduction')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1><i class="fas fa-edit me-2"></i>Edit Deduction</h1>
        <p class="lead">Update deduction details</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Edit Deduction Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('hr-payroll.deductions.update', $deduction) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="employee_id" class="form-label">Employee *</label>
                                <select class="form-select @error('employee_id') is-invalid @enderror" id="employee_id" name="employee_id" required>
                                    <option value="">Select Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ old('employee_id', $deduction->employee_id) == $employee->id ? 'selected' : '' }}>
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
                                        <option value="{{ $key }}" {{ old('deduction_type', $deduction->deduction_type) == $key ? 'selected' : '' }}>
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
                                           id="amount" name="amount" value="{{ old('amount', $deduction->amount) }}" required placeholder="0.00">
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="date" class="form-label">Date *</label>
                                <input type="date" class="form-control @error('date') is-invalid @enderror" 
                                       id="date" name="date" value="{{ old('date', $deduction->date->format('Y-m-d')) }}" required>
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
                                        <option value="{{ $period->id }}" {{ old('payroll_period_id', $deduction->payroll_period_id) == $period->id ? 'selected' : '' }}>
                                            {{ $period->period_name }} ({{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('payroll_period_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Status *</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    @foreach($statusOptions as $key => $label)
                                        <option value="{{ $key }}" {{ old('status', $deduction->status) == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" 
                                      rows="3" placeholder="Enter any notes about this deduction">{{ old('notes', $deduction->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Status Guide:</strong>
                            <ul class="mb-0 mt-2">
                                <li><strong>Pending:</strong> Awaiting approval</li>
                                <li><strong>Approved:</strong> Ready to be applied to payroll</li>
                                <li><strong>Applied:</strong> Already included in payroll calculation</li>
                                <li><strong>Rejected:</strong> Not approved for payroll</li>
                            </ul>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('hr-payroll.deductions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to List
                            </a>
                            <div class="btn-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Update Deduction
                                </button>
                                <a href="{{ route('hr-payroll.deductions.show', $deduction) }}" class="btn btn-outline-info">
                                    <i class="fas fa-eye me-1"></i> View
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Deduction Info -->
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Deduction Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Created:</strong>
                        <div class="text-muted">{{ $deduction->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                    <div class="mb-3">
                        <strong>Created By:</strong>
                        <div class="text-muted">{{ $deduction->creator->name ?? 'System' }}</div>
                    </div>
                    <div class="mb-3">
                        <strong>Last Updated:</strong>
                        <div class="text-muted">{{ $deduction->updated_at->format('M d, Y h:i A') }}</div>
                    </div>
                    <hr>
                    <div class="mb-0">
                        <strong>Current Status:</strong>
                        <div>
                            <span class="badge bg-{{ $deduction->status_color }}">
                                {{ ucfirst($deduction->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                </div>
                <div class="card-body">
                    @if($deduction->status === 'pending')
                    <div class="d-grid gap-2 mb-3">
                        <form action="{{ route('hr-payroll.deductions.approve', $deduction) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100" onclick="return confirm('Approve this deduction?')">
                                <i class="fas fa-check me-1"></i> Approve Deduction
                            </button>
                        </form>
                        <form action="{{ route('hr-payroll.deductions.reject', $deduction) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100" onclick="return confirm('Reject this deduction?')">
                                <i class="fas fa-times me-1"></i> Reject Deduction
                            </button>
                        </form>
                    </div>
                    @endif
                    
                    <div class="d-grid">
                        <form action="{{ route('hr-payroll.deductions.destroy', $deduction) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Delete this deduction permanently?')">
                                <i class="fas fa-trash me-1"></i> Delete Deduction
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection