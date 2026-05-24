<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration - Fix-It Auto Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- jQuery UI CSS for autocomplete -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css">
    <style>
        :root {
            --bg-primary: #f8f9fa;
            --bg-card: #ffffff;
            --bg-header: linear-gradient(135deg, #2c3e50, #3498db);
            --text-primary: #333333;
            --text-secondary: #6c757d;
            --border-color: #dee2e6;
            --input-bg: #ffffff;
            --input-text: #495057;
            --card-shadow: 0 2px 15px rgba(0,0,0,0.08);
            --section-title-color: #2c3e50;
        }

        body.dark-mode {
            --bg-primary: #1a1a2e;
            --bg-card: #16213e;
            --bg-header: linear-gradient(135deg, #0f3460, #533483);
            --text-primary: #e8e8e8;
            --text-secondary: #a0a0b0;
            --border-color: #2a2a4a;
            --input-bg: #1e2a4a;
            --input-text: #d0d0e0;
            --card-shadow: 0 2px 15px rgba(0,0,0,0.3);
            --section-title-color: #8ab4f8;
        }

        body {
            background-color: var(--bg-primary);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            background: var(--bg-card);
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }
        .form-header {
            background: var(--bg-header);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        .form-header h1 {
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }
        .form-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        .form-body {
            padding: 2rem;
        }
        .section-title {
            color: var(--section-title-color);
            border-bottom: 2px solid #3498db;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        .form-label {
            font-weight: 500;
            color: var(--text-primary);
        }
        .required::after {
            content: " *";
            color: #e74c3c;
        }
        .btn-submit {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            border: none;
            padding: 0.75rem 2rem;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        }
        .alert-info {
            background-color: #e8f4fc;
            border-color: #3498db;
            color: #2c3e50;
        }
        body.dark-mode .alert-info {
            background-color: #1a2a4a;
            border-color: #4a7ab5;
            color: #c0d4e8;
        }
        .form-footer {
            background-color: var(--bg-card);
            padding: 1.5rem;
            text-align: center;
            border-top: 1px solid var(--border-color);
            color: var(--text-secondary);
            font-size: 0.9rem;
            transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease;
        }
        .logo {
            max-width: 180px;
            margin-bottom: 1rem;
        }
        .loading-spinner {
            display: none;
        }
        .success-message {
            display: none;
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            padding: 2rem;
            border-radius: 8px;
            text-align: center;
        }
        .error-message {
            display: none;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        /* ====== Dark Mode Overrides ====== */
        body.dark-mode .card {
            background-color: var(--bg-card) !important;
            border-color: var(--border-color) !important;
            color: var(--text-primary);
        }
        body.dark-mode .card-header {
            background-color: #1f2f5e !important;
            border-bottom-color: var(--border-color) !important;
            color: #c8d8f0 !important;
        }
        body.dark-mode .bg-light-subtle {
            background-color: #1f2f5e !important;
        }
        body.dark-mode .form-control {
            background-color: var(--input-bg);
            color: var(--input-text);
            border-color: var(--border-color);
        }
        body.dark-mode .form-control:focus {
            background-color: var(--input-bg);
            color: var(--input-text);
            border-color: #5a7fb5;
            box-shadow: 0 0 0 0.25rem rgba(90, 127, 181, 0.25);
        }
        body.dark-mode .form-control::placeholder {
            color: #7a7a9a;
        }
        body.dark-mode .invalid-feedback {
            color: #ff6b6b;
        }
        body.dark-mode .text-muted {
            color: #a0a0b0 !important;
        }
        body.dark-mode .btn-outline-danger {
            color: #ff6b6b;
            border-color: #ff6b6b;
        }
        body.dark-mode .btn-outline-danger:hover {
            color: #fff;
            background-color: #ff6b6b;
        }
        body.dark-mode .ui-autocomplete {
            background-color: var(--bg-card) !important;
            border-color: var(--border-color) !important;
            color: var(--text-primary) !important;
        }
        body.dark-mode .ui-menu-item {
            border-bottom-color: var(--border-color) !important;
        }
        body.dark-mode .ui-menu-item:hover,
        body.dark-mode .ui-state-focus {
            background-color: #2a3a6a !important;
            color: #e8e8ff !important;
        }
        body.dark-mode .bg-light {
            background-color: #2a3a5a !important;
            color: #d0d0e0 !important;
        }
        body.dark-mode .text-dark {
            color: #d0d0e0 !important;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h1><i class="fas fa-car me-2"></i>Fix-It Auto Services</h1>
                    <p>Customer Registration Form</p>
                    <p class="small">Form expires: {{ $expires_at }}</p>
                </div>
                <button type="button" id="darkModeToggle" class="btn btn-sm btn-light rounded-circle p-2" 
                        style="width: 40px; height: 40px;" title="Toggle Dark Mode">
                    <i class="fas fa-moon"></i>
                </button>
            </div>
        </div>

        <div class="form-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Welcome!</strong> Please fill out this form to register as a customer. All fields marked with * are required.
            </div>

            <div id="errorMessage" class="error-message"></div>
            <div id="successMessage" class="success-message">
                <i class="fas fa-check-circle fa-3x mb-3"></i>
                <h3 id="successTitle">Thank You!</h3>
                <p id="successText">Your information has been submitted successfully.</p>
                <div id="bookingDetails" style="display:none;" class="mt-3 p-3 bg-light text-dark rounded">
                    <p class="mb-1"><strong>Reference Number:</strong> <span id="bookingRef"></span></p>
                    <p class="mb-0"><strong>Scheduled:</strong> <span id="bookingDateTime"></span></p>
                </div>
                <button class="btn btn-light mt-3" onclick="resetForm()">Submit Another Response</button>
            </div>

            <form id="customerForm" onsubmit="submitForm(event)">
                <!-- Customer Information -->
                <div class="mb-4">
                    <h3 class="section-title">
                        <i class="fas fa-user me-2"></i>Customer Information
                    </h3>
                    
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="full_name" class="form-label required">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   placeholder="Enter your full name" required>
                            <div class="invalid-feedback">Please enter your full name.</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   placeholder="your.email@example.com">
                            <div class="invalid-feedback">Please enter a valid email address.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label required">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   placeholder="0912 345 6789" required>
                            <div class="invalid-feedback">Please enter your phone number.</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" 
                                      rows="2" placeholder="Enter your complete address"></textarea>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="facebook_profile" class="form-label">Facebook Profile URL</label>
                            <input type="url" class="form-control" id="facebook_profile" name="facebook_profile" 
                                   placeholder="https://facebook.com/yourprofile">
                            <div class="invalid-feedback">Please enter a valid URL.</div>
                        </div>
                    </div>
                </div>

                <!-- Vehicle Information (Optional) -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3 class="section-title mb-0">
                            <i class="fas fa-car me-2"></i>Vehicle Information (Optional)
                        </h3>
                        <button type="button" class="btn btn-sm btn-success" id="addVehicleBtn">
                            <i class="fas fa-plus-circle me-1"></i> Add Another Vehicle
                        </button>
                    </div>
                    <p class="text-muted mb-3">You can add multiple vehicles now or add them later.</p>

                    <!-- Vehicle Fields Container -->
                    <div id="vehicleFieldsContainer">
                        <!-- First Vehicle (Default) -->
                        <div class="vehicle-section card mb-3" data-vehicle-index="0">
                            <div class="card-header bg-light-subtle py-2 d-flex justify-content-between align-items-center">
                                <span class="fw-medium">Vehicle #1</span>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-vehicle-btn" style="display: none;">
                                    <i class="fas fa-trash-alt me-1"></i> Remove
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Vehicle Brand</label>
                                        <input type="text" class="form-control vehicle-make" name="vehicles[0][make]" 
                                               placeholder="e.g., Toyota" autocomplete="off">
                                        <div class="invalid-feedback vehicle-make-error" style="display: none;"></div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Vehicle Model</label>
                                        <input type="text" class="form-control vehicle-model" name="vehicles[0][model]" 
                                               placeholder="e.g., Camry" autocomplete="off">
                                        <div class="invalid-feedback vehicle-model-error" style="display: none;"></div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Year</label>
                                        <input type="number" class="form-control vehicle-year" name="vehicles[0][year]" 
                                               min="1900" max="{{ date('Y') + 1 }}" placeholder="e.g., 2023">
                                        <div class="invalid-feedback vehicle-year-error" style="display: none;"></div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">License Plate</label>
                                        <input type="text" class="form-control vehicle-plate" name="vehicles[0][plate]" 
                                               placeholder="e.g., ABC123">
                                        <div class="invalid-feedback vehicle-plate-error" style="display: none;"></div>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Color</label>
                                        <input type="text" class="form-control vehicle-color" name="vehicles[0][color]" 
                                               placeholder="e.g., Red" autocomplete="off">
                                        <div class="invalid-feedback vehicle-color-error" style="display: none;"></div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="form-label">VIN (Vehicle Identification Number)</label>
                                        <input type="text" class="form-control vehicle-vin" name="vehicles[0][vin]" 
                                               placeholder="17-character VIN (optional)" maxlength="17">
                                        <div class="invalid-feedback vehicle-vin-error" style="display: none;"></div>
                                        <small class="form-text text-muted">17-character VIN (optional)</small>
                                    </div>
                                </div>
                            </div> <!-- End of card-body for vehicle #1 -->
                        </div> <!-- End of vehicle-section for vehicle #1 -->
                    </div> <!-- End of vehicleFieldsContainer -->
                </div>

                <!-- Schedule Your Appointment -->
                <div class="mb-4">
                    <h3 class="section-title">
                        <i class="fas fa-calendar-check me-2"></i>Schedule Your Appointment (Optional)
                    </h3>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="book_appointment" name="book_appointment" value="1">
                        <label class="form-check-label" for="book_appointment">
                            <strong>I want to book an appointment</strong>
                        </label>
                    </div>

                    <div id="bookingFields" style="display: none;">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="preferred_date" class="form-label required">Preferred Date</label>
                                <input type="date" class="form-control" id="preferred_date" name="preferred_date" 
                                       min="{{ date('Y-m-d') }}">
                                <small class="form-text text-muted">Select a date for your appointment</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="preferred_time" class="form-label required">Preferred Time</label>
                                <input type="time" class="form-control" id="preferred_time" name="preferred_time">
                                <small class="form-text text-muted">Select a preferred time slot</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="appointment_service_type" class="form-label">Service Type</label>
                                <select class="form-select" id="appointment_service_type" name="appointment_service_type">
                                    <option value="">Select a service type...</option>
                                    <option value="preventive_maintenance">🔧 Preventive Maintenance</option>
                                    <option value="auto_mechanical">⚙️ Auto-Mechanical</option>
                                    <option value="auto_electrical">⚡ Auto-Electrical</option>
                                    <option value="auto_electronics">🔌 Auto-Electronics</option>
                                    <option value="auto_air_conditioning">❄️ Auto Air-Conditioning</option>
                                    <option value="body_repair_painting">🎨 Body Repair and Painting</option>
                                    <option value="auto_parts_sales">🔩 Auto Parts Sales</option>
                                    <option value="home_service_request">🏠 Home Service Request</option>
                                </select>
                                <small class="form-text text-muted">What type of service do you need?</small>
                            </div>
                            <div class="col-md-6 mb-3" id="vehicleSelectionSection">
                                <label class="form-label">Vehicle for Service</label>
                                <div class="p-2 bg-light-subtle border rounded" id="vehicleInfoDisplay">
                                    <small class="text-muted">Fill in vehicle details above to see them listed here</small>
                                </div>
                                <small class="form-text text-muted">The vehicle information you provided above will be used</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="appointment_notes" class="form-label">Additional Notes</label>
                                <textarea class="form-control" id="appointment_notes" name="appointment_notes" 
                                          rows="3" placeholder="Describe the issue or any additional information..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="submit" class="btn btn-submit" id="submitBtn">
                        <i class="fas fa-paper-plane me-2"></i> Submit Information
                    </button>
                    <div class="loading-spinner" id="loadingSpinner">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="ms-2">Submitting...</span>
                    </div>
                </div>
            </form>
        </div>

        <div class="form-footer">
            <p>
                <i class="fas fa-shield-alt me-1"></i>
                Your information is secure and will only be used for service purposes.
            </p>
            <p class="small mb-0">
                &copy; {{ date('Y') }} Fix-It Auto Services. All rights reserved.
            </p>
        </div>
    </div>

    <!-- jQuery and jQuery UI libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    
    <script>
        // ============================================
        // VEHICLE AUTOCOMPLETE FUNCTIONALITY — DB-powered
        // ============================================
        
        var API_BASE = window.location.origin;

        // Initialize autocomplete when document is ready
        $(document).ready(function() {
            // ============================================
            // MULTIPLE VEHICLE MANAGEMENT
            // ============================================
            
            let vehicleCounter = 1; // Start from 1 since we already have vehicle #0
            
            // Add Vehicle Button Click Handler
            $('#addVehicleBtn').on('click', function() {
                addNewVehicleSection();
            });
            
            // Function to add a new vehicle section
            function addNewVehicleSection() {
                const newIndex = vehicleCounter;
                vehicleCounter++;
                
                // Clone the first vehicle section
                const firstVehicleSection = $('.vehicle-section').first();
                const newVehicleSection = firstVehicleSection.clone();
                
                // Update the data attribute and header
                newVehicleSection.attr('data-vehicle-index', newIndex);
                newVehicleSection.find('.card-header span').text('Vehicle #' + (newIndex + 1));
                
                // Update all input names and IDs
                newVehicleSection.find('input.vehicle-make').attr('name', 'vehicles[' + newIndex + '][make]').val('').removeAttr('id');
                newVehicleSection.find('input.vehicle-model').attr('name', 'vehicles[' + newIndex + '][model]').val('').removeAttr('id');
                newVehicleSection.find('input.vehicle-year').attr('name', 'vehicles[' + newIndex + '][year]').val('').removeAttr('id');
                newVehicleSection.find('input.vehicle-plate').attr('name', 'vehicles[' + newIndex + '][plate]').val('').removeAttr('id');
                newVehicleSection.find('input.vehicle-vin').attr('name', 'vehicles[' + newIndex + '][vin]').val('').removeAttr('id');
                newVehicleSection.find('input.vehicle-color').attr('name', 'vehicles[' + newIndex + '][color]').val('').removeAttr('id');
                
                // Clear error messages
                newVehicleSection.find('.invalid-feedback').hide().text('');
                newVehicleSection.find('.form-control').removeClass('is-invalid');
                
                // Show remove button for the new section
                newVehicleSection.find('.remove-vehicle-btn').show();
                
                // Add to container
                $('#vehicleFieldsContainer').append(newVehicleSection);
                
                // Initialize autocomplete for the new vehicle fields
                initializeAutocompleteForVehicle(newIndex);
                
                // Scroll to the new vehicle section
                $('html, body').animate({
                    scrollTop: newVehicleSection.offset().top - 100
                }, 500);
                
                // Update console
                console.log('Added new vehicle section #' + newIndex);
            }
            
            // Remove Vehicle Button Click Handler (delegated event)
            $(document).on('click', '.remove-vehicle-btn', function() {
                const vehicleSection = $(this).closest('.vehicle-section');
                const vehicleIndex = vehicleSection.data('vehicle-index');
                
                // Don't remove the first vehicle
                if (vehicleIndex === 0) {
                    alert('Cannot remove the first vehicle section. You can clear the fields instead.');
                    return;
                }
                
                // Remove the section
                vehicleSection.remove();
                
                // Update console
                console.log('Removed vehicle section #' + vehicleIndex);
                
                // Re-index remaining vehicles if needed
                reindexVehicleSections();
            });
            
            // Function to initialize autocomplete for a specific vehicle
            function initializeAutocompleteForVehicle(index) {
                const makeSelector = '.vehicle-section[data-vehicle-index="' + index + '"] .vehicle-make';
                const modelSelector = '.vehicle-section[data-vehicle-index="' + index + '"] .vehicle-model';
                const colorSelector = '.vehicle-section[data-vehicle-index="' + index + '"] .vehicle-color';
                
                // Initialize autocomplete for Vehicle Brand (API-based)
                $(makeSelector).autocomplete({
                    source: function(request, response) {
                        $.ajax({
                            url: API_BASE + '/api/vehicle-brands',
                            data: { term: request.term },
                            success: function(data) { response(data); },
                            error: function() { response([]); }
                        });
                    },
                    minLength: 0,
                    delay: 150,
                    select: function(event, ui) {
                        // Clear model when brand changes
                        var modelField = $(modelSelector);
                        if (modelField.val()) modelField.val('');
                    }
                }).focus(function() {
                    $(this).autocomplete('search', $(this).val());
                });

                // Initialize autocomplete for Vehicle Model (API-based, filtered by brand)
                $(modelSelector).autocomplete({
                    source: function(request, response) {
                        var selectedBrand = $(makeSelector).val();
                        $.ajax({
                            url: API_BASE + '/api/vehicle-models',
                            data: { term: request.term, brand: selectedBrand },
                            success: function(data) { response(data); },
                            error: function() { response([]); }
                        });
                    },
                    minLength: 0,
                    delay: 150
                }).focus(function() {
                    $(this).autocomplete('search', $(this).val());
                });

                // Initialize autocomplete for Vehicle Color (API-based)
                $(colorSelector).autocomplete({
                    source: function(request, response) {
                        $.ajax({
                            url: API_BASE + '/api/vehicle-colors',
                            data: { term: request.term },
                            success: function(data) { response(data); },
                            error: function() { response([]); }
                        });
                    },
                    minLength: 0,
                    delay: 150
                }).focus(function() {
                    $(this).autocomplete('search', $(this).val());
                });
            }
            
            // Function to re-index vehicle sections after removal
            function reindexVehicleSections() {
                const vehicleSections = $('.vehicle-section');
                vehicleCounter = vehicleSections.length;
                
                vehicleSections.each(function(newIndex) {
                    const section = $(this);
                    const oldIndex = section.data('vehicle-index');
                    
                    if (oldIndex !== newIndex) {
                        // Update data attribute
                        section.attr('data-vehicle-index', newIndex);
                        
                        // Update header
                        section.find('.card-header span').text('Vehicle #' + (newIndex + 1));
                        
                        // Update input names
                        section.find('input.vehicle-make').attr('name', 'vehicles[' + newIndex + '][make]');
                        section.find('input.vehicle-model').attr('name', 'vehicles[' + newIndex + '][model]');
                        section.find('input.vehicle-year').attr('name', 'vehicles[' + newIndex + '][year]');
                        section.find('input.vehicle-plate').attr('name', 'vehicles[' + newIndex + '][plate]');
                        section.find('input.vehicle-vin').attr('name', 'vehicles[' + newIndex + '][vin]');
                        section.find('input.vehicle-color').attr('name', 'vehicles[' + newIndex + '][color]');
                        
                        // Update quick select ID if exists
                        const oldQuickSelectId = 'model-quick-select-' + oldIndex;
                        const newQuickSelectId = 'model-quick-select-' + newIndex;
                        $('#' + oldQuickSelectId).attr('id', newQuickSelectId);
                    }
                });
                
                // Show/hide remove buttons (hide for first vehicle)
                $('.vehicle-section').each(function(index) {
                    const removeBtn = $(this).find('.remove-vehicle-btn');
                    if (index === 0) {
                        removeBtn.hide();
                    } else {
                        removeBtn.show();
                    }
                });
            }
            
            // Initialize autocomplete for the first vehicle (index 0)
            initializeAutocompleteForVehicle(0);
            
            // ============================================
            // APPOINTMENT BOOKING TOGGLE
            // ============================================
            
            // Toggle booking fields visibility
            $('#book_appointment').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#bookingFields').slideDown(300);
                } else {
                    $('#bookingFields').slideUp(300);
                }
            });
            
            // Update vehicle info display whenever vehicle fields change
            function updateVehicleInfoDisplay() {
                var make = $('.vehicle-make').first().val();
                var model = $('.vehicle-model').first().val();
                var year = $('.vehicle-year').first().val();
                var color = $('.vehicle-color').first().val();
                var plate = $('.vehicle-plate').first().val();
                
                var parts = [];
                if (year) parts.push(year);
                if (make) parts.push(make);
                if (model) parts.push(model);
                if (color) parts.push('(' + color + ')');
                if (plate) parts.push('[' + plate + ']');
                
                if (parts.length > 0) {
                    $('#vehicleInfoDisplay').html('<strong>' + parts.join(' ') + '</strong>');
                } else {
                    $('#vehicleInfoDisplay').html('<small class="text-muted">Fill in vehicle details above to see them listed here</small>');
                }
            }
            
            // Listen for vehicle field changes
            $(document).on('input change', '.vehicle-make, .vehicle-model, .vehicle-year, .vehicle-color, .vehicle-plate', function() {
                updateVehicleInfoDisplay();
            });
            
            // Update on autocomplete select
            $(document).on('autocompleteselect', '.vehicle-make, .vehicle-model, .vehicle-color', function() {
                setTimeout(updateVehicleInfoDisplay, 100);
            });
            
            // Add custom CSS for autocomplete
            $('head').append('<style>' +
                '.ui-autocomplete {' +
                '    max-height: 200px;' +
                '    overflow-y: auto;' +
                '    overflow-x: hidden;' +
                '    z-index: 1051 !important;' +
                '    background-color: white;' +
                '    border: 1px solid #ddd;' +
                '    border-radius: 4px;' +
                '    box-shadow: 0 2px 10px rgba(0,0,0,0.1);' +
                '}' +
                '.ui-menu-item {' +
                '    padding: 8px 12px;' +
                '    cursor: pointer;' +
                '    border-bottom: 1px solid #f5f5f5;' +
                '}' +
                '.ui-menu-item:last-child {' +
                '    border-bottom: none;' +
                '}' +
                '.ui-menu-item:hover,' +
                '.ui-state-focus {' +
                '    background-color: #f8f9fa;' +
                '    color: #495057;' +
                '}' +
                '.ui-helper-hidden-accessible {' +
                '    display: none;' +
                '}' +
                '#model-quick-select .badge:hover {' +
                '    background-color: #0d6efd !important;' +
                '    color: white !important;' +
                '}' +
                '</style>');
        });

        function submitForm(event) {
            event.preventDefault();
            
            // Show loading spinner
            document.getElementById('submitBtn').style.display = 'none';
            document.getElementById('loadingSpinner').style.display = 'flex';
            document.getElementById('errorMessage').style.display = 'none';
            
            // Get form data
            const form = document.getElementById('customerForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            
            // Ensure book_appointment is included even when unchecked
            data.book_appointment = document.getElementById('book_appointment').checked ? '1' : '0';
            
            // Submit via AJAX
            fetch('{{ route("customers.form.submit", ["token" => $token]) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    // Show success message
                    document.getElementById('customerForm').style.display = 'none';
                    document.getElementById('successMessage').style.display = 'block';
                    document.getElementById('successText').textContent = result.message;
                    
                    // Show booking details if appointment was made
                    if (result.booking_made && result.booking_reference) {
                        document.getElementById('successTitle').textContent = 'Appointment Booked!';
                        document.getElementById('bookingRef').textContent = result.booking_reference;
                        document.getElementById('bookingDetails').style.display = 'block';
                    }
                } else {
                    // Show error message
                    document.getElementById('submitBtn').style.display = 'block';
                    document.getElementById('loadingSpinner').style.display = 'none';
                    
                    let errorHtml = '<i class="fas fa-exclamation-triangle me-2"></i><strong>Error:</strong> ';
                    if (result.errors) {
                        errorHtml += Object.values(result.errors).flat().join('<br>');
                    } else {
                        errorHtml += result.message;
                    }
                    document.getElementById('errorMessage').innerHTML = errorHtml;
                    document.getElementById('errorMessage').style.display = 'block';
                    
                    // Scroll to error
                    document.getElementById('errorMessage').scrollIntoView({ behavior: 'smooth' });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('submitBtn').style.display = 'block';
                document.getElementById('loadingSpinner').style.display = 'none';
                
                document.getElementById('errorMessage').innerHTML = 
                    '<i class="fas fa-exclamation-triangle me-2"></i><strong>Error:</strong> Network error. Please try again.';
                document.getElementById('errorMessage').style.display = 'block';
            });
        }
        
        function resetForm() {
            document.getElementById('customerForm').reset();
            document.getElementById('customerForm').style.display = 'block';
            document.getElementById('successMessage').style.display = 'none';
            document.getElementById('submitBtn').style.display = 'block';
            document.getElementById('loadingSpinner').style.display = 'none';
            document.getElementById('errorMessage').style.display = 'none';
        }
        
        // Form validation
        document.getElementById('customerForm').addEventListener('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            this.classList.add('was-validated');
        });
    </script>
</body>
</html>