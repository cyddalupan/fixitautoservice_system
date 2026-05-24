@extends('layouts.app')

@section('title', 'Create Work Order - Fix-It Auto Services')

@section('content')
<div class="container-fluid py-3">
    @include('partials.customer-process-assets')
    @include('partials.customer-summary-card')
    
    <div class="auto-save-toast" style="display:none;"><i class="fas fa-check-circle"></i> <span></span></div>
    
    <!-- Section Navigation -->
    <div class="section-nav" id="sectionNav">
        <button class="nav-pill active" data-section="customer" onclick="scrollToSection('customerSection')">
            <i class="fas fa-user"></i> Customer
        </button>
        <button class="nav-pill" data-section="vehicle" onclick="scrollToSection('vehicleSection')">
            <i class="fas fa-car"></i> Vehicle
        </button>
        <button class="nav-pill" data-section="service" onclick="scrollToSection('serviceSection')">
            <i class="fas fa-wrench"></i> Service
        </button>
        <button class="nav-pill" data-section="team" onclick="scrollToSection('teamSection')">
            <i class="fas fa-users"></i> Team
        </button>
        <button class="nav-pill" data-section="items" onclick="scrollToSection('itemsSection')">
            <i class="fas fa-list"></i> Items
        </button>
        <button class="nav-pill" data-section="estimates" onclick="scrollToSection('estimatesSection')">
            <i class="fas fa-calculator"></i> Estimates
        </button>
        <button class="nav-pill" data-section="warranty" onclick="scrollToSection('warrantySection')">
            <i class="fas fa-shield-alt"></i> Warranty
        </button>
        <button class="nav-pill" data-section="history" onclick="scrollToSection('historySection')">
            <i class="fas fa-history"></i> History
        </button>
    </div>
    
    <form id="creationForm" action="{{ route('work-orders.store') }}" method="POST" autocomplete="off">
        @csrf
        <input type="hidden" name="customer_selection_mode" value="{{ $selectedCustomer ? 'from_url' : 'manual' }}">
        <input type="hidden" name="appointment_id" value="{{ old('appointment_id', $selectedAppointment->id ?? $selectedInspection->appointment_id ?? '') }}">
        <input type="hidden" name="inspection_id" value="{{ old('inspection_id', $selectedInspection->id ?? '') }}">
        
        <!-- ===== SECTION: CUSTOMER ===== -->
        <div class="form-section" id="customerSection">
            <div class="form-section-header">
                <h6><i class="fas fa-user"></i> Customer Information</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customer_id" class="form-label field-required">Customer</label>
                            @if($selectedCustomer)
                                <input type="hidden" name="customer_id" value="{{ $selectedCustomer->id }}">
                                <div class="form-control-plaintext fw-bold" style="padding: 6px 0;">
                                    <i class="fas fa-user text-primary me-1"></i>
                                    {{ $selectedCustomer->name }}
                                    <small class="text-muted ms-2">({{ $selectedCustomer->phone ?? '' }})</small>
                                </div>
                            @else
                                <select class="form-select @error('customer_id') is-invalid @enderror"
                                        id="customer_id" name="customer_id" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $c)
                                        <option value="{{ $c->id }}" 
                                            {{ old('customer_id', $selectedCustomer->id ?? '') == $c->id ? 'selected' : '' }}>
                                            {{ $c->name }} {{ $c->phone ? '- '.$c->phone : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="vehicle_id" class="form-label field-required">Vehicle</label>
                            <select class="form-select @error('vehicle_id') is-invalid @enderror"
                                    id="vehicle_id" name="vehicle_id" required>
                                <option value="">Select Vehicle</option>
                                @foreach($vehicles as $v)
                                    <option value="{{ $v->id }}"
                                        data-plate="{{ $v->license_plate ?? '' }}"
                                        data-customer="{{ $v->customer_id }}"
                                        {{ old('vehicle_id', $selectedVehicle->id ?? '') == $v->id ? 'selected' : '' }}>
                                        {{ $v->year }} {{ $v->make }} {{ $v->model }}
                                        @if($v->license_plate) [{{ $v->license_plate }}] @endif
                                        @if($v->customer) - {{ $v->customer->name }} @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('vehicle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted"><i class="fas fa-lightbulb"></i> Select customer first to filter vehicles</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="customer_complaints" class="form-label">Customer Complaints</label>
                            <textarea class="form-control @error('customer_complaints') is-invalid @enderror"
                                      id="customer_complaints" name="customer_complaints" rows="2"
                                      placeholder="What did the customer complain about?">{{ old('customer_complaints', $selectedAppointment->service_request ?? $selectedInspection->customer_concerns ?? '') }}</textarea>
                            @error('customer_complaints')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ===== SECTION: VEHICLE ===== -->
        <div class="form-section" id="vehicleSection">
            <div class="form-section-header">
                <h6><i class="fas fa-car"></i> Vehicle Condition</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="odometer_in" class="form-label">Odometer Reading (km)</label>
                            <input type="number" min="0" step="1"
                                   class="form-control @error('odometer_in') is-invalid @enderror"
                                   id="odometer_in" name="odometer_in"
                                   value="{{ old('odometer_in', $selectedVehicle->odometer ?? '') }}"
                                   placeholder="Current ODO">
                            @error('odometer_in')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fuel_level" class="form-label">Fuel Level</label>
                            <select class="form-select @error('fuel_level') is-invalid @enderror" id="fuel_level" name="fuel_level">
                                <option value="">-- Select --</option>
                                <option value="full" {{ old('fuel_level') == 'full' ? 'selected' : '' }}>⛽ Full</option>
                                <option value="3/4" {{ old('fuel_level') == '3/4' ? 'selected' : '' }}>⛽ 3/4</option>
                                <option value="1/2" {{ old('fuel_level') == '1/2' ? 'selected' : '' }}>⛽ 1/2</option>
                                <option value="1/4" {{ old('fuel_level') == '1/4' ? 'selected' : '' }}>⛽ 1/4</option>
                                <option value="empty" {{ old('fuel_level') == 'empty' ? 'selected' : '' }}>⛽ Empty</option>
                            </select>
                            @error('fuel_level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Service Flags</label>
                            <div class="d-flex gap-3 flex-wrap pt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="requires_customer_approval"
                                           name="requires_customer_approval" value="1"
                                           {{ old('requires_customer_approval', $selectedInspection->requires_customer_approval ?? $selectedAppointment->requires_customer_approval ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="requires_customer_approval"><small>Requires Approval</small></label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_rush_order"
                                           name="is_rush_order" value="1"
                                           {{ old('is_rush_order') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_rush_order"><small><i class="fas fa-bolt text-warning"></i> Rush</small></label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="has_safety_concerns"
                                           name="has_safety_concerns" value="1"
                                           {{ old('has_safety_concerns') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="has_safety_concerns"><small><i class="fas fa-exclamation-triangle text-danger"></i> Safety</small></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label for="vehicle_condition" class="form-label">Vehicle Condition Notes</label>
                            <textarea class="form-control @error('vehicle_condition') is-invalid @enderror"
                                      id="vehicle_condition" name="vehicle_condition" rows="2"
                                      placeholder="Visible damage, scratches, pre-existing conditions...">{{ old('vehicle_condition') }}</textarea>
                            @error('vehicle_condition')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ===== SECTION: SERVICE ===== -->
        <div class="form-section" id="serviceSection">
            <div class="form-section-header">
                <h6><i class="fas fa-wrench"></i> Service Details</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="customer_concerns" class="form-label field-required">Customer Concerns</label>
                            <textarea class="form-control @error('customer_concerns') is-invalid @enderror"
                                      id="customer_concerns" name="customer_concerns" rows="3" required
                                      placeholder="What needs to be done?">{{ old('customer_concerns', $selectedAppointment->service_request ?? $selectedInspection->customer_concerns ?? '') }}</textarea>
                            <div class="d-flex flex-wrap gap-1 mt-2">
                                <span class="quick-note-btn" onclick="appendNote('customer_concerns', 'Check engine light on - needs diagnostic scan')"><i class="fas fa-lightbulb"></i> Check Engine</span>
                                <span class="quick-note-btn" onclick="appendNote('customer_concerns', 'Change oil, replace oil filter, top up fluids (PMS)')"><i class="fas fa-oil-can"></i> PMS</span>
                                <span class="quick-note-btn" onclick="appendNote('customer_concerns', 'Vibrations when braking - inspect brake pads and rotors')"><i class="fas fa-car-side"></i> Brake Issue</span>
                                <span class="quick-note-btn" onclick="appendNote('customer_concerns', 'Replace 4 tires, alignment, and balancing')"><i class="fas fa-circle"></i> Tires</span>
                                <span class="quick-note-btn" onclick="appendNote('customer_concerns', "A/C not blowing cold air - check refrigerant and compressor")"><i class="fas fa-snowflake"></i> A/C Issue</span>
                            </div>
                            @error('customer_concerns')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="initial_diagnosis" class="form-label">Initial Diagnosis</label>
                            <textarea class="form-control @error('initial_diagnosis') is-invalid @enderror"
                                      id="initial_diagnosis" name="initial_diagnosis" rows="3"
                                      placeholder="Preliminary findings...">{{ old('initial_diagnosis', $selectedInspection->inspection_notes ?? '') }}</textarea>
                            @error('initial_diagnosis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="recommended_services" class="form-label">Recommended Services</label>
                            <textarea class="form-control @error('recommended_services') is-invalid @enderror"
                                      id="recommended_services" name="recommended_services" rows="3"
                                      placeholder="Suggested repairs/maintenance...">{{ old('recommended_services') }}</textarea>
                            @error('recommended_services')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            @include('partials.service-type-selector', [
                                'selected' => old('service_type', $workOrder->service_type ?? ''),
                                'name' => 'service_type',
                                'label' => 'SERVICE TYPE',
                                'required' => true,
                                'showIcons' => true,
                                'multiple' => true,
                                'placeholder' => 'Select Service Type',
                                'module' => 'work_orders',
                            ])
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="form-label">Bay Number</label>
                            <select class="form-select @error('bay_number') is-invalid @enderror" id="bay_number" name="bay_number">
                                <option value="">-- Auto Assign --</option>
                                @for($i = 1; $i <= 20; $i++)
                                    <option value="{{ $i }}" {{ old('bay_number') == $i ? 'selected' : '' }}>Bay #{{ $i }}</option>
                                @endfor
                            </select>
                            @error('bay_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="additional_notes" class="form-label">Additional Notes</label>
                            <textarea class="form-control @error('additional_notes') is-invalid @enderror"
                                      id="additional_notes" name="additional_notes" rows="2"
                                      placeholder="Any extra instructions...">{{ old('additional_notes') }}</textarea>
                            @error('additional_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">Work Options</label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_warranty_work" name="is_warranty_work" value="1"
                                        {{ old('is_warranty_work') ? 'checked' : '' }}
                                        onchange="$('#warrantyFields').toggle(this.checked)">
                                    <label class="form-check-label" for="is_warranty_work">Warranty Work</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_insurance_work" name="is_insurance_work" value="1"
                                        {{ old('is_insurance_work') ? 'checked' : '' }}
                                        onchange="$('#insuranceFields').toggle(this.checked)">
                                    <label class="form-check-label" for="is_insurance_work">Insurance Work</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_complex_job" name="is_complex_job" value="1"
                                        {{ old('is_complex_job') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_complex_job">Complex Job</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="requires_manager_approval" name="requires_manager_approval" value="1"
                                        {{ old('requires_manager_approval') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="requires_manager_approval">Requires Manager Approval</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ===== SECTION: TEAM ===== -->
        <div class="form-section" id="teamSection">
            <div class="form-section-header">
                <h6><i class="fas fa-users"></i> Team Assignment</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="technician_id" class="form-label">Lead Technician</label>
                            <select class="form-select @error('technician_id') is-invalid @enderror"
                                    id="technician_id" name="technician_id">
                                <option value="">-- Not Assigned --</option>
                                @foreach($technicians as $tech)
                                    <option value="{{ $tech->id }}" {{ old('technician_id', $selectedInspection->technician_id ?? $selectedAppointment->assigned_technician_id ?? '') == $tech->id ? 'selected' : '' }}>
                                        {{ $tech->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('technician_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="service_advisor_id" class="form-label">Service Advisor</label>
                            <select class="form-select @error('service_advisor_id') is-invalid @enderror"
                                    id="service_advisor_id" name="service_advisor_id">
                                <option value="">-- Not Assigned --</option>
                                @foreach($advisors as $adv)
                                    <option value="{{ $adv->id }}" {{ old('service_advisor_id', $selectedAppointment->service_advisor_id ?? '') == $adv->id ? 'selected' : '' }}>
                                        {{ $adv->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('service_advisor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="form-label">Technician Assignments</label>
                            <div class="technician-assignments-container">
                                <div id="assignmentTagList"></div>
                                <div class="add-assignment-row">
                                    <select class="form-select form-select-sm" id="assignment_tech_select" style="width:auto;display:inline-block;">
                                        <option value="">Select Technician</option>
                                        @foreach($technicians as $tech)
                                            <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" class="form-control form-control-sm" id="assignment_role_input" style="width:auto;display:inline-block;" placeholder="Role">
                                    <button type="button" class="btn btn-success btn-sm" onclick="addTechnicianAssignment()">
                                        <i class="fas fa-plus"></i> Add
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">Assign technicians and their roles for this work order</small>
                            <div id="technician_assignment_fields"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ===== SECTION: ITEMS ===== -->
        <div class="form-section" id="itemsSection">
            <div class="form-section-header">
                <h6><i class="fas fa-list"></i> Work Order Items</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
                <div class="mt-1"><button type="button" class="btn btn-sm btn-outline-primary" onclick="addItem()"><i class="fas fa-plus me-1"></i> Add Item</button></div>
            </div>
            <div class="form-section-body">
                <div class="items-container" id="itemsContainer">
                    <p class="text-muted mb-0"><i class="fas fa-info-circle me-1"></i> Add labor, parts, sublet, or other charges below. You can also add them later after creating the work order.</p>
                </div>
            </div>
        </div>
        
        <!-- ===== SECTION: ESTIMATES ===== -->
        <div class="form-section" id="estimatesSection">
            <div class="form-section-header">
                <h6><i class="fas fa-calculator"></i> Estimate Summary</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="estimated_labor_hours" class="form-label">Est. Labor Hours</label>
                            <input type="number" step="0.5" min="0"
                                   class="form-control @error('estimated_labor_hours') is-invalid @enderror"
                                   id="estimated_labor_hours" name="estimated_labor_hours"
                                   value="{{ old('estimated_labor_hours') }}"
                                   placeholder="0" oninput="calculateEstimate()">
                            @error('estimated_labor_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="estimated_labor_cost" class="form-label">Est. Labor Cost (₱)</label>
                            <div class="input-group"><span class="input-group-text">₱</span>
                                <input type="number" step="0.01" min="0"
                                       class="form-control @error('estimated_labor_cost') is-invalid @enderror"
                                       id="estimated_labor_cost" name="estimated_labor_cost"
                                       value="{{ old('estimated_labor_cost') }}"
                                       placeholder="0.00" oninput="calculateEstimate()">
                            </div>
                            @error('estimated_labor_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="estimated_parts_cost" class="form-label">Est. Parts Cost (₱)</label>
                            <div class="input-group"><span class="input-group-text">₱</span>
                                <input type="number" step="0.01" min="0"
                                       class="form-control @error('estimated_parts_cost') is-invalid @enderror"
                                       id="estimated_parts_cost" name="estimated_parts_cost"
                                       value="{{ old('estimated_parts_cost') }}"
                                       placeholder="0.00" oninput="calculateEstimate()">
                            </div>
                            @error('estimated_parts_cost')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="estimated_tax" class="form-label">Est. Tax (₱)</label>
                            <div class="input-group"><span class="input-group-text">₱</span>
                                <input type="number" step="0.01" min="0"
                                       class="form-control @error('estimated_tax') is-invalid @enderror"
                                       id="estimated_tax" name="estimated_tax"
                                       value="{{ old('estimated_tax') }}"
                                       placeholder="0.00" oninput="calculateEstimate()">
                            </div>
                            @error('estimated_tax')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="estimate_notes" class="form-label">Estimate Notes</label>
                            <textarea class="form-control @error('estimate_notes') is-invalid @enderror"
                                      id="estimate_notes" name="estimate_notes" rows="2"
                                      placeholder="Notes for customer on estimate...">{{ old('estimate_notes') }}</textarea>
                            @error('estimate_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Estimated Total</label>
                            <div class="estimated-total-display" id="estimatedTotalDisplay">₱ 0.00</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">Quick Add</label>
                            <div class="d-flex flex-wrap gap-1">
                                <span class="quick-note-btn" onclick="setEstimate('labor', 500)">Labor ₱500</span>
                                <span class="quick-note-btn" onclick="setEstimate('labor', 1500)">Labor ₱1.5k</span>
                                <span class="quick-note-btn" onclick="setEstimate('parts', 5000)">Parts ₱5k</span>
                                <span class="quick-note-btn" onclick="setEstimate('parts', 10000)">Parts ₱10k</span>
                                <span class="quick-note-btn" onclick="setEstimate('all', [1500, 5000, 600])">Typical ₱7.1k</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ===== SECTION: WARRANTY & INSURANCE ===== -->
        <div class="form-section" id="warrantySection">
            <div class="form-section-header">
                <h6><i class="fas fa-shield-alt"></i> Warranty & Insurance</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body">
                <div id="warrantyFields" style="{{ old('is_warranty_work') ? '' : 'display:none' }}">
                    <h6 class="text-primary mb-3"><i class="fas fa-shield-alt me-1"></i> Warranty Information</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="warranty_type" class="form-label">Warranty Type</label>
                                <select class="form-select @error('warranty_type') is-invalid @enderror" id="warranty_type" name="warranty_type">
                                    <option value="">Select Type</option>
                                    <option value="manufacturer" {{ old('warranty_type') == 'manufacturer' ? 'selected' : '' }}>Manufacturer</option>
                                    <option value="extended" {{ old('warranty_type') == 'extended' ? 'selected' : '' }}>Extended</option>
                                    <option value="shop" {{ old('warranty_type') == 'shop' ? 'selected' : '' }}>Shop Warranty</option>
                                </select>
                                @error('warranty_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="warranty_number" class="form-label">Warranty #</label>
                                <input type="text" class="form-control @error('warranty_number') is-invalid @enderror"
                                       id="warranty_number" name="warranty_number" value="{{ old('warranty_number') }}">
                                @error('warranty_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="warranty_expiry" class="form-label">Expiry Date</label>
                                <input type="date" class="form-control @error('warranty_expiry') is-invalid @enderror"
                                       id="warranty_expiry" name="warranty_expiry" value="{{ old('warranty_expiry') }}">
                                @error('warranty_expiry')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="warranty_coverage" class="form-label">Coverage Amount (₱)</label>
                                <input type="number" step="0.01" min="0"
                                       class="form-control @error('warranty_coverage') is-invalid @enderror"
                                       id="warranty_coverage" name="warranty_coverage" value="{{ old('warranty_coverage') }}">
                                @error('warranty_coverage')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div id="insuranceFields" style="{{ old('is_insurance_work') ? '' : 'display:none' }}" class="mt-4">
                    <h6 class="text-warning mb-3"><i class="fas fa-file-invoice me-1"></i> Insurance Information</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="insurance_company" class="form-label">Insurance Company</label>
                                <input type="text" class="form-control @error('insurance_company') is-invalid @enderror"
                                       id="insurance_company" name="insurance_company" value="{{ old('insurance_company') }}">
                                @error('insurance_company')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="insurance_claim_number" class="form-label">Claim #</label>
                                <input type="text" class="form-control @error('insurance_claim_number') is-invalid @enderror"
                                       id="insurance_claim_number" name="insurance_claim_number" value="{{ old('insurance_claim_number') }}">
                                @error('insurance_claim_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="insurance_adjuster" class="form-label">Adjuster</label>
                                <input type="text" class="form-control @error('insurance_adjuster') is-invalid @enderror"
                                       id="insurance_adjuster" name="insurance_adjuster" value="{{ old('insurance_adjuster') }}">
                                @error('insurance_adjuster')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="insurance_deductible" class="form-label">Deductible (₱)</label>
                                <input type="number" step="0.01" min="0"
                                       class="form-control @error('insurance_deductible') is-invalid @enderror"
                                       id="insurance_deductible" name="insurance_deductible" value="{{ old('insurance_deductible') }}">
                                @error('insurance_deductible')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ===== SECTION: HISTORY ===== -->
        <div id="historySection">
            @php
                $woHistory = $customerHistory ?? collect([]);
                $processedWO = $woHistory->map(function($record) {
                    $record->historyTitle = $record->work_order_number ?? 'WO #'.$record->id;
                    $record->iconClass = match($record->work_order_status ?? '') {
                        'completed', 'closed' => 'fas fa-check-circle',
                        'cancelled' => 'fas fa-times-circle',
                        'pending', 'pending_approval' => 'fas fa-clock',
                        'approved' => 'fas fa-thumbs-up',
                        'in_progress' => 'fas fa-cog fa-spin',
                        default => 'fas fa-file-invoice',
                    };
                    $record->statusClass = match($record->work_order_status ?? '') {
                        'completed', 'closed' => 'completed',
                        'cancelled' => 'cancelled',
                        'pending', 'pending_approval' => 'pending',
                        'in_progress', 'approved' => 'progress',
                        default => 'info',
                    };
                    $record->statusLabel = $record->work_order_status ? ucfirst(str_replace('_', ' ', $record->work_order_status)) : '';
                    return $record;
                });
            @endphp
            @include('partials.customer-history', [
                'customerHistory' => $processedWO,
                'historyTitle' => 'Work Order History',
                'historyType' => 'work-order',
                'historyRoute' => '#',
            ])
        </div>
        
        <!-- Sticky Save Bar -->
        <div style="height: 70px;"></div>
        <div class="sticky-save-bar visible">
            <div class="save-info">
                <i class="fas fa-save text-primary"></i>
                <span>All changes are saved as draft automatically</span>
                <span class="auto-save-indicator">• Auto-save active</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('work-orders.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-sm px-4">
                    <i class="fas fa-check me-1"></i> Create Work Order
                </button>
            </div>
        </div>
        <input type="hidden" name="override_duplicate" id="overrideDuplicate" value="">
            </form>
</div>

<script>
$(document).ready(function() {
    function checkVehicleTransactions(vehicleId) {
        if (!vehicleId) return;
        $('.vehicle-transaction-error').remove();
        
        $.get('/api/vehicle-transactions/' + vehicleId, function(data) {
            if (data.has_active_transaction) {
                var tx = data.transactions[0];
                var errMsg = '<div class="vehicle-transaction-error alert alert-danger alert-dismissible fade show mt-2">' +
                    '<i class="fas fa-exclamation-triangle"></i> ' +
                    'This vehicle already has an active ' + tx.type + ' (' + tx.number + '). ' +
                    '<a href="' + tx.url + '" class="alert-link" target="_blank">View ' + tx.type + '</a>' +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                    '</div>';
                $('#vehicle_id').closest('.form-group').after(errMsg);
            }
        });
    }
    
    $('#vehicle_id').on('change', function() {
        checkVehicleTransactions($(this).val());
    });
    
    // Check on page load too
    checkVehicleTransactions($('#vehicle_id').val());
});

function appendNote(fieldId, text) {
    var $field = $('#' + fieldId);
    var current = $field.val() || '';
    $field.val(current + (current ? '\n' : '') + text);
    $field.focus();
    $field.trigger('input');
}
</script>
@include('partials.duplicate-transaction-modal')
@endsection
