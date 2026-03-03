@extends('layouts.app')

@section('title', 'Create New Estimate - Fix-It Auto Services')

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
                        </h5>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th width="40%">Item / Service</th>
                                        <th width="15%">Quantity</th>
                                        <th width="20%">Unit Price</th>
                                        <th width="25%">Total / Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsBody">
                                    <tr id="noItemsRow">
                                        <td colspan="4" class="text-center text-muted py-4">
                                            <i class="fas fa-info-circle me-2"></i>No items added yet. Click "Add Item" to start.
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Subtotal:</td>
                                        <td>₱0.00</td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold">Tax (0%):</td>
                                        <td>₱0.00</td>
                                    </tr>
                                    <tr class="table-primary">
                                        <td colspan="3" class="text-end fw-bold fs-5">Total:</td>
                                        <td class="fw-bold fs-5">₱0.00</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addItemBtn">
                                <i class="fas fa-plus"></i> Add Another Item
                            </button>
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
                                <button type="submit" name="action" value="save_send" class="btn btn-primary ms-2">
                                    <i class="fas fa-paper-plane"></i> Save & Send to Customer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Simple autocomplete implementation for Fixit Auto Services

// Pass PHP inventory data to JavaScript
const rawInventoryItems = @json($inventoryItems);

// Transform inventory items to match expected format
const inventoryItems = rawInventoryItems.map(item => ({
    id: item.id,
    name: item.name,
    part_number: item.part_number,
    description: item.description,
    retail_price: parseFloat(item.retail_price),
    type: 'inventory'
}));

// Test services data (you can replace with real services from database)
const services = [
    { id: 1001, name: 'Oil Change Service', description: 'Complete oil change with filter', retail_price: 89.99, type: 'service' },
    { id: 1002, name: 'Tire Rotation', description: 'Rotate all four tires', retail_price: 39.99, type: 'service' },
    { id: 1003, name: 'Brake Service', description: 'Brake inspection and service', retail_price: 129.99, type: 'service' },
    { id: 1004, name: 'Wheel Alignment', description: 'Four-wheel alignment service', retail_price: 79.99, type: 'service' },
    { id: 1005, name: 'AC Recharge', description: 'Air conditioning system recharge', retail_price: 99.99, type: 'service' }
];

// Combine inventory and services for autocomplete
const allSuggestions = [...inventoryItems, ...services];

console.log('Raw inventory items:', rawInventoryItems.length);
console.log('Transformed inventory items:', inventoryItems.length);
console.log('Services loaded:', services.length);
console.log('Total suggestions:', allSuggestions.length);
console.log('Sample inventory item:', inventoryItems[0]);

document.addEventListener('DOMContentLoaded', function() {
    console.log('Estimate form loaded');
    
    // Replace the alert with actual functionality
    const addItemBtn = document.getElementById('addItemBtn');
    if (addItemBtn) {
        addItemBtn.addEventListener('click', function(e) {
            e.preventDefault();
            addNewItemRow();
        });
    }
});

let itemCounter = 0;

function addNewItemRow() {
    const tbody = document.getElementById('itemsBody');
    const noItemsRow = document.getElementById('noItemsRow');
    
    // Remove "no items" message if present
    if (noItemsRow) {
        noItemsRow.remove();
    }
    
    itemCounter++;
    
    const newRow = document.createElement('tr');
    newRow.id = `itemRow_${itemCounter}`;
    newRow.innerHTML = `
        <td>
            <div style="position: relative;">
                <input type="text" 
                       class="form-control item-input" 
                       name="items[${itemCounter}][item_name]" 
                       placeholder="Type 'bumper', 'oil', 'filter', etc..."
                       autocomplete="off"
                       onfocus="showAutocompleteOnFocus(this, ${itemCounter})"
                       oninput="showAutocomplete(this, ${itemCounter})"
                       required>
                <div class="autocomplete-dropdown" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #ccc; z-index: 1000; max-height: 200px; overflow-y: auto;"></div>
            </div>
        </td>
        <td>
            <input type="number" 
                   class="form-control quantity-input" 
                   name="items[${itemCounter}][quantity]" 
                   value="1" min="1" step="1"
                   onchange="calculateTotal(${itemCounter})"
                   required>
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-text">₱</span>
                <input type="number" 
                       class="form-control price-input" 
                       name="items[${itemCounter}][unit_price]" 
                       value="0.00" min="0" step="0.01"
                       onchange="calculateTotal(${itemCounter})"
                       required>
            </div>
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-text">₱</span>
                <input type="text" 
                       class="form-control total-input" 
                       name="items[${itemCounter}][total]" 
                       value="0.00" readonly>
            </div>
            <button type="button" class="btn btn-sm btn-danger mt-1" onclick="removeItem(${itemCounter})" style="width: 100%;">
                <i class="fas fa-trash"></i> Remove
            </button>
        </td>
    `;
    
    tbody.appendChild(newRow);
    console.log(`Added item row ${itemCounter}`);
}

function showAutocomplete(input, rowId) {
    const searchTerm = input.value.toLowerCase().trim();
    const dropdown = input.nextElementSibling;
    
    console.log(`Searching for: "${searchTerm}" in row ${rowId}`);
    
    // Use real inventory data and services
    let filtered = allSuggestions;
    
    // Only filter if there's a search term
    if (searchTerm.length > 0) {
        filtered = allSuggestions.filter(item => {
            // Search in name, part_number, and description
            const nameMatch = item.name.toLowerCase().includes(searchTerm);
            const partMatch = item.part_number ? item.part_number.toLowerCase().includes(searchTerm) : false;
            const descMatch = item.description ? item.description.toLowerCase().includes(searchTerm) : false;
            
            return nameMatch || partMatch || descMatch;
        });
    }
    
    // Limit to top 10 items when showing all (no search term)
    if (searchTerm.length === 0 && filtered.length > 10) {
        filtered = filtered.slice(0, 10);
    }
    
    if (filtered.length > 0) {
        dropdown.innerHTML = filtered.map(item => `
            <div style="padding: 8px 12px; border-bottom: 1px solid #eee; cursor: pointer;"
                 onclick="selectSuggestion(this, ${rowId}, '${item.name.replace(/'/g, "\\'")}', ${item.retail_price}, ${item.id || 'null'})">
                <strong>${item.name}</strong>
                <span style="float: right; color: green;">$${item.retail_price.toFixed(2)}</span>
                <div style="font-size: 12px; color: #666;">
                    ${item.type === 'inventory' ? 'Inventory Item' : 'Service'}
                    ${item.part_number ? ` • ${item.part_number}` : ''}
                </div>
                ${item.description ? `<div style="font-size: 11px; color: #888;">${item.description}</div>` : ''}
            </div>
        `).join('');
        dropdown.style.display = 'block';
    } else {
        dropdown.style.display = 'none';
    }
}

// New function to show autocomplete on focus/click
function showAutocompleteOnFocus(input, rowId) {
    const dropdown = input.nextElementSibling;
    
    // Only show if dropdown is hidden
    if (dropdown.style.display === 'none' || dropdown.style.display === '') {
        // Clear any existing search term temporarily to show all items
        const originalValue = input.value;
        input.value = '';
        showAutocomplete(input, rowId);
        input.value = originalValue;
    }
}

function selectSuggestion(element, rowId, name, price, inventoryId) {
    const row = document.getElementById(`itemRow_${rowId}`);
    const input = row.querySelector('.item-input');
    const priceInput = row.querySelector('.price-input');
    const dropdown = input.nextElementSibling;
    
    input.value = name;
    priceInput.value = price.toFixed(2);
    dropdown.style.display = 'none';
    
    // Add or update hidden inventory_id field
    let inventoryIdInput = row.querySelector('input[name*="[inventory_id]"]');
    if (!inventoryIdInput) {
        inventoryIdInput = document.createElement('input');
        inventoryIdInput.type = 'hidden';
        inventoryIdInput.name = `items[${rowId}][inventory_id]`;
        inventoryIdInput.className = 'inventory-id-input';
        row.appendChild(inventoryIdInput);
    }
    inventoryIdInput.value = inventoryId !== 'null' ? inventoryId : '';
    
    calculateTotal(rowId);
    console.log(`Selected: ${name} for $${price}, inventory_id: ${inventoryId}`);
}

function calculateTotal(rowId) {
    const row = document.getElementById(`itemRow_${rowId}`);
    const quantity = parseFloat(row.querySelector('.quantity-input').value) || 0;
    const price = parseFloat(row.querySelector('.price-input').value) || 0;
    const total = quantity * price;
    
    row.querySelector('.total-input').value = total.toFixed(2);
    updateGrandTotal();
}

function updateGrandTotal() {
    let subtotal = 0;
    document.querySelectorAll('.total-input').forEach(input => {
        subtotal += parseFloat(input.value) || 0;
    });
    
    // Update UI if there are subtotal/total elements
    const subtotalEl = document.getElementById('subtotalCell');
    const totalEl = document.getElementById('totalCell');
    
    if (subtotalEl) subtotalEl.textContent  = '₱' + subtotal.toFixed(2);
    if (totalEl) totalEl.textContent  = '₱' + subtotal.toFixed(2);
}

function removeItem(rowId) {
    const row = document.getElementById(`itemRow_${rowId}`);
    if (row) {
        row.remove();
        updateGrandTotal();
        
        // Show "no items" message if all items are removed
        const rows = document.querySelectorAll('[id^="itemRow_"]');
        if (rows.length === 0) {
            const tbody = document.getElementById('itemsBody');
            const noItemsRow = document.createElement('tr');
            noItemsRow.id = 'noItemsRow';
            noItemsRow.innerHTML = `
                <td colspan="6" class="text-center text-muted py-4">
                    <i class="fas fa-info-circle me-2"></i>No items added yet. Click "Add Item" to start.
                </td>
            `;
            tbody.appendChild(noItemsRow);
        }
    }
}

// Close autocomplete dropdown when clicking outside
document.addEventListener('click', function(event) {
    const dropdowns = document.querySelectorAll('.autocomplete-dropdown');
    const inputs = document.querySelectorAll('.item-input');
    
    let isClickInsideDropdown = false;
    let isClickInsideInput = false;
    
    dropdowns.forEach(dropdown => {
        if (dropdown.contains(event.target)) {
            isClickInsideDropdown = true;
        }
    });
    
    inputs.forEach(input => {
        if (input.contains(event.target)) {
            isClickInsideInput = true;
        }
    });
    
    // Hide all dropdowns if click is outside both input and dropdown
    if (!isClickInsideDropdown && !isClickInsideInput) {
        dropdowns.forEach(dropdown => {
            dropdown.style.display = 'none';
        });
    }
});
</script>
@endpush
