@extends('layouts.app')

@section('title', 'Edit Compliance Standard')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Edit Standard</h1>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('compliance.standards.update', $standard->id) }}">
                @csrf
                @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Name *</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $standard->name) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Code</label>
                        <input type="text" name="code" class="form-control" value="{{ old('code', $standard->code) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Category</label>
                        <select name="category" class="form-select">
                            <option value="">Select category</option>
                            @foreach($categories as $catOption)
                                <option value="{{ $catOption['value'] }}" {{ old('category', $standard->category) == $catOption['value'] ? 'selected' : '' }}>{{ $catOption['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $standard->description) }}</textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Requirements</label>
                        <textarea name="requirements" class="form-control" rows="5" placeholder="One requirement per line">{{ old('requirements', is_string($standard->requirements) && !json_decode($standard->requirements, true) ? $standard->requirements : (is_array($standard->requirements) ? implode("\n", $standard->requirements) : '')) }}</textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Effective Date</label>
                        <input type="date" name="effective_date" class="form-control" value="{{ old('effective_date', $standard->effective_date ? \Carbon\Carbon::parse($standard->effective_date)->format('Y-m-d') : '') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Expiration Date</label>
                        <input type="date" name="expiration_date" class="form-control" value="{{ old('expiration_date', $standard->expiration_date ? \Carbon\Carbon::parse($standard->expiration_date)->format('Y-m-d') : '') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_mandatory" id="isMandatory" value="1" {{ old('is_mandatory', $standard->is_mandatory) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isMandatory">Mandatory</label>
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Standard</button>
                    <a href="{{ route('compliance.standards.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
