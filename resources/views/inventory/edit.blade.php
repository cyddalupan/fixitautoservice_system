@extends('layouts.app')

@section('title', 'Edit Inventory: ' . $inventory->name)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fas fa-edit text-warning"></i> Edit Inventory Item
                </h1>
                <div>
                    <a href="{{ route('inventory.show', $inventory) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Item
                    </a>
                </div>
            </div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('inventory.index') }}">Inventory</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('inventory.show', $inventory) }}">{{ $inventory->name }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Item Details</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('inventory.update', $inventory) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="part_number" class="form-label">Part Number *</label>
                                <input type="text" class="form-control @error('part_number') is-invalid @enderror" 
                                       id="part_number" name="part_number" 
                                       value="{{ old('part_number', $inventory->part_number) }}" required>
                                @error('part_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Item Name *</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                       id="name" name="name" 
                                       value="{{ old('name', $inventory->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description', $inventory->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="category_id" class="form-label">Category *</label>
                                <select class="form-control @error('category_id') is-invalid @enderror" 
                                        id="category_id" name="category_id" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                            {{ old('category_id', $inventory->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="supplier_id" class="form-label">Supplier *</label>
                                <select class="form-control @error('supplier_id') is-invalid @enderror" 
                                        id="supplier_id" name="supplier_id" required>
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" 
                                            {{ old('supplier_id', $inventory->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="manufacturer" class="form-label">Manufacturer</label>
                                <input type="text" class="form-control @error('manufacturer') is-invalid @enderror" 
                                       id="manufacturer" name="manufacturer" 
                                       value="{{ old('manufacturer', $inventory->manufacturer) }}">
                                @error('manufacturer')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="oem_number" class="form-label">OEM Number</label>
                                <input type="text" class="form-control @error('oem_number') is-invalid @enderror" 
                                       id="oem_number" name="oem_number" 
                                       value="{{ old('oem_number', $inventory->oem_number) }}">
                                @error('oem_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                       id="location" name="location" 
                                       value="{{ old('location', $inventory->location) }}">
                                @error('location')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="bin" class="form-label">Bin</label>
                                <input type="text" class="form-control @error('bin') is-invalid @enderror" 
                                       id="bin" name="bin" 
                                       value="{{ old('bin', $inventory->bin) }}">
                                @error('bin')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="quantity" class="form-label">Quantity *</label>
                                <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                       id="quantity" name="quantity" min="0" step="1"
                                       value="{{ old('quantity', $inventory->quantity) }}" required>
                                @error('quantity')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="minimum_stock" class="form-label">Minimum Stock</label>
                                <input type="number" class="form-control @error('minimum_stock') is-invalid @enderror" 
                                       id="minimum_stock" name="minimum_stock" min="0" step="1"
                                       value="{{ old('minimum_stock', $inventory->minimum_stock) }}">
                                @error('minimum_stock')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="reorder_point" class="form-label">Reorder Point</label>
                                <input type="number" class="form-control @error('reorder_point') is-invalid @enderror" 
                                       id="reorder_point" name="reorder_point" min="0" step="1"
                                       value="{{ old('reorder_point', $inventory->reorder_point) }}">
                                @error('reorder_point')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="cost_price" class="form-label">Cost Price (₱) *</label>
                                <input type="number" class="form-control @error('cost_price') is-invalid @enderror" 
                                       id="cost_price" name="cost_price" min="0" step="0.01"
                                       value="{{ old('cost_price', $inventory->cost_price) }}" required>
                                @error('cost_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="retail_price" class="form-label">Retail Price (₱) *</label>
                                <input type="number" class="form-control @error('retail_price') is-invalid @enderror" 
                                       id="retail_price" name="retail_price" min="0" step="0.01"
                                       value="{{ old('retail_price', $inventory->retail_price) }}" required>
                                @error('retail_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="wholesale_price" class="form-label">Wholesale Price (₱)</label>
                                <input type="number" class="form-control @error('wholesale_price') is-invalid @enderror" 
                                       id="wholesale_price" name="wholesale_price" min="0" step="0.01"
                                       value="{{ old('wholesale_price', $inventory->wholesale_price) }}">
                                @error('wholesale_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="core_price" class="form-label">Core Price (₱)</label>
                                <input type="number" class="form-control @error('core_price') is-invalid @enderror" 
                                       id="core_price" name="core_price" min="0" step="0.01"
                                       value="{{ old('core_price', $inventory->core_price) }}">
                                @error('core_price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="tax_rate" class="form-label">Tax Rate (%)</label>
                                <input type="number" class="form-control @error('tax_rate') is-invalid @enderror" 
                                       id="tax_rate" name="tax_rate" min="0" max="100" step="0.01"
                                       value="{{ old('tax_rate', $inventory->tax_rate) }}">
                                @error('tax_rate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-control @error('status') is-invalid @enderror" 
                                        id="status" name="status">
                                    <option value="in_stock" {{ old('status', $inventory->status) == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                                    <option value="low_stock" {{ old('status', $inventory->status) == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                                    <option value="out_of_stock" {{ old('status', $inventory->status) == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                                    <option value="discontinued" {{ old('status', $inventory->status) == 'discontinued' ? 'selected' : '' }}>Discontinued</option>
                                    <option value="on_order" {{ old('status', $inventory->status) == 'on_order' ? 'selected' : '' }}>On Order</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_taxable" name="is_taxable" 
                                           value="1" {{ old('is_taxable', $inventory->is_taxable) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_taxable">
                                        Taxable Item
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" 
                                           value="1" {{ old('is_active', $inventory->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active Item
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="2">{{ old('notes', $inventory->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('inventory.show', $inventory) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Current Item Info</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Part Number:</th>
                            <td>{{ $inventory->part_number }}</td>
                        </tr>
                        <tr>
                            <th>Name:</th>
                            <td>{{ $inventory->name }}</td>
                        </tr>
                        <tr>
                            <th>Current Stock:</th>
                            <td>
                                <span class="badge {{ $inventory->quantity <= $inventory->reorder_point ? 'bg-danger' : ($inventory->quantity <= $inventory->minimum_stock ? 'bg-warning' : 'bg-success') }}">
                                    {{ number_format($inventory->quantity) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Cost Price:</th>
                            <td>₱{{ number_format($inventory->cost_price, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Retail Price:</th>
                            <td>₱{{ number_format($inventory->retail_price, 2) }}</td>
                        </tr>
                        <tr>
                            <th>Markup:</th>
                            <td>
                                @php
                                    $markup = $inventory->cost_price > 0 ? (($inventory->retail_price - $inventory->cost_price) / $inventory->cost_price * 100) : 0;
                                @endphp
                                <span class="{{ $markup >= 50 ? 'text-success' : 'text-warning' }}">
                                    {{ number_format($markup, 1) }}%
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Total Value:</th>
                            <td>₱{{ number_format($inventory->quantity * $inventory->cost_price, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('inventory.show', $inventory) }}" class="btn btn-outline-primary">
                            <i class="fas fa-eye"></i> View Item
                        </a>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="fas fa-trash"></i> Delete Item
                        </button>
                        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list"></i> Back to Inventory
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong>{{ $inventory->name }}</strong>?</p>
                <p class="text-danger"><strong>Warning:</strong> This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('inventory.destroy', $inventory) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection