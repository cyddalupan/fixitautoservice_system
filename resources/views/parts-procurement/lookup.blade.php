@extends('layouts.app')

@section('title', 'Parts Lookup - FixIt Auto Services')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fas fa-search me-2"></i>Parts Lookup
                </h1>
                <div>
                    <a href="{{ route('parts-procurement.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Parts Orders
                    </a>
                </div>
            </div>
            <p class="text-muted mb-0">Find parts by VIN, category, or part number</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-car me-2"></i>VIN-Based Parts Lookup
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('parts-procurement.lookup.post') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vin" class="form-label fw-bold">
                                        <i class="fas fa-barcode me-1"></i>Vehicle VIN
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg @error('vin') is-invalid @enderror" 
                                           id="vin" 
                                           name="vin" 
                                           value="{{ old('vin') }}" 
                                           placeholder="Enter 17-character VIN" 
                                           maxlength="17"
                                           required>
                                    @error('vin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i>Enter the vehicle's 17-character VIN for accurate parts matching
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="vehicle_info" class="form-label fw-bold">
                                        <i class="fas fa-info-circle me-1"></i>Vehicle Information
                                    </label>
                                    <div class="alert alert-info mb-0">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-car me-2 fa-lg"></i>
                                            <div>
                                                <strong>How it works:</strong><br>
                                                Enter VIN to automatically identify vehicle make, model, and year for accurate parts compatibility.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="part_category" class="form-label fw-bold">
                                        <i class="fas fa-tags me-1"></i>Part Category (Optional)
                                    </label>
                                    <select class="form-select @error('part_category') is-invalid @enderror" 
                                            id="part_category" 
                                            name="part_category">
                                        <option value="">All Categories</option>
                                        <option value="Brakes" {{ old('part_category') == 'Brakes' ? 'selected' : '' }}>Brakes</option>
                                        <option value="Engine" {{ old('part_category') == 'Engine' ? 'selected' : '' }}>Engine</option>
                                        <option value="Suspension" {{ old('part_category') == 'Suspension' ? 'selected' : '' }}>Suspension</option>
                                        <option value="Electrical" {{ old('part_category') == 'Electrical' ? 'selected' : '' }}>Electrical</option>
                                        <option value="Exhaust" {{ old('part_category') == 'Exhaust' ? 'selected' : '' }}>Exhaust</option>
                                        <option value="Cooling" {{ old('part_category') == 'Cooling' ? 'selected' : '' }}>Cooling</option>
                                        <option value="Transmission" {{ old('part_category') == 'Transmission' ? 'selected' : '' }}>Transmission</option>
                                        <option value="Interior" {{ old('part_category') == 'Interior' ? 'selected' : '' }}>Interior</option>
                                        <option value="Exterior" {{ old('part_category') == 'Exterior' ? 'selected' : '' }}>Exterior</option>
                                        <option value="Accessories" {{ old('part_category') == 'Accessories' ? 'selected' : '' }}>Accessories</option>
                                    </select>
                                    @error('part_category')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="part_number" class="form-label fw-bold">
                                        <i class="fas fa-hashtag me-1"></i>Part Number (Optional)
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('part_number') is-invalid @enderror" 
                                           id="part_number" 
                                           name="part_number" 
                                           value="{{ old('part_number') }}" 
                                           placeholder="e.g., ABC12345">
                                    @error('part_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        Enter specific part number if known
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                    <button type="reset" class="btn btn-outline-secondary me-md-2">
                                        <i class="fas fa-redo me-1"></i> Clear
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i> Lookup Parts
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-lightbulb me-2"></i>Quick Search
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('parts-procurement.search') }}" method="GET" class="mb-0">
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   name="keyword" 
                                   placeholder="Search by part name, description, or keyword..." 
                                   required>
                            <button class="btn btn-info" type="submit">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                        </div>
                        <small class="form-text text-muted mt-2">
                            <i class="fas fa-info-circle me-1"></i>Search across all parts in database
                        </small>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-question-circle me-2"></i>How to Use
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item border-0 px-0 py-2">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <span class="badge bg-primary rounded-circle p-2">1</span>
                                </div>
                                <div>
                                    <h6 class="mb-1">Enter Vehicle VIN</h6>
                                    <p class="mb-0 text-muted small">17-character VIN ensures accurate vehicle identification</p>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item border-0 px-0 py-2">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <span class="badge bg-primary rounded-circle p-2">2</span>
                                </div>
                                <div>
                                    <h6 class="mb-1">Filter (Optional)</h6>
                                    <p class="mb-0 text-muted small">Narrow results by category or part number</p>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item border-0 px-0 py-2">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <span class="badge bg-primary rounded-circle p-2">3</span>
                                </div>
                                <div>
                                    <h6 class="mb-1">View Results</h6>
                                    <p class="mb-0 text-muted small">See compatible parts with inventory status and vendor prices</p>
                                </div>
                            </div>
                        </div>
                        <div class="list-group-item border-0 px-0 py-2">
                            <div class="d-flex align-items-start">
                                <div class="me-3">
                                    <span class="badge bg-primary rounded-circle p-2">4</span>
                                </div>
                                <div>
                                    <h6 class="mb-1">Order Parts</h6>
                                    <p class="mb-0 text-muted small">Select best vendor and create parts order directly</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('parts-procurement.create') }}" class="btn btn-outline-primary text-start">
                            <i class="fas fa-plus-circle me-2"></i> Create New Parts Order
                        </a>
                        <a href="{{ route('parts-procurement.returns.index') }}" class="btn btn-outline-danger text-start">
                            <i class="fas fa-undo me-2"></i> Manage Returns
                        </a>
                        <a href="{{ route('parts-procurement.core-returns.index') }}" class="btn btn-outline-info text-start">
                            <i class="fas fa-recycle me-2"></i> Core Returns
                        </a>
                        <a href="{{ route('inventory.index') }}" class="btn btn-outline-success text-start">
                            <i class="fas fa-boxes me-2"></i> Check Inventory
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mt-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mt-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-format VIN to uppercase
        const vinInput = document.getElementById('vin');
        if (vinInput) {
            vinInput.addEventListener('input', function() {
                this.value = this.value.toUpperCase();
            });
        }

        // Fetch categories dynamically (if we had an API endpoint)
        // This is a placeholder for future enhancement
        const categorySelect = document.getElementById('part_category');
        if (categorySelect && categorySelect.options.length <= 1) {
            fetch('/api/parts/categories')
                .then(response => response.json())
                .then(categories => {
                    categories.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category;
                        option.textContent = category;
                        categorySelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error loading categories:', error));
        }
    });
</script>
@endsection
@endsection