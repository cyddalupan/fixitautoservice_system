@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create New Employee</h3>
                    <div class="card-tools">
                        <a href="{{ route('hr-payroll.employees') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Employees
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form action="{{ route('hr-payroll.employees.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" placeholder="Enter full name" value="{{ old('name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" value="{{ old('email') }}" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                                    <small class="form-text text-muted">Minimum 8 characters</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation">Confirm Password *</label>
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm password" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="employee_id">Employee ID</label>
                                    <input type="text" class="form-control" id="employee_id" name="employee_id" placeholder="Enter employee ID" value="{{ old('employee_id') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="text" class="form-control" id="phone" name="phone" placeholder="Enter phone number" value="{{ old('phone') }}">
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="department">Department *</label>
                                    <select class="form-control" id="department" name="department" required>
                                        <option value="">Select Department</option>
                                        <option value="administration" {{ old('department') == 'administration' ? 'selected' : '' }}>Administration</option>
                                        <option value="service" {{ old('department') == 'service' ? 'selected' : '' }}>Service Department</option>
                                        <option value="parts" {{ old('department') == 'parts' ? 'selected' : '' }}>Parts Department</option>
                                        <option value="sales" {{ old('department') == 'sales' ? 'selected' : '' }}>Sales</option>
                                        <option value="accounting" {{ old('department') == 'accounting' ? 'selected' : '' }}>Accounting</option>
                                        <option value="hr" {{ old('department') == 'hr' ? 'selected' : '' }}>Human Resources</option>
                                        <option value="management" {{ old('department') == 'management' ? 'selected' : '' }}>Management</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="position">Position *</label>
                                    <input type="text" class="form-control" id="position" name="position" placeholder="Enter position" value="{{ old('position') }}" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="employment_status">Employment Status *</label>
                                    <select class="form-control" id="employment_status" name="employment_status" required>
                                        <option value="">Select Status</option>
                                        <option value="full_time" {{ old('employment_status') == 'full_time' ? 'selected' : '' }}>Full Time</option>
                                        <option value="part_time" {{ old('employment_status') == 'part_time' ? 'selected' : '' }}>Part Time</option>
                                        <option value="contract" {{ old('employment_status') == 'contract' ? 'selected' : '' }}>Contract</option>
                                        <option value="temporary" {{ old('employment_status') == 'temporary' ? 'selected' : '' }}>Temporary</option>
                                        <option value="intern" {{ old('employment_status') == 'intern' ? 'selected' : '' }}>Intern</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hire_date">Hire Date *</label>
                                    <input type="date" class="form-control" id="hire_date" name="hire_date" value="{{ old('hire_date', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="base_salary">Base Salary</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">$</span>
                                        </div>
                                        <input type="number" class="form-control" id="base_salary" name="base_salary" placeholder="0.00" step="0.01" min="0" value="{{ old('base_salary') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role">User Role *</label>
                                    <select class="form-control" id="role" name="role" required>
                                        <option value="">Select Role</option>
                                        <option value="technician" {{ old('role') == 'technician' ? 'selected' : '' }}>Technician</option>
                                        <option value="service_advisor" {{ old('role') == 'service_advisor' ? 'selected' : '' }}>Service Advisor</option>
                                        <option value="manager" {{ old('role') == 'manager' ? 'selected' : '' }}>Manager</option>
                                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                                    </select>
                                    <small class="form-text text-muted">Note: 'employee' and 'hr' roles are not available in current database schema</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea class="form-control" id="address" name="address" rows="2" placeholder="Enter address">{{ old('address') }}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional notes about the employee">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Employee
                                </button>
                                <a href="{{ route('hr-payroll.employees') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection