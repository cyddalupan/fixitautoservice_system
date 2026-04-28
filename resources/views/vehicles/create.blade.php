@extends('layouts.app')

@section('title', 'Add New Vehicle')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.13.2/themes/base/jquery-ui.min.css">
<style>
    /* Professional autocomplete styling */
    .ui-autocomplete {
        max-height: 300px;
        overflow-y: auto;
        overflow-x: hidden;
        background: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        padding: 4px 0;
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif;
        font-size: 14px;
    }
    .ui-autocomplete .ui-menu-item {
        padding: 8px 16px;
        border: none;
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .ui-autocomplete .ui-menu-item.ui-state-focus,
    .ui-autocomplete .ui-menu-item:hover {
        background: #eef2ff;
        color: #1e40af;
        border: none;
        font-weight: 500;
        margin: 0;
    }
    .ui-autocomplete .ui-menu-item-wrapper {
        padding: 4px 0;
        border: none;
        background: transparent;
    }
    .ui-autocomplete .ui-menu-item-wrapper.ui-state-active {
        background: #eef2ff;
        color: #1e40af;
        border: none;
        margin: 0;
    }
    .ui-helper-hidden-accessible {
        display: none;
    }
    /* Loading indicator inside autocomplete */
    .ui-autocomplete-loading {
        background: white url('https://cdnjs.cloudflare.com/ajax/libs/galleriffic/2.0.1/css/loader.gif') no-repeat right center;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-car me-2"></i>Add New Vehicle
            </h1>
            <p class="text-muted mb-0">Register a new vehicle for a customer</p>
        </div>
        <div>
            <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('vehicles.store') }}" enctype="multipart/form-data">
                    @csrf

                    <!-- Customer Selection -->
                    <div class="mb-3">
                        <label class="form-label required">Customer</label>
                        
                        @if($selectedCustomer)
                            <!-- Show customer as permanent text -->
                            <div class="form-control bg-light">
                                <strong>{{ $selectedCustomer->first_name }} {{ $selectedCustomer->last_name }}</strong>
                                @if($selectedCustomer->email)
                                    ({{ $selectedCustomer->email }})
                                @endif
                                @if($selectedCustomer->phone)
                                    - {{ $selectedCustomer->phone }}
                                @endif
                            </div>
                            <input type="hidden" name="customer_id" value="{{ $selectedCustomerId }}">
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Adding vehicle for this customer. To change customer, go back to customer page.
                            </small>
                        @else
                            <!-- Show dropdown when no customer pre-selected -->
                            <select class="form-select @error('customer_id') is-invalid @enderror" 
                                    id="customer_id" name="customer_id" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" 
                                            {{ old('customer_id', $selectedCustomerId) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->first_name }} {{ $customer->last_name }}
                                        @if($customer->email)
                                            ({{ $customer->email }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        
                        @error('customer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Vehicle Details -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="make" class="form-label required">Brand</label>
                            <input type="text" class="form-control @error('make') is-invalid @enderror" 
                                   id="make" name="make" value="{{ old('make') }}" 
                                   placeholder="e.g., Toyota" autocomplete="off" required>
                            @error('make')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="model" class="form-label required">Model</label>
                            <input type="text" class="form-control @error('model') is-invalid @enderror" 
                                   id="model" name="model" value="{{ old('model') }}" 
                                   placeholder="e.g., Camry" autocomplete="off" required>
                            @error('model')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="year" class="form-label required">Year</label>
                            <input type="number" class="form-control @error('year') is-invalid @enderror" 
                                   id="year" name="year" value="{{ old('year') }}" 
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
                                   id="license_plate" name="license_plate" value="{{ old('license_plate') }}" 
                                   placeholder="e.g., ABC123">
                            @error('license_plate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="vin" class="form-label">VIN (Vehicle Identification Number)</label>
                            <input type="text" class="form-control @error('vin') is-invalid @enderror" 
                                   id="vin" name="vin" value="{{ old('vin') }}" 
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
                                   id="color" name="color" value="{{ old('color') }}" 
                                   placeholder="e.g., Red" autocomplete="off">
                            <div id="color-swatch" style="display:none;margin-top:4px;width:100%;height:24px;border-radius:4px;border:1px solid #ccc;"></div>
                            @error('color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="vehicle_type" class="form-label">Vehicle Type</label>
                            <select class="form-select @error('vehicle_type') is-invalid @enderror" 
                                    id="vehicle_type" name="vehicle_type">
                                <option value="">Select Type (Default: Car)</option>
                                <option value="car" {{ old('vehicle_type') == 'car' ? 'selected' : '' }}>Car</option>
                                <option value="truck" {{ old('vehicle_type') == 'truck' ? 'selected' : '' }}>Truck</option>
                                <option value="suv" {{ old('vehicle_type') == 'suv' ? 'selected' : '' }}>SUV</option>
                                <option value="van" {{ old('vehicle_type') == 'van' ? 'selected' : '' }}>Van</option>
                                <option value="motorcycle" {{ old('vehicle_type') == 'motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                                <option value="commercial" {{ old('vehicle_type') == 'commercial' ? 'selected' : '' }}>Commercial</option>
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
                                </small>
                            </div>
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            
                            <!-- Photo Preview -->
                            <div id="photo-preview" class="mt-3 d-none">
                                <div class="card">
                                    <div class="card-body p-2">
                                        <div class="d-flex align-items-center">
                                            <img id="photo-preview-image" src="#" alt="Preview" 
                                                 class="img-thumbnail me-3" style="max-width: 100px; max-height: 100px;">
                                            <div>
                                                <p class="mb-1"><strong>Selected Photo:</strong></p>
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
                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i> Save Vehicle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Help Card -->
    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Adding Vehicle Tips
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <h6 class="alert-heading">
                        <i class="fas fa-lightbulb me-2"></i>Brand & Model Autocomplete
                    </h6>
                    <p class="mb-0 small">
                        The system will automatically suggest existing brands and models as you type.
                        If you enter a new brand/model combination, it will be added to the system
                        for future suggestions.
                    </p>
                </div>
                
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>VIN:</strong> Optional but recommended for accurate identification
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>License Plate:</strong> Required for service records
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Photo:</strong> Helps identify the vehicle during service
                    </li>
                    <li>
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <strong>Vehicle Type:</strong> Helps with service recommendations
                    </li>
                </ul>
            </div>
        </div>
        
        <!-- Recent Vehicles Card -->
        <div class="card mt-3">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fas fa-history me-2"></i>Recently Added Vehicles
                </h6>
            </div>
            <div class="card-body">
                @php
                    $recentVehicles = \App\Models\Vehicle::with('customer')
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();
                @endphp
                
                @if($recentVehicles->count() > 0)
                    <div class="list-group list-group-flush">
                        @foreach($recentVehicles as $recentVehicle)
                            <div class="list-group-item px-0 py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">{{ $recentVehicle->make }} {{ $recentVehicle->model }}</h6>
                                        <small class="text-muted">
                                            {{ $recentVehicle->year }} • {{ $recentVehicle->license_plate ?? 'No Plate' }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted">
                                            {{ $recentVehicle->customer->first_name }} {{ $recentVehicle->customer->last_name }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mb-0 text-center">
                        <i class="fas fa-car fa-2x mb-2"></i><br>
                        No vehicles added yet
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Photo upload preview
        $('#photo').on('change', function() {
            var file = this.files[0];
            var preview = $('#photo-preview');
            var previewImage = $('#photo-preview-image');
            var fileName = $('#photo-file-name');
            var fileSize = $('#photo-file-size');
            
            if (file) {
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

        // ----- Vehicle Brand & Model Autocomplete (DB-backed via API) -----
        var brandApiUrl = '{{ route("api.vehicle-brands") }}';
        var modelApiUrl = '{{ route("api.vehicle-models") }}';

        // Cache for brand list (to avoid multiple API calls)
        var brandsCache = [];
        var brandsLoaded = false;

        function loadBrands(callback) {
            if (brandsLoaded && brandsCache.length > 0) {
                callback(brandsCache);
                return;
            }
            $.getJSON(brandApiUrl, { term: '' }, function(data) {
                brandsCache = data || [];
                brandsLoaded = true;
                callback(brandsCache);
            }).fail(function() {
                callback([]);
            });
        }

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
                // When a brand is selected, re-fetch models for this brand
                $('#model').val('').autocomplete('option', 'source', function(req, resp) {
                    $.getJSON(modelApiUrl, { term: req.term, brand: ui.item.value }, function(data) {
                        resp(data || []);
                    }).fail(function() {
                        resp([]);
                    });
                });
            }
        }).focus(function() {
            $(this).autocomplete('search', $(this).val());
        });

        // Initialize autocomplete for Vehicle Model (DB-backed, filtered by brand)
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
            // Force model autocomplete to re-query with current brand
            var modelField = $('#model');
            var currentSource = modelField.autocomplete('option', 'source');
            modelField.autocomplete('option', 'source', function(request, response) {
                $.getJSON(modelApiUrl, { term: request.term, brand: brand }, function(data) {
                    response(data || []);
                }).fail(function() {
                    response([]);
                });
            });
        });

        console.log('Vehicle form brand/model autocomplete loaded (DB-backed via API)');

        // ----- Vehicle Color Autocomplete (DB-backed) -----
        var colorApiUrl = '{{ route("api.vehicle-colors") }}';

        $('#color').autocomplete({
            source: function(request, response) {
                $.getJSON(colorApiUrl, { term: request.term }, function(data) {
                    response(data || []);
                }).fail(function() {
                    response([]);
                });
            },
            minLength: 0,
            delay: 150,
            select: function(event, ui) {
                setTimeout(function() {
                    updateColorSwatch(ui.item.value);
                }, 50);
            }
        }).focus(function() {
            $(this).autocomplete('search', $(this).val());
        });

        // When color is typed, also lookup for swatch
        $('#color').on('keyup change', function() {
            var val = $(this).val();
            if (val.length >= 2) {
                updateColorSwatch(val);
            } else {
                $('#color-swatch').hide();
            }
        });

        function updateColorSwatch(colorName) {
            // Try to find the color in the DB by calling API with exact match
            $.getJSON(colorApiUrl, { term: colorName }, function(data) {
                if (data && data.length > 0 && data[0].toLowerCase() === colorName.toLowerCase()) {
                    // We got a match, but we need hex code — let's fetch from the colors endpoint
                    $.getJSON('{{ route("vehicle-colors.index") }}?partial=1', function(colors) {
                        if (colors && colors.length) {
                            for (var i = 0; i < colors.length; i++) {
                                if (colors[i].name.toLowerCase() === colorName.toLowerCase() && colors[i].hex_code) {
                                    $('#color-swatch').css('background', colors[i].hex_code).show();
                                    return;
                                }
                            }
                        }
                        $('#color-swatch').hide();
                    }).fail(function() {
                        $('#color-swatch').hide();
                    });
                } else {
                    $('#color-swatch').hide();
                }
            }).fail(function() {
                $('#color-swatch').hide();
            });
        }

        console.log('Vehicle form color autocomplete loaded (DB-backed)');
    });
</script>
@endpush
@endsection
