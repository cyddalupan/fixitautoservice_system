@extends('layouts.app')

@section('title', 'Create New Estimate - Fix-It Auto Services')

@push('styles')
<style>
/* Autocomplete styles */
.position-relative {
    position: relative !important;
}

.autocomplete-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 2px;
    z-index: 1050;
    display: none;
    max-height: 300px;
    overflow-y: auto;
    background-color: white;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.autocomplete-dropdown .list-group-item {
    border: none;
    border-radius: 0;
    padding: 0.5rem 1rem;
    cursor: pointer;
}

.autocomplete-dropdown .list-group-item:hover {
    background-color: #f8f9fa;
}

.autocomplete-dropdown .list-group-item-secondary {
    background-color: #e9ecef;
    font-size: 0.875rem;
    padding: 0.25rem 1rem;
    cursor: default;
}

.autocomplete-dropdown .list-group-item-secondary:hover {
    background-color: #e9ecef;
}

.description-display {
    display: block;
    margin-top: 0.25rem;
    color: #6c757d;
    font-style: italic;
    min-height: 1.2em;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-invoice-dollar text-primary"></i> Create New Estimate
        </h1>
        <div>
            <a href="{{ route('estimates.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Estimates
            </a>
        </div>
    </div>

    <!-- Create Estimate Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary text-white">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-plus-circle"></i> Estimate Details
            </h6>
        </div>
        <div class="card-body">
            <form action="{{ route('estimates.store') }}" method="POST">
                @csrf
                
                <!-- Hidden fields for appointment/inspection -->
                @if(request('appointment_id'))
                    <input type="hidden" name="appointment_id" value="{{ request('appointment_id') }}">
                    @php
                        $appointment = \App\Models\Appointment::find(request('appointment_id'));
                    @endphp
                @endif

                <!-- Basic Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-info-circle"></i> Basic Information
                        </h5>
                        
                        <!-- Customer Selection -->
                        <div class="mb-3">
                            <label for="customer_id" class="form-label">
                                <i class="fas fa-user"></i> Customer <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('customer_id') is-invalid @enderror" 
                                    id="customer_id" name="customer_id" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" 
                                        @if(old('customer_id') == $customer->id) selected @endif
                                        @if(isset($appointment) && $appointment->customer_id == $customer->id) selected @endif>
                                        {{ $customer->full_name }} 
                                        @if($customer->company_name)
                                            ({{ $customer->company_name }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Vehicle Selection -->
                        <div class="mb-3">
                            <label for="vehicle_id" class="form-label">
                                <i class="fas fa-car"></i> Vehicle <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('vehicle_id') is-invalid @enderror" 
                                    id="vehicle_id" name="vehicle_id" required>
                                <option value="">Select Vehicle</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}" 
                                        @if(old('vehicle_id') == $vehicle->id) selected @endif
                                        @if(isset($appointment) && $appointment->vehicle_id == $vehicle->id) selected @endif>
                                        {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }} 
                                        ({{ $vehicle->license_plate ?? 'No Plate' }})
                                    </option>
                                @endforeach
                            </select>
                            @error('vehicle_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Estimate Number -->
                        <div class="mb-3">
                            <label for="estimate_number" class="form-label">
                                <i class="fas fa-hashtag"></i> Estimate Number
                            </label>
                            <input type="text" class="form-control @error('estimate_number') is-invalid @enderror" 
                                   id="estimate_number" name="estimate_number" 
                                   value="{{ old('estimate_number', 'EST-' . date('Ymd') . '-' . str_pad(($lastEstimateNumber ?? 0) + 1, 4, '0', STR_PAD_LEFT)) }}"
                                   readonly>
                            @error('estimate_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-calendar-alt"></i> Dates & Validity
                        </h5>
                        
                        <!-- Estimate Date -->
                        <div class="mb-3">
                            <label for="estimate_date" class="form-label">
                                <i class="fas fa-calendar"></i> Estimate Date <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control @error('estimate_date') is-invalid @enderror" 
                                   id="estimate_date" name="estimate_date" 
                                   value="{{ old('estimate_date', date('Y-m-d')) }}" required>
                            @error('estimate_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Expiry Date -->
                        <div class="mb-3">
                            <label for="expiry_date" class="form-label">
                                <i class="fas fa-clock"></i> Valid Until <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" 
                                   id="expiry_date" name="expiry_date" 
                                   value="{{ old('expiry_date', date('Y-m-d', strtotime('+30 days'))) }}" required>
                            @error('expiry_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">
                                <i class="fas fa-tag"></i> Status
                            </label>
                            <select class="form-select @error('status') is-invalid @enderror" 
                                    id="status" name="status">
                                <option value="draft" @if(old('status') == 'draft') selected @endif>Draft</option>
                                <option value="pending" @if(old('status') == 'sent') selected @endif>Sent to Customer</option>
                                <option value="accepted" @if(old('status') == 'accepted') selected @endif>Accepted</option>
                                <option value="rejected" @if(old('status') == 'rejected') selected @endif>Rejected</option>
                                <option value="expired" @if(old('status') == 'expired') selected @endif>Expired</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Terms & Notes -->
                        <div class="mb-3">
                            <label for="terms" class="form-label">
                                <i class="fas fa-file-contract"></i> Terms & Conditions
                            </label>
                            <textarea class="form-control @error('terms') is-invalid @enderror" 
                                      id="terms" name="terms" rows="2">{{ old('terms', 'Payment due upon completion. All work guaranteed for 90 days.') }}</textarea>
                            @error('terms')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Simple Items Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-list"></i> Estimate Items
                            <button type="button" class="btn btn-sm btn-success ms-2" id="addItemBtn">
                                <i class="fas fa-plus"></i> Add Item
                            </button>
                        </h5>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered" id="itemsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%">#</th>
                                        <th width="35%">Item / Service</th>
                                        <th width="15%">Quantity</th>
                                        <th width="15%">Unit Price</th>
                                        <th width="15%">Total</th>
                                        <th width="15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <!-- Items will be added here dynamically -->
                                    <tr id="noItemsRow">
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle me-2"></i>No items added yet. Click "Add Item" to start.
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Subtotal:</td>
                                        <td id="subtotalCell">₱0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Tax (0%):</td>
                                        <td id="taxCell">₱0.00</td>
                                        <td></td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td colspan="4" class="text-end fw-bold fs-5">Total:</td>
                                        <td id="totalCell" class="fw-bold fs-5">₱0.00</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Notes Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-sticky-note"></i> Notes & Instructions
                        </h5>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Internal Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="customer_notes" class="form-label">Customer Notes</label>
                            <textarea class="form-control @error('customer_notes') is-invalid @enderror" 
                                      id="customer_notes" name="customer_notes" rows="3">{{ old('customer_notes') }}</textarea>
                            @error('customer_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <div>
                                <button type="submit" name="action" value="save_draft" class="btn btn-outline-primary">
                                    <i class="fas fa-save"></i> Save as Draft
                                </button>
                                <button type="button" class="btn btn-info ms-2" onclick="saveAndPrint()">
                                    <i class="fas fa-print"></i> Save and Print
                                </button>
                                <button type="button" class="btn btn-primary ms-2" id="sendToCustomerBtn">
                                    <i class="fas fa-paper-plane"></i> Save & Send to Customer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            
            <!-- Email Confirmation Modal -->
            <div class="modal fade" id="emailModal" tabindex="-1" aria-labelledby="emailModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="emailModalLabel">
                                <i class="fas fa-envelope"></i> Send Estimate to Customer
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="customerEmail" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="customerEmail" placeholder="Enter customer email address" required>
                                <div class="form-text">The estimate will be sent to this email address.</div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                            <button type="button" class="btn btn-primary" onclick="submitWithEmail()">
                                <i class="fas fa-paper-plane"></i> Send Estimate
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Inventory items data for autocomplete (only items with quantity > 0)
const inventoryItems = @json($inventoryItems->map(function($item) {
    return [
        'id' => $item->id,
        'name' => $item->name,
        'description' => $item->description,
        'part_number' => $item->part_number,
        'retail_price' => floatval($item->retail_price),
        'wholesale_price' => floatval($item->wholesale_price),
        'cost_price' => floatval($item->cost_price),
        'quantity' => intval($item->quantity),
        'manufacturer' => $item->manufacturer ?? '',
        'category_id' => $item->category_id ?? null
    ];
}));

// Debug: Log inventory items count
console.log('DEBUG: Total inventory items loaded:', inventoryItems.length);
if (inventoryItems.length > 0) {
    console.log('DEBUG: First inventory item:', inventoryItems[0]);
}

// Common services for quick selection
const commonServices = [
    { id: 'oil_change', name: 'Oil Change', description: 'Full synthetic oil change with filter', price: 89.99 },
    { id: 'tire_rotation', name: 'Tire Rotation', description: 'Rotate all four tires', price: 39.99 },
    { id: 'brake_inspection', name: 'Brake Inspection', description: 'Complete brake system inspection', price: 49.99 },
    { id: 'alignment', name: 'Wheel Alignment', description: 'Four-wheel alignment', price: 129.99 },
    { id: 'battery_test', name: 'Battery Test', description: 'Complete battery and charging system test', price: 29.99 },
    { id: 'ac_service', name: 'A/C Service', description: 'A/C system recharge and leak test', price: 149.99 }
];

console.log('DEBUG: Common services loaded:', commonServices.length);

let itemCounter = 0;

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Add item button click handler
    document.getElementById('addItemBtn').addEventListener('click', addNewItem);
    
    // Auto-fill from appointment if available
    @if(isset($appointment) && $appointment)
        // Auto-select customer and vehicle
        const customerSelect = document.getElementById('customer_id');
        const vehicleSelect = document.getElementById('vehicle_id');
        
        if (customerSelect) {
            customerSelect.value = '{{ $appointment->customer_id }}';
        }
        
        if (vehicleSelect) {
            vehicleSelect.value = '{{ $appointment->vehicle_id }}';
        }
        
        // Auto-add services from appointment
        @if($appointment->service_request)
            const serviceRequest = '{{ $appointment->service_request }}';
            // Simple parsing - in real app, you'd parse the service request
            if (serviceRequest.toLowerCase().includes('oil')) {
                addServiceItem('oil_change');
            }
            if (serviceRequest.toLowerCase().includes('brake')) {
                addServiceItem('brake_inspection');
            }
            if (serviceRequest.toLowerCase().includes('tire')) {
                addServiceItem('tire_rotation');
            }
        @endif
    @endif
});

// Add a new item row
function addNewItem() {
    itemCounter++;
    const noItemsRow = document.getElementById('noItemsRow');
    if (noItemsRow) {
        noItemsRow.remove();
    }
    
    const tbody = document.getElementById('itemsBody');
    const newRow = document.createElement('tr');
    newRow.id = `itemRow_${itemCounter}`;
    newRow.innerHTML = `
        <td>${itemCounter}</td>
        <td>
            <div class="position-relative">
                <input type="text" 
                       class="form-control item-name-input" 
                       name="items[${itemCounter}][item_name]" 
                       placeholder="Type item or service name..."
                       autocomplete="off"
                       required>
                <input type="hidden" name="items[${itemCounter}][inventory_id]" class="inventory-id-input">
                <input type="hidden" name="items[${itemCounter}][description]" class="description-input">
                
                <!-- Autocomplete dropdown -->
                <div class="autocomplete-dropdown">
                    <div class="list-group"></div>
                </div>
            </div>
            <small class="text-muted description-display"></small>
        </td>
        <td>
            <input type="number" 
                   class="form-control quantity-input" 
                   name="items[${itemCounter}][quantity]" 
                   value="1" min="1" step="1"
                   onchange="updateRowTotal(${itemCounter})" 
                   oninput="updateRowTotal(${itemCounter})"
                   required>
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-text">₱</span>
                <input type="number" 
                       class="form-control unit-price-input" 
                       name="items[${itemCounter}][unit_price]" 
                       value="0.00" min="0" step="0.01"
                       onchange="updateRowTotal(${itemCounter})" 
                       oninput="updateRowTotal(${itemCounter})"
                       required>
            </div>
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-text">₱</span>
                <span class="form-control total-display bg-light">0.00</span>
            </div>
            <input type="hidden" name="items[${itemCounter}][total]" class="total-input" value="0.00">
        </td>
        <td>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeItem(${itemCounter})">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(newRow);
    
    // Initialize autocomplete for this row
    initAutocomplete(itemCounter);
    updateTotals();
}

// Initialize autocomplete for an item row
function initAutocomplete(rowId) {
    console.log('Initializing autocomplete for row', rowId);
    const row = document.getElementById(`itemRow_${rowId}`);
    const input = row.querySelector('.item-name-input');
    const dropdown = row.querySelector('.autocomplete-dropdown');
    const inventoryIdInput = row.querySelector('.inventory-id-input');
    const descriptionInput = row.querySelector('.description-input');
    const descriptionDisplay = row.querySelector('.description-display');
    const unitPriceInput = row.querySelector('.unit-price-input');
    
    // Show autocomplete dropdown
    input.addEventListener('focus', function() {
        console.log('DEBUG: Input focused for row', rowId);
        showAutocomplete(rowId);
    });
    
    input.addEventListener('input', function() {
        console.log('DEBUG: Input changed for row', rowId, 'value:', this.value);
        showAutocomplete(rowId);
    });
    
    input.addEventListener('keydown', function(e) {
        // Show dropdown on arrow down/up
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            showAutocomplete(rowId);
        }
    });
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', function(event) {
        if (!row.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });
    
    // Function to show autocomplete suggestions
    function showAutocomplete(rowId) {
        const searchTerm = input.value.toLowerCase().trim();
        const dropdownList = dropdown.querySelector('.list-group');
        dropdownList.innerHTML = '';
        
        console.log('Searching for:', searchTerm, 'in', inventoryItems.length, 'items and', commonServices.length, 'services');
        
        if (searchTerm.length < 2) {
            dropdown.style.display = 'none';
            return;
        }
        
        // Filter inventory items
        const filteredItems = inventoryItems.filter(item => {
            // Safely check each property (handle null/undefined)
            const name = (item.name || '').toLowerCase();
            const partNumber = (item.part_number || '').toLowerCase();
            const description = (item.description || '').toLowerCase();
            const manufacturer = (item.manufacturer || '').toLowerCase();
            
            const nameMatch = name.includes(searchTerm);
            const partNumberMatch = partNumber.includes(searchTerm);
            const descriptionMatch = description.includes(searchTerm);
            const manufacturerMatch = manufacturer.includes(searchTerm);
            
            const hasMatch = nameMatch || partNumberMatch || descriptionMatch || manufacturerMatch;
            
            if (hasMatch) {
                console.log('DEBUG: Matched item:', item.name, 'search term:', searchTerm);
            }
            
            return hasMatch;
        });
        
        // Filter common services
        const filteredServices = commonServices.filter(service =>
            service.name.toLowerCase().includes(searchTerm) ||
            service.description.toLowerCase().includes(searchTerm)
        );
        
        // Add inventory items to dropdown
        if (filteredItems.length > 0) {
            const header = document.createElement('div');
            header.className = 'list-group-item list-group-item-secondary fw-bold';
            header.textContent = 'Inventory Items';
            dropdownList.appendChild(header);
            
            filteredItems.forEach(item => {
                const option = document.createElement('button');
                option.type = 'button';
                option.className = 'list-group-item list-group-item-action';
                option.innerHTML = `
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>${item.name}</strong>
                            <br>
                            <small class="text-muted">${item.part_number || 'No Part #'} | ${item.manufacturer || 'No Manufacturer'}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-success">$${item.retail_price.toFixed(2)}</span>
                            <br>
                            <small class="text-muted">Stock: ${item.quantity}</small>
                        </div>
                    </div>
                `;
                option.addEventListener('click', function() {
                    selectInventoryItem(rowId, item);
                });
                dropdownList.appendChild(option);
            });
        }
        
        // Add services to dropdown
        if (filteredServices.length > 0) {
            const header = document.createElement('div');
            header.className = 'list-group-item list-group-item-secondary fw-bold';
            header.textContent = 'Common Services';
            dropdownList.appendChild(header);
            
            filteredServices.forEach(service => {
                const option = document.createElement('button');
                option.type = 'button';
                option.className = 'list-group-item list-group-item-action';
                option.innerHTML = `
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>${service.name}</strong>
                            <br>
                            <small class="text-muted">${service.description}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-primary">$${service.price.toFixed(2)}</span>
                        </div>
                    </div>
                `;
                option.addEventListener('click', function() {
                    selectServiceItem(rowId, service);
                });
                dropdownList.appendChild(option);
            });
        }
        
        // Show dropdown if we have results
        if (filteredItems.length > 0 || filteredServices.length > 0) {
            dropdown.style.display = 'block';
        } else {
            dropdown.style.display = 'none';
        }
    }
    
    // Select inventory item
    function selectInventoryItem(rowId, item) {
        input.value = item.name;
        inventoryIdInput.value = item.id;
        descriptionInput.value = item.description || '';
        descriptionDisplay.textContent = item.description || '';
        unitPriceInput.value = item.retail_price.toFixed(2);
        dropdown.style.display = 'none';
        updateRowTotal(rowId);
    }
    
    // Select service item
    function selectServiceItem(rowId, service) {
        input.value = service.name;
        inventoryIdInput.value = '';
        descriptionInput.value = service.description;
        descriptionDisplay.textContent = service.description;
        unitPriceInput.value = service.price.toFixed(2);
        dropdown.style.display = 'none';
        updateRowTotal(rowId);
    }
}

// Update row total
function updateRowTotal(rowId) {
    const row = document.getElementById(`itemRow_${rowId}`);
    const quantityInput = row.querySelector('.quantity-input');
    const unitPriceInput = row.querySelector('.unit-price-input');
    const totalDisplay = row.querySelector('.total-display');
    const totalInput = row.querySelector('.total-input');
    
    const quantity = parseFloat(quantityInput.value) || 0;
    const unitPrice = parseFloat(unitPriceInput.value) || 0;
    const total = quantity * unitPrice;
    
    totalDisplay.textContent = total.toFixed(2);
    totalInput.value = total.toFixed(2);
    updateTotals();
}

// Update all totals
function updateTotals() {
    const rows = document.querySelectorAll('[id^="itemRow_"]');
    let subtotal = 0;
    
    rows.forEach(row => {
        const totalDisplay = row.querySelector('.total-display');
        subtotal += parseFloat(totalDisplay.textContent) || 0;
    });
    
    const taxRate = 0; // You can change this if you have tax
    const tax = subtotal * (taxRate / 100);
    const total = subtotal + tax;
    
    document.getElementById('subtotalCell').textContent  = '₱' + subtotal.toFixed(2);
    document.getElementById('taxCell').textContent  = '₱' + tax.toFixed(2);
    document.getElementById('totalCell').textContent  = '₱' + total.toFixed(2);
}

// Remove item row
function removeItem(rowId) {
    const row = document.getElementById(`itemRow_${rowId}`);
    row.remove();
    
    // Update row numbers
    const rows = document.querySelectorAll('[id^="itemRow_"]');
    if (rows.length === 0) {
        const tbody = document.getElementById('itemsBody');
        tbody.innerHTML = `
            <tr id="noItemsRow">
                <td colspan="6" class="text-center text-muted py-4">
                    <i class="fas fa-info-circle me-2"></i>No items added yet. Click "Add Item" to start.
                </td>
            </tr>
        `;
        itemCounter = 0;
    } else {
        rows.forEach((row, index) => {
            const newRowId = index + 1;
            row.id = `itemRow_${newRowId}`;
            row.querySelector('td:first-child').textContent = newRowId;
            
            // Update all event handlers and names
            const inputs = row.querySelectorAll('[name]');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                if (name && name.includes('[')) {
                    const newName = name.replace(/items\[\d+\]/, `items[${newRowId}]`);
                    input.setAttribute('name', newName);
                }
            });
            
            // Update event handlers
            const quantityInput = row.querySelector('.quantity-input');
            if (quantityInput) {
                quantityInput.setAttribute('onchange', `updateRowTotal(${newRowId})`);
                quantityInput.setAttribute('oninput', `updateRowTotal(${newRowId})`);
            }
            
            const unitPriceInput = row.querySelector('.unit-price-input');
            if (unitPriceInput) {
                unitPriceInput.setAttribute('onchange', `updateRowTotal(${newRowId})`);
                unitPriceInput.setAttribute('oninput', `updateRowTotal(${newRowId})`);
            }
            
            const removeBtn = row.querySelector('.btn-danger');
            if (removeBtn) {
                removeBtn.setAttribute('onclick', `removeItem(${newRowId})`);
            }
        });
        itemCounter = rows.length;
    }
    
    updateTotals();
}

// Add service item from appointment
function addServiceItem(serviceId) {
    addNewItem();
    const lastRow = document.querySelector('[id^="itemRow_"]:last-child');
    if (lastRow) {
        const service = commonServices.find(s => s.id === serviceId);
        if (service) {
            const rowId = lastRow.id.split('_')[1];
            const input = lastRow.querySelector('.item-name-input');
            const inventoryIdInput = lastRow.querySelector('.inventory-id-input');
            const descriptionInput = lastRow.querySelector('.description-input');
            const descriptionDisplay = lastRow.querySelector('.description-display');
            const unitPriceInput = lastRow.querySelector('.unit-price-input');
            
            input.value = service.name;
            inventoryIdInput.value = '';
            descriptionInput.value = service.description;
            descriptionDisplay.textContent = service.description;
            unitPriceInput.value = service.price.toFixed(2);
            
            updateRowTotal(rowId);
        }
    }
}

// Save and Print function
function saveAndPrint() {
    // Submit form first with save_draft action
    const form = document.getElementById('estimateForm');
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'save_print';
    form.appendChild(actionInput);
    form.submit();
}

// Open email modal when clicking Send to Customer button
document.getElementById('sendToCustomerBtn').addEventListener('click', function() {
    // Get customer email from the selected customer
    const customerSelect = document.getElementById('customer_id');
    const selectedOption = customerSelect.options[customerSelect.selectedIndex];
    const customerId = customerSelect.value;
    
    // If customer is selected, try to get their email
    if (customerId) {
        // Check if there's a way to get customer email from the DOM or form
        // For now, just open the modal and let user enter email
    }
    
    // Show the modal using Bootstrap
    const emailModal = new bootstrap.Modal(document.getElementById('emailModal'));
    emailModal.show();
});

// Email modal submit
function submitWithEmail() {
    const email = document.getElementById('customerEmail').value;
    if (!email) {
        alert('Please enter an email address');
        return;
    }
    const form = document.getElementById('estimateForm');
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = 'save_send';
    form.appendChild(actionInput);
    
    const emailInput = document.createElement('input');
    emailInput.type = 'hidden';
    emailInput.name = 'send_to_email';
    emailInput.value = email;
    form.appendChild(emailInput);
    
    form.submit();
}
</script>
@endpush