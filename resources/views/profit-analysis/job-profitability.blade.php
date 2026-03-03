@extends('layouts.app')

@section('title', 'Job Profitability Reports')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-alt"></i> Job Profitability Reports
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#filterModal">
                            <i class="fas fa-filter"></i> Filter Reports
                        </button>
                        <a href="{{ route('profit-analysis.export', request()->all()) }}" class="btn btn-sm btn-secondary">
                            <i class="fas fa-download"></i> Export
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <i class="icon fas fa-check"></i> {{ session('success') }}
                    </div>
                    @endif

                    <!-- Summary Stats -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-info"><i class="fas fa-dollar-sign"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Revenue</span>
                                    <span class="info-box-number">${{ number_format($summary['total_revenue'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-success"><i class="fas fa-chart-pie"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Profit</span>
                                    <span class="info-box-number">${{ number_format($summary['total_profit'], 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-warning"><i class="fas fa-percentage"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Avg. Margin</span>
                                    <span class="info-box-number">{{ number_format($summary['avg_margin'], 1) }}%</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box">
                                <span class="info-box-icon bg-primary"><i class="fas fa-clipboard-list"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Jobs Analyzed</span>
                                    <span class="info-box-number">{{ $summary['job_count'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Summary -->
                    @if($hasFilters)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Showing results for:
                        @if($filters['start_date'])
                        <strong>From {{ $filters['start_date'] }}</strong>
                        @endif
                        @if($filters['end_date'])
                        <strong>To {{ $filters['end_date'] }}</strong>
                        @endif
                        @if($filters['technician_id'])
                        <strong>Technician: {{ $technicianNames[$filters['technician_id']] ?? 'N/A' }}</strong>
                        @endif
                        @if($filters['service_type'])
                        <strong>Service: {{ $filters['service_type'] }}</strong>
                        @endif
                        @if($filters['min_profit_margin'])
                        <strong>Min Margin: {{ $filters['min_profit_margin'] }}%</strong>
                        @endif
                        <a href="{{ route('profit-analysis.job-profitability') }}" class="float-right">
                            <i class="fas fa-times"></i> Clear Filters
                        </a>
                    </div>
                    @endif

                    <!-- Job Profitability Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Work Order</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>Service</th>
                                    <th>Technician</th>
                                    <th>Date</th>
                                    <th>Revenue</th>
                                    <th>Cost</th>
                                    <th>Profit</th>
                                    <th>Margin</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($analyses as $analysis)
                                <tr>
                                    <td>
                                        @if($analysis->workOrder)
                                        <a href="{{ route('work-orders.show', $analysis->workOrder) }}">
                                            {{ $analysis->workOrder->work_order_number }}
                                        </a>
                                        @else
                                        <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($analysis->workOrder && $analysis->workOrder->customer)
                                        {{ $analysis->workOrder->customer->name }}
                                        @else
                                        <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($analysis->workOrder && $analysis->workOrder->vehicle)
                                        {{ $analysis->workOrder->vehicle->make }} {{ $analysis->workOrder->vehicle->model }}
                                        @else
                                        <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $analysis->workOrder->service_type ?? 'N/A' }}</td>
                                    <td>
                                        @if($analysis->workOrder && $analysis->workOrder->technician)
                                        {{ $analysis->workOrder->technician->name }}
                                        @else
                                        <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>{{ $analysis->analysis_date->format('Y-m-d') }}</td>
                                    <td>${{ number_format($analysis->total_revenue, 2) }}</td>
                                    <td>${{ number_format($analysis->total_cost, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $analysis->net_profit >= 0 ? 'success' : 'danger' }}">
                                            ${{ number_format($analysis->net_profit, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="progress progress-xs">
                                            <div class="progress-bar bg-{{ $analysis->net_profit_margin >= 20 ? 'success' : ($analysis->net_profit_margin >= 10 ? 'warning' : 'danger') }}" 
                                                 style="width: {{ min($analysis->net_profit_margin, 100) }}%">
                                            </div>
                                        </div>
                                        <small>{{ number_format($analysis->net_profit_margin, 1) }}%</small>
                                    </td>
                                    <td>
                                        @if($analysis->is_finalized)
                                        <span class="badge bg-success">Finalized</span>
                                        @else
                                        <span class="badge bg-warning">Draft</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('profit-analysis.show', $analysis) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(!$analysis->is_finalized)
                                        <form action="{{ route('profit-analysis.finalize', $analysis) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Finalize this analysis?')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="12" class="text-center">No job profitability data found for the selected filters.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($analyses->hasPages())
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="float-right">
                                {{ $analyses->links() }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Profit Breakdown Chart -->
                    @if($analyses->count() > 0)
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Profit Breakdown</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <canvas id="profitByServiceChart" height="250"></canvas>
                                        </div>
                                        <div class="col-md-6">
                                            <canvas id="profitByTechnicianChart" height="250"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted">
                                Showing {{ $analyses->count() }} of {{ $summary['job_count'] }} jobs
                                @if($analyses->count() < $summary['job_count'])
                                (filtered)
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6 text-right">
                            <small class="text-muted">
                                Last updated: {{ now()->format('Y-m-d H:i') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" role="dialog" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="filterModalLabel">Filter Job Profitability Reports</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('profit-analysis.job-profitability') }}" method="GET">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $filters['start_date'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="technician_id">Technician</label>
                                <select class="form-control" id="technician_id" name="technician_id">
                                    <option value="">All Technicians</option>
                                    @foreach($technicians as $technician)
                                    <option value="{{ $technician->id }}" {{ ($filters['technician_id'] ?? '') == $technician->id ? 'selected' : '' }}>
                                        {{ $technician->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="service_type">Service Type</label>
                                <select class="form-control" id="service_type" name="service_type">
                                    <option value="">All Services</option>
                                    <option value="Oil Change" {{ ($filters['service_type'] ?? '') == 'Oil Change' ? 'selected' : '' }}>Oil Change</option>
                                    <option value="Brake Service" {{ ($filters['service_type'] ?? '') == 'Brake Service' ? 'selected' : '' }}>Brake Service</option>
                                    <option value="Tire Rotation" {{ ($filters['service_type'] ?? '') == 'Tire Rotation' ? 'selected' : '' }}>Tire Rotation</option>
                                    <option value="Diagnostics" {{ ($filters['service_type'] ?? '') == 'Diagnostics' ? 'selected' : '' }}>Diagnostics</option>
                                    <option value="Electrical" {{ ($filters['service_type'] ?? '') == 'Electrical' ? 'selected' : '' }}>Electrical</option>
                                    <option value="Engine Repair" {{ ($filters['service_type'] ?? '') == 'Engine Repair' ? 'selected' : '' }}>Engine Repair</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="min_profit_margin">Minimum Profit Margin (%)</label>
                                <input type="number" class="form-control" id="min_profit_margin" name="min_profit_margin" 
                                       value="{{ $filters['min_profit_margin'] ?? '' }}" min="0" max="100" step="0.1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="max_profit_margin">Maximum Profit Margin (%)</label>
                                <input type="number" class="form-control" id="max_profit_margin" name="max_profit_margin" 
                                       value="{{ $filters['max_profit_margin'] ?? '' }}" min="0" max="100" step="0.1">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="show_only_finalized" name="show_only_finalized" value="1" {{ ($filters['show_only_finalized'] ?? false) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="show_only_finalized">Show Only Finalized Analyses</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="show_only_profitable" name="show_only_profitable" value="1" {{ ($filters['show_only_profitable'] ?? false) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="show_only_profitable">Show Only Profitable Jobs</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="sort_by">Sort By</label>
                                <select class="form-control" id="sort_by" name="sort_by">
                                    <option value="date_desc" {{ ($filters['sort_by'] ?? 'date_desc') == 'date_desc' ? 'selected' : '' }}>Date (Newest First)</option>
                                    <option value="date_asc" {{ ($filters['sort_by'] ?? '') == 'date_asc' ? 'selected' : '' }}>Date (Oldest First)</option>
                                    <option value="profit_desc" {{ ($filters['sort_by'] ?? '') == 'profit_desc' ? 'selected' : '' }}>Profit (Highest First)</option>
                                    <option value="profit_asc" {{ ($filters['sort_by'] ?? '') == 'profit_asc' ? 'selected' : '' }}>Profit (Lowest First)</option>
                                    <option value="margin_desc" {{ ($filters['sort_by'] ?? '') == 'margin_desc' ? 'selected' : '' }}>Margin % (Highest First)</option>
                                    <option value="margin_asc" {{ ($filters['sort_by'] ?? '') == 'margin_asc' ? 'selected' : '' }}>Margin % (Lowest First)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .progress-xs {
        height: 10px;
        margin-top: 5px;
    }
    .table th {
        background-color: #f8f9fa;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Initialize datepickers
    $('input[type="date"]').each(function() {
        if (!$(this).val()) {
            // Set default dates (last 30 days)
            if ($(this).attr('id') === 'start_date') {
                var startDate = new Date();
                startDate.setDate(startDate.getDate() - 30);
                $(this).val(startDate.toISOString().split('T')[0]);
            }
            if ($(this).attr('id') === 'end_date') {
                $(this).val(new Date().toISOString().split('T')[0]);
            }
        }
    });

    // Profit by Service Chart
    @if(isset($profitByService) && count($profitByService) > 0)
    var serviceCtx = document.getElementById('profitByServiceChart').getContext('2d');
    var serviceLabels = {!! json_encode(array_keys($profitByService)) !!};
    var serviceData = {!! json_encode(array_values($profitByService)) !!};
    
    var serviceColors = [
        '#3498db', '#2ecc71', '#e74c3c', '#f39c12', 
        '#9b59b6', '#1abc9c', '#d35400', '#34495e'
    ];
    
    new Chart(serviceCtx, {
        type: 'pie',
        data: {
            labels: serviceLabels,
            datasets: [{
                data: serviceData,
                backgroundColor: serviceColors.slice(0, serviceLabels.length),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right',
                },
                title: {
                    display: true,
                    text: 'Profit by Service Type'
                }
            }
        }
    });
    @endif

    // Profit by Technician Chart
    @if(isset($profitByTechnician) && count($profitByTechnician) > 0)
    var techCtx = document.getElementById('profitByTechnicianChart').getContext('2d');
    var techLabels = {!! json_encode(array_keys($profitByTechnician)) !!};
    var techData = {!! json_encode(array_values($profitByTechnician)) !!};
    
    new Chart(techCtx, {
        type: 'bar',
        data: {
            labels: techLabels,
            datasets: [{
                label: 'Total Profit ($)',
                data: techData,
                backgroundColor: '#2ecc71',
                borderColor: '#27ae60',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Profit by Technician'
                }
            }
        }
    });
    @endif

    // Auto-submit form when certain filters change
    $('#technician_id, #service_type, #sort_by').change(function() {
        if ($(this).val()) {
            $(this).closest('form').submit();
        }
    });
});
</script>
@endpush
@endsection