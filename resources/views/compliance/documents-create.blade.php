@extends('layouts.app')

@section('title', 'Upload Compliance Document')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">Upload Compliance Document</h1>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('compliance.documents.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Document Name *</label>
                        <input type="text" name="document_name" class="form-control" value="{{ old('document_name') }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="document_type" class="form-select">
                            <option value="">Select type</option>
                            @foreach($documentTypes as $typeOption)
                                <option value="{{ $typeOption['value'] }}" {{ old('document_type') == $typeOption['value'] ? 'selected' : '' }}>{{ $typeOption['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Document Number</label>
                        <input type="text" name="document_number" class="form-control" value="{{ old('document_number') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Issuing Authority</label>
                        <input type="text" name="issuing_authority" class="form-control" value="{{ old('issuing_authority') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Related Standard</label>
                        <select name="standard_id" class="form-select">
                            <option value="">No standard</option>
                            @foreach($standards as $standard)
                                <option value="{{ $standard->id }}" {{ old('standard_id') == $standard->id ? 'selected' : '' }}>{{ $standard->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Issue Date</label>
                        <input type="date" name="issue_date" class="form-control" value="{{ old('issue_date') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Expiration Date</label>
                        <input type="date" name="expiration_date" class="form-control" value="{{ old('expiration_date') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Renewal Date</label>
                        <input type="date" name="renewal_date" class="form-control" value="{{ old('renewal_date') }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">File</label>
                        <input type="file" name="file" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Upload</button>
                    <a href="{{ route('compliance.documents.index') }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
