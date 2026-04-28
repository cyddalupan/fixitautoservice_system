@extends('layouts.app')

@section('title', 'Edit Work Order ' . $workOrder->work_order_number . ' - Fix-It Auto Services')

@section('content')
<div class="container-fluid py-3">
    @include('partials.customer-process-assets')
    @include('partials.customer-summary-card')

    <!-- Section Navigation -->
    <div class="section-nav" id="sectionNav">
        <button class="nav-pill active" data-section="details" onclick="scrollToSection('details')"><i class="fas fa-info-circle"></i> Details</button>
        <button class="nav-pill" data-section="vehicle" onclick="scrollToSection('vehicleCondition')"><i class="fas fa-car"></i> Vehicle</button>
        <button class="nav-pill" data-section="team" onclick="scrollToSection('team')"><i class="fas fa-users"></i> Team</button>
        <button class="nav-pill" data-section="concerns" onclick="scrollToSection('concerns')"><i class="fas fa-exclamation-triangle"></i> Concerns</button>
        <button class="nav-pill" data-section="services" onclick="scrollToSection('services')"><i class="fas fa-tools"></i> Services</button>
        <button class="nav-pill" data-section="estimate" onclick="scrollToSection('estimate')"><i class="fas fa-calculator"></i> Estimate</button>
        <button class="nav-pill" data-section="warranty" onclick="scrollToSection('warranty')"><i class="fas fa-shield-alt"></i> Warranty</button>
        <button class="nav-pill" data-section="insurance" onclick="scrollToSection('insurance')"><i class="fas fa-file-invoice"></i> Insurance</button>
        <button class="nav-pill" data-section="notes" onclick="scrollToSection('notes')"><i class="fas fa-sticky-note"></i> Notes</button>
    </div>

    <form action="{{ route('work-orders.update', $workOrder) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- ========== SECTION: Details ========== -->
        <div class="form-section" id="details">
            <div class="form-section-header" data-bs-toggle="collapse" data-bs-target="#collapseDetails" role="button" aria-expanded="true">
                <h6><i class="fas fa-info-circle"></i> Work Order Details</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body collapse show" id="collapseDetails">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="customer_id" class="form-label field-required">Customer</label>
                                <select name="customer_id" id="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ $workOrder->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->first_name }} {{ $customer->last_name }} - {{ $customer->phone }}</option>
                                    @endforeach
                                </select>
                                @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                    <div class="col-md-6">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="vehicle_id" class="form-label field-required">Vehicle</label>
                                <select name="vehicle_id" id="vehicle_id" class="form-select @error('vehicle_id') is-invalid @enderror" required>
                                    <option value="">Select Vehicle</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ $workOrder->vehicle_id == $vehicle->id ? 'selected' : '' }}>{{ $vehicle->make }} {{ $vehicle->model }} - {{ $vehicle->license_plate }}</option>
                                    @endforeach
                                </select>
                                @error('vehicle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                    <div class="col-md-4">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="work_order_number" class="form-label">Work Order Number</label>
                                <input type="text" name="work_order_number" id="work_order_number" class="form-control" value="{{ old('work_order_number', $workOrder->work_order_number) }}" readonly>
                            </div>
                        </div></div>
                    </div>
                    <div class="col-md-4">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="work_order_date" class="form-label field-required">Work Order Date</label>
                                <input type="date" name="work_order_date" id="work_order_date" class="form-control @error('work_order_date') is-invalid @enderror" value="{{ old('work_order_date', $workOrder->work_order_date ? $workOrder->work_order_date->format('Y-m-d') : '') }}" required>
                                @error('work_order_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                    <div class="col-md-4">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="work_order_type" class="form-label">Work Order Type</label>
                                <select name="work_order_type" id="work_order_type" class="form-select @error('work_order_type') is-invalid @enderror">
                                    <option value="">Select Type</option>
                                    <option value="standard" {{ old('work_order_type', $workOrder->work_order_type) == 'standard' ? 'selected' : '' }}>Standard</option>
                                    <option value="emergency" {{ old('work_order_type', $workOrder->work_order_type) == 'emergency' ? 'selected' : '' }}>Emergency</option>
                                    <option value="express" {{ old('work_order_type', $workOrder->work_order_type) == 'express' ? 'selected' : '' }}>Express</option>
                                    <option value="warranty" {{ old('work_order_type', $workOrder->work_order_type) == 'warranty' ? 'selected' : '' }}>Warranty</option>
                                    <option value="insurance" {{ old('work_order_type', $workOrder->work_order_type) == 'insurance' ? 'selected' : '' }}>Insurance</option>
                                </select>
                                @error('work_order_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                    <div class="col-md-6">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="work_order_status" class="form-label field-required">Status</label>
                                <select name="work_order_status" id="work_order_status" class="form-select @error('work_order_status') is-invalid @enderror" required>
                                    <option value="pending" {{ $workOrder->work_order_status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="repairing" {{ $workOrder->work_order_status == 'repairing' ? 'selected' : '' }}>Repairing</option>
                                    <option value="waiting_parts" {{ $workOrder->work_order_status == 'waiting_parts' ? 'selected' : '' }}>Waiting Parts</option>
                                    <option value="completed" {{ $workOrder->work_order_status == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="released" {{ $workOrder->work_order_status == 'released' ? 'selected' : '' }}>Released</option>
                                    <option value="cancelled" {{ $workOrder->work_order_status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                @error('work_order_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                    <div class="col-md-6">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="priority" class="form-label field-required">Priority</label>
                                <select name="priority" id="priority" class="form-select @error('priority') is-invalid @enderror" required>
                                    <option value="low" {{ $workOrder->priority == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="normal" {{ $workOrder->priority == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="high" {{ $workOrder->priority == 'high' ? 'selected' : '' }}>High</option>
                                </select>
                                @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                    <div class="col-md-4">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="bay_number" class="form-label">Bay Number</label>
                                <input type="text" name="bay_number" id="bay_number" class="form-control @error('bay_number') is-invalid @enderror" value="{{ old('bay_number', $workOrder->bay_number) }}" placeholder="e.g. Bay 3">
                                @error('bay_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== SECTION: Vehicle Condition ========== -->
        <div class="form-section" id="vehicleCondition">
            <div class="form-section-header" data-bs-toggle="collapse" data-bs-target="#collapseVehicle" role="button" aria-expanded="true">
                <h6><i class="fas fa-car"></i> Vehicle Condition</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body collapse show" id="collapseVehicle">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="odometer_in" class="form-label">Odometer In (km)</label>
                                <input type="number" min="0" step="1" class="form-control @error('odometer_in') is-invalid @enderror" id="odometer_in" name="odometer_in" value="{{ old('odometer_in', $workOrder->odometer_in) }}" placeholder="Current mileage">
                                @error('odometer_in')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                    <div class="col-md-4">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="fuel_level" class="form-label">Fuel Level</label>
                                <select name="fuel_level" id="fuel_level" class="form-select @error('fuel_level') is-invalid @enderror">
                                    <option value="">-- Select --</option>
                                    <option value="empty" {{ old('fuel_level', $workOrder->fuel_level) == 'empty' ? 'selected' : '' }}>Empty</option>
                                    <option value="1/4" {{ old('fuel_level', $workOrder->fuel_level) == '1/4' ? 'selected' : '' }}>1/4</option>
                                    <option value="1/2" {{ old('fuel_level', $workOrder->fuel_level) == '1/2' ? 'selected' : '' }}>1/2</option>
                                    <option value="3/4" {{ old('fuel_level', $workOrder->fuel_level) == '3/4' ? 'selected' : '' }}>3/4</option>
                                    <option value="full" {{ old('fuel_level', $workOrder->fuel_level) == 'full' ? 'selected' : '' }}>Full</option>
                                </select>
                                @error('fuel_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                    <div class="col-md-4">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="vehicle_condition" class="form-label">Vehicle Condition</label>
                                <select name="vehicle_condition" id="vehicle_condition" class="form-select @error('vehicle_condition') is-invalid @enderror">
                                    <option value="">-- Select --</option>
                                    <option value="excellent" {{ old('vehicle_condition', $workOrder->vehicle_condition) == 'excellent' ? 'selected' : '' }}>Excellent</option>
                                    <option value="good" {{ old('vehicle_condition', $workOrder->vehicle_condition) == 'good' ? 'selected' : '' }}>Good</option>
                                    <option value="fair" {{ old('vehicle_condition', $workOrder->vehicle_condition) == 'fair' ? 'selected' : '' }}>Fair</option>
                                    <option value="poor" {{ old('vehicle_condition', $workOrder->vehicle_condition) == 'poor' ? 'selected' : '' }}>Poor</option>
                                </select>
                                @error('vehicle_condition')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== SECTION: Team ========== -->
        <div class="form-section" id="team">
            <div class="form-section-header" data-bs-toggle="collapse" data-bs-target="#collapseTeam" role="button" aria-expanded="true">
                <h6><i class="fas fa-users"></i> Technician &amp; Staff Assignment</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body collapse show" id="collapseTeam">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="technician_id" class="form-label">Lead Technician</label>
                                <select name="technician_id" id="technician_id" class="form-select @error('technician_id') is-invalid @enderror">
                                    <option value="">Select Technician</option>
                                    @foreach($technicians as $technician)
                                        <option value="{{ $technician->id }}" {{ $workOrder->technician_id == $technician->id ? 'selected' : '' }}>{{ $technician->name }}</option>
                                    @endforeach
                                </select>
                                @error('technician_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                    <div class="col-md-6">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="service_advisor_id" class="form-label">Service Advisor</label>
                                <select name="service_advisor_id" id="service_advisor_id" class="form-select @error('service_advisor_id') is-invalid @enderror">
                                    <option value="">Select Service Advisor</option>
                                    @foreach($advisors as $advisor)
                                        <option value="{{ $advisor->id }}" {{ $workOrder->service_advisor_id == $advisor->id ? 'selected' : '' }}>{{ $advisor->name }}</option>
                                    @endforeach
                                </select>
                                @error('service_advisor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                    <div class="col-12">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label class="form-label">Technician Role Management</label>
                                <div class="technician-select-wrapper">
                                    <div class="technician-tags"></div>
                                    <button type="button" class="btn btn-outline-primary btn-sm tech-select-trigger" style="font-size:0.82rem;"><i class="fas fa-plus me-1"></i> Add Technician</button>
                                    <div class="technician-dropdown">
                                        <input type="text" class="search-input" placeholder="Search technicians...">
                                        @foreach($technicians as $tech)
                                        <div class="tech-option" data-id="{{ $tech->id }}" data-name="{{ $tech->name }}" data-role="Technician"><span class="tech-check"></span><span>{{ $tech->name }}</span></div>
                                        @endforeach
                                    </div>
                                    <input type="hidden" name="technician_assignments" value="{{ json_encode($workOrder->technician_assignments ?? []) }}">
                                </div>
                                <small class="text-muted">Assign technicians with specific roles (e.g. Lead, Assistant, Specialist)</small>
                            </div>
                        </div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== SECTION: Concerns ========== -->
        <div class="form-section" id="concerns">
            <div class="form-section-header" data-bs-toggle="collapse" data-bs-target="#collapseConcerns" role="button" aria-expanded="true">
                <h6><i class="fas fa-exclamation-triangle"></i> Customer Concerns &amp; Diagnosis</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body collapse show" id="collapseConcerns">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="customer_concerns" class="form-label">Customer Concerns</label>
                                <textarea class="form-control @error('customer_concerns') is-invalid @enderror" id="customer_concerns" name="customer_concerns" rows="3" placeholder="What did the customer report?">{{ old('customer_concerns', $workOrder->customer_concerns) }}</textarea>
                                @error('customer_concerns')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                    <div class="col-md-6">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="customer_complaints" class="form-label">Customer Complaints</label>
                                <textarea class="form-control @error('customer_complaints') is-invalid @enderror" id="customer_complaints" name="customer_complaints" rows="3" placeholder="Detailed complaints...">{{ old('customer_complaints', $workOrder->customer_complaints) }}</textarea>
                                @error('customer_complaints')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                    <div class="col-12">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="initial_diagnosis" class="form-label">Initial Diagnosis</label>
                                <textarea class="form-control @error('initial_diagnosis') is-invalid @enderror" id="initial_diagnosis" name="initial_diagnosis" rows="3" placeholder="Initial findings and diagnosis...">{{ old('initial_diagnosis', $workOrder->initial_diagnosis) }}</textarea>
                                @error('initial_diagnosis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== SECTION: Services ========== -->
        <div class="form-section" id="services">
            <div class="form-section-header" data-bs-toggle="collapse" data-bs-target="#collapseServices" role="button" aria-expanded="true">
                <h6><i class="fas fa-tools"></i> Recommended Services &amp; Items</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body collapse show" id="collapseServices">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="recommended_services" class="form-label">Recommended Services</label>
                                <textarea class="form-control @error('recommended_services') is-invalid @enderror" id="recommended_services" name="recommended_services" rows="3" placeholder="Services recommended based on diagnosis...">{{ old('recommended_services', $workOrder->recommended_services) }}</textarea>
                                @error('recommended_services')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                    <div class="col-md-6">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <div class="form-check mb-2">
                                    <input type="checkbox" class="form-check-input" id="has_safety_concerns" name="has_safety_concerns" value="1" {{ old('has_safety_concerns', $workOrder->has_safety_concerns) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="has_safety_concerns"><i class="fas fa-exclamation-circle text-danger me-1"></i> Has Safety Concerns</label>
                                </div>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="requires_customer_approval" name="requires_customer_approval" value="1" {{ old('requires_customer_approval', $workOrder->requires_customer_approval) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="requires_customer_approval"><i class="fas fa-file-signature text-primary me-1"></i> Requires Customer Approval</label>
                                </div>
                            </div>
                        </div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== SECTION: Estimate ========== -->
        <div class="form-section" id="estimate">
            <div class="form-section-header" data-bs-toggle="collapse" data-bs-target="#collapseEstimate" role="button" aria-expanded="true">
                <h6><i class="fas fa-calculator"></i> Estimate &amp; Pricing</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body collapse show" id="collapseEstimate">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="estimated_labor_hours" class="form-label">Estimated Labor Hours</label>
                                <input type="number" min="0" step="0.5" class="form-control @error('estimated_labor_hours') is-invalid @enderror" id="estimated_labor_hours" name="estimated_labor_hours" value="{{ old('estimated_labor_hours', $workOrder->estimated_labor_hours) }}" placeholder="0.0">
                                @error('estimated_labor_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                    <div class="col-md-4">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="estimated_labor_cost" class="form-label">Estimated Labor Cost ($)</label>
                                <input type="number" min="0" step="0.01" class="form-control @error('estimated_labor_cost') is-invalid @enderror" id="estimated_labor_cost" name="estimated_labor_cost" value="{{ old('estimated_labor_cost', $workOrder->estimated_labor_cost) }}" placeholder="0.00">
                                @error('estimated_labor_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                    <div class="col-md-4">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="estimated_parts_cost" class="form-label">Estimated Parts Cost ($)</label>
                                <input type="number" min="0" step="0.01" class="form-control @error('estimated_parts_cost') is-invalid @enderror" id="estimated_parts_cost" name="estimated_parts_cost" value="{{ old('estimated_parts_cost', $workOrder->estimated_parts_cost) }}" placeholder="0.00">
                                @error('estimated_parts_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                    <div class="col-md-4">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="estimated_tax" class="form-label">Estimated Tax ($)</label>
                                <input type="number" min="0" step="0.01" class="form-control @error('estimated_tax') is-invalid @enderror" id="estimated_tax" name="estimated_tax" value="{{ old('estimated_tax', $workOrder->estimated_tax) }}" placeholder="0.00">
                                @error('estimated_tax')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                    <div class="col-md-4">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="estimated_total" class="form-label">Estimated Total ($)</label>
                                <input type="number" min="0" step="0.01" class="form-control @error('estimated_total') is-invalid @enderror" id="estimated_total" name="estimated_total" value="{{ old('estimated_total', $workOrder->estimated_total) }}" placeholder="0.00">
                                @error('estimated_total')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                    <div class="col-12">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="estimate_notes" class="form-label">Estimate Notes</label>
                                <textarea class="form-control @error('estimate_notes') is-invalid @enderror" id="estimate_notes" name="estimate_notes" rows="2" placeholder="Notes about this estimate...">{{ old('estimate_notes', $workOrder->estimate_notes) }}</textarea>
                                @error('estimate_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== SECTION: Warranty ========== -->
        <div class="form-section" id="warranty">
            <div class="form-section-header" data-bs-toggle="collapse" data-bs-target="#collapseWarranty" role="button" aria-expanded="true">
                <h6><i class="fas fa-shield-alt"></i> Warranty</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body collapse show" id="collapseWarranty">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="is_warranty_work" name="is_warranty_work" value="1" {{ old('is_warranty_work', $workOrder->is_warranty_work) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_warranty_work"><i class="fas fa-shield-alt text-info me-1"></i> Warranty Work</label>
                                </div>
                                <label for="warranty_type" class="form-label">Warranty Type</label>
                                <select name="warranty_type" id="warranty_type" class="form-select @error('warranty_type') is-invalid @enderror">
                                    <option value="">-- Select --</option>
                                    <option value="manufacturer" {{ old('warranty_type', $workOrder->warranty_type) == 'manufacturer' ? 'selected' : '' }}>Manufacturer</option>
                                    <option value="extended" {{ old('warranty_type', $workOrder->warranty_type) == 'extended' ? 'selected' : '' }}>Extended</option>
                                    <option value="shop" {{ old('warranty_type', $workOrder->warranty_type) == 'shop' ? 'selected' : '' }}>Shop</option>
                                </select>
                                @error('warranty_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                    <div class="col-md-6">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="warranty_number" class="form-label">Warranty Number</label>
                                <input type="text" name="warranty_number" id="warranty_number" class="form-control @error('warranty_number') is-invalid @enderror" value="{{ old('warranty_number', $workOrder->warranty_number) }}" placeholder="Warranty reference #">
                                @error('warranty_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group mt-2">
                                <label for="warranty_expiry" class="form-label">Warranty Expiry</label>
                                <input type="date" name="warranty_expiry" id="warranty_expiry" class="form-control @error('warranty_expiry') is-invalid @enderror" value="{{ old('warranty_expiry', $workOrder->warranty_expiry ? date('Y-m-d', strtotime($workOrder->warranty_expiry)) : '') }}">
                                @error('warranty_expiry')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-group mt-2">
                                <label for="warranty_coverage" class="form-label">Warranty Coverage</label>
                                <textarea class="form-control @error('warranty_coverage') is-invalid @enderror" id="warranty_coverage" name="warranty_coverage" rows="2" placeholder="What's covered?">{{ old('warranty_coverage', $workOrder->warranty_coverage) }}</textarea>
                                @error('warranty_coverage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== SECTION: Insurance ========== -->
        <div class="form-section" id="insurance">
            <div class="form-section-header" data-bs-toggle="collapse" data-bs-target="#collapseInsurance" role="button" aria-expanded="true">
                <h6><i class="fas fa-file-invoice"></i> Insurance</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body collapse show" id="collapseInsurance">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="is_insurance_work" name="is_insurance_work" value="1" {{ old('is_insurance_work', $workOrder->is_insurance_work) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_insurance_work"><i class="fas fa-file-invoice text-warning me-1"></i> Insurance Work</label>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="insurance_company" class="form-label">Insurance Company</label>
                                        <input type="text" name="insurance_company" id="insurance_company" class="form-control @error('insurance_company') is-invalid @enderror" value="{{ old('insurance_company', $workOrder->insurance_company) }}" placeholder="Company name">
                                        @error('insurance_company')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <label for="insurance_claim_number" class="form-label">Claim Number</label>
                                        <input type="text" name="insurance_claim_number" id="insurance_claim_number" class="form-control @error('insurance_claim_number') is-invalid @enderror" value="{{ old('insurance_claim_number', $workOrder->insurance_claim_number) }}" placeholder="Claim #">
                                        @error('insurance_claim_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-2">
                                        <label for="insurance_adjuster" class="form-label">Adjuster</label>
                                        <input type="text" name="insurance_adjuster" id="insurance_adjuster" class="form-control @error('insurance_adjuster') is-invalid @enderror" value="{{ old('insurance_adjuster', $workOrder->insurance_adjuster) }}" placeholder="Adjuster name">
                                        @error('insurance_adjuster')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-md-2">
                                        <label for="insurance_deductible" class="form-label">Deductible ($)</label>
                                        <input type="number" min="0" step="0.01" name="insurance_deductible" id="insurance_deductible" class="form-control @error('insurance_deductible') is-invalid @enderror" value="{{ old('insurance_d{{ old('insurance_deductible', $workOrder->insurance_deductible) }}" placeholder="0.00">
                                        @error('insurance_deductible')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== SECTION: Notes ========== -->
        <div class="form-section" id="notes">
            <div class="form-section-header" data-bs-toggle="collapse" data-bs-target="#collapseNotes" role="button" aria-expanded="true">
                <h6><i class="fas fa-sticky-note"></i> Additional Notes</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body collapse show" id="collapseNotes">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card"><div class="card-body">
                            <div class="form-group">
                                <label for="additional_notes" class="form-label">Additional Notes</label>
                                <textarea class="form-control @error('additional_notes') is-invalid @enderror" id="additional_notes" name="additional_notes" rows="4" placeholder="Any additional notes or instructions...">{{ old('additional_notes', $workOrder->additional_notes) }}</textarea>
                                @error('additional_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========== STICKY SAVE BAR ========== -->
        <div class="sticky-save-bar">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-white-50"><i class="fas fa-save me-1"></i> Unsaved changes</span>
                </div>
                <div>
                    <a href="{{ route('work-orders.index') }}" class="btn btn-outline-light btn-sm me-2"><i class="fas fa-times me-1"></i> Cancel</a>
                    <button type="submit" class="btn btn-light btn-sm"><i class="fas fa-check me-1"></i> Save Work Order</button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<style>
/* Section Navigation */
.section-nav {
    position: sticky;
    top: 0;
    z-index: 1020;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 10px 15px;
    border-radius: 12px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
}

.nav-pill {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.nav-pill:hover, .nav-pill.active {
    background: rgba(255,255,255,0.95);
    color: #667eea;
    border-color: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.nav-pill i {
    margin-right: 4px;
}

/* Form Sections */
.form-section {
    background: #fff;
    border-radius: 12px;
    margin-bottom: 20px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    overflow: hidden;
    border: 1px solid #eef0f5;
}

.form-section-header {
    background: linear-gradient(135deg, #f8f9ff 0%, #eef0f5 100%);
    padding: 14px 20px;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #e8eaf0;
    user-select: none;
    transition: background 0.2s;
}

.form-section-header:hover {
    background: linear-gradient(135deg, #f0f2ff 0%, #e4e6ee 100%);
}

.form-section-header h6 {
    margin: 0;
    font-weight: 600;
    color: #2d3748;
    font-size: 0.95rem;
}

.form-section-header h6 i {
    color: #667eea;
    margin-right: 8px;
}

.collapse-icon {
    color: #667eea;
    transition: transform 0.3s ease;
}

.form-section-header[aria-expanded="true"] .collapse-icon {
    transform: rotate(180deg);
}

.form-section-body {
    padding: 20px;
}

/* Card styling inside form sections */
.form-section-body .card {
    border: 1px solid #eef0f5;
    border-radius: 8px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.03);
    height: 100%;
}

.form-section-body .card-body {
    padding: 16px;
}

/* Sticky Save Bar */
.sticky-save-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1050;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 12px 24px;
    box-shadow: 0 -4px 20px rgba(102, 126, 234, 0.3);
}

/* Technician Select */
.technician-select-wrapper {
    position: relative;
}

.technician-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 8px;
}

.technician-tag {
    display: inline-flex;
    align-items: center;
    background: #eef2ff;
    border: 1px solid #c7d2fe;
    border-radius: 20px;
    padding: 4px 12px;
    font-size: 0.82rem;
    color: #4338ca;
}

.technician-tag .remove-tag {
    margin-left: 6px;
    cursor: pointer;
    color: #ef4444;
    font-weight: bold;
}

.technician-dropdown {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    z-index: 100;
    max-height: 250px;
    overflow-y: auto;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.technician-dropdown.show {
    display: block;
}

.tech-option {
    padding: 8px 14px;
    cursor: pointer;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background 0.15s;
}

.tech-option:hover {
    background: #f3f4f6;
}

.tech-option.selected .tech-check {
    background: #667eea;
    border-color: #667eea;
}

.tech-option.selected .tech-check::after {
    content: '\2713';
    color: #fff;
    font-size: 11px;
    position: absolute;
    left: 3px;
    top: 1px;
}

.tech-check {
    width: 18px;
    height: 18px;
    border: 2px solid #d1d5db;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    position: relative;
    flex-shrink: 0;
}

.tech-select-trigger {
    margin-bottom: 4px;
}

.search-input {
    width: 100%;
    padding: 8px 12px;
    border: none;
    border-bottom: 1px solid #e5e7eb;
    outline: none;
    font-size: 0.85rem;
}

/* Scroll offset for fixed nav */
[id] {
    scroll-margin-top: 70px;
}

/* Responsive */
@media (max-width: 768px) {
    .section-nav {
        padding: 8px 10px;
        gap: 4px;
    }
    .nav-pill {
        font-size: 0.72rem;
        padding: 4px 10px;
    }
    .form-section-body {
        padding: 12px;
    }
}
</style>
@endpush

@push('scripts')
<script>
// Section navigation scroll spy
function scrollToSection(sectionId) {
    const el = document.getElementById(sectionId);
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

// Update active nav pill on scroll
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('.form-section');
    const navPills = document.querySelectorAll('.nav-pill');

    function updateActivePill() {
        let current = '';
        sections.forEach(section => {
            const rect = section.getBoundingClientRect();
            if (rect.top <= 150) {
                current = section.id;
            }
        });

        navPills.forEach(pill => {
            pill.classList.toggle('active', pill.dataset.section === current);
        });
    }

    window.addEventListener('scroll', updateActivePill);
    updateActivePill();

    // Technician role management
    const trigger = document.querySelector('.tech-select-trigger');
    const dropdown = document.querySelector('.technician-dropdown');
    const tagsContainer = document.querySelector('.technician-tags');
    const hiddenInput = document.querySelector('input[name="technician_assignments"]');
    const options = document.querySelectorAll('.tech-option');
    const searchInput = document.querySelector('.search-input');

    let selectedTechs = [];

    // Load existing assignments
    try {
        if (hiddenInput && hiddenInput.value) {
            const existing = JSON.parse(hiddenInput.value);
            if (Array.isArray(existing)) {
                selectedTechs = existing;
            }
        }
    } catch(e) {}

    function renderTags() {
        if (!tagsContainer) return;
        tagsContainer.innerHTML = '';
        selectedTechs.forEach((tech, idx) => {
            const tag = document.createElement('span');
            tag.className = 'technician-tag';
            tag.innerHTML = `${tech.name || tech.technician_name || 'Unknown'} (${tech.role || 'Technician'}) <span class="remove-tag" data-idx="${idx}">&times;</span>`;
            tagsContainer.appendChild(tag);
        });
        updateHiddenInput();
    }

    function updateHiddenInput() {
        if (hiddenInput) {
            hiddenInput.value = JSON.stringify(selectedTechs);
        }
        options.forEach(opt => {
            const id = parseInt(opt.dataset.id);
            const isSelected = selectedTechs.some(t => t.id == id);
            opt.classList.toggle('selected', isSelected);
        });
    }

    // Toggle dropdown
    if (trigger && dropdown) {
        trigger.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdown.classList.toggle('show');
        });

        document.addEventListener('click', function() {
            dropdown.classList.remove('show');
        });

        dropdown.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    }

    // Select/deselect technician
    options.forEach(opt => {
        opt.addEventListener('click', function() {
            const id = parseInt(this.dataset.id);
            const name = this.dataset.name;
            const role = this.dataset.role || 'Technician';
            const idx = selectedTechs.findIndex(t => t.id == id);

            if (idx >= 0) {
                selectedTechs.splice(idx, 1);
            } else {
                selectedTechs.push({ id, technician_id: id, name, role });
            }
            renderTags();
        });
    });

    // Remove tag
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-tag')) {
            const idx = parseInt(e.target.dataset.idx);
            if (!isNaN(idx) && idx >= 0 && idx < selectedTechs.length) {
                selectedTechs.splice(idx, 1);
                renderTags();
            }
        }
    });

    // Search filter
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            options.forEach(opt => {
                const name = opt.dataset.name.toLowerCase();
                opt.style.display = name.includes(query) ? 'flex' : 'none';
            });
        });
    }

    // Initial render
    renderTags();

    // Estimate auto-calc
    const laborCost = document.getElementById('estimated_labor_cost');
    const partsCost = document.getElementById('estimated_parts_cost');
    const taxField = document.getElementById('estimated_tax');
    const totalField = document.getElementById('estimated_total');

    function calcEstimateTotal() {
        const labor = parseFloat(laborCost?.value) || 0;
        const parts = parseFloat(partsCost?.value) || 0;
        const tax = parseFloat(taxField?.value) || 0;
        const total = labor + parts + tax;
        if (totalField) {
            totalField.value = total.toFixed(2);
        }
    }

    if (laborCost && partsCost && taxField && totalField) {
        [laborCost, partsCost, taxField].forEach(f => {
            f.addEventListener('input', calcEstimateTotal);
        });
    }
});
</script>
@endpush
@endsection
