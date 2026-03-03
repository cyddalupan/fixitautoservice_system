@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Payroll Period</h3>
                    <div class="card-tools">
                        <a href="{{ route('hr-payroll.payroll.periods.show', $period->id) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Period Details
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('hr-payroll.payroll.periods.update', $period->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="period_name">Period Name *</label>
                                    <input type="text" class="form-control" id="period_name" name="period_name" value="{{ old('period_name', $period->period_name) }}" placeholder="e.g., February 2026 Payroll" required>
                                    @error('period_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status *</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="draft" {{ old('status', $period->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="open" {{ old('status', $period->status) == 'open' ? 'selected' : '' }}>Open</option>
                                        <option value="processing" {{ old('status', $period->status) == 'processing' ? 'selected' : '' }}>Processing</option>
                                        <option value="completed" {{ old('status', $period->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="locked" {{ old('status', $period->status) == 'locked' ? 'selected' : '' }}>Locked</option>
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
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date', $period->start_date->format('Y-m-d')) }}" required>
                                    @error('start_date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="end_date">End Date *</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ old('end_date', $period->end_date->format('Y-m-d')) }}" required>
                                    @error('end_date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="pay_date">Pay Date *</label>
                                    <input type="date" class="form-control" id="pay_date" name="pay_date" value="{{ old('pay_date', $period->pay_date->format('Y-m-d')) }}" required>
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
                                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional notes about this payroll period">{{ old('notes', $period->notes) }}</textarea>
                                    @error('notes')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Payroll Period
                                </button>
                                <a href="{{ route('hr-payroll.payroll.periods.show', $period->id) }}" class="btn btn-secondary">
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