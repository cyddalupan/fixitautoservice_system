@extends('layouts.app')

@section('title', 'My Profile - Customer Portal')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-user me-2"></i>My Profile
            </h1>
            <p class="text-muted mb-0">Manage your personal information and preferences</p>
        </div>
        <div>
            <a href="{{ route('portal.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Profile Information -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user-circle me-2"></i>Personal Information
                </h6>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                <form method="POST" action="{{ route('portal.profile.update') }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label">First Name *</label>
                            <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                   id="first_name" name="first_name" value="{{ old('first_name', $customer->first_name) }}" required>
                            @error('first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label">Last Name *</label>
                            <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                   id="last_name" name="last_name" value="{{ old('last_name', $customer->last_name) }}" required>
                            @error('last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $customer->email) }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone Number *</label>
                            <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                   id="phone" name="phone" value="{{ old('phone', $customer->phone) }}" required>
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control @error('address') is-invalid @enderror" 
                               id="address" name="address" value="{{ old('address', $customer->address) }}">
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                   id="city" name="city" value="{{ old('city', $customer->city) }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="state" class="form-label">State</label>
                            <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                   id="state" name="state" value="{{ old('state', $customer->state) }}">
                            @error('state')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-4 mb-3">
                            <label for="zip_code" class="form-label">ZIP Code</label>
                            <input type="text" class="form-control @error('zip_code') is-invalid @enderror" 
                                   id="zip_code" name="zip_code" value="{{ old('zip_code', $customer->zip_code) }}">
                            @error('zip_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="emergency_contact_name" class="form-label">Emergency Contact Name</label>
                            <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                   id="emergency_contact_name" name="emergency_contact_name" 
                                   value="{{ old('emergency_contact_name', $customer->emergency_contact_name) }}">
                            @error('emergency_contact_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="emergency_contact_phone" class="form-label">Emergency Contact Phone</label>
                            <input type="tel" class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                                   id="emergency_contact_phone" name="emergency_contact_phone" 
                                   value="{{ old('emergency_contact_phone', $customer->emergency_contact_phone) }}">
                            @error('emergency_contact_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                        <a href="{{ route('portal.dashboard') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Account Information -->
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-id-card me-2"></i>Account Information
                </h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="avatar-circle mb-3">
                        <i class="fas fa-user fa-3x text-primary"></i>
                    </div>
                    <h5 class="mb-1">{{ $customer->first_name }} {{ $customer->last_name }}</h5>
                    <p class="text-muted mb-0">{{ $customer->email }}</p>
                    <p class="text-muted small mb-0">Customer since {{ $customer->created_at->format('M Y') }}</p>
                </div>
                
                <div class="list-group list-group-flush">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Portal Member Since</span>
                        <span class="text-muted">{{ auth()->guard('portal')->user()->created_at->format('M j, Y') }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Last Login</span>
                        <span class="text-muted">{{ auth()->guard('portal')->user()->last_login_at ? auth()->guard('portal')->user()->last_login_at->format('M j, Y g:i A') : 'Never' }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Account Status</span>
                        <span class="badge bg-success">Active</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Links -->
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-cog me-2"></i>Account Settings
                </h6>
            </div>
            <div class="card-body">
                <div class="list-group list-group-flush">
                    <a href="{{ route('portal.profile.preferences') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-bell me-2"></i>Notification Preferences
                    </a>
                    <a href="{{ route('portal.password.request') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-key me-2"></i>Change Password
                    </a>
                    <a href="{{ route('portal.vehicles') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-car me-2"></i>My Vehicles
                    </a>
                    <a href="{{ route('portal.appointments') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-calendar me-2"></i>My Appointments
                    </a>
                    <a href="{{ route('portal.work-orders') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-clipboard-list me-2"></i>My Work Orders
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .avatar-circle {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        color: white;
    }
</style>
@endsection