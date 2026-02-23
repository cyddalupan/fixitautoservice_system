@extends('layouts.app')

@section('title', 'Account Settings')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Account Settings</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Settings</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3">
            <!-- Settings Navigation -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="#profile" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                            <i class="bi bi-person me-2"></i> Profile
                        </a>
                        <a href="#notifications" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-bell me-2"></i> Notifications
                        </a>
                        <a href="#privacy" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-shield-lock me-2"></i> Privacy & Security
                        </a>
                        <a href="#communication" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-chat-text me-2"></i> Communication
                        </a>
                        <a href="#billing" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-credit-card me-2"></i> Billing
                        </a>
                        <a href="#preferences" class="list-group-item list-group-item-action" data-bs-toggle="list">
                            <i class="bi bi-gear me-2"></i> Preferences
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">Account Status</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="avatar avatar-sm">
                                <img src="{{ auth()->user()->avatar_url ?? asset('images/default-avatar.png') }}" 
                                     alt="{{ auth()->user()->name }}" class="rounded-circle">
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-0">{{ auth()->user()->name }}</h6>
                            <small class="text-muted">Member since {{ auth()->user()->created_at->format('M Y') }}</small>
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <small class="text-muted d-block">Email Verified</small>
                        <div class="d-flex align-items-center">
                            @if(auth()->user()->email_verified_at)
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span class="text-success">Verified</span>
                            @else
                            <i class="bi bi-x-circle-fill text-danger me-2"></i>
                            <span class="text-danger">Not Verified</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <small class="text-muted d-block">Phone Verified</small>
                        <div class="d-flex align-items-center">
                            @if(auth()->user()->phone_verified_at)
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span class="text-success">Verified</span>
                            @else
                            <i class="bi bi-x-circle-fill text-danger me-2"></i>
                            <span class="text-danger">Not Verified</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mb-2">
                        <small class="text-muted d-block">2FA Enabled</small>
                        <div class="d-flex align-items-center">
                            @if(auth()->user()->two_factor_enabled)
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span class="text-success">Enabled</span>
                            @else
                            <i class="bi bi-x-circle-fill text-warning me-2"></i>
                            <span class="text-warning">Disabled</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Profile Tab -->
                <div class="tab-pane fade show active" id="profile">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Profile Information</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('portal.settings.update-profile') }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Full Name</label>
                                        <input type="text" class="form-control" id="name" name="name" 
                                               value="{{ old('name', auth()->user()->name) }}" required>
                                        @error('name')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" name="email" 
                                               value="{{ old('email', auth()->user()->email) }}" required>
                                        @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="{{ old('phone', auth()->user()->phone) }}">
                                        @error('phone')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="date_of_birth" class="form-label">Date of Birth</label>
                                        <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" 
                                               value="{{ old('date_of_birth', auth()->user()->date_of_birth ? auth()->user()->date_of_birth->format('Y-m-d') : '') }}">
                                        @error('date_of_birth')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-12 mb-3">
                                        <label for="address" class="form-label">Address</label>
                                        <textarea class="form-control" id="address" name="address" rows="2">{{ old('address', auth()->user()->address) }}</textarea>
                                        @error('address')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save me-1"></i> Save Changes
                                        </button>
                                        <a href="{{ route('portal.settings.index') }}" class="btn btn-outline-secondary">
                                            Cancel
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Profile Photo Card -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Profile Photo</h5>
                        </div>
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="avatar avatar-xxl">
                                        <img src="{{ auth()->user()->avatar_url ?? asset('images/default-avatar.png') }}" 
                                             alt="{{ auth()->user()->name }}" class="rounded-circle" id="profileAvatar">
                                    </div>
                                </div>
                                <div class="col">
                                    <p class="mb-2">Upload a new profile photo. JPG, GIF or PNG. Max size 2MB.</p>
                                    <form action="{{ route('portal.settings.update-avatar') }}" method="POST" enctype="multipart/form-data" id="avatarForm">
                                        @csrf
                                        @method('PUT')
                                        <div class="input-group">
                                            <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                                            <button class="btn btn-outline-primary" type="submit">Upload</button>
                                        </div>
                                        @error('avatar')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notifications Tab -->
                <div class="tab-pane fade" id="notifications">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Notification Preferences</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('portal.settings.update-notifications') }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <h6 class="mb-3">Email Notifications</h6>
                                <div class="mb-4">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="email_appointment_reminders" 
                                               name="email_appointment_reminders" value="1" 
                                               {{ old('email_appointment_reminders', auth()->user()->notification_settings['email']['appointment_reminders'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="email_appointment_reminders">
                                            Appointment reminders
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="email_service_updates" 
                                               name="email_service_updates" value="1" 
                                               {{ old('email_service_updates', auth()->user()->notification_settings['email']['service_updates'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="email_service_updates">
                                            Service updates
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="email_billing_notifications" 
                                               name="email_billing_notifications" value="1" 
                                               {{ old('email_billing_notifications', auth()->user()->notification_settings['email']['billing_notifications'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="email_billing_notifications">
                                            Billing notifications
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="email_promotional_emails" 
                                               name="email_promotional_emails" value="1" 
                                               {{ old('email_promotional_emails', auth()->user()->notification_settings['email']['promotional_emails'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="email_promotional_emails">
                                            Promotional emails
                                        </label>
                                    </div>
                                </div>
                                
                                <h6 class="mb-3">SMS Notifications</h6>
                                <div class="mb-4">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="sms_appointment_reminders" 
                                               name="sms_appointment_reminders" value="1" 
                                               {{ old('sms_appointment_reminders', auth()->user()->notification_settings['sms']['appointment_reminders'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="sms_appointment_reminders">
                                            Appointment reminders
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="sms_service_updates" 
                                               name="sms_service_updates" value="1" 
                                               {{ old('sms_service_updates', auth()->user()->notification_settings['sms']['service_updates'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="sms_service_updates">
                                            Service updates
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="sms_urgent_alerts" 
                                               name="sms_urgent_alerts" value="1" 
                                               {{ old('sms_urgent_alerts', auth()->user()->notification_settings['sms']['urgent_alerts'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="sms_urgent_alerts">
                                            Urgent alerts
                                        </label>
                                    </div>
                                </div>
                                
                                <h6 class="mb-3">Push Notifications</h6>
                                <div class="mb-4">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="push_appointment_reminders" 
                                               name="push_appointment_reminders" value="1" 
                                               {{ old('push_appointment_reminders', auth()->user()->notification_settings['push']['appointment_reminders'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="push_appointment_reminders">
                                            Appointment reminders
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="push_service_updates" 
                                               name="push_service_updates" value="1" 
                                               {{ old('push_service_updates', auth()->user()->notification_settings['push']['service_updates'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="push_service_updates">
                                            Service updates
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="push_billing_notifications" 
                                               name="push_billing_notifications" value="1" 
                                               {{ old('push_billing_notifications', auth()->user()->notification_settings['push']['billing_notifications'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="push_billing_notifications">
                                            Billing notifications
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i> Save Notification Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Privacy & Security Tab -->
                <div class="tab-pane fade" id="privacy">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Privacy & Security</h5>
                        </div>
                        <div class="card-body">
                            <!-- Password Change -->
                            <div class="mb-4">
                                <h6 class="mb-3">Change Password</h6>
                                <form action="{{ route('portal.settings.change-password') }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="current_password" class="form-label">Current Password</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                                            @error('current_password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="new_password" class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                                            @error('new_password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                                            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
                                        </div>
                                        
                                        <div class="col-12">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="bi bi-key me-1"></i> Change Password
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Two-Factor Authentication -->
                            <div class="mb-4">
                                <h6 class="mb-3">Two-Factor Authentication</h6>
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    Two-factor authentication adds an extra layer of security to your account.
                                </div>
                                
                                @if(auth()->user()->two_factor_enabled)
                                <div class="alert alert-success">
                                    <i class="bi bi-check-circle me-2"></i>
                                    2FA is currently enabled for your account.
                                </div>
                                
                                <form action="{{ route('portal.settings.disable-2fa') }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-shield-slash me-1"></i> Disable 2FA
                                    </button>
                                </form>
                                @else
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    2FA is not enabled. We recommend enabling it for better security.
                                </div>
                                
                                <a href="{{ route('portal.settings.enable-2fa') }}" class="btn btn-primary">
                                    <i class="bi bi-shield-check me-1"></i> Enable 2FA
                                </a>
                                @endif
                            </div>
                            
                            <!-- Session Management -->
                            <div class="mb-4">
                                <h6 class="mb-3">Active Sessions</h6>
                                <p class="text-muted mb-3">Manage your active login sessions across devices.</p>
                                
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Device</th>
                                                <th>Location</th>
                                                <th>Last Active</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <i class="bi bi-laptop me-2"></i>
                                                    <span>Chrome on Windows</span>
                                                </td>
                                                <td>New York, US</td>
                                                <td>Just now</td>
                                                <td>
                                                    <span class="badge bg-success">Current</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    <i class="bi bi-phone me-2"></i>
                                                    <span>Safari on iPhone</span>
                                                </td>
                                                <td>New York, US</td>
                                                <td>2 hours ago</td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-danger">Revoke</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="mt-3">
                                    <form action="{{ route('portal.settings.revoke-other-sessions') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-warning">
                                            <i class="bi bi-door-closed me-1"></i> Revoke All Other Sessions
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                            <!-- Data Privacy -->
                            <div>
                                <h6 class="mb-3">Data Privacy</h6>
                                <div class="alert alert-light">
                                    <i class="bi bi-file-earmark-text me-2"></i>
                                    <strong>Data Export</strong>
                                    <p class="mb-2">You can request a copy of all your personal data.</p>
                                    <a href="{{ route('portal.settings.export-data') }}" class="btn btn-sm btn-outline-primary">
                                        Request Data Export
                                    </a>
                                </div>
                                
                                <div class="alert alert-light">
                                    <i class="bi bi-trash me-2"></i>
                                    <strong>Account Deletion</strong>
                                    <p class="mb-2">Permanently delete your account and all associated data.</p>
                                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                                        Delete Account
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Communication Tab -->
                <div class="tab-pane fade" id="communication">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Communication Preferences</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('portal.settings.update-communication') }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <h6 class="mb-3">Preferred Contact Methods</h6>
                                <div class="mb-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="prefer_email" 
                                               name="prefer_email" value="1" 
                                               {{ old('prefer_email', auth()->user()->communication_preferences['prefer_email'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="prefer_email">
                                            Email
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="prefer_sms" 
                                               name="prefer_sms" value="1" 
                                               {{ old('prefer_sms', auth()->user()->communication_preferences['prefer_sms'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="prefer_sms">
                                            SMS/Text Message
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" id="prefer_phone" 
                                               name="prefer_phone" value="1" 
                                               {{ old('prefer_phone', auth()->user()->communication_preferences['prefer_phone'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="prefer_phone">
                                            Phone Call
                                        </label>
                                    </div>
                                    
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="prefer_push" 
                                               name="prefer_push" value="1" 
                                               {{ old('prefer_push', auth()->user()->communication_preferences['prefer_push'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="prefer_push">
                                            Push Notifications
                                        </label>
                                    </div>
                                </div>
                                
                                <h6 class="mb-3">Communication Hours</h6>
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <label for="communication_start" class="form-label">Start Time</label>
                                        <select class="form-select" id="communication_start" name="communication_start">
                                            @for($i = 0; $i < 24; $i++)
                                            <option value="{{ $i }}:00" {{ old('communication_start', auth()->user()->communication_preferences['start_time'] ?? '9:00') == $i.':00' ? 'selected' : '' }}>
                                                {{ $i }}:00
                                            </option>
                                            @endfor
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="communication_end" class="form-label">End Time</label>
                                        <select class="form-select" id="communication_end" name="communication_end">
                                            @for($i = 0; $i < 24; $i++)
                                            <option value="{{ $i }}:00" {{ old('communication_end', auth()->user()->communication_preferences['end_time'] ?? '17:00') == $i.':00' ? 'selected' : '' }}>
                                                {{ $i }}:00
                                            </option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>
                                
                                <h6 class="mb-3">Do Not Disturb</h6>
                                <div class="mb-4">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="dnd_enabled" 
                                               name="dnd_enabled" value="1" 
                                               {{ old('dnd_enabled', auth()->user()->communication_preferences['dnd_enabled'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="dnd_enabled">
                                            Enable Do Not Disturb
                                        </label>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="dnd_start" class="form-label">DND Start Time</label>
                                            <input type="time" class="form-control" id="dnd_start" name="dnd_start" 
                                                   value="{{ old('dnd_start', auth()->user()->communication_preferences['dnd_start'] ?? '22:00') }}">
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="dnd_end" class="form-label">DND End Time</label>
                                            <input type="time" class="form-control" id="dnd_end" name="dnd_end" 
                                                   value="{{ old('dnd_end', auth()->user()->communication_preferences['dnd_end'] ?? '8:00') }}">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i> Save Communication Preferences
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Billing Tab -->
                <div class="tab-pane fade" id="billing">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Billing Preferences</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('portal.settings.update-billing') }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <h6 class="mb-3">Invoice Preferences</h6>
                                <div class="mb-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" id="invoice_email" 
                                               name="invoice_delivery" value="email" 
                                               {{ old('invoice_delivery', auth()->user()->billing_preferences['invoice_delivery'] ?? 'email') == 'email' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="invoice_email">
                                            Email invoices only
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" id="invoice_postal" 
                                               name="invoice_delivery" value="postal" 
                                               {{ old('invoice_delivery', auth()->user()->billing_preferences['invoice_delivery'] ?? 'email') == 'postal' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="invoice_postal">
                                            Postal mail only
                                        </label>
                                    </div>
                                    
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="invoice_both" 
                                               name="invoice_delivery" value="both" 
                                               {{ old('invoice_delivery', auth()->user()->billing_preferences['invoice_delivery'] ?? 'email') == 'both' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="invoice_both">
                                            Both email and postal mail
                                        </label>
                                    </div>
                                </div>
                                
                                <h6 class="mb-3">Payment Reminders</h6>
                                <div class="mb-4">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="reminder_7_days" 
                                               name="reminder_7_days" value="1" 
                                               {{ old('reminder_7_days', auth()->user()->billing_preferences['reminder_7_days'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="reminder_7_days">
                                            7 days before due date
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="reminder_3_days" 
                                               name="reminder_3_days" value="1" 
                                               {{ old('reminder_3_days', auth()->user()->billing_preferences['reminder_3_days'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="reminder_3_days">
                                            3 days before due date
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="reminder_1_day" 
                                               name="reminder_1_day" value="1" 
                                               {{ old('reminder_1_day', auth()->user()->billing_preferences['reminder_1_day'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="reminder_1_day">
                                            1 day before due date
                                        </label>
                                    </div>
                                </div>
                                
                                <h6 class="mb-3">Auto-Pay Settings</h6>
                                <div class="mb-4">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="auto_pay_enabled" 
                                               name="auto_pay_enabled" value="1" 
                                               {{ old('auto_pay_enabled', auth()->user()->billing_preferences['auto_pay_enabled'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="auto_pay_enabled">
                                            Enable Auto-Pay
                                        </label>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="auto_pay_threshold" class="form-label">Auto-Pay Threshold</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" id="auto_pay_threshold" 
                                                       name="auto_pay_threshold" min="0" step="0.01" 
                                                       value="{{ old('auto_pay_threshold', auth()->user()->billing_preferences['auto_pay_threshold'] ?? 100) }}">
                                            </div>
                                            <small class="text-muted">Only auto-pay invoices below this amount</small>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label for="auto_pay_method" class="form-label">Default Payment Method</label>
                                            <select class="form-select" id="auto_pay_method" name="auto_pay_method">
                                                <option value="credit_card" {{ old('auto_pay_method', auth()->user()->billing_preferences['auto_pay_method'] ?? 'credit_card') == 'credit_card' ? 'selected' : '' }}>
                                                    Credit Card
                                                </option>
                                                <option value="bank_transfer" {{ old('auto_pay_method', auth()->user()->billing_preferences['auto_pay_method'] ?? 'credit_card') == 'bank_transfer' ? 'selected' : '' }}>
                                                    Bank Transfer
                                                </option>
                                                <option value="paypal" {{ old('auto_pay_method', auth()->user()->billing_preferences['auto_pay_method'] ?? 'credit_card') == 'paypal' ? 'selected' : '' }}>
                                                    PayPal
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i> Save Billing Preferences
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Preferences Tab -->
                <div class="tab-pane fade" id="preferences">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">General Preferences</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('portal.settings.update-preferences') }}" method="POST">
                                @csrf
                                @method('PUT')
                                
                                <h6 class="mb-3">Theme & Appearance</h6>
                                <div class="mb-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" id="theme_light" 
                                               name="theme" value="light" 
                                               {{ old('theme', auth()->user()->preferences['theme'] ?? 'light') == 'light' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="theme_light">
                                            Light Theme
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" id="theme_dark" 
                                               name="theme" value="dark" 
                                               {{ old('theme', auth()->user()->preferences['theme'] ?? 'light') == 'dark' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="theme_dark">
                                            Dark Theme
                                        </label>
                                    </div>
                                    
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" id="theme_auto" 
                                               name="theme" value="auto" 
                                               {{ old('theme', auth()->user()->preferences['theme'] ?? 'light') == 'auto' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="theme_auto">
                                            Auto (Follow system)
                                        </label>
                                    </div>
                                </div>
                                
                                <h6 class="mb-3">Language & Region</h6>
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3">
                                        <label for="language" class="form-label">Language</label>
                                        <select class="form-select" id="language" name="language">
                                            <option value="en" {{ old('language', auth()->user()->preferences['language'] ?? 'en') == 'en' ? 'selected' : '' }}>
                                                English
                                            </option>
                                            <option value="es" {{ old('language', auth()->user()->preferences['language'] ?? 'en') == 'es' ? 'selected' : '' }}>
                                                Español
                                            </option>
                                            <option value="fr" {{ old('language', auth()->user()->preferences['language'] ?? 'en') == 'fr' ? 'selected' : '' }}>
                                                Français
                                            </option>
                                            <option value="de" {{ old('language', auth()->user()->preferences['language'] ?? 'en') == 'de' ? 'selected' : '' }}>
                                                Deutsch
                                            </option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="timezone" class="form-label">Timezone</label>
                                        <select class="form-select" id="timezone" name="timezone">
                                            <option value="America/New_York" {{ old('timezone', auth()->user()->preferences['timezone'] ?? 'UTC') == 'America/New_York' ? 'selected' : '' }}>
                                                Eastern Time (ET)
                                            </option>
                                            <option value="America/Chicago" {{ old('timezone', auth()->user()->preferences['timezone'] ?? 'UTC') == 'America/Chicago' ? 'selected' : '' }}>
                                                Central Time (CT)
                                            </option>
                                            <option value="America/Denver" {{ old('timezone', auth()->user()->preferences['timezone'] ?? 'UTC') == 'America/Denver' ? 'selected' : '' }}>
                                                Mountain Time (MT)
                                            </option>
                                            <option value="America/Los_Angeles" {{ old('timezone', auth()->user()->preferences['timezone'] ?? 'UTC') == 'America/Los_Angeles' ? 'selected' : '' }}>
                                                Pacific Time (PT)
                                            </option>
                                            <option value="UTC" {{ old('timezone', auth()->user()->preferences['timezone'] ?? 'UTC') == 'UTC' ? 'selected' : '' }}>
                                                UTC
                                            </option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="date_format" class="form-label">Date Format</label>
                                        <select class="form-select" id="date_format" name="date_format">
                                            <option value="m/d/Y" {{ old('date_format', auth()->user()->preferences['date_format'] ?? 'm/d/Y') == 'm/d/Y' ? 'selected' : '' }}>
                                                MM/DD/YYYY
                                            </option>
                                            <option value="d/m/Y" {{ old('date_format', auth()->user()->preferences['date_format'] ?? 'm/d/Y') == 'd/m/Y' ? 'selected' : '' }}>
                                                DD/MM/YYYY
                                            </option>
                                            <option value="Y-m-d" {{ old('date_format', auth()->user()->preferences['date_format'] ?? 'm/d/Y') == 'Y-m-d' ? 'selected' : '' }}>
                                                YYYY-MM-DD
                                            </option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="time_format" class="form-label">Time Format</label>
                                        <select class="form-select" id="time_format" name="time_format">
                                            <option value="12" {{ old('time_format', auth()->user()->preferences['time_format'] ?? '12') == '12' ? 'selected' : '' }}>
                                                12-hour (AM/PM)
                                            </option>
                                            <option value="24" {{ old('time_format', auth()->user()->preferences['time_format'] ?? '12') == '24' ? 'selected' : '' }}>
                                                24-hour
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                
                                <h6 class="mb-3">Dashboard Preferences</h6>
                                <div class="mb-4">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="show_welcome_message" 
                                               name="show_welcome_message" value="1" 
                                               {{ old('show_welcome_message', auth()->user()->preferences['show_welcome_message'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_welcome_message">
                                            Show welcome message
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="show_recent_activity" 
                                               name="show_recent_activity" value="1" 
                                               {{ old('show_recent_activity', auth()->user()->preferences['show_recent_activity'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_recent_activity">
                                            Show recent activity
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="show_upcoming_appointments" 
                                               name="show_upcoming_appointments" value="1" 
                                               {{ old('show_upcoming_appointments', auth()->user()->preferences['show_upcoming_appointments'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_upcoming_appointments">
                                            Show upcoming appointments
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="show_service_history" 
                                               name="show_service_history" value="1" 
                                               {{ old('show_service_history', auth()->user()->preferences['show_service_history'] ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="show_service_history">
                                            Show service history
                                        </label>
                                    </div>
                                </div>
                                
                                <h6 class="mb-3">Accessibility</h6>
                                <div class="mb-4">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="high_contrast" 
                                               name="high_contrast" value="1" 
                                               {{ old('high_contrast', auth()->user()->preferences['high_contrast'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="high_contrast">
                                            High contrast mode
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="larger_text" 
                                               name="larger_text" value="1" 
                                               {{ old('larger_text', auth()->user()->preferences['larger_text'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="larger_text">
                                            Larger text size
                                        </label>
                                    </div>
                                    
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="reduced_motion" 
                                               name="reduced_motion" value="1" 
                                               {{ old('reduced_motion', auth()->user()->preferences['reduced_motion'] ?? false) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="reduced_motion">
                                            Reduced motion
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i> Save Preferences
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteAccountModalLabel">Delete Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone.
                </div>
                <p>Deleting your account will:</p>
                <ul>
                    <li>Permanently delete all your personal data</li>
                    <li>Remove all your service records and history</li>
                    <li>Cancel any pending appointments</li>
                    <li>Delete all your messages and documents</li>
                </ul>
                <p>Are you sure you want to delete your account?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('portal.settings.delete-account') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Yes, Delete My Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab activation
    const tabLinks = document.querySelectorAll('[data-bs-toggle="list"]');
    tabLinks.forEach(link => {
        link.addEventListener('click', function() {
            tabLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Profile photo preview
    const avatarInput = document.getElementById('avatar');
    const avatarPreview = document.getElementById('profileAvatar');
    
    if (avatarInput && avatarPreview) {
        avatarInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    avatarPreview.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });
    
    // Password strength indicator
    const newPasswordInput = document.getElementById('new_password');
    if (newPasswordInput) {
        newPasswordInput.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            updatePasswordStrengthIndicator(strength);
        });
    }
    
    function calculatePasswordStrength(password) {
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        
        return strength;
    }
    
    function updatePasswordStrengthIndicator(strength) {
        const indicator = document.getElementById('passwordStrengthIndicator');
        if (!indicator) return;
        
        let text = '';
        let color = '';
        
        switch(strength) {
            case 0:
            case 1:
                text = 'Very Weak';
                color = 'danger';
                break;
            case 2:
                text = 'Weak';
                color = 'warning';
                break;
            case 3:
                text = 'Fair';
                color = 'info';
                break;
            case 4:
                text = 'Good';
                color = 'primary';
                break;
            case 5:
                text = 'Strong';
                color = 'success';
                break;
        }
        
        indicator.textContent = text;
        indicator.className = `badge bg-${color}`;
    }
    
    // Auto-pay threshold validation
    const autoPayEnabled = document.getElementById('auto_pay_enabled');
    const autoPayThreshold = document.getElementById('auto_pay_threshold');
    
    if (autoPayEnabled && autoPayThreshold) {
        autoPayEnabled.addEventListener('change', function() {
            autoPayThreshold.disabled = !this.checked;
        });
        
        // Initial state
        autoPayThreshold.disabled = !autoPayEnabled.checked;
    }
    
    // Theme preview
    const themeRadios = document.querySelectorAll('input[name="theme"]');
    themeRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'dark') {
                document.body.classList.add('bg-dark', 'text-light');
            } else {
                document.body.classList.remove('bg-dark', 'text-light');
            }
        });
    });
    
    // Communication hours validation
    const communicationStart = document.getElementById('communication_start');
    const communicationEnd = document.getElementById('communication_end');
    
    if (communicationStart && communicationEnd) {
        communicationStart.addEventListener('change', validateCommunicationHours);
        communicationEnd.addEventListener('change', validateCommunicationHours);
    }
    
    function validateCommunicationHours() {
        const start = communicationStart.value;
        const end = communicationEnd.value;
        
        if (start && end) {
            const startHour = parseInt(start.split(':')[0]);
            const endHour = parseInt(end.split(':')[0]);
            
            if (endHour <= startHour) {
                communicationEnd.classList.add('is-invalid');
                communicationEnd.nextElementSibling.textContent = 'End time must be after start time';
            } else {
                communicationEnd.classList.remove('is-invalid');
            }
        }
    }
    
    // DND time validation
    const dndStart = document.getElementById('dnd_start');
    const dndEnd = document.getElementById('dnd_end');
    
    if (dndStart && dndEnd) {
        dndStart.addEventListener('change', validateDNDTimes);
        dndEnd.addEventListener('change', validateDNDTimes);
    }
    
    function validateDNDTimes() {
        const start = dndStart.value;
        const end = dndEnd.value;
        
        if (start && end) {
            const startHour = parseInt(start.split(':')[0]);
            const endHour = parseInt(end.split(':')[0]);
            
            if (endHour <= startHour) {
                dndEnd.classList.add('is-invalid');
                dndEnd.nextElementSibling.textContent = 'DND end time must be after start time';
            } else {
                dndEnd.classList.remove('is-invalid');
            }
        }
    }
    
    // Auto-save preferences (optional)
    let saveTimeout;
    const preferenceForm = document.querySelector('form[action*="update-preferences"]');
    
    if (preferenceForm) {
        const inputs = preferenceForm.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(() => {
                    preferenceForm.submit();
                }, 2000);
            });
        });
    }
});
</script>
@endsection