@extends('layouts.app')

@section('title', 'Add Vehicle Color')

@section('content')
<div class="page-header">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-plus me-2"></i>Add Vehicle Color</h1>
        <p class="text-muted mb-0">Create a new vehicle color</p>
    </div>
    <a href="{{ route('vehicle-colors.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('vehicle-colors.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Color Name <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" required maxlength="50">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="hex_code" class="form-label">Hex Code</label>
                    <div class="input-group">
                        <input type="color" id="hex_picker" class="form-control form-control-color"
                               value="{{ old('hex_code', '#000000') }}" style="max-width:60px"
                               onchange="$('#hex_code').val(this.value)">
                        <input type="text" id="hex_code" name="hex_code" class="form-control @error('hex_code') is-invalid @enderror"
                               value="{{ old('hex_code') }}" placeholder="#FF0000" maxlength="7">
                    </div>
                    @error('hex_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <small class="text-muted">Color picker on the left, or type hex code manually.</small>
                </div>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" id="is_active" name="is_active" class="form-check-input" value="1" checked>
                <label for="is_active" class="form-check-label">Active</label>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Color</button>
        </form>
    </div>
</div>
@endsection
