@extends('layouts.app')

@section('title', 'Parts Lookup Results - FixIt Auto Services')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0">
                        <i class="fas fa-search me-2"></i>Parts Lookup Results
                    </h1>
                    <p class="text-muted mb-0">
                        Compatible parts for 
                        <strong>{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</strong>
                        (VIN: {{ $vin }})
                    </p>
                </div>
                <div>
                    <a href="{{ route('parts-procurement.lookup') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-search me-1"></i> New Search
                    </a>
                    <a href="{{ route('parts-procurement.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Orders
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Vehicle Information Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-car me-2"></i>Vehicle Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted mb-1">Year</label>
                                <div class="fs-5">{{ $vehicle->year }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted mb-1">Make</label>
                                <div class="fs-5">{{ $vehicle->make }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted mb-1">Model</label>
                                <div class="fs-5">{{ $vehicle->model }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted mb-1">VIN</label>
                                <div class="fs-6 font-monospace">{{ $vin }}</div>
                            </div>
                        </div>
                    </div>
                    @if($vehicle->trim || $vehicle->engine)
                    <div class="row">
                        @if($vehicle->trim)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted mb-1">Trim</label>
                                <div>{{ $vehicle->trim }}</div>
                            </div>
                        </div>
                        @endif
                        @if($vehicle->engine)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted mb-1">Engine</label>
                                <div>{{ $vehicle->engine }}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Search Filters Summary -->
    @if($partCategory || $partNumber)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h6 class="mb-3">
                        <i class="fas fa-filter me-2"></i>Applied Filters
                    </h6>
                    <div class="d-flex flex-wrap gap-2">
                        @if($partCategory)
                        <span class="badge bg-info">
                            <i class="fas fa-tag me-1"></i> Category: {{ $partCategory }}
                        </span>
                        @endif
                        @if($partNumber)
                        <span class="badge bg-info">
                            <i class="fas fa-hashtag me-1"></i> Part #: {{ $partNumber }}
                        </span>
                        @endif
                        <a href="{{ route('parts-procurement.lookup') }}?vin={{ $vin }}" class="badge bg-secondary text-decoration-none">
                            <i class="fas fa-times me-1"></i> Clear Filters
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Parts Results -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Compatible Parts
                            <span class="badge bg-primary ms-2">{{ count($parts) }} found</span>
                        </h5>
                        <div class="text-muted small">
                            Showing parts compatible with this vehicle
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if(count($parts) > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40"></th>
                                        <th>Part Name</th>
                                        <th>Part Number</th>
                                        <th>Category</th>
                                        <th class="text-center">Inventory</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Best Price</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($parts as $item)
                                    @php
                                        $part = $item['part'];
                                        $inStock = $item['in_stock'];
                                        $needsOrder = $item['needs_order'];
                                        $vendorPrices = $vendorPrices[$part->part_number] ?? [];
                                        $bestPrice = !empty($vendorPrices) ? $vendorPrices[0]['total'] : null;
                                        $bestVendor = !empty($vendorPrices) ? $vendorPrices[0]['vendor_name'] : null;
                                    @endphp
                                    <tr>
                                        <td class="text-center">
                                            @if($inStock > 0)
                                                <span class="badge bg-success rounded-circle p-2" title="In Stock">
                                                    <i class="fas fa-check"></i>
                                                </span>
                                            @else
                                                <span class="badge bg-danger rounded-circle p-2" title="Out of Stock">
                                                    <i class="fas fa-times"></i>
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-bold">{{ $part->part_name }}</div>
                                            @if($part->description)
                                            <div class="text-muted small">{{ Str::limit($part->description, 50) }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            <code class="font-monospace">{{ $part->part_number }}</code>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $part->category }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($inStock > 0)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-box me-1"></i> {{ $inStock }} in stock
                                                </span>
                                            @else
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-box me-1"></i> Out of stock
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($inStock > 0)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i> Available
                                                </span>
                                            @elseif(!empty($vendorPrices))
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-shopping-cart me-1"></i> Needs Order
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-question-circle me-1"></i> Check Suppliers
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($bestPrice)
                                                <div class="fw-bold text-success">${{ number_format($bestPrice, 2) }}</div>
                                                <div class="text-muted small">{{ $bestVendor }}</div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                @if($inStock > 0)
                                                    <a href="{{ route('inventory.index') }}?search={{ $part->part_number }}" 
                                                       class="btn btn-outline-success" 
                                                       title="View in Inventory">
                                                        <i class="fas fa-box"></i>
                                                    </a>
                                                @endif
                                                
                                                @if($needsOrder && !empty($vendorPrices))
                                                    <a href="{{ route('parts-procurement.create') }}?part_number={{ $part->part_number }}&vin={{ $vin }}" 
                                                       class="btn btn-outline-primary" 
                                                       title="Order Part">
                                                        <i class="fas fa-shopping-cart"></i>
                                                    </a>
                                                @endif
                                                
                                                <button type="button" 
                                                        class="btn btn-outline-info" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#partDetailsModal{{ $part->id }}"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Part Details Modal -->
                                    <div class="modal fade" id="partDetailsModal{{ $part->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title">
                                                        <i class="fas fa-info-circle me-2"></i>{{ $part->part_name }}
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h6>Part Information</h6>
                                                            <table class="table table-sm">
                                                                <tr>
                                                                    <th width="120">Part Number:</th>
                                                                    <td><code>{{ $part->part_number }}</code></td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Category:</th>
                                                                    <td>{{ $part->category }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Manufacturer:</th>
                                                                    <td>{{ $part->manufacturer ?? 'N/A' }}</td>
                                                                </tr>
                                                                <tr>
                                                                    <th>OEM Number:</th>
                                                                    <td>{{ $part->oem_number ?? 'N/A' }}</td>
                                                                </tr>
                                                            </table>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <h6>Inventory Status</h6>
                                                            @if($inStock > 0)
                                                                <div class="alert alert-success">
                                                                    <i class="fas fa-check-circle me-2"></i>
                                                                    <strong>In Stock:</strong> {{ $inStock }} units available
                                                                </div>
                                                                <a href="{{ route('inventory.index') }}?search={{ $part->part_number }}" 
                                                                   class="btn btn-success btn-sm">
                                                                    <i class="fas fa-box me-1"></i> View in Inventory
                                                                </a>
                                                            @else
                                                                <div class="alert alert-warning">
                                                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                                                    <strong>Out of Stock</strong> - Needs to be ordered
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    @if($part->description)
                                                    <div class="row mt-3">
                                                        <div class="col-12">
                                                            <h6>Description</h6>
                                                            <div class="card card-body bg-light">
                                                                {{ $part->description }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif

                                                    @if(!empty($vendorPrices))
                                                    <div class="row mt-3">
                                                        <div class="col-12">
                                                            <h6>Vendor Prices</h6>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-hover">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Vendor</th>
                                                                            <th class="text-center">Price</th>
                                                                            <th class="text-center">Shipping</th>
                                                                            <th class="text-center">Total</th>
                                                                            <th class="text-center">Delivery</th>
                                                                            <th class="text-center">Status</th>
                                                                            <th class="text-center">Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($vendorPrices as $price)
                                                                        <tr>
                                                                            <td>
                                                                                <div class="fw-bold">{{ $price['vendor_name'] }}</div>
                                                                                <div class="small text-muted">
                                                                                    <i class="fas fa-star text-warning"></i> {{ $price['rating'] }}/5
                                                                                </div>
                                                                            </td>
                                                                            <td class="text-center">${{ number_format($price['price'], 2) }}</td>
                                                                            <td class="text-center">${{ number_format($price['shipping'], 2) }}</td>
                                                                            <td class="text-center fw-bold">${{ number_format($price['total'], 2) }}</td>
                                                                            <td class="text-center">
                                                                                @if($price['delivery_days'] == 0)
                                                                                    <span class="badge bg-success">Same Day</span>
                                                                                @else
                                                                                    {{ $price['delivery_days'] }} day(s)
                                                                                @endif
                                                                            </td>
                                                                            <td class="text-center">
                                                                                @if($price['in_stock'] == 'In Stock')
                                                                                    <span class="badge bg-success">In Stock</span>
                                                                                @else
                                                                                    <span class="badge bg-warning text-dark">Backorder</span>
                                                                                @endif
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <a href="{{ route('parts-procurement.create') }}?part_number={{ $part->part_number }}&vendor_id={{ $price['vendor_id'] }}&vin={{ $vin }}" 
                                                                                   class="btn btn-primary btn-sm">
                                                                                    <i class="fas fa-cart-plus me-1"></i> Order
                                                                                </a>
                                                                            </td>
                                                                        </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                    @if($needsOrder && !empty($vendorPrices))
                                                    <a href="{{ route('parts-procurement.create') }}?part_number={{ $part->part_number }}&vin={{ $vin }}" 
                                                       class="btn btn-primary">
                                                        <i class="fas fa-shopping-cart me-1"></i> Order This Part
                                                    </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-search fa-4x text-muted"></i>
                            </div>
                            <h4 class="text-muted mb-3">No Compatible Parts Found</h4>
                            <p class="text-muted mb-4">
                                No parts were found matching your search criteria for this vehicle.
                            </p>
                            <div class="d-flex justify-content-center gap-2">
                                <a href="{{ route('parts-procurement.lookup') }}?vin={{ $vin }}" class="btn btn-outline-primary">
                                    <i class="fas fa-redo me-1"></i> Try Different Filters
                                </a>
                                <a href="{{ route('parts-procurement.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-1"></i> Create Manual Order
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
                @if(count($parts) > 0)
                <div class="card-footer bg-white border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Click <i class="fas fa-eye text-info"></i> to view part details and vendor prices
                        </div>
                        <div>
                            <a href="{{ route('parts-procurement.create') }}?vin={{ $vin }}" class="btn btn-primary">
                                <i class="fas fa-shopping-cart me-1"></i> Create Order from Results
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
