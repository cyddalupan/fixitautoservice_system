@extends('layouts.app')

@section('title', 'Preferences')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Preferences</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('portal.settings.index') }}">Settings</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Preferences</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('portal.settings.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Settings
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Quick Preferences Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Quick Preferences</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('portal.settings.update-quick-preferences') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="quick_theme" class="form-label">Theme</label>
                                <select class="form-select" id="quick_theme" name="theme">
                                    <option value="light" {{ old('theme', auth()->user()->preferences['theme'] ?? 'light') == 'light' ? 'selected' : '' }}>
                                        Light
                                    </option>
                                    <option value="dark" {{ old('theme', auth()->user()->preferences['theme'] ?? 'light') == 'dark' ? 'selected' : '' }}>
                                        Dark
                                    </option>
                                    <option value="auto" {{ old('theme', auth()->user()->preferences['theme'] ?? 'light') == 'auto' ? 'selected' : '' }}>
                                        Auto
                                    </option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="quick_language" class="form-label">Language</label>
                                <select class="form-select" id="quick_language" name="language">
                                    <option value="en" {{ old('language', auth()->user()->preferences['language'] ?? 'en') == 'en' ? 'selected' : '' }}>
                                        English
                                    </option>
                                    <option value="es" {{ old('language', auth()->user()->preferences['language'] ?? 'en') == 'es' ? 'selected' : '' }}>
                                        Español
                                    </option>
                                    <option value="fr" {{ old('language', auth()->user()->preferences['language'] ?? 'en') == 'fr' ? 'selected' : '' }}>
                                        Français
                                    </option>
                                </select>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="quick_timezone" class="form-label">Timezone</label>
                                <select class="form-select" id="quick_timezone" name="timezone">
                                    <option value="America/New_York" {{ old('timezone', auth()->user()->preferences['timezone'] ?? 'UTC') == 'America/New_York' ? 'selected' : '' }}>
                                        Eastern Time (ET)
                                    </option>
                                    <option value="America/Chicago" {{ old('timezone', auth()->user()->preferences['timezone'] ?? 'UTC') == 'America/Chicago' ? 'selected' : '' }}>
                                        Central Time (CT)
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
                                <label for="quick_date_format" class="form-label">Date Format</label>
                                <select class="form-select" id="quick_date_format" name="date_format">
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
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Save Quick Preferences
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Dashboard Widgets Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Dashboard Widgets</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('portal.settings.update-dashboard-widgets') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <p class="text-muted mb-3">Select which widgets to display on your dashboard:</p>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="widget_welcome" 
                                           name="widgets[]" value="welcome" 
                                           {{ in_array('welcome', old('widgets', auth()->user()->preferences['dashboard_widgets'] ?? ['welcome', 'appointments', 'service_history'])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="widget_welcome">
                                        Welcome Message
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="widget_appointments" 
                                           name="widgets[]" value="appointments" 
                                           {{ in_array('appointments', old('widgets', auth()->user()->preferences['dashboard_widgets'] ?? ['welcome', 'appointments', 'service_history'])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="widget_appointments">
                                        Upcoming Appointments
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="widget_service_history" 
                                           name="widgets[]" value="service_history" 
                                           {{ in_array('service_history', old('widgets', auth()->user()->preferences['dashboard_widgets'] ?? ['welcome', 'appointments', 'service_history'])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="widget_service_history">
                                        Service History
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="widget_recent_activity" 
                                           name="widgets[]" value="recent_activity" 
                                           {{ in_array('recent_activity', old('widgets', auth()->user()->preferences['dashboard_widgets'] ?? ['welcome', 'appointments', 'service_history'])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="widget_recent_activity">
                                        Recent Activity
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="widget_vehicles" 
                                           name="widgets[]" value="vehicles" 
                                           {{ in_array('vehicles', old('widgets', auth()->user()->preferences['dashboard_widgets'] ?? ['welcome', 'appointments', 'service_history'])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="widget_vehicles">
                                        My Vehicles
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="widget_billing" 
                                           name="widgets[]" value="billing" 
                                           {{ in_array('billing', old('widgets', auth()->user()->preferences['dashboard_widgets'] ?? ['welcome', 'appointments', 'service_history'])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="widget_billing">
                                        Billing Summary
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="widget_loyalty" 
                                           name="widgets[]" value="loyalty" 
                                           {{ in_array('loyalty', old('widgets', auth()->user()->preferences['dashboard_widgets'] ?? ['welcome', 'appointments', 'service_history'])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="widget_loyalty">
                                        Loyalty Points
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="widget_messages" 
                                           name="widgets[]" value="messages" 
                                           {{ in_array('messages', old('widgets', auth()->user()->preferences['dashboard_widgets'] ?? ['welcome', 'appointments', 'service_history'])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="widget_messages">
                                        Recent Messages
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Save Widget Preferences
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Accessibility Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Accessibility</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('portal.settings.update-accessibility') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="access_high_contrast" 
                                   name="high_contrast" value="1" 
                                   {{ old('high_contrast', auth()->user()->preferences['high_contrast'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="access_high_contrast">
                                High Contrast Mode
                            </label>
                            <small class="text-muted d-block">Increases contrast for better visibility</small>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="access_larger_text" 
                                   name="larger_text" value="1" 
                                   {{ old('larger_text', auth()->user()->preferences['larger_text'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="access_larger_text">
                                Larger Text Size
                            </label>
                            <small class="text-muted d-block">Increases font size throughout the application</small>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="access_reduced_motion" 
                                   name="reduced_motion" value="1" 
                                   {{ old('reduced_motion', auth()->user()->preferences['reduced_motion'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="access_reduced_motion">
                                Reduced Motion
                            </label>
                            <small class="text-muted d-block">Reduces animations and transitions</small>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="access_screen_reader" 
                                   name="screen_reader" value="1" 
                                   {{ old('screen_reader', auth()->user()->preferences['screen_reader'] ?? false) ? 'checked' : '' }}>
                            <label class="form-check-label" for="access_screen_reader">
                                Screen Reader Optimized
                            </label>
                            <small class="text-muted d-block">Enhances compatibility with screen readers</small>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Save Accessibility Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Export Preferences Card -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Export & Reset</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="mb-2">Export Preferences</h6>
                        <p class="text-muted mb-3">Download your current preferences as a JSON file.</p>
                        <a href="{{ route('portal.settings.export-preferences') }}" class="btn btn-outline-primary w-100">
                            <i class="bi bi-download me-1"></i> Export Preferences
                        </a>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="mb-2">Import Preferences</h6>
                        <p class="text-muted mb-3">Upload a JSON file to restore your preferences.</p>
                        <form action="{{ route('portal.settings.import-preferences') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="input-group">
                                <input type="file" class="form-control" id="preferences_file" name="preferences_file" accept=".json">
                                <button class="btn btn-outline-secondary" type="submit">Import</button>
                            </div>
                        </form>
                    </div>
                    
                    <div>
                        <h6 class="mb-2">Reset to Defaults</h6>
                        <p class="text-muted mb-3">Reset all preferences to their default values.</p>
                        <form action="{{ route('portal.settings.reset-preferences') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Are you sure you want to reset all preferences to defaults?')">
                                <i class="bi bi-arrow-clockwise me-1"></i> Reset to Defaults
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Theme preview
    const themeSelect = document.getElementById('quick_theme');
    if (themeSelect) {
        themeSelect.addEventListener('change', function() {
            if (this.value === 'dark') {
                document.body.classList.add('bg-dark', 'text-light');
            } else {
                document.body.classList.remove('bg-dark', 'text-light');
            }
        });
    }
    
    // Accessibility toggles
    const highContrastToggle = document.getElementById('access_high_contrast');
    const largerTextToggle = document.getElementById('access_larger_text');
    
    if (highContrastToggle) {
        highContrastToggle.addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.add('high-contrast');
            } else {
                document.body.classList.remove('high-contrast');
            }
        });
    }
    
    if (largerTextToggle) {
        largerTextToggle.addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.add('larger-text');
            } else {
                document.body.classList.remove('larger-text');
            }
        });
    }
    
    // Widget selection validation
    const widgetForm = document.querySelector('form[action*="update-dashboard-widgets"]');
    if (widgetForm) {
        widgetForm.addEventListener('submit', function(e) {
            const checkedWidgets = widgetForm.querySelectorAll('input[name="widgets[]"]:checked');
            if (checkedWidgets.length === 0) {
                e.preventDefault();
                alert('Please select at least one widget to display on your dashboard.');
            }
        });
    }
    
    // File upload validation
    const fileInput = document.getElementById('preferences_file');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                if (file.type !== 'application/json') {
                    alert('Please select a JSON file.');
                    this.value = '';
                }
                
                if (file.size > 1024 * 1024) { // 1MB limit
                    alert('File size must be less than 1MB.');
                    this.value = '';
                }
            }
        });
    }
    
    // Reset confirmation
    const resetForm = document.querySelector('form[action*="reset-preferences"]');
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to reset all preferences to default values? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    }
    
    // Auto-save for quick preferences
    let quickSaveTimeout;
    const quickPrefForm = document.querySelector('form[action*="update-quick-preferences"]');
    
    if (quickPrefForm) {
        const inputs = quickPrefForm.querySelectorAll('select');
        inputs.forEach(input => {
            input.addEventListener('change', function() {
                clearTimeout(quickSaveTimeout);
                quickSaveTimeout = setTimeout(() => {
                    quickPrefForm.submit();
                }, 1500);
            });
        });
    }
    
    // Accessibility preview
    function updateAccessibilityPreview() {
        const body = document.body;
        
        // Remove all accessibility classes first
        body.classList.remove('high-contrast', 'larger-text');
        
        // Add classes based on current toggle states
        if (highContrastToggle && highContrastToggle.checked) {
            body.classList.add('high-contrast');
        }
        
        if (largerTextToggle && largerTextToggle.checked) {
            body.classList.add('larger-text');
        }
    }
    
    // Initialize accessibility preview
    updateAccessibilityPreview();
    
    // Update preview when toggles change
    if (highContrastToggle) {
        highContrastToggle.addEventListener('change', updateAccessibilityPreview);
    }
    
    if (largerTextToggle) {
        largerTextToggle.addEventListener('change', updateAccessibilityPreview);
    }
});
</script>

<style>
.high-contrast {
    --bs-body-bg: #000 !important;
    --bs-body-color: #fff !important;
    --bs-card-bg: #111 !important;
    --bs-card-color: #fff !important;
    --bs-border-color: #444 !important;
}

.larger-text {
    font-size: 1.1rem !important;
}

.larger-text .card-title {
    font-size: 1.25rem !important;
}

.larger-text .form-label {
    font-size: 1rem !important;
}

.larger-text .btn {
    font-size: 1.1rem !important;
    padding: 0.5rem 1rem !important;
}
</style>
