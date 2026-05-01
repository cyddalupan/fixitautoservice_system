@extends('layouts.app')

@section('title', 'Add Personnel')

@push('styles')
<style>
/* ── Form redesign ── */
.prsnl-form-wrap {
    background: #f4f6fa;
    min-height: 100vh;
    padding-top: .5rem;
    padding-bottom: 100px;
}
body.dark-mode .prsnl-form-wrap {
    background: #1a1d23;
}
.prsnl-form-wrap .page-title {
    color: #1a2332;
    font-size: 1.15rem;
    font-weight: 700;
}
body.dark-mode .prsnl-form-wrap .page-title { color: #e4e6eb; }

/* ── Section cards ── */
.prsnl-section {
    border-radius: 12px;
    border: 0;
    box-shadow: 0 1px 3px rgba(0,0,0,.04);
    margin-bottom: 1rem;
}
body.dark-mode .prsnl-section { background: #2a2d35; }
.prsnl-section-header {
    background: transparent;
    border-bottom: 1px solid #eef0f3;
    padding: .75rem 1rem;
    font-size: .82rem;
    font-weight: 600;
    color: #1a2332;
    display: flex;
    align-items: center;
    gap: .5rem;
}
body.dark-mode .prsnl-section-header {
    border-bottom-color: #3a3d45;
    color: #e4e6eb;
}
.prsnl-section-header i {
    font-size: .85rem;
    width: 18px;
    text-align: center;
}
.prsnl-section-body {
    padding: 1rem;
}

/* ── Form labels ── */
.prsnl-label {
    font-size: .78rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: .25rem;
}
body.dark-mode .prsnl-label { color: #d1d5db; }
.prsnl-input {
    font-size: .82rem;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    padding: .4rem .65rem;
    transition: border-color .15s, box-shadow .15s;
}
body.dark-mode .prsnl-input {
    background: #1f2937;
    border-color: #4b5563;
    color: #e4e6eb;
}
.prsnl-input:focus {
    border-color: #4361ee;
    box-shadow: 0 0 0 2px rgba(67, 97, 238, .15);
}
.prsnl-textarea {
    font-size: .82rem;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    min-height: 80px;
    resize: vertical;
    padding: .4rem .65rem;
}
body.dark-mode .prsnl-textarea {
    background: #1f2937;
    border-color: #4b5563;
    color: #e4e6eb;
}

/* ── Role tags ── */
.prsnl-role-chips {
    display: flex;
    flex-wrap: wrap;
    gap: .4rem;
    margin-top: .4rem;
}
.prsnl-role-chip {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
    padding: .2rem .6rem;
    border-radius: 20px;
    font-size: .75rem;
    font-weight: 500;
    background: #eef1ff;
    color: #4361ee;
    border: 1px solid #ccd5ff;
}
body.dark-mode .prsnl-role-chip {
    background: #1a2a4a;
    border-color: #2a4a7a;
    color: #90aef9;
}
.prsnl-role-chip .remove-role {
    cursor: pointer;
    font-size: .7rem;
    opacity: .7;
    transition: opacity .15s;
}
.prsnl-role-chip .remove-role:hover { opacity: 1; }

/* ── Image upload ── */
.prsnl-photo-wrap {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.prsnl-photo-preview {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    overflow: hidden;
    background: #eef1ff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 600;
    color: #4361ee;
    border: 2px dashed #ccd5ff;
    flex-shrink: 0;
    object-fit: cover;
}
body.dark-mode .prsnl-photo-preview { border-color: #2a4a7a; }
.prsnl-photo-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.prsnl-photo-actions {
    display: flex;
    flex-direction: column;
    gap: .4rem;
}
.prsnl-photo-actions .btn {
    font-size: .75rem;
    padding: .25rem .75rem;
}

/* ── Sticky save bar ── */
.prsnl-sticky-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 1050;
    background: #fff;
    border-top: 1px solid #eef0f3;
    padding: .75rem 1.5rem;
    box-shadow: 0 -2px 8px rgba(0,0,0,.05);
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: .75rem;
}
body.dark-mode .prsnl-sticky-bar {
    background: #2a2d35;
    border-top-color: #3a3d45;
}

/* ── Custom select ── */
.prsnl-role-select {
    font-size: .82rem;
    border-radius: 6px;
    border: 1px solid #d1d5db;
    padding: .4rem .65rem;
}
body.dark-mode .prsnl-role-select {
    background: #1f2937;
    border-color: #4b5563;
    color: #e4e6eb;
}

/* ── HR-style form grid ── */
@media (min-width: 768px) {
    .prsnl-form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0 1.5rem;
    }
    .prsnl-form-grid-full {
        grid-column: 1 / -1;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 prsnl-form-wrap">
    <!-- ══ HEADER ══ -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 page-title">
                <i class="fas fa-user-plus me-2 accent-icon" style="color:#4361ee;"></i>Add Personnel
            </h4>
            <p class="mb-0 text-muted small">Create a new staff member record</p>
        </div>
        <a href="{{ route('personnel.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <form method="POST" action="{{ route('personnel.store') }}" enctype="multipart/form-data">
        @csrf

        <!-- ══ SECTION A: BASIC INFO ══ -->
        <div class="card prsnl-section">
            <div class="prsnl-section-header">
                <i class="fas fa-id-card" style="color:#4361ee;"></i> Basic Information
            </div>
            <div class="prsnl-section-body">
                <div class="prsnl-form-grid">
                    <div class="mb-3">
                        <label class="prsnl-label">Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control prsnl-input @error('name') is-invalid @enderror"
                               name="name" value="{{ old('name') }}" placeholder="e.g. Juan Dela Cruz">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="prsnl-label">Primary Role <span class="text-danger">*</span></label>
                        <select class="form-select prsnl-input @error('role') is-invalid @enderror" name="role" id="primaryRole">
                            <option value="">Select primary role</option>
                            @foreach($availableRoles as $key => $label)
                                <option value="{{ $key }}" {{ old('role') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3 prsnl-form-grid-full">
                        <label class="prsnl-label">Additional Roles</label>
                        <select class="form-select prsnl-role-select" id="addRoleSelect">
                            <option value="">Select additional role...</option>
                            @foreach($availableRoles as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="prsnl-role-chips" id="roleChips">
                            <!-- Selected additional roles appear here as chips -->
                        </div>
                        <input type="hidden" name="roles" id="rolesInput" value="{{ old('roles') ? implode(',', old('roles')) : '' }}">
                        @error('roles')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        @error('roles.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        <small class="text-muted" style="font-size:.68rem;">Select additional roles from the dropdown. Click the ✕ to remove.</small>
                    </div>

                    <div class="mb-3">
                        <label class="prsnl-label">Status</label>
                        <div class="d-flex gap-3 pt-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_active" value="1" id="statusActive" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="statusActive" style="font-size:.78rem;">Active</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_active" value="0" id="statusInactive" {{ old('is_active') == '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="statusInactive" style="font-size:.78rem;">Inactive</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="prsnl-label">Position / Title</label>
                        <input type="text" class="form-control prsnl-input @error('specialization') is-invalid @enderror"
                               name="specialization" value="{{ old('specialization') }}" placeholder="e.g. Lead Technician">
                        @error('specialization')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ SECTION B: CONTACT ══ -->
        <div class="card prsnl-section">
            <div class="prsnl-section-header">
                <i class="fas fa-phone-alt" style="color:#2ec4b6;"></i> Contact Information
            </div>
            <div class="prsnl-section-body">
                <div class="prsnl-form-grid">
                    <div class="mb-3">
                        <label class="prsnl-label">Phone Number</label>
                        <input type="text" class="form-control prsnl-input @error('phone') is-invalid @enderror"
                               name="phone" value="{{ old('phone') }}" placeholder="e.g. 0917xxxxxxx">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="prsnl-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control prsnl-input @error('email') is-invalid @enderror"
                               name="email" value="{{ old('email') }}" placeholder="e.g. juan@example.com">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3 prsnl-form-grid-full">
                        <label class="prsnl-label">Address</label>
                        <textarea class="form-control prsnl-textarea @error('address') is-invalid @enderror"
                                  name="address" placeholder="Complete address">{{ old('address') }}</textarea>
                        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ SECTION C: WORK INFO ══ -->
        <div class="card prsnl-section">
            <div class="prsnl-section-header">
                <i class="fas fa-briefcase" style="color:#f7a429;"></i> Work Information
            </div>
            <div class="prsnl-section-body">
                <div class="prsnl-form-grid">
                    <div class="mb-3">
                        <label class="prsnl-label">Employee ID</label>
                        <input type="text" class="form-control prsnl-input @error('employee_id') is-invalid @enderror"
                               name="employee_id" value="{{ old('employee_id') }}" placeholder="e.g. EMP-001">
                        @error('employee_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="prsnl-label">Hire Date</label>
                        <input type="date" class="form-control prsnl-input @error('hire_date') is-invalid @enderror"
                               name="hire_date" value="{{ old('hire_date') }}">
                        @error('hire_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="prsnl-label">Employment Type</label>
                        <select class="form-select prsnl-input @error('employment_type') is-invalid @enderror" name="employment_type">
                            <option value="">Select type...</option>
                            <option value="full_time" {{ old('employment_type') == 'full_time' ? 'selected' : '' }}>Full Time</option>
                            <option value="part_time" {{ old('employment_type') == 'part_time' ? 'selected' : '' }}>Part Time</option>
                            <option value="contractual" {{ old('employment_type') == 'contractual' ? 'selected' : '' }}>Contractual</option>
                            <option value="probationary" {{ old('employment_type') == 'probationary' ? 'selected' : '' }}>Probationary</option>
                        </select>
                        @error('employment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="prsnl-label">Shift Schedule</label>
                        <select class="form-select prsnl-input @error('shift_schedule') is-invalid @enderror" name="shift_schedule">
                            <option value="">Select shift...</option>
                            <option value="day" {{ old('shift_schedule') == 'day' ? 'selected' : '' }}>Day Shift</option>
                            <option value="mid" {{ old('shift_schedule') == 'mid' ? 'selected' : '' }}>Mid Shift</option>
                            <option value="night" {{ old('shift_schedule') == 'night' ? 'selected' : '' }}>Night Shift</option>
                            <option value="flexible" {{ old('shift_schedule') == 'flexible' ? 'selected' : '' }}>Flexible</option>
                        </select>
                        @error('shift_schedule')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="prsnl-label">Hourly Rate (₱)</label>
                        <input type="number" step="0.01" min="0" class="form-control prsnl-input @error('hourly_rate') is-invalid @enderror"
                               name="hourly_rate" value="{{ old('hourly_rate') }}" placeholder="e.g. 150.00">
                        @error('hourly_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3 prsnl-form-grid-full">
                        <label class="prsnl-label">Skills / Specializations</label>
                        <textarea class="form-control prsnl-textarea @error('skills') is-invalid @enderror"
                                  name="skills" placeholder="List skills separated by commas (e.g. Engine Repair, Electrical, AC)">{{ old('skills') }}</textarea>
                        @error('skills')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3 prsnl-form-grid-full">
                        <label class="prsnl-label">Account Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control prsnl-input @error('password') is-invalid @enderror"
                               name="password" placeholder="Min 8 characters">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ SECTION D: PROFILE IMAGE ══ -->
        <div class="card prsnl-section">
            <div class="prsnl-section-header">
                <i class="fas fa-camera" style="color:#6f42c1;"></i> Profile Photo
            </div>
            <div class="prsnl-section-body">
                <div class="prsnl-photo-wrap">
                    <div class="prsnl-photo-preview" id="photoPreview">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="prsnl-photo-actions">
                        <input type="file" class="d-none" id="photoInput" name="profile_photo" accept="image/*">
                        <input type="hidden" name="cropped_image" id="croppedImageInput" value="">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('photoInput').click();">
                            <i class="fas fa-upload me-1"></i> Upload Photo
                        </button>
                        <small class="text-muted" style="font-size:.68rem;">JPEG, PNG, or WebP. Max 2MB.</small>
                        @error('profile_photo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ SECTION E: NOTES ══ -->
        <div class="card prsnl-section">
            <div class="prsnl-section-header">
                <i class="fas fa-sticky-note" style="color:#20c997;"></i> Notes
            </div>
            <div class="prsnl-section-body">
                <textarea class="form-control prsnl-textarea @error('notes') is-invalid @enderror"
                          name="notes" placeholder="Any additional notes about this personnel...">{{ old('notes') }}</textarea>
                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        @include('partials.avatar-crop-modal')

    <!-- ══ STICKY SAVE BAR ══ -->
        <div class="prsnl-sticky-bar">
            <a href="{{ route('personnel.index') }}" class="btn btn-sm btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-sm btn-primary px-4">
                <i class="fas fa-save me-1"></i> Save Personnel
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
// ── Role chips management ──
document.addEventListener('DOMContentLoaded', function () {
    const select = document.getElementById('addRoleSelect');
    const chips = document.getElementById('roleChips');
    const hidden = document.getElementById('rolesInput');
    const primarySelect = document.getElementById('primaryRole');
    const selectedRoles = new Set();

    // Load existing selected roles from hidden input (for validation errors)
    if (hidden.value) {
        hidden.value.split(',').forEach(r => {
            r = r.trim();
            if (r) selectedRoles.add(r);
        });
        renderChips();
    }

    select.addEventListener('change', function () {
        const val = this.value;
        if (!val) return;
        if (selectedRoles.has(val)) {
            this.value = '';
            return;
        }
        // Prevent selecting primary role as additional
        if (primarySelect.value === val) {
            alert('This role is already selected as the primary role.');
            this.value = '';
            return;
        }
        selectedRoles.add(val);
        this.value = '';
        renderChips();
    });

    function renderChips() {
        chips.innerHTML = '';
        const labels = @json($availableRoles);
        selectedRoles.forEach(role => {
            const chip = document.createElement('span');
            chip.className = 'prsnl-role-chip';
            chip.innerHTML = `${labels[role] || role} <span class="remove-role" data-role="${role}">✕</span>`;
            chip.querySelector('.remove-role').addEventListener('click', function () {
                selectedRoles.delete(this.dataset.role);
                renderChips();
            });
            chips.appendChild(chip);
        });
        hidden.value = Array.from(selectedRoles).join(',');
    }

    // ── Photo upload + crop ──
    initAvatarCrop({
        fileInput: '#photoInput',
        previewWrap: '#photoPreview',
        previewImg: '#photoPreview img',
        hiddenInput: '#croppedImageInput',
        aspectRatio: 1,
    });
});
</script>
@endpush
@endsection
