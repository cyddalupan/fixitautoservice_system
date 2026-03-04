@extends('layouts.app')

@section('title', 'Edit Personnel')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header">
                <h1 class="mb-0">
                    <i class="fas fa-user-edit me-2"></i>Edit Personnel
                </h1>
                <p class="text-muted">Update information for {{ $user->name }}</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edit Personnel Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('personnel.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h6 class="mb-3">Basic Information</h6>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="role" class="form-label">Role *</label>
                                    <select class="form-control @error('role') is-invalid @enderror" 
                                            id="role" name="role" required>
                                        <option value="">Select Role</option>
                                        @foreach($roles as $key => $label)
                                            <option value="{{ $key }}" {{ old('role', $user->role) == $key ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Professional Information -->
                            <div class="col-md-6">
                                <h6 class="mb-3">Professional Information</h6>
                                
                                <div class="mb-3">
                                    <label for="specialization" class="form-label">Specialization</label>
                                    <textarea class="form-control @error('specialization') is-invalid @enderror" 
                                              id="specialization" name="specialization" rows="3">{{ old('specialization', $user->specialization) }}</textarea>
                                    @error('specialization')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="years_experience" class="form-label">Years of Experience</label>
                                    <input type="number" class="form-control @error('years_experience') is-invalid @enderror" 
                                           id="years_experience" name="years_experience" 
                                           value="{{ old('years_experience', $user->years_experience) }}" min="0">
                                    @error('years_experience')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="certifications" class="form-label">Certifications</label>
                                    <textarea class="form-control @error('certifications') is-invalid @enderror" 
                                              id="certifications" name="certifications" rows="3">{{ old('certifications', is_array($user->certifications) ? implode(', ', $user->certifications) : $user->certifications) }}</textarea>
                                    <small class="form-text text-muted">Enter certifications separated by commas</small>
                                    @error('certifications')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="skills" class="form-label">Skills</label>
                                    <textarea class="form-control @error('skills') is-invalid @enderror" 
                                              id="skills" name="skills" rows="3">{{ old('skills', is_array($user->skills) ? implode(', ', $user->skills) : $user->skills) }}</textarea>
                                    <small class="form-text text-muted">Enter skills separated by commas</small>
                                    @error('skills')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="hourly_rate" class="form-label">Hourly Rate ($)</label>
                                    <input type="number" step="0.01" class="form-control @error('hourly_rate') is-invalid @enderror" 
                                           id="hourly_rate" name="hourly_rate" 
                                           value="{{ old('hourly_rate', $user->hourly_rate) }}" min="0">
                                    @error('hourly_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Account Status -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="mb-3">Account Status</h6>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" 
                                           id="is_active" name="is_active" value="1" 
                                           {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active Account
                                    </label>
                                    <small class="form-text text-muted d-block">
                                        Uncheck to deactivate this personnel account
                                    </small>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" 
                                           id="can_login" name="can_login" value="1" 
                                           {{ old('can_login', $user->can_login) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="can_login">
                                        Allow System Login
                                    </label>
                                    <small class="form-text text-muted d-block">
                                        Allow this personnel to log in to the system
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Password Reset (Optional) -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="mb-3">Password Reset (Optional)</h6>
                                <p class="text-muted mb-3">
                                    Leave password fields blank to keep current password. Only fill if you want to change the password.
                                </p>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password" class="form-label">New Password</label>
                                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                                   id="password" name="password">
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                            <input type="password" class="form-control" 
                                                   id="password_confirmation" name="password_confirmation">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('personnel.show', $user) }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Update Personnel
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

<!-- JavaScript for form validation and enhancements -->
@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Phone number formatting
        const phoneInput = document.getElementById('phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length > 0) {
                    if (value.length <= 3) {
                        value = value;
                    } else if (value.length <= 6) {
                        value = value.slice(0, 3) + '-' + value.slice(3);
                    } else {
                        value = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6, 10);
                    }
                }
                e.target.value = value;
            });
        }

        // Hourly rate formatting
        const hourlyRateInput = document.getElementById('hourly_rate');
        if (hourlyRateInput) {
            hourlyRateInput.addEventListener('blur', function(e) {
                let value = parseFloat(e.target.value);
                if (!isNaN(value)) {
                    e.target.value = value.toFixed(2);
                }
            });
        }

        // Form validation
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Validate password confirmation
                const password = document.getElementById('password').value;
                const passwordConfirm = document.getElementById('password_confirmation').value;
                
                if (password && password !== passwordConfirm) {
                    e.preventDefault();
                    alert('Passwords do not match. Please confirm your password.');
                    document.getElementById('password_confirmation').focus();
                }
            });
        }
    });
</script>
@endsection
@endsection