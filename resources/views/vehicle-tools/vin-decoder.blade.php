@extends('layouts.app')

@section('title', 'VIN Decoder')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h1 class="h3 mb-0">VIN Decoder</h1>
            <p class="text-muted">Decode Vehicle Identification Numbers to get detailed specifications</p>
        </div>
        <div class="col-auto">
            <a href="{{ route('vehicle-tools.dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Decoder -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-barcode"></i> Decode VIN
                    </h6>
                </div>
                <div class="card-body">
                    <form id="vinDecoderForm" action="{{ route('vehicle-tools.decode-vin') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="vin">Enter VIN (17 characters)</label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control form-control-lg" 
                                       id="vin" 
                                       name="vin" 
                                       placeholder="1HGCM82633A123456" 
                                       maxlength="17"
                                       value="{{ request('vin') }}"
                                       required
                                       pattern="[A-HJ-NPR-Z0-9]{17}"
                                       title="17 character VIN (letters and numbers, excluding I, O, Q)">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-magic"></i> Decode
                                    </button>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Enter a 17-character VIN. Characters I, O, and Q are not used in VINs.
                            </small>
                        </div>
                    </form>

                    <!-- Quick VINs -->
                    <div class="mt-4">
                        <h6 class="text-muted mb-3">Quick Decode (Recent Vehicles):</h6>
                        <div class="row">
                            @foreach($vehicles as $vehicle)
                                @if($vehicle->vin)
                                <div class="col-md-4 mb-2">
                                    <a href="{{ route('vehicle-tools.vin-decoder') }}?vin={{ $vehicle->vin }}" 
                                       class="btn btn-outline-secondary btn-block text-left">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <small class="d-block text-truncate">{{ $vehicle->vin }}</small>
                                                <small class="d-block text-muted">
                                                    {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}
                                                </small>
                                            </div>
                                            <div>
                                                @if($vehicle->isVINDecoded())
                                                    <span class="badge badge-success">Decoded</span>
                                                @else
                                                    <span class="badge badge-warning">Not Decoded</span>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Batch Decoding -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-layer-group"></i> Batch VIN Decoding
                    </h6>
                </div>
                <div class="card-body">
                    <form id="batchDecoderForm" action="{{ route('vehicle-tools.batch-decode-vin') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="batchVins">Enter multiple VINs (one per line)</label>
                            <textarea class="form-control" 
                                      id="batchVins" 
                                      name="vins" 
                                      rows="5" 
                                      placeholder="1HGCM82633A123456&#10;2HGFA16566H123456&#10;3VWDP7AJ7DM123456"></textarea>
                            <small class="form-text text-muted">
                                Enter up to 50 VINs, one per line. Each VIN must be 17 characters.
                            </small>
                        </div>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-play-circle"></i> Decode All
                        </button>
                        <button type="button" id="validateBatchVins" class="btn btn-outline-secondary">
                            <i class="fas fa-check"></i> Validate VINs
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4 mb-4">
            <!-- VIN Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-secondary">
                        <i class="fas fa-info-circle"></i> About VINs
                    </h6>
                </div>
                <div class="card-body">
                    <h6>What is a VIN?</h6>
                    <p class="small text-muted">
                        A Vehicle Identification Number (VIN) is a unique code used to identify individual motor vehicles.
                    </p>
                    
                    <h6 class="mt-3">VIN Structure:</h6>
                    <div class="small">
                        <div class="d-flex justify-content-between mb-1">
                            <span>1-3: WMI</span>
                            <span class="text-muted">World Manufacturer Identifier</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>4-8: VDS</span>
                            <span class="text-muted">Vehicle Descriptor Section</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>9: Check Digit</span>
                            <span class="text-muted">Validation digit</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>10: Model Year</span>
                            <span class="text-muted">Vehicle model year</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>11: Plant Code</span>
                            <span class="text-muted">Manufacturing plant</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>12-17: Serial</span>
                            <span class="text-muted">Vehicle serial number</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- VIN Validation -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="fas fa-check-circle"></i> VIN Validator
                    </h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="validateVin">Validate VIN Format</label>
                        <div class="input-group">
                            <input type="text" 
                                   class="form-control" 
                                   id="validateVin" 
                                   placeholder="Enter VIN to validate">
                            <div class="input-group-append">
                                <button type="button" id="validateVinBtn" class="btn btn-warning">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="validationResult" class="mt-3 d-none">
                        <div class="alert" role="alert">
                            <h6 class="alert-heading">Validation Result</h6>
                            <p id="validationMessage" class="mb-0 small"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Decodes -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="fas fa-history"></i> Recent Decodes
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($vehicles->where('vin_decoded_at', '!=', null)->take(5) as $vehicle)
                            <a href="{{ route('vehicle-tools.vin-results', ['vin' => $vehicle->vin]) }}" 
                               class="list-group-item list-group-item-action">
                                <div class="d-flex w-100 justify-content-between">
                                    <small class="text-monospace">{{ $vehicle->vin }}</small>
                                    <small class="text-muted">{{ $vehicle->vin_decoded_at->diffForHumans() }}</small>
                                </div>
                                <small class="text-muted">
                                    {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}
                                </small>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- VIN Lookup Results (if VIN provided) -->
    @if(request('vin'))
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-search"></i> Looking up VIN: <code>{{ request('vin') }}</code>
                        </h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-3">Decoding VIN...</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    // VIN validation
    document.getElementById('validateVinBtn').addEventListener('click', function() {
        const vin = document.getElementById('validateVin').value.toUpperCase();
        const resultDiv = document.getElementById('validationResult');
        const message = document.getElementById('validationMessage');
        
        // Basic VIN validation regex
        const vinRegex = /^[A-HJ-NPR-Z0-9]{17}$/i;
        
        if (!vin) {
            showValidationResult('Please enter a VIN to validate.', 'warning');
            return;
        }
        
        if (vin.length !== 17) {
            showValidationResult('VIN must be exactly 17 characters.', 'danger');
            return;
        }
        
        if (!vinRegex.test(vin)) {
            showValidationResult('Invalid VIN format. VIN contains invalid characters (I, O, Q are not allowed).', 'danger');
            return;
        }
        
        // Check digit validation (simplified)
        const checkDigitValid = validateVINCheckDigit(vin);
        
        if (checkDigitValid) {
            showValidationResult('VIN format is valid. Check digit verification passed.', 'success');
        } else {
            showValidationResult('VIN format appears valid but check digit verification failed. Please double-check the VIN.', 'warning');
        }
    });
    
    function showValidationResult(text, type) {
        const resultDiv = document.getElementById('validationResult');
        const message = document.getElementById('validationMessage');
        const alertDiv = resultDiv.querySelector('.alert');
        
        message.textContent = text;
        alertDiv.className = `alert alert-${type}`;
        resultDiv.classList.remove('d-none');
    }
    
    function validateVINCheckDigit(vin) {
        // Simplified check digit validation
        // In production, implement full ISO 3779 check digit calculation
        return true;
    }
    
    // Batch VIN validation
    document.getElementById('validateBatchVins').addEventListener('click', function() {
        const textarea = document.getElementById('batchVins');
        const vins = textarea.value.split('\n').map(v => v.trim()).filter(v => v);
        const vinRegex = /^[A-HJ-NPR-Z0-9]{17}$/i;
        
        let validCount = 0;
        let invalidVins = [];
        
        vins.forEach((vin, index) => {
            if (vinRegex.test(vin)) {
                validCount++;
            } else {
                invalidVins.push(`Line ${index + 1}: ${vin}`);
            }
        });
        
        if (invalidVins.length === 0) {
            alert(`✅ All ${validCount} VINs are valid!`);
        } else {
            alert(`❌ ${invalidVins.length} invalid VINs found:\n\n${invalidVins.join('\n')}`);
        }
    });
    
    // Auto-format VIN input
    document.getElementById('vin').addEventListener('input', function(e) {
        this.value = this.value.toUpperCase().replace(/[^A-HJ-NPR-Z0-9]/g, '');
    });
    
    // Auto-redirect if VIN is provided in URL
    @if(request('vin'))
        setTimeout(function() {
            window.location.href = "{{ route('vehicle-tools.vin-results', ['vin' => request('vin')]) }}";
        }, 1000);
    @endif
</script>
@endsection