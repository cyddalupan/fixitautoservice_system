@extends('layouts.app')

@section('title', 'Edit Customer - Fix-It Auto Services')
@section('body-class', 'page-customers')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-module-header">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="module-icon">
                    <i class="fas fa-user-edit"></i>
                </div>
                <div>
                    <h1 class="h4 mb-1" style="font-weight: 700;">Edit Customer</h1>
                    <ol class="breadcrumb m-0 p-0" style="background: none; font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('customers.show', $customer) }}">{{ $customer->first_name }} {{ $customer->last_name }}</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('customers.show', $customer) }}" class="btn-filter-outline">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <a href="{{ route('customers.index') }}" class="btn-filter-outline">
                    <i class="fas fa-arrow-left me-1"></i> Back to Customers
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="main-card">
                <div class="main-card-header">
                    <i class="fas fa-edit me-2" style="color: var(--module-active);"></i> Edit Customer Profile
                </div>
                <div class="main-card-body p-4">
                    <form method="POST" action="{{ route('customers.update', $customer) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <!-- Name Row -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                           id="first_name" name="first_name" 
                                           value="{{ old('first_name', $customer->first_name) }}" required>
                                </div>
                                @error('first_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                           id="last_name" name="last_name" 
                                           value="{{ old('last_name', $customer->last_name) }}" required>
                                </div>
                                @error('last_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Contact Row -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" 
                                           value="{{ old('phone', $customer->phone) }}" required>
                                </div>
                                <div class="form-text">Auto-formatted as you type</div>
                                @error('phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" 
                                           value="{{ old('email', $customer->email) }}">
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Social & Address -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="facebook_profile" class="form-label">Facebook Profile / Messenger</label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fab fa-facebook-messenger"></i></span>
                                    <input type="text" class="form-control @error('facebook_profile') is-invalid @enderror" 
                                           id="facebook_profile" name="facebook_profile" 
                                           value="{{ old('facebook_profile', $customer->facebook_profile) }}">
                                </div>
                                <div class="form-text">Facebook profile URL or Messenger username</div>
                                @error('facebook_profile')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="address" class="form-label">Address</label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                           id="address" name="address" 
                                           value="{{ old('address', $customer->address) }}">
                                </div>
                                @error('address')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Additional Info -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="city" class="form-label">City</label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fas fa-city"></i></span>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" 
                                           value="{{ old('city', $customer->city) }}">
                                </div>
                                @error('city')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="state" class="form-label">State / Province</label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fas fa-map"></i></span>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                           id="state" name="state" 
                                           value="{{ old('state', $customer->state) }}">
                                </div>
                                @error('state')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="zip_code" class="form-label">Zip Code</label>
                                <div class="input-group-fixit">
                                    <span class="input-group-text"><i class="fas fa-mailbox"></i></span>
                                    <input type="text" class="form-control @error('zip_code') is-invalid @enderror" 
                                           id="zip_code" name="zip_code" 
                                           value="{{ old('zip_code', $customer->zip_code) }}">
                                </div>
                                @error('zip_code')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Status & Type -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <label for="customer_type" class="form-label">Customer Type</label>
                                <select class="form-select @error('customer_type') is-invalid @enderror" 
                                        id="customer_type" name="customer_type">
                                    <option value="individual" {{ $customer->customer_type == 'individual' ? 'selected' : '' }}>Individual</option>
                                    <option value="business" {{ $customer->customer_type == 'business' ? 'selected' : '' }}>Business</option>
                                </select>
                                @error('customer_type')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="segment" class="form-label">Segment</label>
                                <select class="form-select @error('segment') is-invalid @enderror" 
                                        id="segment" name="segment">
                                    <option value="">Select Segment</option>
                                    <option value="standard" {{ $customer->segment == 'standard' ? 'selected' : '' }}>Standard</option>
                                    <option value="premium" {{ $customer->segment == 'premium' ? 'selected' : '' }}>Premium</option>
                                    <option value="vip" {{ $customer->segment == 'vip' ? 'selected' : '' }}>VIP</option>
                                    <option value="fleet" {{ $customer->segment == 'fleet' ? 'selected' : '' }}>Fleet</option>
                                </select>
                                @error('segment')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="is_active" class="form-label">Status</label>
                                <select class="form-select @error('is_active') is-invalid @enderror" 
                                        id="is_active" name="is_active">
                                    <option value="1" {{ $customer->is_active ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ !$customer->is_active ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('is_active')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Preferred Contact -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="preferred_contact_method" class="form-label">Preferred Contact Method</label>
                                <select class="form-select" id="preferred_contact_method" name="preferred_contact_method">
                                    <option value="">Select</option>
                                    <option value="phone" {{ $customer->preferred_contact_method == 'phone' ? 'selected' : '' }}>Phone Call</option>
                                    <option value="sms" {{ $customer->preferred_contact_method == 'sms' ? 'selected' : '' }}>SMS / Text</option>
                                    <option value="email" {{ $customer->preferred_contact_method == 'email' ? 'selected' : '' }}>Email</option>
                                    <option value="facebook" {{ $customer->preferred_contact_method == 'facebook' ? 'selected' : '' }}>Facebook Messenger</option>
                                </select>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="mb-4">
                            <label for="notes" class="form-label">Notes (Internal)</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3"
                                      placeholder="Any additional notes about this customer">{{ old('notes', $customer->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <!-- Submit -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted"><span class="text-danger">*</span> Required fields</small>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-times me-1"></i> Cancel
                                </a>
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fas fa-save me-1"></i> Update Customer
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Customer Portal Login Card -->
            <div class="main-card mb-4">
                <div class="main-card-header">
                    <i class="fas fa-user-lock me-2" style="color: var(--module-active);"></i> Customer Portal Login
                </div>
                <div class="card-body p-4">
                    @php $portalUser = $customer->portalUser; @endphp
                    
                    @if($portalUser)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Login Email</label>
                            <div class="d-flex align-items-center">
                                <input type="email" class="form-control" id="portalEmail" value="{{ $portalUser->email }}" readonly>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <div>
                                @if($portalUser->is_active)
                                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Active</span>
                                @else
                                    <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Disabled</span>
                                @endif
                                @if($portalUser->email_verified_at)
                                    <span class="badge bg-info ms-1"><i class="fas fa-envelope me-1"></i> Email Verified</span>
                                @else
                                    <span class="badge bg-warning ms-1"><i class="fas fa-envelope me-1"></i> Not Verified</span>
                                @endif
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Update Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="portalPassword" placeholder="New password (leave blank to keep current)" autocomplete="new-password">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePortalPassword()">
                                    <i class="fas fa-eye" id="portalPasswordToggle"></i>
                                </button>
                            </div>
                            <div class="mt-2 d-flex gap-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" id="portalToggleActive" {{ $portalUser->is_active ? 'checked' : '' }}>
                                    <label class="form-check-label" for="portalToggleActive">Account Active</label>
                                </div>
                            </div>
                            <button class="btn btn-primary btn-sm mt-2" onclick="updatePortal({{ $customer->id }})">
                                <i class="fas fa-save me-1"></i> Save Portal Settings
                            </button>
                        </div>
                        <div class="mt-3 pt-3 border-top">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Customer can book appointments at <a href="https://app.fixitautoservices.com/booking/login" target="_blank">app.fixitautoservices.com/booking/login</a>
                            </small>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-user-circle fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-3">This customer doesn't have a portal account yet.</p>
                            <button class="btn btn-primary" onclick="createPortal({{ $customer->id }})">
                                <i class="fas fa-plus me-1"></i> Create Portal Account
                            </button>
                            <div class="mt-3 text-start" id="portalCreateForm" style="display:none;">
                                <div class="mb-2">
                                    <label class="form-label fw-bold">Email</label>
                                    <input type="email" class="form-control" id="newPortalEmail" value="{{ $customer->email }}" placeholder="customer@email.com">
                                </div>
                                <div class="mb-2">
                                    <label class="form-label fw-bold">Password</label>
                                    <input type="password" class="form-control" id="newPortalPassword" placeholder="Set a password">
                                </div>
                                <button class="btn btn-success btn-sm" onclick="saveNewPortal({{ $customer->id }})">
                                    <i class="fas fa-check me-1"></i> Create
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" onclick="document.getElementById('portalCreateForm').style.display='none'">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-4">
            <!-- Profile Photo Card -->
            <div class="main-card mb-4">
                <div class="main-card-header">
                    <i class="fas fa-camera me-2" style="color: var(--module-active);"></i> Profile Photo
                </div>
                <div class="main-card-body px-3 py-3 text-center">
                    <div style="width:120px;height:120px;border-radius:50%;margin:0 auto 1rem;overflow:hidden;border:3px solid #eef0f3;background:#f4f6fa;display:flex;align-items:center;justify-content:center;" id="customerEditPhotoPreview">
                        @if($customer->profile_picture)
                            <img src="{{ asset('storage/' . $customer->profile_picture) }}" alt="{{ $customer->first_name }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <i class="fas fa-user" style="font-size:2.5rem;color:#9ca3af;"></i>
                        @endif
                    </div>
                    <input type="file" class="d-none" id="customerEditPhotoInput" name="profile_picture" accept="image/*">
                    <input type="hidden" name="cropped_image" id="customerEditCroppedInput" value="">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('customerEditPhotoInput').click();">
                        <i class="fas fa-upload me-1"></i> @if($customer->profile_picture) Replace Photo @else Upload Photo @endif
                    </button>
                    @if($customer->profile_picture)
                        <div class="mt-2">
                            <label class="btn btn-outline-danger btn-sm" id="customerRemovePhotoBtn" style="cursor:pointer;font-size:.72rem;">
                                <i class="fas fa-trash me-1"></i> Remove
                            </label>
                            <input type="hidden" name="remove_photo" id="customerRemovePhotoInput" value="0">
                        </div>
                    @endif
                </div>
            </div>

            <div class="main-card">
                <div class="main-card-header">
                    <i class="fas fa-info-circle me-2" style="color: var(--module-active);"></i> Quick Info
                </div>
                <div class="main-card-body px-3 py-3">
                    <div class="customer-details-section">
                        <div class="detail-label">Customer ID</div>
                        <div class="detail-row">
                            <i class="fas fa-hashtag detail-icon"></i>
                            <code>#{{ $customer->id }}</code>
                        </div>
                    </div>
                    <div class="customer-details-section">
                        <div class="detail-label">Member Since</div>
                        <div class="detail-row">
                            <i class="fas fa-calendar detail-icon"></i>
                            <span>{{ $customer->created_at->format('F j, Y') }}</span>
                        </div>
                    </div>
                    <div class="customer-details-section">
                        <div class="detail-label">Last Updated</div>
                        <div class="detail-row">
                            <i class="fas fa-clock detail-icon"></i>
                            <span>{{ $customer->updated_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="customer-details-section">
                        <div class="detail-label">Loyalty Points</div>
                        <div class="detail-row">
                            <i class="fas fa-star detail-icon" style="color: #f59e0b;"></i>
                            <strong>{{ number_format($customer->loyalty_points) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <div class="main-card mt-4">
                <div class="main-card-header">
                    <i class="fas fa-external-link-alt me-2" style="color: var(--module-active);"></i> Quick Links
                </div>
                <div class="main-card-body px-3 py-3">
                    <div class="d-grid gap-2">
                        <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-primary btn-sm text-start">
                            <i class="fas fa-eye me-2"></i> View Customer Profile
                        </a>
                        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-sm text-start">
                            <i class="fas fa-users me-2"></i> All Customers
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('partials.avatar-crop-modal')

@push('scripts')
<script>
// ── Customer photo upload + crop ──
document.addEventListener('DOMContentLoaded', function () {
    initAvatarCrop({
        fileInput: '#customerEditPhotoInput',
        previewWrap: '#customerEditPhotoPreview',
        previewImg: '#customerEditPhotoPreview img',
        hiddenInput: '#customerEditCroppedInput',
        aspectRatio: 1,
    });

    const removeBtn = document.getElementById('customerRemovePhotoBtn');
    if (removeBtn) {
        removeBtn.addEventListener('click', function () {
            if (confirm('Remove the current profile photo?')) {
                document.getElementById('customerRemovePhotoInput').value = '1';
                document.getElementById('customerEditPhotoPreview').innerHTML = '<i class="fas fa-user" style="font-size:2.5rem;color:#9ca3af;"></i>';
                document.getElementById('customerEditCroppedInput').value = '';
                this.style.display = 'none';
            }
        });
    }
});

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

        // Handle form submission with AJAX and SweetAlert2
        $('form').on('submit', function(e) {
            e.preventDefault();
            
            var form = $(this);
            var formData = new FormData(this);
            var submitButton = form.find('button[type="submit"]');
            var originalButtonText = submitButton.html();
            
            // Show loading state
            submitButton.prop('disabled', true);
            submitButton.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...');
            
            // Submit form via AJAX
            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-HTTP-Method-Override': 'PUT' // Laravel method spoofing for PUT requests
                },
                success: function(response) {
                    if (response.success) {
                        // Show beautiful success notification
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: response.message || 'Customer updated successfully',
                            showConfirmButton: true,
                            confirmButtonText: 'View Customer',
                            confirmButtonColor: '#3085d6',
                            allowOutsideClick: false,
                            timer: 5000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Redirect to customer profile
                                window.location.href = response.redirect_url || '{{ route("customers.show", $customer) }}';
                            } else {
                                // Auto-redirect after timer
                                window.location.href = response.redirect_url || '{{ route("customers.show", $customer) }}';
                            }
                        });
                    }
                },
                error: function(xhr) {
                    // Restore button state
                    submitButton.prop('disabled', false);
                    submitButton.html(originalButtonText);
                    
                    if (xhr.status === 422) {
                        // Validation errors
                        var errors = xhr.responseJSON.errors;
                        var errorMessages = '';
                        
                        $.each(errors, function(field, messages) {
                            var fieldLabel = field.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                            errorMessages += '<strong>' + fieldLabel + ':</strong> ' + messages.join(', ') + '<br>';
                        });
                        
                        // Show error notification
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            html: errorMessages,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#d33'
                        });
                    } else if (xhr.status === 500) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: 'Internal server error. Please try again later.',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#d33'
                        });
                    } else {
                        // General error
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'An error occurred while updating the customer. Please try again.',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#d33'
                        });
                    }
                }
            });
        });
    });
</script>

<script>
// ── Portal Account Management ──
function togglePortalPassword() {
    var pwd = document.getElementById('portalPassword');
    var icon = document.getElementById('portalPasswordToggle');
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        pwd.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function updatePortal(customerId) {
    var password = document.getElementById('portalPassword').value;
    var isActive = document.getElementById('portalToggleActive').checked;
    
    $.ajax({
        url: '/portal-admin/' + customerId + '/update',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            password: password,
            is_active: isActive ? 1 : 0,
        },
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: 'Portal settings saved successfully.',
                timer: 2000,
                showConfirmButton: true
            });
            document.getElementById('portalPassword').value = '';
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: xhr.responseJSON?.message || 'Failed to update portal settings.',
                confirmButtonColor: '#d33'
            });
        }
    });
}

function createPortal(customerId) {
    var form = document.getElementById('portalCreateForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

function saveNewPortal(customerId) {
    var email = document.getElementById('newPortalEmail').value;
    var password = document.getElementById('newPortalPassword').value;
    
    if (!email || !password) {
        Swal.fire({ icon: 'warning', title: 'Required', text: 'Email and password are required.' });
        return;
    }
    
    $.ajax({
        url: '/portal-admin/' + customerId + '/create',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            email: email,
            password: password,
        },
        success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Created!',
                text: 'Portal account created successfully.',
                timer: 2000,
                showConfirmButton: true
            }).then(function() {
                location.reload();
            });
        },
        error: function(xhr) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: xhr.responseJSON?.message || 'Failed to create portal account.',
                confirmButtonColor: '#d33'
            });
        }
    });
}
</script>
@endpush
