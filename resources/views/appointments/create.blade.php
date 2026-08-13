@extends('layouts.app')

@section('title', 'Create Appointment - Fix-It Auto Services')

@section('content')
<div class="container-fluid py-3">
    @include('partials.customer-process-assets')
    @include('partials.customer-summary-card')
    
    <div class="auto-save-toast" style="display:none;"><i class="fas fa-check-circle"></i> <span></span></div>
    
    <!-- Section Navigation -->
    <div class="section-nav" id="sectionNav">
        <button class="nav-pill active" data-section="details" onclick="scrollToSection('details')">
            <i class="fas fa-info-circle"></i> Details
        </button>
        <button class="nav-pill" data-section="service" onclick="scrollToSection('service')">
            <i class="fas fa-wrench"></i> Service
        </button>
        <button class="nav-pill" data-section="notes" onclick="scrollToSection('notes')">
            <i class="fas fa-sticky-note"></i> Notes
        </button>
        <button class="nav-pill" data-section="history" onclick="scrollToSection('historySection')">
            <i class="fas fa-history"></i> History
        </button>
    </div>
    
    <form action="{{ route('appointments.store') }}" method="POST" autocomplete="off">
        @csrf
        <input type="hidden" name="customer_selection_mode" id="customer_selection_mode" value="{{ $selectedCustomer ? 'from_url' : 'existing' }}">
        
                <!-- ===== SECTION: Details ===== -->
        <div class="form-section" id="details">
            <div class="form-section-header">
                <h6><i class="fas fa-info-circle"></i> Appointment Details</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <!-- Customer (typeable: pick existing OR type new name) -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="customer_search" class="form-label field-required">Customer</label>
                            <input type="text" class="form-control @error('customer_id') is-invalid @enderror"
                                   id="customer_search" name="customer_search" list="customer-list"
                                   value="{{ old('customer_search', (isset($selectedCustomer) && $selectedCustomer) ? $selectedCustomer->first_name.' '.$selectedCustomer->last_name : '') }}"
                                   placeholder="Type to search or enter a new customer name">
                            <input type="hidden" name="customer_id" id="customer_id"
                                   value="{{ old('customer_id', (isset($selectedCustomer) && $selectedCustomer) ? $selectedCustomer->id : '') }}">
                            <datalist id="customer-list">
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->first_name }} {{ $customer->last_name }}" data-customer-id="{{ $customer->id }}"></option>
                                @endforeach
                            </datalist>
                            @if(isset($selectedCustomer) && $selectedCustomer)
                            <small class="form-text text-muted">
                                <i class="fas fa-lock me-1"></i> {{ $selectedCustomer->first_name }} {{ $selectedCustomer->last_name }}
                            </small>
                            @endif
                            @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="mt-2">
                                <button type="button" class="btn btn-sm btn-outline-primary" id="add-new-customer-btn">
                                    <i class="fas fa-user-plus me-1"></i> Add New Customer
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Vehicle: Make + Model + Year -->
                    <div class="col-md-6">
                        <div class="row g-2">
                            <div class="col-md-5">
                                <div class="form-group position-relative">
                                    <label for="vehicle_brand" class="form-label field-required">Car Brand</label>
                                    <input type="text" class="form-control @error('vehicle_brand') is-invalid @enderror"
                                           id="vehicle_brand" name="vehicle_brand" list="brand-list" autocomplete="off"
                                           value="{{ old('vehicle_brand', $selectedVehicle->make ?? '') }}"
                                           required placeholder="e.g. Toyota">
                                    <datalist id="brand-list">
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand }}"></option>
                                        @endforeach
                                    </datalist>
                                    <div id="brand-suggestions" class="vehicle-suggestions-dropdown" style="display:none;">
                                        @foreach($brands as $brand)
                                            <div class="vehicle-suggestion-item" data-value="{{ $brand }}">{{ $brand }}</div>
                                        @endforeach
                                    </div>
                                    @error('vehicle_brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group position-relative">
                                    <label for="vehicle_model" class="form-label field-required">Model</label>
                                    <input type="text" class="form-control @error('vehicle_model') is-invalid @enderror"
                                           id="vehicle_model" name="vehicle_model" list="model-list" autocomplete="off"
                                           value="{{ old('vehicle_model', $selectedVehicle->model ?? '') }}"
                                           required placeholder="e.g. Vios">
                                    <datalist id="model-list"></datalist>
                                    <div id="model-suggestions" class="vehicle-suggestions-dropdown" style="display:none;"></div>
                                    @error('vehicle_model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="vehicle_year" class="form-label field-required">Year</label>
                                    <input type="text" class="form-control @error('vehicle_year') is-invalid @enderror"
                                           id="vehicle_year" name="vehicle_year"
                                           value="{{ old('vehicle_year', $selectedVehicle->year ?? '') }}"
                                           required placeholder="e.g. 2023" maxlength="4">
                                    @error('vehicle_year')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="plate_number" class="form-label">Plate Number</label>
                                    <input type="text" class="form-control @error('plate_number') is-invalid @enderror"
                                           id="plate_number" name="plate_number"
                                           value="{{ old('plate_number', $selectedVehicle->license_plate ?? '') }}"
                                           placeholder="e.g. ABC-1234">
                                    @error('plate_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Manual Add (New Customer) — hidden by default, shown via toggle -->
                    <div class="col-12">
                        <div class="form-group manual-add-panel d-none" id="manual-add-panel">
                            <label class="form-label fw-semibold"><i class="fas fa-user-plus me-1"></i> New Customer (Manual Add)</label>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="client_name" class="form-label field-required">Client Name</label>
                                        <input type="text" class="form-control @error('client_name') is-invalid @enderror"
                                               id="client_name" name="client_name"
                                               value="{{ old('client_name') }}"
                                               placeholder="e.g. Juan Dela Cruz">
                                        @error('client_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="contact_no" class="form-label">Contact No.</label>
                                        <input type="text" class="form-control @error('contact_no') is-invalid @enderror"
                                               id="contact_no" name="contact_no"
                                               value="{{ old('contact_no') }}"
                                               placeholder="e.g. 09171234567">
                                        @error('contact_no')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Pindutin ang "Add New Customer" kung hindi mahanap ang customer sa search.
                            </small>
                        </div>
                    </div>
                    
                    <!-- Appointment Date -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="appointment_date" class="form-label field-required">Appointment Date</label>
                            <input type="date" class="form-control @error('appointment_date') is-invalid @enderror"
                                   id="appointment_date" name="appointment_date"
                                   value="{{ old('appointment_date', date('Y-m-d')) }}" required>
                            @error('appointment_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    
                    <!-- Appointment Time -->
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="appointment_time" class="form-label field-required">Appointment Time</label>
                            <input type="time" class="form-control @error('appointment_time') is-invalid @enderror"
                                   id="appointment_time" name="appointment_time"
                                   value="{{ old('appointment_time', '09:00') }}" required>
                            @error('appointment_time')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div><!-- ===== SECTION: Service ===== -->
        <div class="form-section" id="service">
            <div class="form-section-header">
                <h6><i class="fas fa-wrench"></i> Service Information</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <!-- Service Type -->
                    <div class="col-md-6">
                        @include('partials.service-type-selector', [
                            'selected' => old('service_type', ''),
                            'name' => 'service_type',
                            'label' => 'SERVICE TYPE',
                            'required' => true,
                            'showIcons' => true,
                            'multiple' => true,
                            'placeholder' => 'Select Service Type',
                            'module' => 'appointments',
                        ])
                    </div>

                    <!-- Description -->
                    <div class="col-12">
                        <div class="form-group">
                            <label for="description" class="form-label">Service Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3"
                                      placeholder="Describe the service needed...">{{ old('description') }}</textarea>

                            <!-- Quick suggestion chips: hidden until a main service is selected.
                                 Only the branches of the selected service(s) are revealed. -->
                            <div id="service-suggestions" class="d-none mt-2">
                                <label class="form-label" style="font-size:0.8rem; color:#6c5ce7;">
                                    <i class="fas fa-magic me-1"></i>Quick suggestions
                                </label>
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="quick-note-chip" data-service="preventive_maintenance" onclick="appendNote('description', 'Needs PMS - change oil and filters')"><i class="fas fa-oil-can"></i> PMS - Oil & Filters</span>
                                    <span class="quick-note-chip" data-service="preventive_maintenance" onclick="appendNote('description', 'Check engine light is on')"><i class="fas fa-lightbulb"></i> Check Engine</span>
                                    <span class="quick-note-chip" data-service="preventive_maintenance" onclick="appendNote('description', 'Needs brake fluid top up')"><i class="fas fa-tint"></i> Brake Fluid</span>
                                    <span class="quick-note-chip" data-service="basic_tune_up" onclick="appendNote('description', 'General tune up - spark plugs and filters')"><i class="fas fa-cog"></i> Spark Plugs</span>
                                    <span class="quick-note-chip" data-service="basic_tune_up" onclick="appendNote('description', 'Check engine light is on')"><i class="fas fa-lightbulb"></i> Check Engine</span>
                                    <span class="quick-note-chip" data-service="basic_tune_up" onclick="appendNote('description', 'Needs ignition system check')"><i class="fas fa-bolt"></i> Ignition Check</span>
                                    <span class="quick-note-chip" data-service="egr_service" onclick="appendNote('description', 'EGR valve cleaning needed')"><i class="fas fa-recycle"></i> EGR Cleaning</span>
                                    <span class="quick-note-chip" data-service="egr_service" onclick="appendNote('description', 'Engine check light on due to EGR')"><i class="fas fa-lightbulb"></i> Check Engine</span>
                                    <span class="quick-note-chip" data-service="egr_service" onclick="appendNote('description', 'Poor idle / rough running')"><i class="fas fa-tachometer-alt"></i> Rough Idle</span>
                                    <span class="quick-note-chip" data-service="aircon_cleaning" onclick="appendNote('description', 'Aircon cleaning - evaporator and blower')"><i class="fas fa-wind"></i> Evaporator Clean</span>
                                    <span class="quick-note-chip" data-service="aircon_cleaning" onclick="appendNote('description', 'Aircon not cooling properly')"><i class="fas fa-snowflake"></i> A/C Issue</span>
                                    <span class="quick-note-chip" data-service="aircon_cleaning" onclick="appendNote('description', 'Bad smell from aircon vents')"><i class="fas fa-odor"></i> Bad Smell</span>
                                    <span class="quick-note-chip" data-service="aircon_general_cleaning" onclick="appendNote('description', 'General aircon cleaning service')"><i class="fas fa-wind"></i> General Clean</span>
                                    <span class="quick-note-chip" data-service="aircon_general_cleaning" onclick="appendNote('description', 'Aircon not cooling properly')"><i class="fas fa-snowflake"></i> A/C Issue</span>
                                    <span class="quick-note-chip" data-service="aircon_general_cleaning" onclick="appendNote('description', 'Cabin filter replacement')"><i class="fas fa-filter"></i> Cabin Filter</span>
                                    <span class="quick-note-chip" data-service="aircon_service" onclick="appendNote('description', 'Aircon not cooling properly')"><i class="fas fa-snowflake"></i> A/C Issue</span>
                                    <span class="quick-note-chip" data-service="aircon_service" onclick="appendNote('description', 'Aircon recharging / refrigerant check')"><i class="fas fa-fill"></i> Recharge</span>
                                    <span class="quick-note-chip" data-service="aircon_service" onclick="appendNote('description', 'Aircon compressor check')"><i class="fas fa-cog"></i> Compressor</span>
                                    <span class="quick-note-chip" data-service="underchassis_service" onclick="appendNote('description', 'Unusual noise when braking')"><i class="fas fa-volume-up"></i> Brake Noise</span>
                                    <span class="quick-note-chip" data-service="underchassis_service" onclick="appendNote('description', 'Car pulls to the left')"><i class="fas fa-arrows-alt-h"></i> Alignment</span>
                                    <span class="quick-note-chip" data-service="underchassis_service" onclick="appendNote('description', 'Underchassis inspection needed')"><i class="fas fa-search"></i> Underchassis Check</span>
                                    <span class="quick-note-chip" data-service="engine_service" onclick="appendNote('description', 'Check engine light is on')"><i class="fas fa-lightbulb"></i> Check Engine</span>
                                    <span class="quick-note-chip" data-service="engine_service" onclick="appendNote('description', 'Engine noise / knocking')"><i class="fas fa-volume-up"></i> Engine Noise</span>
                                    <span class="quick-note-chip" data-service="engine_service" onclick="appendNote('description', 'Engine overheating')"><i class="fas fa-temperature-high"></i> Overheating</span>
                                </div>
                            </div>

                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ===== SECTION: Notes ===== -->
        <div class="form-section" id="notes">
            <div class="form-section-header">
                <h6><i class="fas fa-sticky-note"></i> Additional Notes</h6>
                <div class="collapse-icon"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="form-section-body">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="notes" class="form-label">Admin Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror"
                                      id="notes" name="notes" rows="3"
                                      placeholder="Internal notes (not visible to customer)...">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- ===== SECTION: History ===== -->
        <div id="historySection">
            @php
                $historyRecords = $customerHistory ?? collect([]);
                $processedHistory = $historyRecords->map(function($record) {
                    $record->historyTitle = $record->appointment_number ?? 'Appointment #'.$record->id;
                    $record->iconClass = match($record->appointment_status ?? '') {
                        'completed', 'checked_out' => 'fas fa-check-circle',
                        'cancelled', 'no_show' => 'fas fa-times-circle',
                        'scheduled', 'confirmed' => 'fas fa-calendar-check',
                        default => 'fas fa-calendar-alt',
                    };
                    $record->statusClass = match($record->appointment_status ?? '') {
                        'completed', 'checked_out' => 'completed',
                        'cancelled', 'no_show' => 'cancelled',
                        'scheduled', 'confirmed' => 'pending',
                        default => 'info',
                    };
                    $record->statusLabel = $record->appointment_status ? ucfirst(str_replace('_', ' ', $record->appointment_status)) : '';
                    return $record;
                });
            @endphp
            @include('partials.customer-history', [
                'customerHistory' => $processedHistory,
                'historyTitle' => 'Appointment History',
                'historyType' => 'appointment',
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
                <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary btn-sm px-4">
                    <i class="fas fa-check me-1"></i> Create Appointment
                </button>
            </div>
        </div>
    </form>
</div>

<script>
// Vehicle autocomplete data - all customer vehicles
var customerVehiclesData = {!! json_encode($customerVehicles->map(function($v) {
    return [
        'id' => $v->id,
        'label' => $v->year.' '.$v->make.' '.$v->model.($v->license_plate ? ' ['.$v->license_plate.']' : ''),
        'year' => $v->year,
        'make' => $v->make,
        'model' => $v->model,
        'license_plate' => $v->license_plate,
    ];
})) !!};

function appendNote(fieldId, text) {
    var $field = $('#' + fieldId);
    var current = $field.val() || '';
    $field.val(current + (current ? '\n' : '') + text);
    $field.focus();
    $field.trigger('input');
}

function fillVehicleDescription(vehicle) {
    var desc = vehicle.year + ' ' + vehicle.make + ' ' + vehicle.model;
    if (vehicle.license_plate) {
        desc += ' - ' + vehicle.license_plate;
    }
    $('#vehicle_description').val(desc);
    $('#vehicle_id').val(vehicle.id);
    $('#vehicle-suggestions').hide();
}

$(document).ready(function() {
    initTechnicianMultiSelect(".technician-select-wrapper:not([data-tech-init])");

    // ===== SERVICE SUGGESTIONS =====
    // The quick suggestion chips under Service Description only appear after
    // the user selects a main service; only branches of the selected
    // service(s) are revealed.
    function refreshServiceSuggestions() {
        var selected = [];
        $('.service-card-grid input[name="service_type[]"]:checked').each(function() {
            selected.push($(this).val());
        });

        var $container = $('#service-suggestions');
        if (!$container.length) return;

        var shown = false;
        $container.find('.quick-note-chip').each(function() {
            var service = $(this).data('service');
            var match = selected.indexOf(service) > -1;
            $(this).toggle(match);
            if (match) shown = true;
        });
        $container.toggleClass('d-none', !shown);
    }

    $(document).on('change', '.service-card-grid input[name="service_type[]"]', refreshServiceSuggestions);
    refreshServiceSuggestions();

    // ===== VEHICLE AUTOSUGGEST =====
    var $vehInput = $('#vehicle_description');
    var $suggestions = $('#vehicle-suggestions');
    
    if (customerVehiclesData.length > 0) {
        // Show suggestions as user types
        $vehInput.on('input focus', function() {
            var val = $(this).val().toLowerCase().trim();
            
            if (!val) {
                // Show all vehicles if input is empty (focus only)
                if (document.activeElement === this && customerVehiclesData.length <= 10) {
                    renderSuggestions(customerVehiclesData);
                } else {
                    $suggestions.hide();
                }
                return;
            }
            
            var matches = customerVehiclesData.filter(function(v) {
                return v.label.toLowerCase().indexOf(val) > -1
                    || v.make.toLowerCase().indexOf(val) > -1
                    || v.model.toLowerCase().indexOf(val) > -1
                    || (v.license_plate && v.license_plate.toLowerCase().indexOf(val) > -1);
            });
            
            if (matches.length > 0) {
                renderSuggestions(matches);
            } else {
                // Clear vehicle_id if typed text doesn't match any vehicle
                if ($('#vehicle_id').val()) {
                    $('#vehicle_id').val('');
                }
                $suggestions.hide();
            }
        });
        
        function renderSuggestions(vehicles) {
            $suggestions.empty();
            vehicles.forEach(function(v) {
                var $item = $('<div class="vehicle-suggestion-item"></div>');
                $item.html(
                    '<div class="suggestion-main">' + v.year + ' ' + v.make + ' ' + v.model + '</div>'
                    + (v.license_plate ? '<div class="suggestion-sub">' + v.license_plate + '</div>' : '')
                );
                $item.on('click', function() {
                    fillVehicleDescription(v);
                });
                $suggestions.append($item);
            });
            $suggestions.show();
        }
        
        // Hide on blur (with delay for click to register)
        $vehInput.on('blur', function() {
            setTimeout(function() { $suggestions.hide(); }, 200);
        });
        
        // Auto-fill if a vehicle_id is already set (from URL param)
        var initialVehicleId = parseInt($('#vehicle_id').val());
        if (initialVehicleId) {
            var matched = customerVehiclesData.find(function(v) { return v.id === initialVehicleId; });
            if (matched) {
                fillVehicleDescription(matched);
            }
        }
    }
    
    // ===== VEHICLE PILL HANDLER =====
    // The customer-summary-card onclick tries $('#vehicle_id').trigger('change')
    // Intercept the change event to fill description
    $(document).on('change', '#vehicle_id', function() {
        var vehId = parseInt($(this).val());
        if (!vehId) return;
        var matched = customerVehiclesData.find(function(v) { return v.id === vehId; });
        if (matched) {
            var desc = matched.year + ' ' + matched.make + ' ' + matched.model;
            if (matched.license_plate) {
                desc += ' - ' + matched.license_plate;
            }
            $('#vehicle_description').val(desc);
        }
    });
    
    // Also handle manual typing clearing the vehicle_id link
    $vehInput.on('change', function() {
        // If user typed something completely different, clear hidden vehicle_id
        var val = $(this).val().toLowerCase().trim();
        var currentVehId = parseInt($('#vehicle_id').val());
        if (currentVehId && customerVehiclesData.length > 0) {
            var matched = customerVehiclesData.find(function(v) { return v.id === currentVehId; });
            if (matched && val !== matched.label.toLowerCase().trim()) {
                // Don't clear immediately - let them choose from suggestions
            }
        }
    });

    // ===== CUSTOMER SEARCH -> HIDDEN ID SYNC =====
    var $customerSearch = $('#customer_search');
    var $customerId = $('#customer_id');
    var $manualPanel = $('#manual-add-panel');
    var $addNewBtn = $('#add-new-customer-btn');

    if ($customerSearch.length) {
        // Remember which full names map to which customer ids
        var customerIdMap = {};
        $('#customer-list option').each(function() {
            customerIdMap[this.value.trim().toLowerCase()] = $(this).data('customer-id');
        });

        function showManualPanel(show) {
            if (show) {
                $manualPanel.removeClass('d-none');
                $addNewBtn.addClass('active');
                $addNewBtn.html('<i class="fas fa-user-plus me-1"></i> Cancel New Customer');
                $('#customer_selection_mode').val('manual');
                $customerId.val('');
            } else {
                $manualPanel.addClass('d-none');
                $addNewBtn.removeClass('active');
                $addNewBtn.html('<i class="fas fa-user-plus me-1"></i> Add New Customer');
                $('#customer_selection_mode').val('existing');
            }
        }

        // Toggle button: explicit "Add New Customer" option (search-first UX)
        $addNewBtn.on('click', function(e) {
            e.preventDefault();
            var showing = !$manualPanel.hasClass('d-none');
            showManualPanel(!showing);
        });

        // When a customer is picked from the datalist (or matches exactly), set the hidden id
        $customerSearch.on('change input', function() {
            var typed = $(this).val().trim().toLowerCase();
            var matchedId = customerIdMap[typed];
            if (matchedId) {
                $customerId.val(matchedId);
                $('#customer_selection_mode').val('existing');
                showManualPanel(false); // picking existing customer hides manual panel
            } else if ($(this).val() === '') {
                $customerId.val('');
                showManualPanel(false);
            }
            // No match + non-empty text => new customer; keep customer_id empty
            // so the backend falls back to manual-add (client_name) mode.
        });
    }

    // ===== BRAND/MODEL DEPENDENT DATALIST + VISIBLE DROPDOWNS =====
    var modelsByBrand = {!! json_encode($modelsByBrand ?? (object)[]) !!};
    var $brandInput = $('#vehicle_brand');
    var $modelInput = $('#vehicle_model');
    var $modelList = $('#model-list');
    var $brandSuggestions = $('#brand-suggestions');
    var $modelSuggestions = $('#model-suggestions');

    function updateModelDatalist() {
        if (!$modelInput.length) return;
        var brand = ($brandInput.val() || '').trim().toLowerCase();
        var models = [];
        // Normalize brand keys for case-insensitive lookup
        Object.keys(modelsByBrand).forEach(function(key) {
            if (key.toLowerCase() === brand) {
                models = modelsByBrand[key];
            }
        });
        // Also match if user typed a prefix of a known brand
        if (!models.length && brand) {
            Object.keys(modelsByBrand).forEach(function(key) {
                if (key.toLowerCase().indexOf(brand) > -1) {
                    models = modelsByBrand[key];
                }
            });
        }
        $modelList.empty();
        (models || []).forEach(function(m) {
            $modelList.append($('<option>').attr('value', m));
        });
        return models || [];
    }

    function showSuggestions($dd, items) {
        if (!$dd.length) return;
        $dd.empty();
        (items || []).slice(0, 50).forEach(function(v) {
            var $item = $('<div class="vehicle-suggestion-item" data-value="' + v + '"></div>').text(v);
            $item.on('mousedown', function(e) {
                e.preventDefault();
                $dd.hide();
                var $input = $dd.is($brandSuggestions) ? $brandInput : $modelInput;
                $input.val(v).trigger('input');
            });
            $dd.append($item);
        });
        $dd.show();
    }

    function filterBrandSuggestions() {
        var term = ($brandInput.val() || '').trim().toLowerCase();
        var items = $brandSuggestions.find('.vehicle-suggestion-item').map(function() {
            return $(this).attr('data-value');
        }).get().filter(function(b) {
            return !term || b.toLowerCase().indexOf(term) > -1;
        });
        showSuggestions($brandSuggestions, items);
    }

    function filterModelSuggestions() {
        var term = ($modelInput.val() || '').trim().toLowerCase();
        var all = updateModelDatalist() || [];
        var items = all.filter(function(m) {
            return !term || m.toLowerCase().indexOf(term) > -1;
        });
        showSuggestions($modelSuggestions, items);
    }

    if ($brandInput.length) {
        $brandInput.on('focus input', filterBrandSuggestions);
        $brandInput.on('blur', function() { setTimeout(function() { $brandSuggestions.hide(); }, 200); });
        $brandInput.on('input change', updateModelDatalist);
    }
    if ($modelInput.length) {
        $modelInput.on('focus input', filterModelSuggestions);
        $modelInput.on('blur', function() { setTimeout(function() { $modelSuggestions.hide(); }, 200); });
    }

    updateModelDatalist(); // populate on load (e.g. edit with pre-filled brand)
});
</script>

<style>
.vehicle-suggestions-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 999;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 0 0 6px 6px;
    max-height: 200px;
    overflow-y: auto;
    box-shadow: 0 4px 12px rgba(0,0,0,0.12);
}
.vehicle-suggestion-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.15s;
}
.vehicle-suggestion-item:last-child {
    border-bottom: none;
}
.vehicle-suggestion-item:hover {
    background: #e8f4fd;
}
.suggestion-main {
    font-size: 0.85rem;
    font-weight: 500;
    color: #1a1a2e;
}
.suggestion-sub {
    font-size: 0.75rem;
    color: #6c757d;
    margin-top: 1px;
}

[data-theme="dark"] .vehicle-suggestions-dropdown {
    background: var(--dark-card);
    border-color: var(--dark-border);
    box-shadow: 0 4px 12px rgba(0,0,0,0.45);
}

[data-theme="dark"] .vehicle-suggestion-item {
    border-bottom-color: var(--dark-border);
}

[data-theme="dark"] .vehicle-suggestion-item:hover {
    background: var(--dark-hover);
}

[data-theme="dark"] .suggestion-main {
    color: var(--dark-text);
}

[data-theme="dark"] .suggestion-sub {
    color: var(--dark-text-secondary);
}
</style>
@endsection
