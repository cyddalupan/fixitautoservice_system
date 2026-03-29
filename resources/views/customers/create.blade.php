@extends('layouts.app')

@section('title', 'Add New Customer - Fix-It Auto Services')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-user-plus me-2"></i>Add New Customer
            </h1>
            <p class="text-muted mb-0">Create a new customer profile</p>
        </div>
        <div>
            <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Customers
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('customers.store') }}">
                    @csrf
                    
                    <div class="row">
                        <!-- Required Information -->
                        <div class="col-md-6">
                            <h5 class="mb-3 border-bottom pb-2">Required Information</h5>
                            
                            <div class="form-group mb-3">
                                <label for="full_name" class="form-label">Full Name *</label>
                                <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                                       id="full_name" name="full_name" value="{{ old('full_name') }}" required>
                                @error('full_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="phone" class="form-label">Mobile Number *</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone') }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                            </div>
                            
                        </div>
                        
                        <!-- Optional Information -->
                        <div class="col-md-6">
                            <h5 class="mb-3 border-bottom pb-2">Optional Information</h5>
                            
                            <div class="form-group mb-3">
                                <label for="facebook_profile" class="form-label">Facebook Profile / Messenger</label>
                                <input type="text" class="form-control @error('facebook_profile') is-invalid @enderror" 
                                       id="facebook_profile" name="facebook_profile" value="{{ old('facebook_profile') }}">
                                @error('facebook_profile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                                <small class="form-text text-muted">Facebook profile URL or Messenger username</small>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                       id="address" name="address" value="{{ old('address') }}">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                            </div>
                            
                            <!-- Vehicle Information - Add Vehicle Immediately -->
                            <div class="card mb-4">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-0">
                                            <i class="fas fa-car me-2"></i>Vehicle Information
                                        </h6>
                                        <small class="text-muted">Add vehicles now (optional)</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-success" id="addVehicleBtn">
                                        <i class="fas fa-plus-circle me-1"></i> Add Another Vehicle
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Optional:</strong> You can add vehicles now or add them later in the customer profile.
                                    </div>
                                    
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
                                    
                                    <div class="form-group mb-3">
                                        <label class="form-label">Vehicle Brand</label>
                                        <input type="text" class="form-control vehicle-make" 
                                               name="vehicles[0][make]" value="{{ old('vehicles.0.make') }}" 
                                               placeholder="e.g., Toyota" autocomplete="off">
                                        <div class="invalid-feedback vehicle-make-error" style="display: none;"></div>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label class="form-label">Vehicle Model</label>
                                        <input type="text" class="form-control vehicle-model" 
                                               name="vehicles[0][model]" value="{{ old('vehicles.0.model') }}" 
                                               placeholder="e.g., Camry" autocomplete="off">
                                        <div class="invalid-feedback vehicle-model-error" style="display: none;"></div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">Year</label>
                                                <input type="number" class="form-control vehicle-year" 
                                                       name="vehicles[0][year]" value="{{ old('vehicles.0.year') }}" 
                                                       min="1900" max="{{ date('Y') + 1 }}" placeholder="e.g., 2023">
                                                <div class="invalid-feedback vehicle-year-error" style="display: none;"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-3">
                                                <label class="form-label">License Plate</label>
                                                <input type="text" class="form-control vehicle-plate" 
                                                       name="vehicles[0][plate]" value="{{ old('vehicles.0.plate') }}" 
                                                       placeholder="e.g., ABC123">
                                                <div class="invalid-feedback vehicle-plate-error" style="display: none;"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label class="form-label">VIN (Vehicle Identification Number)</label>
                                        <input type="text" class="form-control vehicle-vin" 
                                               name="vehicles[0][vin]" value="{{ old('vehicles.0.vin') }}" 
                                               placeholder="17-character VIN (optional)" maxlength="17">
                                        <div class="invalid-feedback vehicle-vin-error" style="display: none;"></div>
                                        <small class="form-text text-muted">17-character VIN (optional)</small>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label class="form-label">Color</label>
                                        <input type="text" class="form-control vehicle-color" 
                                               name="vehicles[0][color]" value="{{ old('vehicles.0.color') }}" 
                                               placeholder="e.g., Red" autocomplete="off">
                                        <div class="invalid-feedback vehicle-color-error" style="display: none;"></div>
                                    </div>
                                    
                                    </div> <!-- End of card-body for vehicle #1 -->
                                        </div> <!-- End of vehicle-section for vehicle #1 -->
                                    </div> <!-- End of vehicleFieldsContainer -->
                                    
                                    <div class="alert alert-light border mt-3">
                                        <i class="fas fa-lightbulb me-2"></i>
                                        <strong>Tip:</strong> You can add multiple vehicles now or add them later in the customer profile.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Customer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Auto-format phone number
        $('#phone').on('input', function() {
            var phone = $(this).val().replace(/\D/g, '');
            if (phone.length > 3 && phone.length <= 6) {
                phone = phone.replace(/(\d{3})(\d+)/, '($1) $2');
            } else if (phone.length > 6) {
                phone = phone.replace(/(\d{3})(\d{3})(\d+)/, '($1) $2-$3');
            }
            $(this).val(phone);
        });

        // Handle form submission with AJAX and SweetAlert2
        $('form').on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var formData = new FormData(this);
            var submitButton = form.find('button[type="submit"]');
            var originalButtonText = submitButton.html();
            
            // Show loading state
            submitButton.prop('disabled', true);
            submitButton.html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
            
            // Submit form via AJAX
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        // Show beautiful success notification
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message,
                            showConfirmButton: true,
                            confirmButtonText: 'View Customer',
                            confirmButtonColor: '#3085d6',
                            allowOutsideClick: false,
                            timer: 5000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Redirect to customer profile
                                window.location.href = response.redirect_url;
                            } else {
                                // Auto-redirect after timer
                                window.location.href = response.redirect_url;
                            }
                        });
                    }
                },
                error: function(xhr) {
                    // Restore button state
                    submitButton.prop('disabled', false);
                    submitButton.html(originalButtonText);
                    
                    if (xhr.status === 422) {
                        // Validation errors
                        var errors = xhr.responseJSON.errors;
                        var errorMessages = '';
                        
                        $.each(errors, function(field, messages) {
                            errorMessages += '<strong>' + field + ':</strong> ' + messages.join(', ') + '<br>';
                        });
                        
                        // Show error notification
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            html: errorMessages,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#d33'
                        });
                    } else {
                        // General error
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while saving the customer. Please try again.',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#d33'
                        });
                    }
                }
            });
        });

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

        // Initialize autocomplete for Vehicle Brand
        $('#vehicle_make').autocomplete({
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
            minLength: 0, // Show suggestions even when clicking/empty
            delay: 100,
            select: function(event, ui) {
                // When a brand is selected, update model suggestions
                updateModelSuggestions(ui.item.value);
            }
        }).focus(function() {
            // Trigger autocomplete when field gets focus
            $(this).autocomplete('search', $(this).val());
        });

        // Initialize autocomplete for Vehicle Model
        $('#vehicle_model').autocomplete({
            source: function(request, response) {
                var term = request.term.toLowerCase();
                var selectedBrand = $('#vehicle_make').val();
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
                    var popularModels = ['Camry', 'Corolla', 'Civic', 'Accord', 'F-150', 'RAV4', 'CR-V'];
                    matches = popularModels;
                }
                
                // Sort alphabetically and return
                matches.sort();
                response(matches.slice(0, 15));
            },
            minLength: 0, // Show suggestions even when clicking/empty
            delay: 100
        }).focus(function() {
            // Trigger autocomplete when field gets focus
            $(this).autocomplete('search', $(this).val());
        });

        // Initialize autocomplete for Vehicle Color
        $('#vehicle_color').autocomplete({
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
            minLength: 0, // Show suggestions even when clicking/empty
            delay: 100
        }).focus(function() {
            // Trigger autocomplete when field gets focus
            $(this).autocomplete('search', $(this).val());
        });

        // Function to update model suggestions based on selected brand
        function updateModelSuggestions(brand) {
            var quickSelect = $('#model-quick-select');
            
            if (brand && carModelsByBrand[brand]) {
                // Pre-populate some common models for quick selection
                var commonModels = carModelsByBrand[brand].slice(0, 5);
                
                if (quickSelect.length === 0) {
                    quickSelect = $('<div id="model-quick-select" class="mt-2"></div>');
                    $('#vehicle_model').after(quickSelect);
                }
                
                quickSelect.html('<small class="text-muted">Common ' + brand + ' models: </small>');
                $.each(commonModels, function(i, model) {
                    quickSelect.append('<span class="badge bg-light text-dark me-1 mb-1 cursor-pointer" style="cursor: pointer;" onclick="$(\'#vehicle_model\').val(\'' + model + '\');">' + model + '</span> ');
                });
            } else {
                quickSelect.remove();
            }
        }

        // Clear quick-select when brand is cleared
        $('#vehicle_make').on('input', function() {
            if (!$(this).val()) {
                $('#model-quick-select').remove();
            }
        });

        // Load jQuery UI CSS for autocomplete styling
        if (!$('link[href*="jquery-ui"]').length) {
            $('head').append('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css">');
        }

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

        // Debug: Check if autocomplete is available
        console.log('jQuery UI autocomplete available:', $.ui && $.ui.autocomplete ? 'YES' : 'NO');
        console.log('Vehicle make element found:', $('#vehicle_make').length > 0);
        console.log('Vehicle model element found:', $('#vehicle_model').length > 0);
        console.log('Vehicle color element found:', $('#vehicle_color').length > 0);
        
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
        
        // Update console
        console.log('Multiple vehicle management initialized');
        console.log('Vehicle sections found:', $('.vehicle-section').length);
    });
</script>
@endpush