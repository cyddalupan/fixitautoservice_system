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

        // ----- Vehicle Brand & Model Autocomplete (DB-backed via API) -----
        var brandApiUrl = '{{ route("api.vehicle-brands") }}';
        var modelApiUrl = '{{ route("api.vehicle-models") }}';

        // Common car colors
        const commonColors = [
            'White', 'Black', 'Gray', 'Silver', 'Red', 'Blue', 'Green', 'Yellow',
            'Orange', 'Purple', 'Brown', 'Beige', 'Gold', 'Bronze', 'Maroon',
            'Navy Blue', 'Teal', 'Turquoise', 'Pink', 'Burgundy', 'Charcoal',
            'Champagne', 'Cream', 'Ivory', 'Pearl White', 'Metallic Gray',
            'Midnight Black', 'Royal Blue', 'Forest Green', 'Sunset Orange'
        ];

        // Initialize autocomplete for Vehicle Brand (DB-backed)
        $('#make').autocomplete({
            source: function(request, response) {
                $.getJSON(brandApiUrl, { term: request.term }, function(data) {
                    response(data || []);
                }).fail(function() {
                    response([]);
                });
            },
            minLength: 0,
            delay: 150,
            select: function(event, ui) {
                // When a brand is selected, update model source for this brand
                $('#model').autocomplete('option', 'source', function(req, resp) {
                    $.getJSON(modelApiUrl, { term: req.term, brand: ui.item.value }, function(data) {
                        resp(data || []);
                    }).fail(function() {
                        resp([]);
                    });
                });
                updateModelSuggestions(ui.item.value);
            }
        }).focus(function() {
            $(this).autocomplete('search', $(this).val());
        });

        // Initialize autocomplete for Vehicle Model (DB-backed)
        $('#model').autocomplete({
            source: function(request, response) {
                var selectedBrand = $('#make').val();
                if (selectedBrand) {
                    $.getJSON(modelApiUrl, { term: request.term, brand: selectedBrand }, function(data) {
                        response(data || []);
                    }).fail(function() {
                        response([]);
                    });
                } else {
                    response([]);
                }
            },
            minLength: 0,
            delay: 150
        }).focus(function() {
            var selectedBrand = $('#make').val();
            if (selectedBrand) {
                $(this).autocomplete('search', $(this).val());
            }
        });

        // If brand changes via typing (not selection), update model source
        $('#make').on('keyup change', function() {
            var brand = $(this).val();
            $('#model').autocomplete('option', 'source', function(request, response) {
                $.getJSON(modelApiUrl, { term: request.term, brand: brand }, function(data) {
                    response(data || []);
                }).fail(function() {
                    response([]);
                });
            });
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

        // Function to update model quick-select badges based on selected brand (DB-backed)
        function updateModelSuggestions(brand) {
            var quickSelect = $('#model-quick-select');
            
            if (brand) {
                // Fetch top models for this brand from API
                $.getJSON(modelApiUrl, { term: '', brand: brand }, function(data) {
                    var models = data || [];
                    var commonModels = models.slice(0, 5);
                    
                    var qs = $('#model-quick-select');
                    if (qs.length === 0) {
                        qs = $('<div id="model-quick-select" class="mt-2"></div>');
                        $('#model').after(qs);
                    }
                    
                    qs.html('<small class="text-muted">Common ' + brand + ' models: </small>');
                    $.each(commonModels, function(i, model) {
                        qs.append('<span class="badge bg-light text-dark me-1 mb-1 cursor-pointer" style="cursor: pointer;" onclick="$(\'#model\').val(\'' + model.replace(/'/g, "\\'") + '\');">' + model + '</span> ');
                    });
                }).fail(function() {
                    quickSelect.remove();
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