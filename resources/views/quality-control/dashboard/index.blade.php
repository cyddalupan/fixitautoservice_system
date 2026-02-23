@extends('layouts.app')

@section('title', 'Quality Control Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="h3 mb-0">
                    <i class="fas fa-chart-line text-primary me-2"></i>
                    Quality Control Dashboard
                </h1>
                <p class="text-muted mb-0">Monitor quality metrics and compliance across the organization</p>
            </div>
            <div class="col-auto">
                <a href="{{ route('dashboard.export') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-download me-1"></i> Export Report
                </a>
                <a href="{{ route('dashboard.alerts') }}" class="btn btn-outline-danger">
                    <i class="fas fa-bell me-1"></i> View Alerts
                </a>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Quality Pass Rate</h6>
                            <h2 class="card-title mb-0">{{ number_format($metrics['quality_pass_rate'], 1) }}%</h2>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small>
                            @if($metrics['quality_pass_rate_trend'] > 0)
                                <i class="fas fa-arrow-up text-success me-1"></i>
                                <span class="text-success">+{{ number_format($metrics['quality_pass_rate_trend'], 1) }}%</span>
                            @elseif($metrics['quality_pass_rate_trend'] < 0)
                                <i class="fas fa-arrow-down text-danger me-1"></i>
                                <span class="text-danger">{{ number_format($metrics['quality_pass_rate_trend'], 1) }}%</span>
                            @else
                                <i class="fas fa-minus text-muted me-1"></i>
                                <span class="text-muted">No change</span>
                            @endif
                            from last month
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Customer Satisfaction</h6>
                            <h2 class="card-title mb-0">{{ number_format($metrics['customer_satisfaction'], 1) }}/5</h2>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-smile"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small>
                            {{ $metrics['recommendation_rate'] }}% would recommend
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Compliance Rate</h6>
                            <h2 class="card-title mb-0">{{ number_format($metrics['compliance_rate'], 1) }}%</h2>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-file-contract"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small>
                            {{ $metrics['expiring_documents'] }} documents expiring soon
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-subtitle mb-2">Pending Approvals</h6>
                            <h2 class="card-title mb-0">{{ $metrics['pending_approvals'] }}</h2>
                        </div>
                        <div class="card-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="mt-2">
                        <small>
                            {{ $metrics['avg_approval_time'] }} days avg approval time
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Critical Alerts -->
    @if($alerts->count() > 0)
        <div class="card mb-4 border-danger">
            <div class="card-header bg-danger text-white">
                <h6 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Critical Alerts ({{ $alerts->count() }})
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($alerts as $alert)
                        <div class="col-md-6 mb-3">
                            <div class="card border-danger">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-title mb-1">{{ $alert['title'] }}</h6>
                                            <p class="card-text small text-muted mb-2">{{ $alert['description'] }}</p>
                                            <small class="text-danger">
                                                <i class="fas fa-clock me-1"></i>
                                                {{ $alert['time_ago'] }}
                                            </small>
                                        </div>
                                        <div>
                                            <span class="badge bg-danger">{{ $alert['priority'] }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Charts Section -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Quality Trends (Last 30 Days)
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="height: 300px;">
                        <!-- Chart would be rendered here with Chart.js -->
                        <div class="text-center py-5">
                            <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Quality trends chart would display here</p>
                            <p class="small text-muted">(Integrated with Chart.js for real-time data visualization)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-light">
                    <h6 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Top Performing Technicians
                    </h6>
                </div>
                <div class="card-body">
                    @if($topTechnicians->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($topTechnicians as $technician)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">{{ $technician->name }}</h6>
                                            <small class="text-muted">{{ $technician->specialization ?? 'General Technician' }}</small>
                                        </div>
                                        <div class="text-end">
                                            <div class="h5 mb-0">{{ number_format($technician->avg_score, 1) }}/100</div>
                                            <small class="text-muted">{{ $technician->checks_count }} checks</small>
                                        </div>
                                    </div>
                                    <div class="progress mt-2" style="height: 8px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: {{ $technician->avg_score }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-users fa-2x text-muted mb-3"></i>
                            <p class="text-muted">No technician data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row">
        <div class="col-md-3">
            <div class="card card-link">
                <a href="{{ route('quality-checks.index') }}" class="text-decoration-none">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-clipboard-check fa-2x text-primary"></i>
                        </div>
                        <h5 class="card-title">Quality Checks</h5>
                        <p class="card-text text-muted small">Manage quality check templates</p>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-link">
                <a href="{{ route('work-order-quality.index') }}" class="text-decoration-none">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-clipboard-list fa-2x text-success"></i>
                        </div>
                        <h5 class="card-title">Work Order Quality</h5>
                        <p class="card-text text-muted small">Approve quality checks</p>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-link">
                <a href="{{ route('compliance.index') }}" class="text-decoration-none">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-file-contract fa-2x text-warning"></i>
                        </div>
                        <h5 class="card-title">Compliance</h5>
                        <p class="card-text text-muted small">Manage documents</p>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-link">
                <a href="{{ route('customer-satisfaction.index') }}" class="text-decoration-none">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-smile fa-2x text-info"></i>
                        </div>
                        <h5 class="card-title">Customer Satisfaction</h5>
                        <p class="card-text text-muted small">View survey results</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .card-link {
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid #dee2e6;
    }
    .card-link:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        border-color: #0d6efd;
    }
    .stat-card .card-icon {
        font-size: 2.5rem;
        opacity: 0.8;
    }
</style>
@endpush

@push('scripts')
<script>
    // Auto-refresh dashboard every 5 minutes
    setTimeout(function() {
        location.reload();
    }, 300000);
</script>
@endpush
@endsection