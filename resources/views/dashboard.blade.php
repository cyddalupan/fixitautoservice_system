@extends('layouts.app')

@section('title', 'Dashboard - Fix-It Auto Services')

@section('content')
<div class="page-header">
    <h1 class="h3 mb-0">
        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
    </h1>
    <p class="text-muted mb-0">Welcome back! Here's what's happening with your auto service business.</p>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase mb-0">Total Customers</h6>
                        <h2 class="mb-0">{{ $stats['total_customers'] }}</h2>
                        <small class="opacity-75">+12% from last month</small>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase mb-0">Revenue This Month</h6>
                        <h2 class="mb-0">${{ number_format($stats['revenue_this_month'], 2) }}</h2>
                        <small class="opacity-75">+18% from last month</small>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase mb-0">Pending Services</h6>
                        <h2 class="mb-0">{{ $stats['pending_services'] }}</h2>
                        <small class="opacity-75">+3 from yesterday</small>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-wrench"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase mb-0">Upcoming Services</h6>
                        <h2 class="mb-0">{{ $stats['upcoming_services'] }}</h2>
                        <small class="opacity-75">Next 30 days</small>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Services -->
    <div class="col-lg-8 mb-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-history me-2"></i>Recent Services
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Work Order</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Service Type</th>
                                <th>Status</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentServices as $service)
                            <tr>
                                <td>
                                    <strong>{{ $service->work_order_number }}</strong>
                                    <br>
                                    <small class="text-muted">{{ $service->service_date->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="customer-avatar me-2">
                                            {{ substr($service->customer->first_name, 0, 1) }}{{ substr($service->customer->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <strong>{{ $service->customer->full_name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $service->customer->phone }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <i class="fas fa-car vehicle-icon me-2"></i>
                                    {{ $service->vehicle->year }} {{ $service->vehicle->make }}
                                    <br>
                                    <small class="text-muted">{{ $service->vehicle->license_plate }}</small>
                                </td>
                                <td>{{ $service->service_type }}</td>
                                <td>
                                    <span class="service-status-badge status-{{ str_replace('_', '-', $service->service_status) }}">
                                        {{ ucfirst(str_replace('_', ' ', $service->service_status)) }}
                                    </span>
                                    <br>
                                    <span class="payment-status-badge payment-{{ $service->payment_status }}">
                                        {{ ucfirst($service->payment_status) }}
                                    </span>
                                </td>
                                <td>
                                    <strong>${{ number_format($service->final_amount, 2) }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        @if($service->customer_rating)
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $service->customer_rating)
                                                    <i class="fas fa-star text-warning"></i>
                                                @else
                                                    <i class="far fa-star text-muted"></i>
                                                @endif
                                            @endfor
                                        @else
                                            <span class="text-muted">No rating</span>
                                        @endif
                                    </small>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="#" class="btn btn-outline-primary">View All Services</a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Upcoming Services & Quick Stats -->
    <div class="col-lg-4 mb-4">
        <!-- Upcoming Services -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-calendar-check me-2"></i>Upcoming Services
                </h5>
            </div>
            <div class="card-body">
                @if($upcomingServices->count() > 0)
                    @foreach($upcomingServices as $vehicle)
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <div class="flex-shrink-0">
                            <i class="fas fa-car fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</h6>
                            <small class="text-muted">{{ $vehicle->customer->full_name }}</small>
                            <div class="mt-1">
                                <span class="badge bg-{{ $vehicle->service_status_color }}">
                                    {{ $vehicle->next_service_due }}
                                </span>
                                <small class="text-muted ms-2">
                                    {{ $vehicle->next_service_date->format('M d') }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No upcoming services in the next 30 days</p>
                    </div>
                @endif
                <div class="text-center">
                    <a href="#" class="btn btn-sm btn-outline-primary">Schedule Service</a>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-pie me-2"></i>Service Types
                </h5>
            </div>
            <div class="card-body">
                <canvas id="serviceTypesChart" height="200"></canvas>
                <div class="mt-3">
                    @foreach($serviceTypes as $type)
                    <div class="d-flex justify-content-between mb-2">
                        <span>{{ $type->service_type }}</span>
                        <span class="badge bg-primary">{{ $type->count }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Chart -->
<div class="row mt-4">
    <div class="col-12">
        <div class="chart-container">
            <h5 class="mb-3">
                <i class="fas fa-chart-line me-2"></i>Monthly Revenue Trend
            </h5>
            <canvas id="revenueChart" height="100"></canvas>
        </div>
    </div>
</div>

<!-- Top Customers -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-crown me-2"></i>Top Customers
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Total Services</th>
                                <th>Total Spent</th>
                                <th>Avg. Service Cost</th>
                                <th>Last Service</th>
                                <th>Loyalty Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topCustomers as $customer)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="customer-avatar me-2">
                                            {{ substr($customer->first_name, 0, 1) }}{{ substr($customer->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <strong>{{ $customer->full_name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $customer->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $customer->service_records_count ?? 0 }}</td>
                                <td>${{ number_format($customer->service_records_sum_final_amount ?? 0, 2) }}</td>
                                <td>
                                    @if($customer->service_records_count > 0)
                                        ${{ number_format(($customer->service_records_sum_final_amount ?? 0) / $customer->service_records_count, 2) }}
                                    @else
                                        $0.00
                                    @endif
                                </td>
                                <td>
                                    @if($customer->last_service_date)
                                        {{ \Carbon\Carbon::parse($customer->last_service_date)->format('M d, Y') }}
                                    @else
                                        <span class="text-muted">No services</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-warning">{{ $customer->loyalty_points }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_keys($revenueByMonth->toArray())) !!},
            datasets: [{
                label: 'Revenue',
                data: {!! json_encode(array_values($revenueByMonth->toArray())) !!},
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    
    // Service Types Chart
    const serviceTypesCtx = document.getElementById('serviceTypesChart').getContext('2d');
    const serviceTypesChart = new Chart(serviceTypesCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($serviceTypes->pluck('service_type')->toArray()) !!},
            datasets: [{
                data: {!! json_encode($serviceTypes->pluck('count')->toArray()) !!},
                backgroundColor: [
                    '#3498db',
                    '#2ecc71',
                    '#e74c3c',
                    '#f39c12',
                    '#9b59b6'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
</script>
@endpush
@endsection