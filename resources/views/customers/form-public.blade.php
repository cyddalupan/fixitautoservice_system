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
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .form-header {
            background: linear-gradient(135deg, #2c3e50, #3498db);
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
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        .form-label {
            font-weight: 500;
            color: #34495e;
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
        .form-footer {
            background-color: #f8f9fa;
            padding: 1.5rem;
            text-align: center;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 0.9rem;
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
    </style>
</head>
<body>
    <div class="form-container">
        <div class="form-header">
            <h1><i class="fas fa-car me-2"></i>Fix-It Auto Services</h1>
            <p>Customer Registration Form</p>
            <p class="small">Form expires: {{ $expires_at }}</p>
        </div>

        <div class="form-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Welcome!</strong> Please fill out this form to register as a customer. All fields marked with * are required.
            </div>

            <div id="errorMessage" class="error-message"></div>
            <div id="successMessage" class="success-message">
                <i class="fas fa-check-circle fa-3x mb-3"></i>
                <h3>Thank You!</h3>
                <p id="successText">Your information has been submitted successfully.</p>
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
        // VEHICLE AUTOCOMPLETE FUNCTIONALITY
        // ============================================
        
        // Common car brands in the Philippines (static list + database)
        const commonBrands = [
            'Toyota', 'Honda', 'Ford', 'Chevrolet', 'Nissan', 'Hyundai', 'Kia', 
            'Mitsubishi', 'Mazda', 'Subaru', 'Volkswagen', 'BMW', 'Mercedes-Benz',
            'Audi', 'Lexus', 'Isuzu', 'Suzuki', 'Volvo', 'Jeep', 'Dodge', 'Chrysler',
            'Ram', 'GMC', 'Buick', 'Cadillac', 'Acura', 'Infiniti', 'Lincoln',
            'Mini', 'Porsche', 'Land Rover', 'Jaguar', 'Ferrari', 'Lamborghini',
            'Maserati', 'Bentley', 'Rolls-Royce', 'Tesla', 'Fiat', 'Alfa Romeo',
            // Additional brands common in the Philippines
            'Foton', 'JAC', 'Geely', 'MG', 'Peugeot', 'Renault', 'SsangYong',
            'Chery', 'Changan', 'BYD', 'Haima', 'DFSK', 'Mahindra', 'Tata',
            'Proton', 'Great Wall', 'Haval', 'JMC', 'King Long', 'Golden Dragon',
            'Yutong', 'Higer', 'Fuso', 'Hino', 'UD Trucks', 'Scania', 'MAN',
            'Iveco', 'Kenworth', 'Peterbilt', 'Freightliner', 'Mack', 'Volvo Trucks'
        ];

        // Common car colors
        const commonColors = [
            'White', 'Black', 'Gray', 'Silver', 'Red', 'Blue', 'Green', 'Yellow',
            'Orange', 'Purple', 'Brown', 'Beige', 'Gold', 'Bronze', 'Maroon',
            'Navy Blue', 'Teal', 'Turquoise', 'Pink', 'Burgundy', 'Charcoal',
            'Champagne', 'Cream', 'Ivory', 'Pearl White', 'Metallic Gray',
            'Midnight Black', 'Royal Blue', 'Forest Green', 'Sunset Orange'
        ];

        // Car models by brand (common models in the Philippines)
        const carModelsByBrand = {
            'Toyota': ['Vios', 'Wigo', 'Fortuner', 'Hilux', 'Innova', 'Corolla', 'Camry', 'RAV4', 'Land Cruiser', 'Hiace', 'Rush', 'Avanza', 'Yaris', 'Prius', 'C-HR', 'Alphard', 'Vellfire', 'Granvia', 'Coaster', '86', 'Supra', 'GR Yaris', 'GR Corolla', 'Sienna', 'Tacoma', 'Tundra', '4Runner', 'Highlander', 'Sequoia'],
            'Honda': ['City', 'Brio', 'Civic', 'Accord', 'CR-V', 'HR-V', 'BR-V', 'Jazz', 'Mobilio', 'Odyssey', 'Pilot', 'Ridgeline', 'HRV', 'CRV', 'Fit', 'Legend', 'NSX'],
            'Ford': ['Ranger', 'Everest', 'Territory', 'F-150', 'Raptor', 'Wildtrak', 'Explorer', 'Expedition', 'Escape', 'Focus', 'Mustang', 'Fiesta', 'EcoSport', 'Edge', 'Bronco', 'Maverick', 'Transit'],
            'Chevrolet': ['Trailblazer', 'Colorado', 'Captiva', 'Spark', 'Cruze', 'Malibu', 'Tahoe', 'Suburban', 'Silverado', 'Traverse', 'Orlando', 'Aveo', 'Optra', 'Sail', 'Beat'],
            'Nissan': ['Navara', 'Terra', 'Urvan', 'Almera', 'X-Trail', 'Patrol', 'Juke', 'Kicks', 'Leaf', 'Sentra', 'Altima', 'Maxima', '370Z', 'GT-R', 'NV350', 'Livina', 'Grand Livina', 'Serena'],
            'Hyundai': ['Accent', 'Elantra', 'Sonata', 'Tucson', 'Santa Fe', 'Creta', 'Staria', 'Stargazer', 'Ioniq', 'Kona', 'Palisade', 'Venue', 'i10', 'i20', 'i30', 'H-100', 'Grand Starex', 'Starex', 'H350'],
            'Kia': ['Seltos', 'Sportage', 'Sorento', 'Carnival', 'Stonic', 'Rio', 'Forte', 'Optima', 'Soul', 'Telluride', 'Picanto', 'K2500', 'K2700', 'Pride', 'Carens', 'Niro', 'EV6', 'Soul EV'],
            'Mitsubishi': ['Montero Sport', 'Strada', 'Xpander', 'Mirage', 'Mirage G4', 'Lancer', 'Outlander', 'Eclipse Cross', 'Pajero', 'L300', 'Adventure', 'Delica', 'Fuso', 'Canter', 'Xforce'],
            'Mazda': ['CX-5', 'CX-9', 'CX-30', 'CX-8', 'Mazda3', 'Mazda6', 'BT-50', 'MX-5', 'CX-3', 'CX-60', 'CX-90', '2', '5', '8', 'RX-8', 'RX-7'],
            'Subaru': ['Forester', 'Outback', 'XV', 'Impreza', 'Legacy', 'WRX', 'BRZ', 'Levorg', 'Ascent', 'Solterra', 'Crosstrek'],
            'Volkswagen': ['Tiguan', 'Teramont', 'Santana', 'Lavida', 'Polo', 'Golf', 'Passat', 'T-Cross', 'T-Roc', 'Beetle', 'Jetta', 'Touran', 'Sharan', 'Caddy', 'Transporter', 'Amarok'],
            'BMW': ['3 Series', '5 Series', '7 Series', 'X1', 'X3', 'X5', 'X7', 'i3', 'i8', '2 Series', '4 Series', '6 Series', '8 Series', 'X2', 'X4', 'X6', 'i4', 'iX', 'Z4', 'M3', 'M5'],
            'Mercedes-Benz': ['C-Class', 'E-Class', 'S-Class', 'GLA', 'GLC', 'GLE', 'GLS', 'A-Class', 'B-Class', 'CLA', 'CLS', 'G-Class', 'GLB', 'GLC Coupe', 'GLE Coupe', 'AMG GT', 'EQC', 'EQE', 'EQS'],
            'Audi': ['A3', 'A4', 'A6', 'A8', 'Q2', 'Q3', 'Q5', 'Q7', 'Q8', 'TT', 'A1', 'A5', 'A7', 'Q4', 'e-tron', 'e-tron GT', 'RS3', 'RS5', 'RS6', 'RS7', 'R8'],
            'Lexus': ['ES', 'LS', 'RX', 'NX', 'UX', 'LX', 'GX', 'IS', 'RC', 'LC', 'LM', 'UX 300e', 'RZ'],
            'Isuzu': ['D-Max', 'MU-X', 'Crosswind', 'Alterra', 'Hi-Lander', 'N-Series', 'F-Series', 'Giga', 'Forward', 'ELF', 'Journey', 'Sportivo', 'X-Rider'],
            'Suzuki': ['Ertiga', 'Swift', 'Ciaz', 'Vitara', 'Jimny', 'Carry', 'APV', 'Celerio', 'S-Presso', 'XL7', 'Baleno', 'Ignis', 'S-Cross', 'Every', 'Super Carry', 'Katana', 'Burgman'],
            'Volvo': ['XC40', 'XC60', 'XC90', 'S60', 'S90', 'V60', 'V90', 'C40', 'EX30', 'EX90', 'S40', 'V40', 'C30', 'XC70', 'V70', 'S80'],
            // Additional brands common in the Philippines
            'Foton': ['Thunder', 'Gratour', 'Tunland', 'View', 'Blizzard', 'Toplander', 'Midi', 'Sauvana'],
            'JAC': ['S3', 'S5', 'T6', 'T8', 'N56', 'N90', 'X200', 'X500'],
            'Geely': ['Coolray', 'Azkarra', 'Okavango', 'Emgrand', 'Geometry C', 'Geometry A', 'Borui', 'Boyue', 'Xingyue'],
            'MG': ['ZS', 'HS', 'RX5', 'RX8', '5', '6', 'ZS EV', 'HS Plug-in', 'Marvel R'],
            'Peugeot': ['2008', '3008', '5008', '208', '308', '508', 'Partner', 'Expert', 'Traveller'],
            'Renault': ['Koleos', 'Captur', 'Megane', 'Clio', 'Talisman', 'Kadjar', 'Arkana', 'Zoe', 'Twizy'],
            'SsangYong': ['Tivoli', 'Korando', 'Rexton', 'Musso', 'Actyon', 'Rodius', 'Stavic', 'Kyron'],
            'Chery': ['Tiggo', 'Arrizo', 'QQ', 'Fulwin', 'OMODA', 'Jaecoo'],
            'Changan': ['CS35', 'CS55', 'CS75', 'CS85', 'CS95', 'Eado', 'Alsvin', 'Hunter', 'UNI-K', 'UNI-V'],
            'BYD': ['Dolphin', 'Atto 3', 'Han', 'Tang', 'Song', 'Qin', 'Yuan', 'Seal', 'Seagull']
        };

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
                
                // Initialize autocomplete for Vehicle Brand
                $(makeSelector).autocomplete({
                    source: function(request, response) {
                        var term = request.term.toLowerCase();
                        var matches = [];
                        
                        // Check static list first
                        $.each(commonBrands, function(i, brand) {
                            if (brand.toLowerCase().indexOf(term) >= 0) {
                                matches.push(brand);
                            }
                        });
                        
                        // If no term (empty search), show all brands
                        if (!term) {
                            matches = commonBrands.slice(0, 20);
                        }
                        
                        // Sort alphabetically and return
                        matches.sort();
                        response(matches.slice(0, 15));
                    },
                    minLength: 0,
                    delay: 100,
                    select: function(event, ui) {
                        // When a brand is selected, update model suggestions
                        updateModelSuggestionsForVehicle(index, ui.item.value);
                    }
                }).focus(function() {
                    // Trigger autocomplete when field gets focus
                    $(this).autocomplete('search', $(this).val());
                });

                // Initialize autocomplete for Vehicle Model
                $(modelSelector).autocomplete({
                    source: function(request, response) {
                        var term = request.term.toLowerCase();
                        var selectedBrand = $(makeSelector).val();
                        var matches = [];
                        
                        // If a brand is selected, show models for that brand
                        if (selectedBrand && carModelsByBrand[selectedBrand]) {
                            $.each(carModelsByBrand[selectedBrand], function(i, model) {
                                if (!term || model.toLowerCase().indexOf(term) >= 0) {
                                    matches.push(model);
                                }
                            });
                        }
                        
                        // If no term (empty search) and no brand selected, show popular models
                        if (!term && !selectedBrand) {
                            // Show some popular models from common brands
                            var popularModels = ['Vios', 'Wigo', 'Fortuner', 'Hilux', 'City', 'Brio', 'Xpander', 'Montero Sport'];
                            matches = popularModels;
                        }
                        
                        // Sort alphabetically and return
                        matches.sort();
                        response(matches.slice(0, 15));
                    },
                    minLength: 0,
                    delay: 100
                }).focus(function() {
                    // Trigger autocomplete when field gets focus
                    $(this).autocomplete('search', $(this).val());
                });

                // Initialize autocomplete for Vehicle Color
                $(colorSelector).autocomplete({
                    source: function(request, response) {
                        var term = request.term.toLowerCase();
                        var matches = [];
                        
                        // Check static list
                        $.each(commonColors, function(i, color) {
                            if (!term || color.toLowerCase().indexOf(term) >= 0) {
                                matches.push(color);
                            }
                        });
                        
                        // If no term (empty search), show common colors
                        if (!term) {
                            matches = ['Black', 'White', 'Gray', 'Silver', 'Red', 'Blue', 'Green'];
                        }
                        
                        // Sort alphabetically and return
                        matches.sort();
                        response(matches.slice(0, 10));
                    },
                    minLength: 0,
                    delay: 100
                }).focus(function() {
                    // Trigger autocomplete when field gets focus
                    $(this).autocomplete('search', $(this).val());
                });
            }
            
            // Function to update model suggestions for a specific vehicle
            function updateModelSuggestionsForVehicle(index, brand) {
                const modelSelector = '.vehicle-section[data-vehicle-index="' + index + '"] .vehicle-model';
                const quickSelectId = 'model-quick-select-' + index;
                let quickSelect = $('#' + quickSelectId);
                
                if (brand && carModelsByBrand[brand]) {
                    // Pre-populate some common models for quick selection
                    var commonModels = carModelsByBrand[brand].slice(0, 5);
                    
                    if (quickSelect.length === 0) {
                        quickSelect = $('<div id="' + quickSelectId + '" class="mt-2"></div>');
                        $(modelSelector).after(quickSelect);
                    }
                    
                    quickSelect.html('<small class="text-muted">Common ' + brand + ' models: </small>');
                    $.each(commonModels, function(i, model) {
                        quickSelect.append('<span class="badge bg-light text-dark me-1 mb-1 cursor-pointer" style="cursor: pointer;" onclick="$(this).closest(\'.vehicle-section\').find(\'.vehicle-model\').val(\'' + model + '\');">' + model + '</span> ');
                    });
                } else {
                    quickSelect.remove();
                }
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