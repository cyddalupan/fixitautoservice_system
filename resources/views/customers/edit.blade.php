@extends('layouts.app')

@section('title', 'Edit Customer - Fix-It Auto Services')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-user-edit me-2"></i>Edit Customer
            </h1>
            <p class="text-muted mb-0">Update customer profile information</p>
        </div>
        <div>
            <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Customers
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ route('customers.update', $customer) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <!-- Personal Information -->
                        <div class="col-md-6">
                            <h5 class="mb-3 border-bottom pb-2">Personal Information</h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="first_name" class="form-label">First Name *</label>
                                        <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                               id="first_name" name="first_name" value="{{ old('first_name', $customer->first_name) }}" required>
                                        @error('first_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="last_name" class="form-label">Last Name *</label>
                                        <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                               id="last_name" name="last_name" value="{{ old('last_name', $customer->last_name) }}" required>
                                        @error('last_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                               id="email" name="email" value="{{ old('email', $customer->email) }}">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="phone" class="form-label">Phone Number *</label>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                               id="phone" name="phone" value="{{ old('phone', $customer->phone) }}" required>
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="customer_type" class="form-label">Customer Type *</label>
                                <select class="form-select @error('customer_type') is-invalid @enderror" 
                                        id="customer_type" name="customer_type" required>
                                    <option value="">Select Type</option>
                                    <option value="individual" {{ old('customer_type', $customer->customer_type) == 'individual' ? 'selected' : '' }}>Individual</option>
                                    <option value="commercial" {{ old('customer_type', $customer->customer_type) == 'commercial' ? 'selected' : '' }}>Commercial</option>
                                    <option value="fleet" {{ old('customer_type', $customer->customer_type) == 'fleet' ? 'selected' : '' }}>Fleet</option>
                                </select>
                                @error('customer_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="segment" class="form-label">Customer Segment</label>
                                <select class="form-select @error('segment') is-invalid @enderror" 
                                        id="segment" name="segment">
                                    <option value="">Select Segment</option>
                                    <option value="premium" {{ old('segment', $customer->segment) == 'premium' ? 'selected' : '' }}>Premium</option>
                                    <option value="regular" {{ old('segment', $customer->segment) == 'regular' ? 'selected' : '' }}>Regular</option>
                                    <option value="commercial" {{ old('segment', $customer->segment) == 'commercial' ? 'selected' : '' }}>Commercial</option>
                                </select>
                                @error('segment')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <!-- Address Information -->
                        <div class="col-md-6">
                            <h5 class="mb-3 border-bottom pb-2">Address Information</h5>
                            
                            <div class="form-group mb-3">
                                <label for="address" class="form-label">Street Address</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                       id="address" name="address" value="{{ old('address', $customer->address) }}">
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                               id="city" name="city" value="{{ old('city', $customer->city) }}">
                                        @error('city')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="state" class="form-label">State</label>
                                        <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                               id="state" name="state" value="{{ old('state', $customer->state) }}">
                                        @error('state')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="zip_code" class="form-label">ZIP Code</label>
                                        <input type="text" class="form-control @error('zip_code') is-invalid @enderror" 
                                               id="zip_code" name="zip_code" value="{{ old('zip_code', $customer->zip_code) }}">
                                        @error('zip_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="country" class="form-label">Country</label>
                                        <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                               id="country" name="country" value="{{ old('country', $customer->country ?? 'USA') }}">
                                        @error('country')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="preferred_contact_method" class="form-label">Preferred Contact Method</label>
                                <select class="form-select @error('preferred_contact_method') is-invalid @enderror" 
                                        id="preferred_contact_method" name="preferred_contact_method">
                                    <option value="">Select Method</option>
                                    <option value="phone" {{ old('preferred_contact_method', $customer->preferred_contact_method) == 'phone' ? 'selected' : '' }}>Phone</option>
                                    <option value="email" {{ old('preferred_contact_method', $customer->preferred_contact_method) == 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="sms" {{ old('preferred_contact_method', $customer->preferred_contact_method) == 'sms' ? 'selected' : '' }}>SMS</option>
                                </select>
                                @error('preferred_contact_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                            </div>
                            
                            <div class="form-group mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $customer->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active Customer
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Additional Information -->
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3 border-bottom pb-2">Additional Information</h5>
                            
                            <div class="form-group mb-3">
                                <label for="company_name" class="form-label">Company Name</label>
                                <input type="text" class="form-control @error('company_name') is-invalid @enderror" 
                                       id="company_name" name="company_name" value="{{ old('company_name', $customer->company_name) }}">
                                @error('company_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="tax_id" class="form-label">Tax ID / EIN</label>
                                <input type="text" class="form-control @error('tax_id') is-invalid @enderror" 
                                       id="tax_id" name="tax_id" value="{{ old('tax_id', $customer->tax_id) }}">
                                @error('tax_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="loyalty_points" class="form-label">Loyalty Points</label>
                                <input type="number" class="form-control @error('loyalty_points') is-invalid @enderror" 
                                       id="loyalty_points" name="loyalty_points" value="{{ old('loyalty_points', $customer->loyalty_points) }}" min="0">
                                @error('loyalty_points')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                            </div>
                            
                            <h5 class="mb-3 border-bottom pb-2 mt-4">Financial Information</h5>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="credit_limit" class="form-label">Credit Limit (₱)</label>
                                        <input type="number" class="form-control @error('credit_limit') is-invalid @enderror" 
                                               id="credit_limit" name="credit_limit" value="{{ old('credit_limit', $customer->credit_limit) }}" min="0" step="0.01">
                                        @error('credit_limit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror>
                                        <small class="form-text text-muted">Maximum credit allowed</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="balance" class="form-label">Current Balance (₱)</label>
                                        <input type="number" class="form-control @error('balance') is-invalid @enderror" 
                                               id="balance" name="balance" value="{{ old('balance', $customer->balance) }}" step="0.01">
                                        @error('balance')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror>
                                        <small class="form-text text-muted">Current account balance</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="payment_terms" class="form-label">Payment Terms *</label>
                                <select class="form-select @error('payment_terms') is-invalid @enderror" 
                                        id="payment_terms" name="payment_terms" required>
                                    <option value="">Select Terms</option>
                                    <option value="net_15" {{ old('payment_terms', $customer->payment_terms) == 'net_15' ? 'selected' : '' }}>Net 15</option>
                                    <option value="net_30" {{ old('payment_terms', $customer->payment_terms) == 'net_30' ? 'selected' : '' }}>Net 30</option>
                                    <option value="net_60" {{ old('payment_terms', $customer->payment_terms) == 'net_60' ? 'selected' : '' }}>Net 60</option>
                                    <option value="cod" {{ old('payment_terms', $customer->payment_terms) == 'cod' ? 'selected' : '' }}>Cash on Delivery</option>
                                </select>
                                @error('payment_terms')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="preferred_contact" class="form-label">Preferred Contact Method *</label>
                                <select class="form-select @error('preferred_contact') is-invalid @enderror" 
                                        id="preferred_contact" name="preferred_contact" required>
                                    <option value="">Select Method</option>
                                    <option value="email" {{ old('preferred_contact', $customer->preferred_contact) == 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="phone" {{ old('preferred_contact', $customer->preferred_contact) == 'phone' ? 'selected' : '' }}>Phone</option>
                                    <option value="sms" {{ old('preferred_contact', $customer->preferred_contact) == 'sms' ? 'selected' : '' }}>SMS</option>
                                </select>
                                @error('preferred_contact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                            </div>
                            
                            <div class="form-group mb-3">
                                <label for="is_active" class="form-label">Account Status</label>
                                <select class="form-select @error('is_active') is-invalid @enderror" 
                                        id="is_active" name="is_active">
                                    <option value="1" {{ old('is_active', $customer->is_active) == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('is_active', $customer->is_active) == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h5 class="mb-3 border-bottom pb-2">Notes</h5>
                            
                            <div class="form-group mb-3">
                                <label for="notes" class="form-label">Customer Notes</label>
                                <textarea class="form-control @error('notes') is-invalid @enderror" 
                                          id="notes" name="notes" rows="5">{{ old('notes', $customer->notes) }}</textarea>
                                @error('notes')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror>
                                <small class="form-text text-muted">Add any important notes about this customer</small>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Update Customer
                            </button>
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fas fa-trash me-1"></i> Delete
                            </button>
                        </div>
                    </div>
                </form>
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
                <p>Are you sure you want to delete customer <strong>{{ $customer->first_name }} {{ $customer->last_name }}</strong>?</p>
                <p class="text-danger"><strong>Warning:</strong> This action cannot be undone. All associated vehicles and service records will also be deleted.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" action="{{ route('customers.destroy', $customer) }}" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Customer</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Auto-format phone number
        $('#phone').on('input', function() {
            var phone = $(this).val().replace(/\D/g, '');
            if (phone.length > 3 && phone.length <= 6) {
                phone = phone.replace(/(\d{3})(\d+)/, '($1) $2');
            } else if (phone.length > 6) {
                phone = phone.replace(/(\d{3})(\d{3})(\d+)/, '($1) $2-$3');
            }
            $(this).val(phone);
        });
        
        // Show/hide company fields based on customer type
        $('#customer_type').on('change', function() {
            var type = $(this).val();
            if (type === 'commercial' || type === 'fleet') {
                $('#company_name').closest('.form-group').show();
                $('#tax_id').closest('.form-group').show();
            } else {
                $('#company_name').closest('.form-group').hide();
                $('#tax_id').closest('.form-group').hide();
            }
        });
        
        // Trigger change on page load
        $('#customer_type').trigger('change');
    });
</script>
@endsection