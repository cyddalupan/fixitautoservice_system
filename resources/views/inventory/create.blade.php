@extends('layouts.app')

@section('title', 'Add New Inventory Item')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Add New Inventory Item</h1>
                <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Inventory
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Item Details</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('inventory.store') }}">
                        @csrf
                        
                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Basic Information</h5>
                                
                                <div class="form-group">
                                    <label for="part_number">Part Number *</label>
                                    <input type="text" class="form-control @error('part_number') is-invalid @enderror" 
                                           id="part_number" name="part_number" value="{{ old('part_number') }}" required>
                                    @error('part_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Unique identifier for this part</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="name">Name *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="category_id">Category *</label>
                                            <select class="form-control @error('category_id') is-invalid @enderror" 
                                                    id="category_id" name="category_id" required>
                                                <option value="">Select Category</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('category_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="supplier_id">Supplier *</label>
                                            <select class="form-control @error('supplier_id') is-invalid @enderror" 
                                                    id="supplier_id" name="supplier_id" required>
                                                <option value="">Select Supplier</option>
                                                @foreach($suppliers as $supplier)
                                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                                        {{ $supplier->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('supplier_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Identification -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Identification</h5>
                                
                                <div class="form-group">
                                    <label for="manufacturer">Manufacturer</label>
                                    <input type="text" class="form-control @error('manufacturer') is-invalid @enderror" 
                                           id="manufacturer" name="manufacturer" value="{{ old('manufacturer') }}">
                                    @error('manufacturer')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="oem_number">OEM Number</label>
                                    <input type="text" class="form-control @error('oem_number') is-invalid @enderror" 
                                           id="oem_number" name="oem_number" value="{{ old('oem_number') }}">
                                    @error('oem_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="upc">UPC / Barcode</label>
                                    <input type="text" class="form-control @error('upc') is-invalid @enderror" 
                                           id="upc" name="upc" value="{{ old('upc') }}">
                                    @error('upc')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="location">Location</label>
                                            <input type="text" class="form-control @error('location') is-invalid @enderror" 
                                                   id="location" name="location" value="{{ old('location') }}">
                                            @error('location')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">e.g., Shelf A, Bin 3</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="bin">Bin</label>
                                            <input type="text" class="form-control @error('bin') is-invalid @enderror" 
                                                   id="bin" name="bin" value="{{ old('bin') }}">
                                            @error('bin')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="row">
                            <!-- Stock Information -->
                            <div class="col-md-4">
                                <h5 class="mb-3">Stock Information</h5>
                                
                                <div class="form-group">
                                    <label for="quantity">Initial Quantity *</label>
                                    <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                           id="quantity" name="quantity" value="{{ old('quantity', 0) }}" min="0" required>
                                    @error('quantity')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="minimum_stock">Minimum Stock Level *</label>
                                    <input type="number" class="form-control @error('minimum_stock') is-invalid @enderror" 
                                           id="minimum_stock" name="minimum_stock" value="{{ old('minimum_stock', 5) }}" min="0" required>
                                    @error('minimum_stock')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Alert when stock reaches this level</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="reorder_point">Reorder Point *</label>
                                    <input type="number" class="form-control @error('reorder_point') is-invalid @enderror" 
                                           id="reorder_point" name="reorder_point" value="{{ old('reorder_point', 10) }}" min="0" required>
                                    @error('reorder_point')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Automatically create purchase order at this level</small>
                                </div>
                            </div>
                            
                            <!-- Pricing -->
                            <div class="col-md-4">
                                <h5 class="mb-3">Pricing</h5>
                                
                                <div class="form-group">
                                    <label for="cost_price">Cost Price *</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">₱</span>
                                        </div>
                                        <input type="number" step="0.01" class="form-control @error('cost_price') is-invalid @enderror" 
                                               id="cost_price" name="cost_price" value="{{ old('cost_price', 0) }}" min="0" required>
                                        @error('cost_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="retail_price">Retail Price *</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">₱</span>
                                        </div>
                                        <input type="number" step="0.01" class="form-control @error('retail_price') is-invalid @enderror" 
                                               id="retail_price" name="retail_price" value="{{ old('retail_price', 0) }}" min="0" required>
                                        @error('retail_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="wholesale_price">Wholesale Price</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">₱</span>
                                        </div>
                                        <input type="number" step="0.01" class="form-control @error('wholesale_price') is-invalid @enderror" 
                                               id="wholesale_price" name="wholesale_price" value="{{ old('wholesale_price') }}" min="0">
                                        @error('wholesale_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="core_price">Core Price</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">₱</span>
                                        </div>
                                        <input type="number" step="0.01" class="form-control @error('core_price') is-invalid @enderror" 
                                               id="core_price" name="core_price" value="{{ old('core_price') }}" min="0">
                                        @error('core_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <small class="form-text text-muted">Core charge for exchange parts</small>
                                </div>
                            </div>
                            
                            <!-- Tax & Status -->
                            <div class="col-md-4">
                                <h5 class="mb-3">Tax & Status</h5>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_taxable" name="is_taxable" value="1" {{ old('is_taxable', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_taxable">Taxable Item</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="tax_rate">Tax Rate (%)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" class="form-control @error('tax_rate') is-invalid @enderror" 
                                               id="tax_rate" name="tax_rate" value="{{ old('tax_rate', 0) }}" min="0" max="100">
                                        <div class="input-group-append">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        @error('tax_rate')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_active">Active Item</label>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="image_url">Image URL</label>
                                    <input type="url" class="form-control @error('image_url') is-invalid @enderror" 
                                           id="image_url" name="image_url" value="{{ old('image_url') }}">
                                    @error('image_url')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                <div class="form-group">
                                    <label for="barcode">Custom Barcode</label>
                                    <input type="text" class="form-control @error('barcode') is-invalid @enderror" 
                                           id="barcode" name="barcode" value="{{ old('barcode') }}">
                                    @error('barcode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <!-- Notes -->
                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Inventory Item
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Auto-calculate markup when prices change
        $('#cost_price, #retail_price').on('input', function() {
            var cost = parseFloat($('#cost_price').val()) || 0;
            var retail = parseFloat($('#retail_price').val()) || 0;
            
            if (cost > 0 && retail > 0) {
                var markup = ((retail - cost) / cost) * 100;
                $('#markup_display').text('Markup: ' + markup.toFixed(2) + '%');
            }
        });
        
        // Set default values if empty
        if (!$('#minimum_stock').val()) {
            $('#minimum_stock').val(5);
        }
        if (!$('#reorder_point').val()) {
            $('#reorder_point').val(10);
        }
    });
</script>
@endsection