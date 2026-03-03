@extends('layouts.app')

@section('title', 'Add New Technician')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-plus"></i> Add New Technician
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('technicians.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <form action="{{ route('technicians.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Full Name *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}">
                                    @error('phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="specialization">Specialization</label>
                                    <select class="form-control @error('specialization') is-invalid @enderror" 
                                            id="specialization" name="specialization">
                                        <option value="">Select Specialization</option>
                                        <option value="General Mechanic" {{ old('specialization') == 'General Mechanic' ? 'selected' : '' }}>General Mechanic</option>
                                        <option value="Electrical Systems" {{ old('specialization') == 'Electrical Systems' ? 'selected' : '' }}>Electrical Systems</option>
                                        <option value="Engine Repair" {{ old('specialization') == 'Engine Repair' ? 'selected' : '' }}>Engine Repair</option>
                                        <option value="Transmission" {{ old('specialization') == 'Transmission' ? 'selected' : '' }}>Transmission</option>
                                        <option value="Brakes & Suspension" {{ old('specialization') == 'Brakes & Suspension' ? 'selected' : '' }}>Brakes & Suspension</option>
                                        <option value="AC & Heating" {{ old('specialization') == 'AC & Heating' ? 'selected' : '' }}>AC & Heating</option>
                                        <option value="Diagnostics" {{ old('specialization') == 'Diagnostics' ? 'selected' : '' }}>Diagnostics</option>
                                        <option value="Body Work" {{ old('specialization') == 'Body Work' ? 'selected' : '' }}>Body Work</option>
                                        <option value="Paint" {{ old('specialization') == 'Paint' ? 'selected' : '' }}>Paint</option>
                                    </select>
                                    @error('specialization')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="years_experience">Years of Experience</label>
                                    <input type="number" class="form-control @error('years_experience') is-invalid @enderror" 
                                           id="years_experience" name="years_experience" 
                                           value="{{ old('years_experience') }}" min="0" max="50">
                                    @error('years_experience')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="shift_schedule">Shift Schedule</label>
                                    <select class="form-control @error('shift_schedule') is-invalid @enderror" 
                                            id="shift_schedule" name="shift_schedule">
                                        <option value="">Select Shift</option>
                                        <option value="Morning (6AM-2PM)" {{ old('shift_schedule') == 'Morning (6AM-2PM)' ? 'selected' : '' }}>Morning (6AM-2PM)</option>
                                        <option value="Afternoon (2PM-10PM)" {{ old('shift_schedule') == 'Afternoon (2PM-10PM)' ? 'selected' : '' }}>Afternoon (2PM-10PM)</option>
                                        <option value="Night (10PM-6AM)" {{ old('shift_schedule') == 'Night (10PM-6AM)' ? 'selected' : '' }}>Night (10PM-6AM)</option>
                                        <option value="Flexible" {{ old('shift_schedule') == 'Flexible' ? 'selected' : '' }}>Flexible</option>
                                        <option value="Full Day (8AM-5PM)" {{ old('shift_schedule') == 'Full Day (8AM-5PM)' ? 'selected' : '' }}>Full Day (8AM-5PM)</option>
                                    </select>
                                    @error('shift_schedule')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="emergency_contact_name">Emergency Contact Name</label>
                                    <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                           id="emergency_contact_name" name="emergency_contact_name" 
                                           value="{{ old('emergency_contact_name') }}">
                                    @error('emergency_contact_name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="emergency_contact_phone">Emergency Contact Phone</label>
                                    <input type="text" class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                                           id="emergency_contact_phone" name="emergency_contact_phone" 
                                           value="{{ old('emergency_contact_phone') }}">
                                    @error('emergency_contact_phone')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="skills">Skills</label>
                                    <div id="skills-container">
                                        @if(old('skills'))
                                            @foreach(old('skills') as $skill)
                                                <div class="input-group mb-2">
                                                    <input type="text" class="form-control" name="skills[]" value="{{ $skill }}">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-danger remove-skill">×</button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" id="add-skill" class="btn btn-sm btn-secondary mt-2">
                                        <i class="fas fa-plus"></i> Add Skill
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="certifications">Certifications</label>
                                    <div id="certifications-container">
                                        @if(old('certifications'))
                                            @foreach(old('certifications') as $certification)
                                                <div class="input-group mb-2">
                                                    <input type="text" class="form-control" name="certifications[]" value="{{ $certification }}">
                                                    <div class="input-group-append">
                                                        <button type="button" class="btn btn-danger remove-certification">×</button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <button type="button" id="add-certification" class="btn btn-sm btn-secondary mt-2">
                                        <i class="fas fa-plus"></i> Add Certification
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Note:</strong> The technician will be created with default password "technician123". 
                            They should change it on their first login.
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Create Technician
                        </button>
                        <a href="{{ route('technicians.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Add skill field
        $('#add-skill').click(function() {
            const container = $('#skills-container');
            const field = `
                <div class="input-group mb-2">
                    <input type="text" class="form-control" name="skills[]" placeholder="e.g., Engine Repair, Diagnostics">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger remove-skill">×</button>
                    </div>
                </div>
            `;
            container.append(field);
        });

        // Add certification field
        $('#add-certification').click(function() {
            const container = $('#certifications-container');
            const field = `
                <div class="input-group mb-2">
                    <input type="text" class="form-control" name="certifications[]" placeholder="e.g., ASE Certified, EPA 609">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-danger remove-certification">×</button>
                    </div>
                </div>
            `;
            container.append(field);
        });

        // Remove skill field
        $(document).on('click', '.remove-skill', function() {
            $(this).closest('.input-group').remove();
        });

        // Remove certification field
        $(document).on('click', '.remove-certification', function() {
            $(this).closest('.input-group').remove();
        });
    });
</script>
@endsection