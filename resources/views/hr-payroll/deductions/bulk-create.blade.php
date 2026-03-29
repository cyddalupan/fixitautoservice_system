@extends('layouts.app')

@section('title', 'Bulk Create Deductions')

@section('content')
<div class="container-fluid">
    <div class="page-header">
        <h1><i class="fas fa-users me-2"></i>Bulk Create Deductions</h1>
        <p class="lead">Create deductions for multiple employees at once</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Bulk Deduction Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('hr-payroll.deductions.bulk-store') }}" method="POST" id="bulkDeductionForm">
                        @csrf
                        
                        <!-- Deduction Type -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="deduction_type" class="form-label">Deduction Type *</label>
                                <select class="form-select" id="deduction_type" name="deduction_type" required>
                                    <option value="">Select Type</option>
                                    @foreach($deductionTypes as $type => $label)
                                        <option value="{{ $type }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="amount" class="form-label">Amount (per employee) *</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" step="0.01" min="0" class="form-control" 
                                           id="amount" name="amount" value="" required placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Date and Payroll Period -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="date" class="form-label">Date *</label>
                                <input type="date" class="form-control" 
                                       id="date" name="date" value="{{ date('Y-m-d') }}" required>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="payroll_period_id" class="form-label">Payroll Period (Optional)</label>
                                <select class="form-select" id="payroll_period_id" name="payroll_period_id">
                                    <option value="">Not assigned to payroll</option>
                                    @foreach($payrollPeriods as $period)
                                        <option value="{{ $period->id }}">
                                            {{ $period->name }} ({{ $period->start_date->format('M d') }} - {{ $period->end_date->format('M d, Y') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        
                        <!-- Notes -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <label for="notes" class="form-label">Notes (Optional) - Will apply to all deductions</label>
                                <textarea class="form-control" id="notes" name="notes" 
                                          rows="2" placeholder="Enter notes about these deductions"></textarea>
                            </div>
                        </div>
                        
                        <!-- Employee Selection -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-users me-2"></i>Select Employees</h6>
                                        <small class="text-muted">Check the employees you want to apply this deduction to</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach($employees as $employee)
                                            <div class="col-md-6 mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input employee-checkbox" 
                                                           type="checkbox" 
                                                           name="employee_ids[]" 
                                                           value="{{ $employee->id }}" 
                                                           id="employee_{{ $employee->id }}">
                                                    <label class="form-check-label" for="employee_{{ $employee->id }}">
                                                        <strong>{{ $employee->name }}</strong>
                                                        <br>
                                                        <small class="text-muted">{{ $employee->email }}</small>
                                                    </label>
                                                </div>
                                            </div>
                                            @endforeach
                                        </div>
                                        
                                        <div class="mt-3">
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="selectAll">
                                                <i class="fas fa-check-square me-1"></i>Select All
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="deselectAll">
                                                <i class="fas fa-square me-1"></i>Deselect All
                                            </button>
                                            <span class="ms-3">
                                                <span id="selectedCount">0</span> employees selected
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('hr-payroll.deductions.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i>Back to Deductions
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitBtn">
                                        <i class="fas fa-save me-1"></i>Create Deductions
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sidebar with Instructions -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Instructions</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info no-auto-dismiss">
                        <h6><i class="fas fa-lightbulb me-2"></i>How to use Bulk Create:</h6>
                        <ol class="mb-0">
                            <li>Select the <strong>deduction type</strong> (SSS, Tax, etc.)</li>
                            <li>Enter the <strong>amount</strong> (same amount will apply to all selected employees)</li>
                            <li>Choose the <strong>date</strong> for the deductions</li>
                            <li>Select a <strong>payroll period</strong> if applicable</li>
                            <li>Add any <strong>notes</strong> (optional)</li>
                            <li><strong>Check the employees</strong> you want to apply deductions to</li>
                            <li>Click <strong>Create Deductions</strong></li>
                        </ol>
                    </div>
                    
                    <div class="alert alert-warning no-auto-dismiss">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>Important Notes:</h6>
                        <ul class="mb-0">
                            <li>All deductions will be created with <strong>Pending</strong> status</li>
                            <li>You can approve/reject deductions later from the main deductions page</li>
                            <li>Same amount and details will apply to all selected employees</li>
                            <li>You can create individual deductions for different amounts</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .alert.no-auto-dismiss {
        border-left-width: 4px;
        animation: pulse-border 2s infinite;
    }
    
    @keyframes pulse-border {
        0% { border-left-color: inherit; }
        50% { border-left-color: rgba(255, 255, 255, 0.7); }
        100% { border-left-color: inherit; }
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Update selected count
        function updateSelectedCount() {
            var checkboxes = document.querySelectorAll('.employee-checkbox:checked');
            document.getElementById('selectedCount').textContent = checkboxes.length;
            
            // Disable submit button if no employees selected
            var submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = checkboxes.length === 0;
        }
        
        // Select All button
        document.getElementById('selectAll').addEventListener('click', function() {
            var checkboxes = document.querySelectorAll('.employee-checkbox');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = true;
            }
            updateSelectedCount();
        });
        
        // Deselect All button
        document.getElementById('deselectAll').addEventListener('click', function() {
            var checkboxes = document.querySelectorAll('.employee-checkbox');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = false;
            }
            updateSelectedCount();
        });
        
        // Update count when checkboxes change
        var checkboxes = document.querySelectorAll('.employee-checkbox');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].addEventListener('change', updateSelectedCount);
        }
        
        // Form validation
        document.getElementById('bulkDeductionForm').addEventListener('submit', function(e) {
            var checkboxes = document.querySelectorAll('.employee-checkbox:checked');
            if (checkboxes.length === 0) {
                e.preventDefault();
                alert('Please select at least one employee.');
                return false;
            }
            
            var amount = document.getElementById('amount').value;
            if (!amount || parseFloat(amount) <= 0) {
                e.preventDefault();
                alert('Please enter a valid amount greater than 0.');
                return false;
            }
            
            // Confirm before submitting
            if (!confirm('Create ' + checkboxes.length + ' deduction(s) with amount ₱' + parseFloat(amount).toFixed(2) + ' each?')) {
                e.preventDefault();
                return false;
            }
        });
        
        // Initialize count
        updateSelectedCount();
    });
</script>
@endpush