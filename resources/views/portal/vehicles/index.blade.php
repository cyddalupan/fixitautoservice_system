@extends('layouts.app')

@section('title', 'My Vehicles - Customer Portal')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-car me-2"></i>My Vehicles
            </h1>
            <p class="text-muted mb-0">Manage your vehicles and view service history</p>
        </div>
        <div>
            <a href="{{ route('portal.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    @if($vehicles->count() > 0)
        @foreach($vehicles as $vehicle)
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}
                        </h6>
                        <span class="badge bg-{{ $vehicle->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($vehicle->status) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 text-center mb-3">
                                <div class="vehicle-icon">
                                    <i class="fas fa-car fa-3x text-primary"></i>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="vehicle-details">
                                    <p class="mb-2">
                                        <i class="fas fa-id-card me-2 text-muted"></i>
                                        <strong>VIN:</strong> {{ $vehicle->vin ?? 'Not Provided' }}
                                    </p>
                                    <p class="mb-2">
                                        <i class="fas fa-tag me-2 text-muted"></i>
                                        <strong>License Plate:</strong> {{ $vehicle->license_plate ?? 'Not Provided' }}
                                    </p>
                                    <p class="mb-2">
                                        <i class="fas fa-gas-pump me-2 text-muted"></i>
                                        <strong>Fuel Type:</strong> {{ $vehicle->fuel_type ?? 'N/A' }}
                                    </p>
                                    <p class="mb-2">
                                        <i class="fas fa-cog me-2 text-muted"></i>
                                        <strong>Transmission:</strong> {{ $vehicle->transmission ?? 'N/A' }}
                                    </p>
                                    <p class="mb-2">
                                        <i class="fas fa-tachometer-alt me-2 text-muted"></i>
                                        <strong>Mileage:</strong> {{ number_format($vehicle->current_mileage ?? 0) }} miles
                                    </p>
                                    <p class="mb-0">
                                        <i class="fas fa-calendar me-2 text-muted"></i>
                                        <strong>Last Service:</strong> 
                                        @if($vehicle->last_service_date)
                                            {{ $vehicle->last_service_date->format('M j, Y') }}
                                        @else
                                            Never
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <small class="text-muted">Service Records</small>
                                <span class="badge bg-info">{{ $vehicle->serviceRecords->count() }} records</span>
                            </div>
                            
                            @if($vehicle->serviceRecords->count() > 0)
                                <div class="service-history">
                                    @foreach($vehicle->serviceRecords->take(3) as $record)
                                        <div class="service-record mb-2 p-2 border rounded">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small class="text-muted">{{ $record->service_date->format('M j, Y') }}</small>
                                                    <br>
                                                    <span class="small">{{ $record->service_type }}</span>
                                                </div>
                                                <span class="badge bg-secondary">${{ number_format($record->total_cost, 2) }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                    
                                    @if($vehicle->serviceRecords->count() > 3)
                                        <div class="text-center mt-2">
                                            <small class="text-muted">+{{ $vehicle->serviceRecords->count() - 3 }} more records</small>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="text-center py-3 border rounded">
                                    <i class="fas fa-history fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">No service records yet</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('portal.vehicles.show', $vehicle) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye me-1"></i> View Details
                            </a>
                            <a href="{{ route('portal.vehicles.service-history', $vehicle) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-history me-1"></i> Full History
                            </a>
                            <a href="{{ route('portal.appointments.create') }}?vehicle_id={{ $vehicle->id }}" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-calendar-plus me-1"></i> Schedule Service
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-car fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted mb-3">No Vehicles Found</h4>
                    <p class="text-muted mb-4">
                        You don't have any vehicles registered in our system yet.
                    </p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        To add vehicles to your account, please contact our service department during business hours.
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .vehicle-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        color: white;
    }
    
    .service-record {
        background: #f8f9fa;
        transition: background-color 0.2s;
    }
    
    .service-record:hover {
        background: #e9ecef;
    }
</style>
@endsection