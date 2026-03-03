@extends('layouts.app')

@section('title', 'Create Parts Order - Fix-It Auto Services')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-shopping-cart me-2"></i>Create Parts Order
            </h1>
            <p class="text-muted mb-0">Create a new parts procurement order</p>
        </div>
        <div>
            <a href="{{ route('parts-procurement.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Orders
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('parts-procurement.store') }}" id="partsOrderForm">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="vendor_id" class="form-label">Vendor *</label>
                                <select class="form-select @error('vendor_id') is-invalid @enderror" 
                                        id="vendor_id" name="vendor_id" required>
                                    <option value="">Select Vendor</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}" {{ old('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                            {{ $vendor->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('vendor_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="work_order_id" class="form-label">Work Order</label>
                                <select class="form-select @error('work_order_id') is-invalid @enderror" 
                                        id="work_order_id" name="work_order_id">
                                    <option value="">Select Work Order</option>
                                    @foreach($workOrders as $workOrder)
                                        <option value="{{ $workOrder->id }}" {{ old('work_order_id') == $workOrder->id ? 'selected' : '' }}>
                                            WO-{{ $workOrder->id }} - {{ $workOrder->customer->first_name ?? 'N/A' }} {{ $workOrder->customer->last_name ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('work_order_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label for="customer_id" class="form-label">Customer</label>
                                <select class="form-select @error('customer_id') is-invalid @enderror" 
                                        id="customer_id" name="customer_id">
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->first_name }} {{ $customer->last_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="vehicle_id" class="form-label">Vehicle</label>
                                <select class="form-select @error('vehicle_id') is-invalid @enderror" 
                                        id="vehicle_id" name="vehicle_id">
                                    <option value="">Select Vehicle</option>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                                            {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->license_plate ?? 'No Plate' }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('vehicle_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label for="shipping_method" class="form-label">Shipping Method</label>
                                <select class="form-select @error('shipping_method') is-invalid @enderror" 
                                        id="shipping_method" name="shipping_method">
                                    <option value="">Select Shipping Method</option>
                                    <option value="ground" {{ old('shipping_method') == 'ground' ? 'selected' : '' }}>Ground Shipping</option>
                                    <option value="express" {{ old('shipping_method') == 'express' ? 'selected' : '' }}>Express Shipping</option>
                                    <option value="overnight" {{ old('shipping_method') == 'overnight' ? 'selected' : '' }}>Overnight Shipping</option>
                                    <option value="pickup" {{ old('shipping_method') == 'pickup' ? 'selected' : '' }}>Local Pickup</option>
                                </select>
                                @error('shipping_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="form-group mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Order Items -->
                    <div class="row">
                        <div class="col-12">
                            <h5 class="mb-3">Order Items</h5>
                            
                            <div id="orderItems">
                                <!-- Items will be added here dynamically -->
                                <div class="order-item card mb-3" data-index="0">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Part Name *</label>
                                                    <input type="text" class="form-control" name="items[0][part_name]" required>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Part Number</label>
                                                    <input type="text" class="form-control" name="items[0][part_number]">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">OEM Number</label>
                                                    <input type="text" class="form-control" name="items[0][oem_number]">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Quantity *</label>
                                                    <input type="number" class="form-control" name="items[0][quantity]" value="1" min="1" required>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Unit Price *</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">$</span>
                                                        <input type="number" step="0.01" class="form-control" name="items[0][unit_price]" value="0.00" min="0" required>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea class="form-control" name="items[0][description]" rows="2"></textarea>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Core Charge</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">$</span>
                                                        <input type="number" step="0.01" class="form-control" name="items[0][core_charge]" value="0.00" min="0">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Core Return Required</label>
                                                    <div class="form-check mt-2">
                                                        <input class="form-check-input" type="checkbox" name="items[0][core_return_required]" value="1">
                                                        <label class="form-check-label">Yes</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group mb-3">
                                                    <label class="form-label">Inventory Item</label>
                                                    <select class="form-select" name="items[0][inventory_item_id]">
                                                        <option value="">Select from Inventory</option>
                                                        @foreach($inventoryItems as $item)
                                                            <option value="{{ $item->id }}">
                                                                {{ $item->name }} ({{ $item->part_number ?? 'N/A' }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="d-flex justify-content-end">
                                                    <button type="button" class="btn btn-danger btn-sm remove-item" style="display: none;">
                                                        <i class="fas fa-trash me-1"></i> Remove Item
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="text-center mb-4">
                                <button type="button" id="addItem" class="btn btn-outline-primary">
                                    <i class="fas fa-plus me-1"></i> Add Another Item
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="mb-3">Order Summary</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <strong>Subtotal:</strong> <span id="subtotal">₱0.00</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Core Charge:</strong> <span id="coreCharge">₱0.00</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <strong>Shipping:</strong> <span id="shipping">₱0.00</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Tax:</strong> <span id="tax">₱0.00</span>
                                            </div>
                                            <div class="mb-2">
                                                <strong>Total:</strong> <span id="total" class="h5">₱0.00</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('parts-procurement.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Create Order
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        let itemIndex = 1;
        
        // Add new item
        $('#addItem').on('click', function() {
            const newItem = $(`
                <div class="order-item card mb-3" data-index="${itemIndex}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Part Name *</label>
                                    <input type="text" class="form-control" name="items[${itemIndex}][part_name]" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label class="form-label">Part Number</label>
                                    <input type="text" class="form-control" name="items[${itemIndex}][part_number]">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label class="form-label">OEM Number</label>
                                    <input type="text" class="form-control" name="items[${itemIndex}][oem_number]">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label class="form-label">Quantity *</label>
                                    <input type="number" class="form-control" name="items[${itemIndex}][quantity]" value="1" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label class="form-label">Unit Price *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control" name="items[${itemIndex}][unit_price]" value="0.00" min="0" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="items[${itemIndex}][description]" rows="2"></textarea>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">Core Charge</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control" name="items[${itemIndex}][core_charge]" value="0.00" min="0">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label class="form-label">Core Return Required</label>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="items[${itemIndex}][core_return_required]" value="1">
                                        <label class="form-check-label">Yes</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label">Inventory Item</label>
                                    <select class="form-select" name="items[${itemIndex}][inventory_item_id]">
                                        <option value="">Select from Inventory</option>
                                        @foreach($inventoryItems as $item)
                                            <option value="{{ $item->id }}">
                                                {{ $item->name }} ({{ $item->part_number ?? 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-danger btn-sm remove-item">
                                        <i class="fas fa-trash me-1"></i> Remove Item
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            
            $('#orderItems').append(newItem);
            itemIndex++;
            
            // Show remove button on first item
            $('.order-item:first-child .remove-item').show();
        });
        
        // Remove item
        $(document).on('click', '.remove-item', function() {
            $(this).closest('.order-item').remove();
            
            // Hide remove button if only one item left
            if ($('.order-item').length === 1) {
                $('.order-item:first-child .remove-item').hide();
            }
            
            calculateTotals();
        });
        
        // Calculate totals
        function calculateTotals() {
            let subtotal = 0;
            let coreCharge = 0;
            
            $('.order-item').each(function() {
                const quantity = parseFloat($(this).find('input[name*="[quantity]"]').val()) || 0;
                const unitPrice = parseFloat($(this).find('input[name*="[unit_price]"]').val()) || 0;
                const itemCoreCharge = parseFloat($(this).find('input[name*="[core_charge]"]').val()) || 0;
                
                subtotal += quantity * unitPrice;
                coreCharge += quantity * itemCoreCharge;
            });
            
            // For now, we'll use fixed shipping and tax
            // In a real implementation, these would be calculated based on vendor, shipping method, etc.
            const shipping = 0;
            const tax = subtotal * 0.08; // 8% tax
            
            $('#subtotal').text('₱' + subtotal.toFixed(2));
            $('#coreCharge').text('₱' + coreCharge.toFixed(2));
            $('#shipping').text('₱' + shipping.toFixed(2));
            $('#tax').text('₱' + tax.toFixed(2));
            $('#total').text('₱' + (subtotal + shipping + tax).toFixed(2));
        }
        
        // Recalculate totals when any input changes
        $(document).on('input', 'input[name*="[quantity]"], input[name*="[unit_price]"], input[name*="[core_charge]"]', calculateTotals);
        
        // Initial calculation
        calculateTotals();
        
        // Load vehicles when customer is selected
        $('#customer_id').on('change', function() {
            const customerId = $(this).val();
            const vehicleSelect = $('#vehicle_id');
            
            if (customerId) {
                // In a real implementation, this would be an AJAX call
                // For now, we'll just filter the existing options
                vehicleSelect.find('option').each(function() {
                    const option = $(this);
                    if (option.val() === '') {
                        option.show();
                    } else {
                        // This is a simplified version - in reality, you'd need to know which vehicles belong to which customer
                        option.show();
                    }
                });
            }
        });
    });
</script>
@endsection