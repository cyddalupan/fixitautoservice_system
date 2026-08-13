@extends('layouts.app')

@section('title', 'Settings — Fix-It Auto Services')

@section('body-class', 'settings-page')

@push('styles')
<style>
    .settings-page .main-content-area {
        background-color: #f8f9fc !important;
    }

    .settings-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
        margin-bottom: 24px;
        overflow: hidden;
    }

    .settings-card .card-header {
        background: linear-gradient(135deg, #2c3e50, #34495e);
        color: white;
        border-bottom: none;
        padding: 16px 24px;
        font-weight: 600;
        font-size: 1.1rem;
    }

    .settings-card .card-header i {
        margin-right: 10px;
        opacity: 0.9;
    }

    .settings-card .card-body {
        padding: 24px;
    }

    .settings-card .form-label {
        font-weight: 500;
        color: #2c3e50;
        margin-bottom: 6px;
    }

    .settings-card .form-control,
    .settings-card .form-select {
        border-radius: 8px;
        border: 1px solid #d1d5db;
        padding: 10px 14px;
        font-size: 0.95rem;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .settings-card .form-control:focus,
    .settings-card .form-select:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.15);
    }

    .settings-card .btn {
        border-radius: 8px;
        padding: 10px 24px;
        font-weight: 500;
    }

    .settings-card .btn-primary {
        background: #3498db;
        border: none;
    }

    .settings-card .btn-primary:hover {
        background: #2980b9;
    }

    .settings-card .btn-success {
        background: #27ae60;
        border: none;
    }

    .settings-card .btn-success:hover {
        background: #219a52;
    }

    .settings-card .btn-outline-secondary {
        border-color: #d1d5db;
        color: #6b7280;
    }

    .settings-card .btn-outline-secondary:hover {
        background: #f3f4f6;
        border-color: #9ca3af;
    }

    .password-mask {
        font-family: monospace;
        letter-spacing: 3px;
        color: #6b7280;
        font-size: 1.1rem;
    }

    /* Toast / Alert styles */
    .toast-container {
        position: fixed;
        top: 70px;
        right: 20px;
        z-index: 9999;
    }

    .toast-message {
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        padding: 14px 20px;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideIn 0.3s ease;
        min-width: 300px;
        max-width: 450px;
    }

    .toast-message.success { border-left: 4px solid #27ae60; }
    .toast-message.error { border-left: 4px solid #e74c3c; }

    @keyframes slideIn {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }

    .log-table {
        font-size: 0.9rem;
    }

    .log-table th {
        background: #f8f9fc;
        border-bottom: 2px solid #e5e7eb;
        color: #374151;
        font-weight: 600;
        padding: 12px 14px;
    }

    .log-table td {
        padding: 10px 14px;
        vertical-align: middle;
    }

    .log-table .badge {
        font-size: 0.8rem;
        padding: 4px 10px;
        border-radius: 20px;
    }

    .log-table .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .log-table .badge-danger {
        background: #fce4e4;
        color: #991b1b;
    }

    .log-filters {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
        margin-bottom: 16px;
    }

    .log-filters .form-control,
    .log-filters .form-select {
        border-radius: 8px;
        border: 1px solid #d1d5db;
        padding: 8px 12px;
        font-size: 0.85rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 py-3">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1"><i class="fas fa-cog me-2 text-secondary"></i>Settings</h3>
            <p class="text-muted mb-0 small">Configure system email and monitor magic link activity</p>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <div class="row">
        {{-- LEFT COLUMN: SMTP Settings --}}
        <div class="col-lg-7">
            <div class="settings-card" id="smtpCard">
                <div class="card-header">
                    <i class="fas fa-envelope"></i> SMTP Email Configuration
                </div>
                <div class="card-body">
                    <form id="smtpForm">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">SMTP Host</label>
                                <input type="text" class="form-control" name="mail_host" id="mail_host" placeholder="smtp.gmail.com">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">SMTP Port</label>
                                <input type="number" class="form-control" name="mail_port" id="mail_port" placeholder="587">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Encryption</label>
                                <select class="form-select" name="mail_encryption" id="mail_encryption">
                                    <option value="tls">TLS</option>
                                    <option value="ssl">SSL</option>
                                    <option value="none">None</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Email Address (From)</label>
                                <input type="email" class="form-control" name="mail_from_address" id="mail_from_address" placeholder="noreply@app.fixitautoservices.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">From Name</label>
                                <input type="text" class="form-control" name="mail_from_name" id="mail_from_name" placeholder="Fix-It Auto Services">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">SMTP Username (email)</label>
                                <input type="email" class="form-control" name="mail_username" id="mail_username" placeholder="your@gmail.com">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">App Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="mail_password" id="mail_password" placeholder="Enter app password" autocomplete="new-password">
                                    <button type="button" class="btn btn-outline-secondary" id="togglePasswordVisibility">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text small text-muted">
                                    <span id="passwordStatus"></span>
                                    Leave blank to keep existing password.
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1">
                                    <label class="form-check-label" for="is_active">
                                        <strong>Active</strong> — Use this SMTP configuration for sending emails
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" id="saveSmtpBtn">
                                <i class="fas fa-save me-1"></i> Save Settings
                            </button>
                            <button type="button" class="btn btn-success" id="testEmailBtn" disabled>
                                <i class="fas fa-paper-plane me-1"></i> Send Test Email
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: Test Email Form --}}
        <div class="col-lg-5">
            <div class="settings-card">
                <div class="card-header">
                    <i class="fas fa-vial"></i> Email Test
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Send a test email to verify your SMTP configuration is working correctly.</p>

                    <div class="mb-3">
                        <label class="form-label">Send test to</label>
                        <input type="email" class="form-control" id="testEmailAddress" placeholder="youremail@example.com" value="{{ Auth::user()->email ?? '' }}">
                    </div>

                    <button type="button" class="btn btn-success w-100" id="sendTestEmailBtn">
                        <i class="fas fa-paper-plane me-1"></i> Send Test Email
                    </button>

                    <div id="testEmailResult" class="mt-3 small" style="display:none;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- SERVICE TYPES CARD --}}
    <div class="settings-card mt-2">
        <div class="card-header">
            <i class="fas fa-tools"></i> Service Types
        </div>
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <p class="mb-0 small text-muted">Manage the services offered in the booking form and appointment create page.</p>
            </div>
            <a href="{{ route('settings.service-types.index') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-edit me-1"></i> Manage Services
            </a>
        </div>
    </div>

    {{-- MAGIC LINK LOGS SECTION --}}
    <div class="settings-card mt-2">
        <div class="card-header">
            <i class="fas fa-link"></i> Magic Link Request Logs
        </div>
        <div class="card-body">
            {{-- Filters --}}
            <div class="log-filters">
                <div>
                    <select class="form-select" id="logStatusFilter" style="min-width:130px;">
                        <option value="">All Statuses</option>
                        <option value="success">Success</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div>
                    <input type="date" class="form-control" id="logDateFrom" placeholder="From date">
                </div>
                <div>
                    <input type="date" class="form-control" id="logDateTo" placeholder="To date">
                </div>
                <div>
                    <input type="text" class="form-control" id="logSearch" placeholder="Search email..." style="min-width:200px;">
                </div>
                <div>
                    <button class="btn btn-outline-secondary btn-sm" id="applyLogFilters">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
                <div>
                    <button class="btn btn-outline-secondary btn-sm" id="resetLogFilters">
                        <i class="fas fa-undo me-1"></i> Reset
                    </button>
                </div>
            </div>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table log-table mb-0">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>Customer / Email</th>
                            <th>Status</th>
                            <th>Error Message</th>
                        </tr>
                    </thead>
                    <tbody id="magicLinkLogsBody">
                        @forelse($magicLinkLogs as $log)
                        <tr>
                            <td class="text-nowrap">{{ $log->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                @if($log->customer)
                                <div><strong>{{ $log->customer->first_name ?? '' }} {{ $log->customer->last_name ?? '' }}</strong></div>
                                @endif
                                <div class="small text-muted">{{ $log->email }}</div>
                            </td>
                            <td>
                                @if($log->status === 'success')
                                <span class="badge badge-success">Success</span>
                                @else
                                <span class="badge badge-danger">Failed</span>
                                @endif
                            </td>
                            <td>
                                @if($log->error_message)
                                <span class="small text-danger">{{ $log->error_message }}</span>
                                @else
                                <span class="small text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No magic link requests yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($magicLinkLogs->hasPages())
            <div class="d-flex justify-content-center mt-3">
                {{ $magicLinkLogs->links() }}
            </div>
            @endif

            <div id="magicLinkLogsEmpty" style="display:none;">
                <p class="text-center text-muted py-4 mb-0">No magic link requests found matching your filters.</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';

    const toastContainer = document.getElementById('toastContainer');

    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = 'toast-message ' + type;
        const icon = type === 'success' ? 'fa-check-circle text-success' : 'fa-exclamation-circle text-danger';
        toast.innerHTML = '<i class="fas ' + icon + '"></i><span>' + message + '</span>';
        toastContainer.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.3s';
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    function setBtnLoading(btn, isLoading) {
        if (isLoading) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Settings';
        }
    }

    // ==========================================
    // 1. LOAD SMTP SETTINGS
    // ==========================================
    function loadSmtpSettings() {
        fetch('/api/settings/smtp')
            .then(r => r.json())
            .then(data => {
                document.getElementById('mail_host').value = data.mail_host || 'smtp.gmail.com';
                document.getElementById('mail_port').value = data.mail_port || 587;
                document.getElementById('mail_from_address').value = data.mail_from_address || 'noreply@app.fixitautoservices.com';
                document.getElementById('mail_from_name').value = data.mail_from_name || 'Fix-It Auto Services';
                document.getElementById('mail_username').value = data.mail_username || '';
                document.getElementById('mail_encryption').value = data.mail_encryption || 'tls';
                document.getElementById('is_active').checked = data.is_active || false;

                // Password status display
                const passwordField = document.getElementById('mail_password');
                const pwStatus = document.getElementById('passwordStatus');
                if (data.has_password) {
                    passwordField.placeholder = 'Leave blank to keep existing';
                    pwStatus.innerHTML = '<span class="text-success"><i class="fas fa-lock me-1"></i> Password is set</span>';
                } else {
                    passwordField.placeholder = 'Enter app password';
                    pwStatus.innerHTML = '<span class="text-warning"><i class="fas fa-exclamation-triangle me-1"></i> No password set</span>';
                }

                // Enable test email button if SMTP is active with password
                document.getElementById('testEmailBtn').disabled = !(data.is_active && data.has_password);
                document.getElementById('sendTestEmailBtn').disabled = !(data.is_active && data.has_password);
            })
            .catch(err => {
                console.error('Failed to load SMTP settings:', err);
            });
    }

    // ==========================================
    // 2. SAVE SMTP SETTINGS
    // ==========================================
    document.getElementById('smtpForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('saveSmtpBtn');
        setBtnLoading(btn, true);

        const formData = new FormData(this);
        formData.set('is_active', document.getElementById('is_active').checked ? '1' : '0');

        fetch('/api/settings/smtp', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: formData,
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                loadSmtpSettings(); // Reload to get fresh state
            } else {
                showToast(data.message || 'Failed to save settings.', 'error');
            }
        })
        .catch(err => {
            showToast('Network error while saving settings.', 'error');
        })
        .finally(() => {
            setBtnLoading(btn, false);
        });
    });

    // ==========================================
    // 3. SEND TEST EMAIL (separate button in right column)
    // ==========================================
    document.getElementById('sendTestEmailBtn').addEventListener('click', function() {
        const email = document.getElementById('testEmailAddress').value;
        if (!email) {
            showToast('Please enter a test email address.', 'error');
            return;
        }

        const btn = this;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

        const resultDiv = document.getElementById('testEmailResult');

        fetch('/api/settings/test-email', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ test_email: email }),
        })
        .then(r => r.json())
        .then(data => {
            resultDiv.style.display = 'block';
            if (data.success) {
                resultDiv.className = 'mt-3 p-2 bg-success bg-opacity-10 rounded small text-success';
                resultDiv.innerHTML = '<i class="fas fa-check-circle me-1"></i> ' + data.message;
                showToast(data.message, 'success');
            } else {
                resultDiv.className = 'mt-3 p-2 bg-danger bg-opacity-10 rounded small text-danger';
                resultDiv.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> ' + data.message;
                showToast(data.message, 'error');
            }
        })
        .catch(err => {
            resultDiv.style.display = 'block';
            resultDiv.className = 'mt-3 p-2 bg-danger bg-opacity-10 rounded small text-danger';
            resultDiv.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> Network error sending test email.';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> Send Test Email';
        });
    });

    // ==========================================
    // 4. SEND TEST EMAIL (button inside SMTP form)
    // ==========================================
    document.getElementById('testEmailBtn').addEventListener('click', function() {
        document.getElementById('sendTestEmailBtn').click();
    });

    // ==========================================
    // 5. MAGIC LINK LOGS FILTERING
    // ==========================================
    function applyFilters() {
        const status = document.getElementById('logStatusFilter').value;
        const dateFrom = document.getElementById('logDateFrom').value;
        const dateTo = document.getElementById('logDateTo').value;
        const search = document.getElementById('logSearch').value;

        let params = new URLSearchParams();
        if (status) params.set('status', status);
        if (dateFrom) params.set('date_from', dateFrom);
        if (dateTo) params.set('date_to', dateTo);
        if (search) params.set('search', search);

        // Reload page with filter params
        window.location.href = '/settings?' + params.toString();
    }

    document.getElementById('applyLogFilters').addEventListener('click', applyFilters);

    document.getElementById('resetLogFilters').addEventListener('click', function() {
        window.location.href = '/settings';
    });

    // Enter key in search field triggers filter
    document.getElementById('logSearch').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') applyFilters();
    });

    // ==========================================
    // 6. TOGGLE PASSWORD VISIBILITY
    // ==========================================
    document.getElementById('togglePasswordVisibility').addEventListener('click', function() {
        const pw = document.getElementById('mail_password');
        const icon = this.querySelector('i');
        if (pw.type === 'password') {
            pw.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            pw.type = 'password';
            icon.className = 'fas fa-eye';
        }
    });

    // ==========================================
    // 7. LOAD URL PARAMS INTO FILTERS ON PAGE LOAD
    // ==========================================
    (function loadFiltersFromUrl() {
        const params = new URLSearchParams(window.location.search);
        if (params.get('status')) document.getElementById('logStatusFilter').value = params.get('status');
        if (params.get('date_from')) document.getElementById('logDateFrom').value = params.get('date_from');
        if (params.get('date_to')) document.getElementById('logDateTo').value = params.get('date_to');
        if (params.get('search')) document.getElementById('logSearch').value = params.get('search');
    })();

    // Init
    loadSmtpSettings();
})();
</script>
@endpush
