@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-{{ $sectionColor }} text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-{{ $sectionIcon }} me-2"></i>{{ $pageTitle }} - Centralized Service View
                    </h4>
                    <p class="mb-0">{{ $pageDescription }}</p>
                </div>
                <div class="card-body">
                    <!-- Filter Form -->
                    <form method="GET" action="{{ request()->url() }}" class="row g-3">
                        <div class="col-md-5">
                            <label for="customer_id" class="form-label">Filter by Customer</label>
                            <select class="form-select" id="customer_id" name="customer_id" onchange="this.form.submit()">
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ $customerId == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->last_name }}, {{ $customer->first_name }} - {{ $customer->phone }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="vehicle_id" class="form-label">Filter by Vehicle</label>
                            <select class="form-select" id="vehicle_id" name="vehicle_id" onchange="this.form.submit()">
                                <option value="">Select Vehicle</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" {{ $vehicleId == $vehicle->id ? 'selected' : '' }}>
                                        {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->license_plate }}) - {{ $vehicle->customer->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <a href="{{ request()->url() }}" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-times me-1"></i> Clear Filters
                            </a>
                        </div>
                    </form>
                    
                    <!-- Summary Card -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-chart-bar me-2"></i>System Summary
                                        @if($customerId)
                                            <small class="float-end">Filtered by: Customer</small>
                                        @elseif($vehicleId)
                                            <small class="float-end">Filtered by: Vehicle</small>
                                        @else
                                            <small class="float-end">Showing: ALL Vehicles</small>
                                        @endif
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="card border-primary">
                                                <div class="card-body">
                                                    <h3 class="text-primary">{{ $scheduledCount }}</h3>
                                                    <p class="mb-0">Scheduled</p>
                                                    <small class="text-muted">Appointments</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="card border-warning">
                                                <div class="card-body">
                                                    <h3 class="text-warning">{{ $repairOrderCount }}</h3>
                                                    <p class="mb-0">Repair Orders</p>
                                                    <small class="text-muted">Active</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="card border-success">
                                                <div class="card-body">
                                                    <h3 class="text-success">{{ $estimateCount }}</h3>
                                                    <p class="mb-0">Estimates</p>
                                                    <small class="text-muted">Pending</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 col-6 mb-3">
                                            <div class="card border-danger">
                                                <div class="card-body">
                                                    <h3 class="text-danger">{{ $jobOrderCount }}</h3>
                                                    <p class="mb-0">Job Orders</p>
                                                    <small class="text-muted">In Progress</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section Content -->
                    @yield('section_content')
                    
                </div>
            </div>
        </div>
    </div>
</div>
@endsection