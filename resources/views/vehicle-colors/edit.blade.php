@extends('layouts.app')

@section('title', 'Edit Color')

@section('content')
<div class="page-header">
    <div>
        <h1 class="h3 mb-0"><i class="fas fa-edit me-2"></i>Edit Color: {{ $vehicleColor->name }}</h1>
    </div>
    <a href="{{ route('vehicle-colors.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('vehicle-colors.update', $vehicleColor) }}" method="POST">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Color Name <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $vehicleColor->name) }}" required maxlength="50">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="hex_code" class="form-label">Hex Code</label>
                    <div class="input-group">
                        <input type="color" id="hex_picker" class="form-control form-control-color"
                               value="{{ old('hex_code', $vehicleColor->hex_code ?? '#000000') }}" style="max-width:60px"
                               onchange="$('#hex_code').val(this.value)">
                        <input type="text" id="hex_code" name="hex_code" class="form-control @error('hex_code') is-invalid @enderror"
                               value="{{ old('hex_code', $vehicleColor->hex_code) }}" placeholder="#FF0000" maxlength="7">
                    </div>
                    @error('hex_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" id="is_active" name="is_active" class="form-check-input" value="1"
                       {{ $vehicleColor->is_active ? 'checked' : '' }}>
                <label for="is_active" class="form-check-label">Active</label>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Update Color</button>
        </form>
    </div>
</div>
@endsection
