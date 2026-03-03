@extends('layouts.app')

@section('title', 'Create Supplier')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Create New Supplier</h1>
                <div>
                    <a href="{{ route('inventory.suppliers.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Suppliers
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Supplier Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('inventory.suppliers.store') }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Supplier Name *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_person">Contact Person</label>
                                    <input type="text" class="form-control @error('contact_person') is-invalid @enderror" 
                                           id="contact_person" name="contact_person" value="{{ old('contact_person') }}">
                                    @error('contact_person')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="credit_limit">Credit Limit (₱)</label>
                                    <input type="number" class="form-control @error('credit_limit') is-invalid @enderror" 
                                           id="credit_limit" name="credit_limit" value="{{ old('credit_limit', 0) }}" step="0.01" min="0">
                                    @error('credit_limit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="payment_terms">Payment Terms (days)</label>
                                    <input type="number" class="form-control @error('payment_terms') is-invalid @enderror" 
                                           id="payment_terms" name="payment_terms" value="{{ old('payment_terms', 30) }}" min="0">
                                    @error('payment_terms')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="tax_id">Tax ID</label>
                                    <input type="text" class="form-control @error('tax_id') is-invalid @enderror" 
                                           id="tax_id" name="tax_id" value="{{ old('tax_id') }}">
                                    @error('tax_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', 1) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Active Supplier</label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="is_preferred" name="is_preferred" value="1" {{ old('is_preferred') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_preferred">Preferred Supplier</label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group mt-4">
                            <label for="notes">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Supplier
                            </button>
                            <a href="{{ route('inventory.suppliers.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Guidelines</h6>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold">Required Fields:</h6>
                    <ul class="mb-3">
                        <li><strong>Supplier Name</strong> - Unique name for identification</li>
                    </ul>
                    
                    <h6 class="font-weight-bold">Optional Information:</h6>
                    <ul>
                        <li><strong>Contact Person</strong> - Primary contact for orders</li>
                        <li><strong>Email & Phone</strong> - Communication details</li>
                        <li><strong>Address</strong> - Physical or mailing address</li>
                        <li><strong>Credit Limit</strong> - Maximum credit allowed (₱0 for cash only)</li>
                        <li><strong>Payment Terms</strong> - Days to pay invoices (default: 30)</li>
                        <li><strong>Tax ID</strong> - For official documentation</li>
                    </ul>
                    
                    <h6 class="font-weight-bold">Status:</h6>
                    <ul>
                        <li><strong>Active</strong> - Can receive orders (default: checked)</li>
                        <li><strong>Preferred</strong> - Priority supplier for auto-ordering</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection