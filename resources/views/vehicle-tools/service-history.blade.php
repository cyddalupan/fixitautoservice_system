@extends('layouts.app')

@section('title', 'Vehicle Service History')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Vehicle Service History</h1>
            <p class="text-muted">View and manage service records for vehicles</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('vehicle-tools.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Vehicle Selector -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-car"></i> Select Vehicle
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('vehicle-tools.service-history') }}" class="row">
                        <div class="col-md-8">
                            <select name="vehicleId" id="vehicleSelect" class="form-control" onchange="this.form.submit()">
                                <option value="">Select a vehicle...</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}" 
                                            {{ $vehicle && $vehicle->id == $v->id ? 'selected' : '' }}
                                            data-vin="{{ $v->vin }}"
                                            data-customer="{{ $v->customer ? $v->customer->full_name : 'No Customer' }}">
                                        {{ $v->year }} {{ $v->make }} {{ $v->model }} 
                                        @if($v->trim) - {{ $v->trim }} @endif
                                        @if($v->vin) ({{ $v->vin }}) @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control" 
                                       placeholder="Search VIN or customer..." 
                                       id="vehicleSearch">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($vehicle)
        <!-- Vehicle Information -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="mb-1">
                                    {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}
                                    @if($vehicle->trim) <small class="text-muted">{{ $vehicle->trim }}</small> @endif
                                </h4>
                                <p class="text-muted mb-2">
                                    <i class="fas fa-barcode"></i> VIN: <code>{{ $vehicle->vin }}</code> |
                                    <i class="fas fa-user"></i> Customer: 
                                    @if($vehicle->customer)
                                        <a href="{{ route('customers.show', $vehicle->customer->id) }}">
                                            {{ $vehicle->customer->full_name }}
                                        </a>
                                    @else
                                        <span class="text-muted">No customer assigned</span>
                                    @endif
                                </p>
                                
                                <!-- Vehicle Status Badges -->
                                <div class="mb-3">
                                    @if($vehicle->vin)
                                        @php
                                            $vinStatus = $vehicle->vin_decoding_status_with_color;
                                        @endphp
                                        <span class="badge badge-{{ $vinStatus['color'] }} mr-2">
                                            <i class="fas fa-barcode"></i> VIN: {{ $vinStatus['label'] }}
                                        </span>
                                    @endif
                                    
                                    @php
                                        $recallStatus = $vehicle->recall_status_with_color;
                                    @endphp
                                    <span class="badge badge-{{ $recallStatus['color'] }} mr-2">
                                        <i class="fas fa-exclamation-triangle"></i> Recalls: {{ $recallStatus['label'] }}
                                    </span>
                                    
                                    @if($vehicle->has_warranty)
                                        <span class="badge badge-success mr-2">
                                            <i class="fas fa-shield-alt"></i> Warranty Active
                                        </span>
                                    @endif
                                    
                                    @if($vehicle->is_active)
                                        <span class="badge badge-primary">Active</span>
                                    @else
                                        <span class="badge badge-secondary">Inactive</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="btn-group">
                                    <a href="{{ route('vehicle-tools.check-recalls', $vehicle->id) }}" 
                                       class="btn btn-warning">
                                        <i class="fas fa-exclamation-triangle"></i> Check Recalls
                                    </a>
                                    <a href="{{ route('vehicle-tools.export-vehicle-data', $vehicle->id) }}" 
                                       class="btn btn-success">
                                        <i class="fas fa-download"></i> Export Data
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Vehicle Details -->
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <div class="card border-left-primary h-100">
                                    <div class="card-body">
                                        <h6 class="text-primary">Odometer</h6>
                                        <h4 class="mb-0">{{ number_format($vehicle->odometer) }} mi</h4>
                                        <small class="text-muted">Current mileage</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-left-info h-100">
                                    <div class="card-body">
                                        <h6 class="text-info">Service Count</h6>
                                        <h4 class="mb-0">{{ $vehicle->total_service_count }}</h4>
                                        <small class="text-muted">Total services</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-left-success h-100">
                                    <div class="card-body">
                                        <h6 class="text-success">Avg. Service Cost</h6>
                                        <h4 class="mb-0">${{ number_format($vehicle->average_service_cost, 2) }}</h4>
                                        <small class="text-muted">Per service</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-left-warning h-100">
                                    <div class="card-body">
                                        <h6 class="text-warning">Next Service</h6>
                                        <h4 class="mb-0">
                                            @if($vehicle->next_service_date)
                                                {{ $vehicle->next_service_date->format('M d, Y') }}
                                            @else
                                                Not Scheduled
                                            @endif
                                        </h4>
                                        <small class="text-muted">
                                            @if($vehicle->calculateNextServiceOdometer())
                                                at {{ number_format($vehicle->calculateNextServiceOdometer()) }} mi
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Service Records -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-history"></i> Service Records
                        </h6>
                        <div>
                            <span class="badge badge-primary">
                                {{ $serviceRecords->total() }} Total Records
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($serviceRecords->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Service Type</th>
                                            <th>Odometer</th>
                                            <th>Technician</th>
                                            <th>Cost</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($serviceRecords as $record)
                                        <tr>
                                            <td>
                                                {{ $record->service_date->format('M d, Y') }}
                                                <br>
                                                <small class="text-muted">{{ $record->service_date->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                <strong>{{ $record->service_type }}</strong>
                                                @if($record->description)
                                                    <br>
                                                    <small class="text-muted">{{ Str::limit($record->description, 50) }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ number_format($record->odometer_at_service) }} mi
                                                @if($record->next_service_odometer)
                                                    <br>
                                                    <small class="text-muted">Next: {{ number_format($record->next_service_odometer) }} mi</small>
                                                @endif
                                            </td>
                                            <td>
                                                @if($record->technician)
                                                    {{ $record->technician->name }}
                                                @else
                                                    <span class="text-muted">Not assigned</span>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>${{ number_format($record->total_cost, 2) }}</strong>
                                                <br>
                                                <small class="text-muted">
                                                    Parts: ${{ number_format($record->parts_cost, 2) }} |
                                                    Labor: ${{ number_format($record->labor_cost, 2) }}
                                                </small>
                                            </td>
                                            <td>
                                                @if($record->status === 'completed')
                                                    <span class="badge badge-success">Completed</span>
                                                @elseif($record->status === 'in_progress')
                                                    <span class="badge badge-warning">In Progress</span>
                                                @elseif($record->status === 'scheduled')
                                                    <span class="badge badge-info">Scheduled</span>
                                                @else
                                                    <span class="badge badge-secondary">{{ ucfirst($record->status) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('service-records.show', $record->id) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="d-flex justify-content-center">
                                {{ $serviceRecords->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Service Records Found</h5>
                                <p class="text-muted">This vehicle has no service history yet.</p>
                                <a href="{{ route('service-records.create', ['vehicle_id' => $vehicle->id]) }}" 
                                   class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add First Service Record
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Service Statistics -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-info">
                            <i class="fas fa-chart-line"></i> Service Cost Trend
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($serviceRecords->count() > 1)
                            <canvas id="costChart" height="200"></canvas>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-chart-line fa-2x text-muted mb-3"></i>
                                <p class="text-muted">Not enough data for chart</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-success">
                            <i class="fas fa-chart-pie"></i> Service Type Distribution
                        </h6>
                    </div>
                    <div class="card-body">
                        @if($serviceRecords->count() > 0)
                            <canvas id="typeChart" height="200"></canvas>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-chart-pie fa-2x text-muted mb-3"></i>
                                <p class="text-muted">Not enough data for chart</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- VIN Decoding Information (if available) -->
        @if($vehicle->vinCache)
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-secondary">
                                <i class="fas fa-car"></i> Vehicle Specifications (from VIN Decoding)
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>Basic Information</h6>
                                    <dl class="row">
                                        <dt class="col-sm-4">Engine</dt>
                                        <dd class="col-sm-8">{{ $vehicle->vinCache->engine ?? 'N/A' }}</dd>
                                        
                                        <dt class="col-sm-4">Transmission</dt>
                                        <dd class="col-sm-8">{{ $vehicle->vinCache->transmission ?? 'N/A' }}</dd>
                                        
                                        <dt class="col-sm-4">Drive Type</dt>
                                        <dd class="col-sm-8">{{ $vehicle->vinCache->drive_type ?? 'N/A' }}</dd>
                                        
                                        <dt class="col-sm-4">Fuel Type</dt>
                                        <dd class="col-sm-8">{{ $vehicle->vinCache->fuel_type ?? 'N/A' }}</dd>
                                    </dl>
                                </div>
                                <div class="col-md-6">
                                    <h6>Dimensions</h6>
                                    <dl class="row">
                                        <dt class="col-sm-4">Body Style</dt>
                                        <dd class="col-sm-8">{{ $vehicle->vinCache->body_style ?? 'N/A' }}</dd>
                                        
                                        <dt class="col-sm-4">Doors</dt>
                                        <dd class="col-sm-8">{{ $vehicle->vinCache->doors ?? 'N/A' }}</dd>
                                        
                                        <dt class="col-sm-4">Passengers</dt>
                                        <dd class="col-sm-8">{{ $vehicle->vinCache->passenger_capacity ?? 'N/A' }}</dd>
                                        
                                        <dt class="col-sm-4">Weight</dt>
                                        <dd class="col-sm-8">{{ $vehicle->vinCache->gross_vehicle_weight ?? 'N/A' }}</dd>
                                    </dl>
                                </div>
                            </div>
                            
                            <!-- Maintenance Schedule -->
                            @if($vehicle->vinCache->maintenance_schedule)
                                <div class="mt-4">
                                    <h6>Recommended Maintenance Schedule</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Service</th>
                                                    <th>Interval (miles)</th>
                                                    <th>Interval (months)</th>
                                                    <th>Estimated Cost</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($vehicle->vinCache->maintenance_schedule as $service)
                                                    <tr>
                                                        <td>{{ $service['service'] ?? 'N/A' }}</td>
                                                        <td>{{ $service['mile_interval'] ?? 'N/A' }}</td>
                                                        <td>{{ $service['month_interval'] ?? 'N/A' }}</td>
                                                        <td>${{ $service['estimated_cost'] ?? 'N/A' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @else
        <!-- No Vehicle Selected -->
        <div class="row">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-car fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Select a Vehicle</h5>
                        <p class="text-muted">Choose a vehicle from the dropdown above to view its service history.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Vehicle search
    document.getElementById('vehicleSearch').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const options = document.getElementById('vehicleSelect').options;
        
        for (let i = 0; i < options.length; i++) {
            const option = options[i];
            const text = option.text.toLowerCase();
            const vin = option.dataset.vin ? option.dataset.vin.toLowerCase() : '';
            const customer = option.dataset.customer ? option.dataset.customer.toLowerCase() : '';
            
            if (searchTerm === '' || text.includes(searchTerm) || vin.includes(searchTerm) || customer.includes(searchTerm)) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        }
    });
    
    document.getElementById('clearSearch').addEventListener('click', function() {
        document.getElementById('vehicleSearch').value = '';
        const options = document.getElementById('vehicleSelect').options;
        for (let i = 0; i < options.length; i++) {
            options[i].style.display = '';
        }
    });
    
    // Charts
    @if($vehicle && $serviceRecords->count() > 1)
        // Cost Trend Chart
        const costCtx = document.getElementById('costChart').getContext('2d');
        const costChart = new Chart(costCtx, {
            type: 'line',
            data: {
                labels: [
                    @foreach($serviceRecords as $record)
                        '{{ $record->service_date->format("M y") }}',
                    @endforeach
                ].reverse(),
                datasets: [{
                    label: 'Service Cost ($)',
                    data: [
                        @foreach($serviceRecords as $record)
                            {{ $record->total_cost }},
                        @endforeach
                    ].reverse(),
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
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
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });
    @endif
    
    @if($vehicle && $serviceRecords->count() > 0)
        // Service Type Distribution Chart
        const typeCtx = document.getElementById('typeChart').getContext('2d');
        
        // Group service records by type
        const serviceTypes = {};
        @foreach($serviceRecords as $record)
            const type = '{{ $record->service_type }}';
            if (serviceTypes[type]) {
                serviceTypes[type]++;
            } else {
                serviceTypes[type] = 1;
            }
        @endforeach
        
        const typeLabels = Object.keys(serviceTypes);
        const typeData = Object.values(serviceTypes);
        
        // Generate colors
        const backgroundColors = [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
            '#858796', '#6f42c1', '#20c9a6', '#fd7e14', '#e83e8c'
        ];
        
        const typeChart = new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: typeLabels,
                datasets: [{
                    data: typeData,
                    backgroundColor: backgroundColors.slice(0, typeLabels.length),
                    hoverBackgroundColor: backgroundColors.slice(0, typeLabels.length),
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                },
                cutout: '70%',
            }
        });
    @endif
    
    // Auto-select vehicle from URL parameter
    @if(request('vehicleId'))
        document.getElementById('vehicleSelect').value = '{{ request('vehicleId') }}';
    @endif
</script>
@endsection