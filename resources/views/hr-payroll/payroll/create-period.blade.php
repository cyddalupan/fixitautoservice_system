@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Create New Payroll Period</h3>
                    <div class="card-tools">
                        <a href="{{ route('hr-payroll.payroll.periods') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Payroll Periods
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    
                    <form method="POST" action="{{ route('hr-payroll.payroll.periods.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="period_name">Period Name *</label>
                                    <input type="text" class="form-control" id="period_name" name="period_name" value="{{ old('period_name') }}" placeholder="e.g., February 2026 Payroll" required>
                                    @error('period_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status *</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="open" {{ old('status') == 'open' ? 'selected' : '' }}>Open</option>
                                        <option value="processing" {{ old('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                                        <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="locked" {{ old('status') == 'locked' ? 'selected' : '' }}>Locked</option>
                                    </select>
                                    @error('status')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="start_date">Start Date *</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                                    @error('start_date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="end_date">End Date *</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                                    @error('end_date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="pay_date">Pay Date *</label>
                                    <input type="date" class="form-control" id="pay_date" name="pay_date" value="{{ old('pay_date') }}" required>
                                    @error('pay_date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional notes about this payroll period">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Payroll Period
                                </button>
                                <a href="{{ route('hr-payroll.payroll.periods') }}" class="btn btn-secondary">
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