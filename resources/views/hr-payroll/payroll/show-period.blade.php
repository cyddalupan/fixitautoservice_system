@extends('layouts.app')

@section('title', 'Payroll Period Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">Payroll Period Details</h1>
                    <p class="text-muted">View details for payroll period: {{ $period->period_name }}</p>
                </div>
                <div>
                    <a href="{{ route('hr-payroll.payroll.periods') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Periods
                    </a>
                    <a href="{{ route('hr-payroll.payroll.periods.edit', $period->id) }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Period
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Period Summary -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Period Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-calendar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Period Name</span>
                                    <span class="info-box-number">{{ $period->period_name }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-calendar-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Date Range</span>
                                    <span class="info-box-number">{{ $period->start_date->format('M d, Y') }} - {{ $period->end_date->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                @php
                                    $statusColors = [
                                        'draft' => 'secondary',
                                        'open' => 'info',
                                        'processing' => 'warning',
                                        'completed' => 'success',
                                        'locked' => 'danger',
                                        'paid' => 'primary'
                                    ];
                                    $statusColor = $statusColors[$period->status] ?? 'secondary';
                                @endphp
                                <span class="info-box-icon bg-{{ $statusColor }}"><i class="fas fa-tag"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Status</span>
                                    <span class="info-box-number">
                                        <span class="badge bg-{{ $statusColor }}">{{ ucfirst($period->status) }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Employees</span>
                                    <span class="info-box-number">{{ $stats['employee_count'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-money-bill-wave"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Gross Pay</span>
                                    <span class="info-box-number">₱{{ number_format($stats['total_gross'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-danger"><i class="fas fa-minus-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Deductions</span>
                                    <span class="info-box-number">₱{{ number_format($stats['total_deductions'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-hand-holding-usd"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Net Pay</span>
                                    <span class="info-box-number">₱{{ number_format($stats['total_net'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($period->processed_by)
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="alert alert-info">
                                <i class="fas fa-user-check me-2"></i>
                                Processed by: {{ $period->processedBy->name ?? 'Unknown' }} on {{ $period->processed_at->format('M d, Y h:i A') }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Payroll Records -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Payroll Records</h5>
                </div>
                <div class="card-body">
                    @if($payrollRecords->isEmpty())
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            No payroll records found for this period.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Employee</th>
                                        <th>Employee ID</th>
                                        <th>Regular Hours</th>
                                        <th>Overtime Hours</th>
                                        <th>Hourly Rate</th>
                                        <th>Regular Pay</th>
                                        <th>Overtime Pay</th>
                                        <th>Gross Pay</th>
                                        <th>Deductions</th>
                                        <th>Net Pay</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payrollRecords as $record)
                                    <tr>
                                        <td>
                                            <a href="{{ route('hr-payroll.employees.show', $record->employee_id) }}">
                                                {{ $record->employee->name ?? 'Unknown' }}
                                            </a>
                                        </td>
                                        <td>{{ $record->employee->employee_id ?? 'N/A' }}</td>
                                        <td>{{ $record->regular_hours }}</td>
                                        <td>{{ $record->overtime_hours }}</td>
                                        <td>₱{{ number_format($record->hourly_rate, 2) }}</td>
                                        <td>₱{{ number_format($record->regular_pay, 2) }}</td>
                                        <td>₱{{ number_format($record->overtime_pay, 2) }}</td>
                                        <td>₱{{ number_format($record->total_gross, 2) }}</td>
                                        <td>₱{{ number_format($record->total_deductions, 2) }}</td>
                                        <td>₱{{ number_format($record->net_pay, 2) }}</td>
                                        <td>
                                            @php
                                                $recordStatusColors = [
                                                    'pending' => 'warning',
                                                    'calculated' => 'info',
                                                    'approved' => 'success',
                                                    'paid' => 'primary',
                                                    'cancelled' => 'danger'
                                                ];
                                                $recordStatusColor = $recordStatusColors[$record->status] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $recordStatusColor }}">{{ ucfirst($record->status) }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-active">
                                        <td colspan="5" class="text-right"><strong>Totals:</strong></td>
                                        <td><strong>₱{{ number_format($payrollRecords->sum('regular_pay'), 2) }}</strong></td>
                                        <td><strong>₱{{ number_format($payrollRecords->sum('overtime_pay'), 2) }}</strong></td>
                                        <td><strong>₱{{ number_format($payrollRecords->sum('total_gross'), 2) }}</strong></td>
                                        <td><strong>₱{{ number_format($payrollRecords->sum('total_deductions'), 2) }}</strong></td>
                                        <td><strong>₱{{ number_format($payrollRecords->sum('net_pay'), 2) }}</strong></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-3">
                            {{ $payrollRecords->links() }}
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <div>
                            @if($period->status === 'draft' || $period->status === 'open')
                                <form action="{{ route('hr-payroll.payroll.periods.process', $period->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to process payroll for this period?')">
                                        <i class="fas fa-calculator me-2"></i> Process Payroll
                                    </button>
                                </form>
                            @endif
                        </div>
                        <div>
                            @if($period->status === 'completed')
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
                                    <i class="fas fa-download me-2"></i> Export Payroll
                                </button>
                            @endif
                            
                            @if($period->status !== 'locked' && $period->status !== 'paid')
                                <form action="{{ route('hr-payroll.payroll.periods.destroy', $period->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this payroll period? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-trash me-2"></i> Delete Period
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">Export Payroll Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Select export format for payroll period: <strong>{{ $period->period_name }}</strong></p>
                <div class="list-group">
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-excel me-2 text-success"></i> Export to Excel (XLSX)
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-pdf me-2 text-danger"></i> Export to PDF
                    </a>
                    <a href="#" class="list-group-item list-group-item-action">
                        <i class="fas fa-file-csv me-2 text-primary"></i> Export to CSV
                    </a>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- CSS for info boxes -->
<style>
.info-box {
    display: flex;
    align-items: center;
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    margin-bottom: 1rem;
}
.info-box-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    border-radius: 0.375rem;
    margin-right: 1rem;
    color: white;
    font-size: 1.5rem;
}
.info-box-content {
    flex: 1;
}
.info-box-text {
    display: block;
    font-size: 0.875rem;
    color: #6c757d;
}
.info-box-number {
    display: block;
    font-size: 1.5rem;
    font-weight: 600;
    color: #343a40;
}
</style>
@endsection