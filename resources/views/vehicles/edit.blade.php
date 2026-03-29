@extends('layouts.app')

@section('title', 'Edit Vehicle')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-car me-2"></i>Edit Vehicle
            </h1>
            <p class="text-muted mb-0">{{ $vehicle->make }} {{ $vehicle->model }} {{ $vehicle->year }}</p>
        </div>
        <div>
            <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Vehicle
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('vehicles.update', $vehicle) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <!-- Customer Selection -->
                    <div class="mb-3">
                        <label class="form-label required">Customer</label>
                        
                        <!-- Show vehicle's current customer as permanent text -->
                        <div class="form-control bg-light">
                            <strong>{{ $vehicle->customer->first_name }} {{ $vehicle->customer->last_name }}</strong>
                            @if($vehicle->customer->email)
                                ({{ $vehicle->customer->email }})
                            @endif
                            @if($vehicle->customer->phone)
                                - {{ $vehicle->customer->phone }}
                            @endif
                        </div>
                        <input type="hidden" name="customer_id" value="{{ $vehicle->customer_id }}">
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Vehicle belongs to this customer. Customer cannot be changed.
                        </small>
                        
                        @error('customer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Vehicle Details -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="make" class="form-label required">Brand</label>
                            <input type="text" class="form-control @error('make') is-invalid @enderror" 
                                   id="make" name="make" value="{{ old('make', $vehicle->make) }}" 
                                   placeholder="e.g., Toyota" autocomplete="off" required>
                            @error('make')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="model" class="form-label required">Model</label>
                            <input type="text" class="form-control @error('model') is-invalid @enderror" 
                                   id="model" name="model" value="{{ old('model', $vehicle->model) }}" 
                                   placeholder="e.g., Camry" autocomplete="off" required>
                            @error('model')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="year" class="form-label required">Year</label>
                            <input type="number" class="form-control @error('year') is-invalid @enderror" 
                                   id="year" name="year" value="{{ old('year', $vehicle->year) }}" 
                                   min="1900" max="{{ date('Y') + 1 }}" 
                                   placeholder="e.g., 2023" required>
                            @error('year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Additional Details -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="license_plate" class="form-label">License Plate</label>
                            <input type="text" class="form-control @error('license_plate') is-invalid @enderror" 
                                   id="license_plate" name="license_plate" value="{{ old('license_plate', $vehicle->license_plate) }}" 
                                   placeholder="e.g., ABC123">
                            @error('license_plate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="vin" class="form-label">VIN (Vehicle Identification Number)</label>
                            <input type="text" class="form-control @error('vin') is-invalid @enderror" 
                                   id="vin" name="vin" value="{{ old('vin', $vehicle->vin) }}" 
                                   placeholder="17-character VIN" maxlength="17">
                            @error('vin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Optional Fields -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="color" class="form-label">Color</label>
                            <input type="text" class="form-control @error('color') is-invalid @enderror" 
                                   id="color" name="color" value="{{ old('color', $vehicle->color) }}" 
                                   placeholder="e.g., Red" autocomplete="off">
                            @error('color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="vehicle_type" class="form-label">Vehicle Type</label>
                            <select class="form-select @error('vehicle_type') is-invalid @enderror" 
                                    id="vehicle_type" name="vehicle_type">
                                <option value="">Select Type (Default: Car)</option>
                                <option value="car" {{ old('vehicle_type', $vehicle->vehicle_type) == 'car' ? 'selected' : '' }}>Car</option>
                                <option value="truck" {{ old('vehicle_type', $vehicle->vehicle_type) == 'truck' ? 'selected' : '' }}>Truck</option>
                                <option value="suv" {{ old('vehicle_type', $vehicle->vehicle_type) == 'suv' ? 'selected' : '' }}>SUV</option>
                                <option value="van" {{ old('vehicle_type', $vehicle->vehicle_type) == 'van' ? 'selected' : '' }}>Van</option>
                                <option value="motorcycle" {{ old('vehicle_type', $vehicle->vehicle_type) == 'motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                                <option value="commercial" {{ old('vehicle_type', $vehicle->vehicle_type) == 'commercial' ? 'selected' : '' }}>Commercial</option>
                            </select>
                            @error('vehicle_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Photo Upload -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <label for="photo" class="form-label">Vehicle Photo</label>
                            
                            <!-- Current Photo (if exists) -->
                            @if($vehicle->photo && $vehicle->photo_path)
                                <div class="mb-3">
                                    <p class="mb-2"><strong>Current Photo:</strong></p>
                                    <div class="card">
                                        <div class="card-body p-2">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ Storage::url($vehicle->photo_path) }}" 
                                                     alt="{{ $vehicle->make }} {{ $vehicle->model }}"
                                                     class="img-thumbnail me-3" style="max-width: 100px; max-height: 100px;">
                                                <div>
                                                    <p class="mb-1 text-muted small">{{ $vehicle->photo }}</p>
                                                    <p class="mb-0">
                                                        <a href="{{ Storage::url($vehicle->photo_path) }}" 
                                                           target="_blank" 
                                                           class="text-decoration-none small">
                                                            <i class="fas fa-external-link-alt me-1"></i> View Full Size
                                                        </a>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <small class="text-muted mt-2 d-block">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Upload a new photo below to replace the current one.
                                    </small>
                                </div>
                            @endif
                            
                            <!-- Photo Upload -->
                            <div class="input-group">
                                <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                                       id="photo" name="photo" accept="image/*">
                                <button class="btn btn-outline-secondary" type="button" id="photo-clear">
                                    <i class="fas fa-times"></i> Clear
                                </button>
                            </div>
                            <div class="form-text">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Upload a photo of the vehicle (JPG, PNG, GIF, WebP). Max size: 5MB.
                                    @if($vehicle->photo && $vehicle->photo_path)
                                        Leave empty to keep current photo.
                                    @endif
                                </small>
                            </div>
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <!-- New Photo Preview -->
                            <div id="photo-preview" class="mt-3 d-none">
                                <div class="card">
                                    <div class="card-body p-2">
                                        <div class="d-flex align-items-center">
                                            <img id="photo-preview-image" src="#" alt="Preview" 
                                                 class="img-thumbnail me-3" style="max-width: 100px; max-height: 100px;">
                                            <div>
                                                <p class="mb-1"><strong>New Photo:</strong></p>
                                                <p class="mb-1 text-muted small" id="photo-file-name"></p>
                                                <p class="mb-0 text-muted small" id="photo-file-size"></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Update Vehicle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Help Card -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-info-circle me-2"></i>About Vehicle Registration
                </h6>
            </div>
            <div class="card-body">
                <p class="small text-muted">
                    <strong>Why register vehicles?</strong><br>
                    Vehicle registration helps track service history, schedule maintenance, and provide better customer service.
                </p>
                <p class="small text-muted">
                    <strong>Required Fields:</strong><br>
                    Customer, Make, Model, and Year are required to create a vehicle record.
                </p>
                <p class="small text-muted">
                    <strong>VIN Number:</strong><br>
                    The Vehicle Identification Number is a unique 17-character code that identifies each vehicle.
                </p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
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
        $('#make').autocomplete({
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
        $('#model').autocomplete({
            source: function(request, response) {
                var term = request.term.toLowerCase();
                var selectedBrand = $('#make').val();
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
        $('#color').autocomplete({
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
                    $('#model').after(quickSelect);
                }
                
                quickSelect.html('<small class="text-muted">Common ' + brand + ' models: </small>');
                $.each(commonModels, function(i, model) {
                    quickSelect.append('<span class="badge bg-light text-dark me-1 mb-1 cursor-pointer" style="cursor: pointer;" onclick="$(\'#model\').val(\'' + model + '\');">' + model + '</span> ');
                });
            } else {
                quickSelect.remove();
            }
        }

        // Clear quick-select when brand is cleared
        $('#make').on('input', function() {
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
        console.log('Vehicle make element found:', $('#make').length > 0);
        console.log('Vehicle model element found:', $('#model').length > 0);
        console.log('Vehicle color element found:', $('#color').length > 0);

        // ============================================
        // PHOTO UPLOAD FUNCTIONALITY
        // ============================================

        // Photo preview functionality
        $('#photo').on('change', function() {
            var file = this.files[0];
            var preview = $('#photo-preview');
            var previewImage = $('#photo-preview-image');
            var fileName = $('#photo-file-name');
            var fileSize = $('#photo-file-size');
            
            if (file) {
                // Validate file size (5MB max)
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size exceeds 5MB limit. Please choose a smaller file.');
                    $(this).val('');
                    preview.addClass('d-none');
                    return;
                }
                
                // Validate file type
                var validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    alert('Please select a valid image file (JPG, PNG, GIF, or WebP).');
                    $(this).val('');
                    preview.addClass('d-none');
                    return;
                }
                
                // Show file info
                fileName.text(file.name);
                fileSize.text(formatFileSize(file.size));
                
                // Create preview
                var reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.attr('src', e.target.result);
                    preview.removeClass('d-none');
                }
                reader.readAsDataURL(file);
            } else {
                preview.addClass('d-none');
            }
        });

        // Clear photo button
        $('#photo-clear').on('click', function() {
            $('#photo').val('');
            $('#photo-preview').addClass('d-none');
            $('#photo-preview-image').attr('src', '#');
            $('#photo-file-name').text('');
            $('#photo-file-size').text('');
        });

        // Format file size function
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            var k = 1024;
            var sizes = ['Bytes', 'KB', 'MB', 'GB'];
            var i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // Form validation for file size
        $('form').on('submit', function(e) {
            var fileInput = $('#photo')[0];
            if (fileInput.files.length > 0) {
                var file = fileInput.files[0];
                if (file.size > 5 * 1024 * 1024) {
                    e.preventDefault();
                    alert('File size exceeds 5MB limit. Please choose a smaller file.');
                    return false;
                }
            }
            return true;
        });

        console.log('Photo upload functionality loaded');
    });
</script>
@endpush
@endsection