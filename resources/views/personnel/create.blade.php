@extends('layouts.app')

@section('title', 'Add New Personnel')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header">
                <h1 class="mb-0">
                    <i class="fas fa-user-plus me-2"></i>Add New Personnel
                </h1>
                <p class="text-muted">Add a new staff member to the system</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Personnel Information</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('personnel.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <!-- Basic Information -->
                            <div class="col-md-6">
                                <h6 class="mb-3">Basic Information</h6>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Role *</label>
                                    
                                    <!-- Hidden input to store selected role for form submission -->
                                    <input type="hidden" id="role" name="role" value="{{ old('role') }}" required>
                                    @error('role')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    
                                    <!-- Custom Dropdown Container -->
                                    <div class="custom-dropdown-container">
                                        <!-- Selected Role Display -->
                                        <div class="selected-role-display form-control @error('role') is-invalid @enderror" 
                                             id="selectedRoleDisplay" style="cursor: pointer;">
                                            <span id="selectedRoleText">{{ old('role') ? ucfirst(str_replace('_', ' ', old('role'))) : 'Select Role' }}</span>
                                            <span class="float-end"><i class="fas fa-chevron-down"></i></span>
                                        </div>
                                        
                                        <!-- Dropdown Options -->
                                        <div class="dropdown-options-container" id="dropdownOptions" style="display: none;">
                                            <div class="dropdown-options">
                                                <!-- Options will be populated by JavaScript -->
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Add Another Role Button -->
                                    <div class="mt-2" id="additionalRolesSection" style="display: none;">
                                        <button type="button" class="btn btn-outline-primary btn-sm" id="addAnotherRoleBtn">
                                            <i class="fas fa-plus-circle"></i> Add Another Role
                                        </button>
                                        <small class="text-muted d-block mt-1">For personnel with multiple roles (e.g., Office Staff who is also a Technician)</small>
                                    </div>
                                    
                                    <!-- Additional Roles Container -->
                                    <div class="mt-3" id="additionalRolesContainer">
                                        <div class="text-muted mb-2" id="additionalRolesTitle" style="display: none;">Additional Roles:</div>
                                        <!-- Additional role dropdowns will be added here -->
                                    </div>
                                    
                                    <small class="text-muted d-block mt-2">Click on role to select, click (x) to delete custom roles</small>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="col-md-6">
                                <h6 class="mb-3">Additional Information</h6>

                                <div class="mb-3">
                                    <label for="specialization" class="form-label">Specialization / Title</label>
                                    <div class="input-group mb-2">
                                        <input type="text" class="form-control @error('specialization') is-invalid @enderror" 
                                               id="specialization" name="specialization" value="{{ old('specialization') }}">
                                        <button type="button" class="btn btn-outline-secondary" id="addSpecializationBtn" title="Add Another Specialization">
                                            <i class="fas fa-plus"></i> Add
                                        </button>
                                    </div>
                                    <div id="additionalSpecializations"></div>
                                    <small class="text-muted">e.g., "Master Technician", "Service Manager", "CEO" - Click "Add" for multiple specializations</small>
                                    @error('specialization')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="years_experience" class="form-label">Years of Experience</label>
                                    <input type="number" class="form-control @error('years_experience') is-invalid @enderror" 
                                           id="years_experience" name="years_experience" 
                                           value="{{ old('years_experience', 0) }}" min="0">
                                    @error('years_experience')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="hire_date" class="form-label">Hire Date</label>
                                    <input type="date" class="form-control @error('hire_date') is-invalid @enderror" 
                                           id="hire_date" name="hire_date" value="{{ old('hire_date') }}">
                                    @error('hire_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Status *</label>
                                    <div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="is_active" 
                                                   id="active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="active">Active</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="is_active" 
                                                   id="inactive" value="0" {{ old('is_active') == '0' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="inactive">Inactive</label>
                                        </div>
                                    </div>
                                    @error('is_active')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('personnel.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Create Personnel
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

@push('styles')
<style>
/* Custom Dropdown Styles */
.custom-dropdown-container {
    position: relative;
}

.selected-role-display {
    background-color: white;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    padding: 0.375rem 0.75rem;
    min-height: 38px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.selected-role-display:hover {
    border-color: #86b7fe;
}

.dropdown-options-container {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1000;
    background: white;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    max-height: 200px;
    overflow-y: auto;
    margin-top: 2px;
}

.dropdown-options {
    padding: 0.25rem;
}

.role-option {
    padding: 0.5rem 0.75rem;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 0.25rem;
    margin-bottom: 0.125rem;
    position: relative;
}

.role-option span {
    flex-grow: 1;
}

.delete-role-btn {
    background: none;
    border: none;
    color: #dc3545;
    cursor: pointer;
    padding: 0.125rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
    opacity: 0.7;
    position: absolute;
    right: 0.5rem;
    top: 50%;
    transform: translateY(-50%);
}

.role-option:hover {
    background-color: #f8f9fa;
}

.role-option.selected {
    background-color: #0d6efd;
    color: white;
}

.role-option.selected:hover {
    background-color: #0b5ed7;
}

.delete-role-btn:hover {
    opacity: 1;
    background-color: rgba(220, 53, 69, 0.1);
}

/* Add New Role Option Styles */
.add-new-role-option:hover {
    background-color: rgba(13, 110, 253, 0.1) !important;
}

.add-new-role-option:hover i,
.add-new-role-option:hover span {
    color: #0b5ed7 !important;
}

/* Additional Role Dropdown Styles */
.additional-role-dropdown-container {
    border-left: 3px solid #0d6efd;
    padding-left: 1rem;
    margin-left: 0.5rem;
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
}

.additional-role-dropdown-container .custom-dropdown-container {
    margin-bottom: 0.5rem;
}

.additional-role-dropdown-container .selected-role-display {
    background-color: #f8f9fa;
}

.additional-role-dropdown-container .dropdown-options-container {
    z-index: 999; /* Slightly lower than main dropdown */
}

.role-option.selected .delete-role-btn {
    color: rgba(255, 255, 255, 0.8);
}

.role-option.selected .delete-role-btn:hover {
    color: white;
    background-color: rgba(255, 255, 255, 0.2);
}

/* Additional Roles Styles */
.additional-role-item {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    padding: 0.5rem;
    margin-bottom: 0.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.additional-role-text {
    font-weight: 500;
}

.remove-additional-role {
    background: none;
    border: none;
    color: #dc3545;
    cursor: pointer;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
}

.remove-additional-role:hover {
    background-color: rgba(220, 53, 69, 0.1);
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set default hire date to today
    const hireDateInput = document.getElementById('hire_date');
    if (!hireDateInput.value) {
        const today = new Date().toISOString().split('T')[0];
        hireDateInput.value = today;
    }

    // ============================================
    // CUSTOM ROLE DROPDOWN WITH DELETE BUTTONS (X)
    // ============================================
    
    // DOM Elements
    const roleInput = document.getElementById('role');
    const selectedRoleDisplay = document.getElementById('selectedRoleDisplay');
    const selectedRoleText = document.getElementById('selectedRoleText');
    const dropdownOptions = document.getElementById('dropdownOptions');
    const dropdownOptionsContainer = dropdownOptions.querySelector('.dropdown-options');
    const addAnotherRoleBtn = document.getElementById('addAnotherRoleBtn');
    const additionalRolesSection = document.getElementById('additionalRolesSection');
    const additionalRolesContainer = document.getElementById('additionalRolesContainer');
    const specializationInput = document.getElementById('specialization');
    
    // Define predefined roles that cannot be deleted
    const predefinedRoles = ['technician', 'service_advisor', 'manager', 'admin', 'office_staff', 'executive'];
    const predefinedRoleNames = {
        'technician': 'Technician',
        'service_advisor': 'Service Advisor', 
        'manager': 'Manager',
        'admin': 'Administrator',
        'office_staff': 'Office Staff',
        'executive': 'Executive'
    };
    
    // Store all roles (predefined + custom)
    let allRoles = [];
    
    // Store additional role dropdowns (for multiple roles feature)
    let additionalRoleDropdowns = [];
    
    // Counter for additional role dropdown IDs
    let additionalRoleCounter = 0;
    
    // Load custom roles from localStorage
    function loadCustomRolesFromStorage() {
        try {
            const savedRoles = localStorage.getItem('fixit_custom_roles');
            if (savedRoles) {
                return JSON.parse(savedRoles);
            }
        } catch (e) {
            console.error('Error loading custom roles from localStorage:', e);
        }
        return [];
    }
    
    // Save custom roles to localStorage
    function saveCustomRolesToStorage() {
        const customRoles = allRoles.filter(role => !role.isPredefined);
        try {
            localStorage.setItem('fixit_custom_roles', JSON.stringify(customRoles));
        } catch (e) {
            console.error('Error saving custom roles to localStorage:', e);
        }
    }
    
    // Initialize roles from predefined list and localStorage
    function initializeRoles() {
        allRoles = [];
        
        // Add predefined roles
        Object.keys(predefinedRoleNames).forEach(roleValue => {
            allRoles.push({
                value: roleValue,
                name: predefinedRoleNames[roleValue],
                isPredefined: true
            });
        });
        
        // Load custom roles from localStorage
        const savedCustomRoles = loadCustomRolesFromStorage();
        savedCustomRoles.forEach(customRole => {
            // Check if role already exists (avoid duplicates)
            const roleExists = allRoles.some(r => r.value === customRole.value);
            if (!roleExists) {
                allRoles.push({
                    value: customRole.value,
                    name: customRole.name,
                    isPredefined: false
                });
            }
        });
        
        // Check if there's an old value from form submission
        const oldRoleValue = roleInput.value;
        if (oldRoleValue && !predefinedRoles.includes(oldRoleValue)) {
            // Check if this custom role already exists
            const roleExists = allRoles.some(r => r.value === oldRoleValue);
            if (!roleExists) {
                // Add custom role from old form submission
                allRoles.push({
                    value: oldRoleValue,
                    name: oldRoleValue.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
                    isPredefined: false
                });
            }
        }
        
        updateAllDropdowns();
        updateSelectedRoleDisplay();
        
        // Show "Add Another Role" button if a role is already selected
        if (roleInput.value) {
            additionalRolesSection.style.display = 'block';
        }
    }
    
    // Render the dropdown options
    function renderRoleDropdown() {
        dropdownOptionsContainer.innerHTML = '';
        
        allRoles.forEach(role => {
            const optionDiv = document.createElement('div');
            optionDiv.className = 'role-option';
            optionDiv.dataset.value = role.value;
            optionDiv.dataset.name = role.name;
            
            // Check if this role is currently selected
            if (roleInput.value === role.value) {
                optionDiv.classList.add('selected');
            }
            
            // Role name
            const nameSpan = document.createElement('span');
            nameSpan.textContent = role.name;
            
            // Delete button (only for custom roles)
            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';
            deleteBtn.className = 'delete-role-btn';
            deleteBtn.innerHTML = '<i class="fas fa-times"></i>';
            deleteBtn.title = role.isPredefined ? 'Predefined role cannot be deleted' : 'Delete this role';
            
            if (role.isPredefined) {
                deleteBtn.disabled = true;
                deleteBtn.style.opacity = '0.3';
                deleteBtn.style.cursor = 'not-allowed';
            } else {
                deleteBtn.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent selecting the role
                    deleteRole(role.value);
                });
            }
            
            // Select role on click
            optionDiv.addEventListener('click', function(e) {
                // Check if click is on delete button or its icon
                const isDeleteButton = e.target.classList.contains('delete-role-btn') || 
                                      e.target.closest('.delete-role-btn');
                if (!isDeleteButton) {
                    selectRole(role.value);
                    closeDropdown();
                }
            });
            
            optionDiv.appendChild(nameSpan);
            optionDiv.appendChild(deleteBtn);
            dropdownOptionsContainer.appendChild(optionDiv);
        });
        
        // Add "Add New Role" option at the bottom of the dropdown
        const addNewRoleOption = document.createElement('div');
        addNewRoleOption.className = 'role-option add-new-role-option';
        addNewRoleOption.style.borderTop = '1px solid #dee2e6';
        addNewRoleOption.style.marginTop = '0.25rem';
        addNewRoleOption.style.paddingTop = '0.5rem';
        
        const addIcon = document.createElement('i');
        addIcon.className = 'fas fa-plus me-2';
        addIcon.style.color = '#0d6efd';
        
        const addText = document.createElement('span');
        addText.textContent = 'Add New Role';
        addText.style.color = '#0d6efd';
        addText.style.fontWeight = '500';
        
        addNewRoleOption.appendChild(addIcon);
        addNewRoleOption.appendChild(addText);
        
        addNewRoleOption.addEventListener('click', function(e) {
            e.stopPropagation();
            addNewRole();
        });
        
        dropdownOptionsContainer.appendChild(addNewRoleOption);
    }
    
    // Select a role
    function selectRole(roleValue) {
        const role = allRoles.find(r => r.value === roleValue);
        if (role) {
            roleInput.value = roleValue;
            selectedRoleText.textContent = role.name;
            
            // Update specialization placeholder based on role
            updateSpecializationPlaceholder(roleValue);
            
            // Show "Add Another Role" button if a role is selected
            additionalRolesSection.style.display = 'block';
            
            // Update dropdown selection
            renderRoleDropdown();
        }
    }
    
    // Delete a role
    function deleteRole(roleValue) {
        const role = allRoles.find(r => r.value === roleValue);
        if (role && !role.isPredefined) {
            if (confirm(`Are you sure you want to delete the role "${role.name}"?`)) {
                // Remove from allRoles
                allRoles = allRoles.filter(r => r.value !== roleValue);
                
                // Save to localStorage
                saveCustomRolesToStorage();
                
                // If this was the selected role, clear selection
                if (roleInput.value === roleValue) {
                    roleInput.value = '';
                    selectedRoleText.textContent = 'Select Role';
                    additionalRolesSection.style.display = 'none';
                }
                
                // Clear any additional role dropdowns that have this role selected
                additionalRoleDropdowns.forEach(dropdown => {
                    if (dropdown.hiddenInput.value === roleValue) {
                        dropdown.selectedRoleText.textContent = 'Select Role';
                        dropdown.hiddenInput.value = '';
                    }
                });
                
                // Update all dropdowns
                updateAllDropdowns();
                updateSelectedRoleDisplay();
                
                alert(`Role "${role.name}" has been deleted successfully.`);
            }
        }
    }
    
    // Add a new role
    function addNewRole() {
        const roleName = prompt('Enter new role name:');
        if (roleName && roleName.trim()) {
            const roleValue = roleName.toLowerCase().replace(/\s+/g, '_');
            
            // Check if role already exists
            const roleExists = allRoles.some(r => r.value === roleValue);
            if (roleExists) {
                alert(`Role "${roleName}" already exists!`);
                return;
            }
            
            // Add new role
            allRoles.push({
                value: roleValue,
                name: roleName,
                isPredefined: false
            });
            
            // Save to localStorage
            saveCustomRolesToStorage();
            
            // Select the new role
            selectRole(roleValue);
            updateAllDropdowns();
            
            alert(`New role "${roleName}" added successfully!\n\nYou can delete it by clicking the (x) button next to it.`);
        }
    }
    
    // Add another role (multiple roles feature)
    // Add another role (multiple roles feature) - Creates duplicate dropdown
    function addAnotherRole() {
        // Increment counter for unique IDs
        additionalRoleCounter++;
        
        // Create a new dropdown container
        const dropdownId = `additionalRoleDropdown_${additionalRoleCounter}`;
        const dropdownContainer = document.createElement('div');
        dropdownContainer.className = 'additional-role-dropdown-container mb-3';
        dropdownContainer.id = dropdownId;
        
        // Create label
        const label = document.createElement('label');
        label.className = 'form-label';
        label.textContent = `Additional Role ${additionalRoleDropdowns.length + 1}`;
        dropdownContainer.appendChild(label);
        
        // Create custom dropdown container
        const customDropdownContainer = document.createElement('div');
        customDropdownContainer.className = 'custom-dropdown-container';
        
        // Create selected role display
        const selectedRoleDisplay = document.createElement('div');
        selectedRoleDisplay.className = 'selected-role-display form-control';
        selectedRoleDisplay.style.cursor = 'pointer';
        
        const selectedRoleText = document.createElement('span');
        selectedRoleText.className = 'selected-role-text';
        selectedRoleText.textContent = 'Select Role';
        
        const dropdownArrow = document.createElement('span');
        dropdownArrow.className = 'float-end';
        dropdownArrow.innerHTML = '<i class="fas fa-chevron-down"></i>';
        
        selectedRoleDisplay.appendChild(selectedRoleText);
        selectedRoleDisplay.appendChild(dropdownArrow);
        
        // Create dropdown options container
        const dropdownOptionsContainer = document.createElement('div');
        dropdownOptionsContainer.className = 'dropdown-options-container';
        dropdownOptionsContainer.style.display = 'none';
        
        const dropdownOptions = document.createElement('div');
        dropdownOptions.className = 'dropdown-options';
        
        // Populate dropdown options
        allRoles.forEach(role => {
            const roleOption = document.createElement('div');
            roleOption.className = 'role-option';
            roleOption.dataset.value = role.value;
            
            const roleNameSpan = document.createElement('span');
            roleNameSpan.textContent = role.name;
            
            roleOption.appendChild(roleNameSpan);
            
            // Add delete button for custom roles
            if (!role.isPredefined) {
                const deleteBtn = document.createElement('button');
                deleteBtn.type = 'button';
                deleteBtn.className = 'delete-role-btn';
                deleteBtn.innerHTML = '<i class="fas fa-times"></i>';
                deleteBtn.title = 'Delete this role';
                deleteBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    deleteRole(role.value);
                });
                roleOption.appendChild(deleteBtn);
            }
            
            roleOption.addEventListener('click', function() {
                // Update this dropdown's selection
                selectedRoleText.textContent = role.name;
                dropdownOptionsContainer.style.display = 'none';
                
                // Update hidden input
                const hiddenInput = dropdownContainer.querySelector('.additional-role-input');
                if (hiddenInput) {
                    hiddenInput.value = role.value;
                }
            });
            
            dropdownOptions.appendChild(roleOption);
        });
        
        dropdownOptionsContainer.appendChild(dropdownOptions);
        
        // Create hidden input for form submission
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.className = 'additional-role-input';
        hiddenInput.name = 'additional_roles[]';
        
        // Toggle dropdown on click
        selectedRoleDisplay.addEventListener('click', function() {
            const isVisible = dropdownOptionsContainer.style.display === 'block';
            dropdownOptionsContainer.style.display = isVisible ? 'none' : 'block';
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!customDropdownContainer.contains(event.target)) {
                dropdownOptionsContainer.style.display = 'none';
            }
        });
        
        // Assemble the dropdown
        customDropdownContainer.appendChild(selectedRoleDisplay);
        customDropdownContainer.appendChild(dropdownOptionsContainer);
        customDropdownContainer.appendChild(hiddenInput);
        
        // Create remove button for this additional role
        const removeContainer = document.createElement('div');
        removeContainer.className = 'mt-2';
        
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-outline-danger btn-sm';
        removeBtn.innerHTML = '<i class="fas fa-trash"></i> Remove This Role';
        
        removeBtn.addEventListener('click', function() {
            // Remove this dropdown
            dropdownContainer.remove();
            
            // Remove from array
            const index = additionalRoleDropdowns.findIndex(d => d.id === dropdownId);
            if (index !== -1) {
                additionalRoleDropdowns.splice(index, 1);
            }
            
            // Update labels
            updateAdditionalRoleLabels();
            
            // Show/hide title
            const title = document.getElementById('additionalRolesTitle');
            if (additionalRoleDropdowns.length === 0) {
                title.style.display = 'none';
            }
        });
        
        removeContainer.appendChild(removeBtn);
        
        // Add everything to the container
        dropdownContainer.appendChild(customDropdownContainer);
        dropdownContainer.appendChild(removeContainer);
        
        // Add to DOM
        additionalRolesContainer.appendChild(dropdownContainer);
        
        // Store reference
        additionalRoleDropdowns.push({
            id: dropdownId,
            container: dropdownContainer,
            selectedRoleText: selectedRoleText,
            hiddenInput: hiddenInput
        });
        
        // Show title
        const title = document.getElementById('additionalRolesTitle');
        title.style.display = 'block';
        
        // Update labels
        updateAdditionalRoleLabels();
        
        // Clear primary selection so user can select a different role if needed
        roleInput.value = '';
        selectedRoleText.textContent = 'Select Role';
        // Keep "Add another role" button visible so user can add more roles
        // Button stays visible because a role was just added
    }
    
    // Update labels for additional role dropdowns
    function updateAdditionalRoleLabels() {
        additionalRoleDropdowns.forEach((dropdown, index) => {
            const label = dropdown.container.querySelector('label');
            if (label) {
                label.textContent = `Additional Role ${index + 1}`;
            }
        });
    }
    
    // Update all dropdowns when roles change
    function updateAllDropdowns() {
        // Update main dropdown
        renderRoleDropdown();
        
        // Update all additional role dropdowns
        additionalRoleDropdowns.forEach(dropdown => {
            const dropdownOptions = dropdown.container.querySelector('.dropdown-options');
            if (dropdownOptions) {
                dropdownOptions.innerHTML = '';
                
                allRoles.forEach(role => {
                    const roleOption = document.createElement('div');
                    roleOption.className = 'role-option';
                    roleOption.dataset.value = role.value;
                    
                    const roleNameSpan = document.createElement('span');
                    roleNameSpan.textContent = role.name;
                    
                    roleOption.appendChild(roleNameSpan);
                    
                    // Add delete button for custom roles
                    if (!role.isPredefined) {
                        const deleteBtn = document.createElement('button');
                        deleteBtn.type = 'button';
                        deleteBtn.className = 'delete-role-btn';
                        deleteBtn.innerHTML = '<i class="fas fa-times"></i>';
                        deleteBtn.title = 'Delete this role';
                        deleteBtn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            deleteRole(role.value);
                        });
                        roleOption.appendChild(deleteBtn);
                    }
                    
                    roleOption.addEventListener('click', function() {
                        // Update this dropdown's selection
                        const selectedRoleText = dropdown.container.querySelector('.selected-role-text');
                        if (selectedRoleText) {
                            selectedRoleText.textContent = role.name;
                        }
                        
                        // Update hidden input
                        const hiddenInput = dropdown.container.querySelector('.additional-role-input');
                        if (hiddenInput) {
                            hiddenInput.value = role.value;
                        }
                        
                        // Hide dropdown
                        const dropdownOptionsContainer = dropdown.container.querySelector('.dropdown-options-container');
                        if (dropdownOptionsContainer) {
                            dropdownOptionsContainer.style.display = 'none';
                        }
                    });
                    
                    dropdownOptions.appendChild(roleOption);
                });
                
                // Add "Add New Role" option at the bottom of additional dropdowns too
                const addNewRoleOption = document.createElement('div');
                addNewRoleOption.className = 'role-option add-new-role-option';
                addNewRoleOption.style.borderTop = '1px solid #dee2e6';
                addNewRoleOption.style.marginTop = '0.25rem';
                addNewRoleOption.style.paddingTop = '0.5rem';
                
                const addIcon = document.createElement('i');
                addIcon.className = 'fas fa-plus me-2';
                addIcon.style.color = '#0d6efd';
                
                const addText = document.createElement('span');
                addText.textContent = 'Add New Role';
                addText.style.color = '#0d6efd';
                addText.style.fontWeight = '500';
                
                addNewRoleOption.appendChild(addIcon);
                addNewRoleOption.appendChild(addText);
                
                addNewRoleOption.addEventListener('click', function(e) {
                    e.stopPropagation();
                    addNewRole();
                });
                
                dropdownOptions.appendChild(addNewRoleOption);
            }
        });
    }
    
    // Update selected role display
    function updateSelectedRoleDisplay() {
        const roleValue = roleInput.value;
        if (roleValue) {
            const role = allRoles.find(r => r.value === roleValue);
            if (role) {
                selectedRoleText.textContent = role.name;
            }
        }
    }
    
    // Update specialization placeholder based on role
    function updateSpecializationPlaceholder(roleValue) {
        switch(roleValue) {
            case 'technician':
                specializationInput.placeholder = 'e.g., Master Technician, Electrical Specialist';
                break;
            case 'service_advisor':
                specializationInput.placeholder = 'e.g., Service Advisor, Customer Service';
                break;
            case 'manager':
                specializationInput.placeholder = 'e.g., Service Manager, Operations Manager';
                break;
            case 'admin':
                specializationInput.placeholder = 'e.g., System Administrator, IT Support';
                break;
            case 'office_staff':
                specializationInput.placeholder = 'e.g., Receptionist, Accounting';
                break;
            case 'executive':
                specializationInput.placeholder = 'e.g., CEO, Operations Director';
                break;
            default:
                specializationInput.placeholder = 'Specialization / Title';
        }
    }
    
    // Toggle dropdown
    selectedRoleDisplay.addEventListener('click', function() {
        if (dropdownOptions.style.display === 'block') {
            closeDropdown();
        } else {
            openDropdown();
        }
    });
    
    function openDropdown() {
        dropdownOptions.style.display = 'block';
        selectedRoleDisplay.style.borderColor = '#86b7fe';
        selectedRoleDisplay.style.boxShadow = '0 0 0 0.25rem rgba(13, 110, 253, 0.25)';
    }
    
    function closeDropdown() {
        dropdownOptions.style.display = 'none';
        selectedRoleDisplay.style.borderColor = '#ced4da';
        selectedRoleDisplay.style.boxShadow = 'none';
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!selectedRoleDisplay.contains(e.target) && !dropdownOptionsContainer.contains(e.target)) {
            closeDropdown();
        }
    });
    
    // Initialize
    initializeRoles();
    
    // Event Listeners
    addAnotherRoleBtn.addEventListener('click', addAnotherRole);
    
    // ============================================
    // SPECIALIZATION FUNCTIONALITY (KEEP EXISTING)
    // ============================================
    
    // Add Specialization Button Functionality
    let specializationCount = 0;
    document.getElementById('addSpecializationBtn').addEventListener('click', function() {
        specializationCount++;
        const container = document.getElementById('additionalSpecializations');
        
        const newFieldDiv = document.createElement('div');
        newFieldDiv.className = 'input-group mb-2';
        newFieldDiv.innerHTML = `
            <input type="text" class="form-control" 
                   name="specializations[]" 
                   placeholder="Additional specialization #${specializationCount + 1}">
            <button type="button" class="btn btn-outline-danger remove-specialization" title="Remove this specialization">
                <i class="fas fa-times"></i>
            </button>
        `;
        
        container.appendChild(newFieldDiv);
        
        // Add event listener to remove button
        newFieldDiv.querySelector('.remove-specialization').addEventListener('click', function() {
            newFieldDiv.remove();
        });
    });

    // Handle form submission for multiple roles
    document.querySelector('form').addEventListener('submit', function(e) {
        // Combine all specializations into one field (comma-separated)
        const mainSpecialization = document.getElementById('specialization').value;
        const additionalSpecializations = Array.from(document.querySelectorAll('input[name="specializations[]"]'))
            .map(input => input.value.trim())
            .filter(value => value);
        
        if (additionalSpecializations.length > 0) {
            const allSpecializations = [mainSpecialization, ...additionalSpecializations]
                .filter(value => value)
                .join(', ');
            document.getElementById('specialization').value = allSpecializations;
        }
        
        // Handle multiple roles - create hidden inputs for additional roles
        additionalRoles.forEach((role, index) => {
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = `additional_roles[${index}]`;
            hiddenInput.value = role.value;
            this.appendChild(hiddenInput);
        });
    });
});
</script>
@endpush
@endsection