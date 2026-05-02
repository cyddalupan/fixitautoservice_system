<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Get a Quote - Fixit Auto Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- jQuery UI CSS (load before custom CSS to allow overrides) -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <style>
        /* Dark Mode Toggle Switch */
        .dark-mode-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .toggle-switch {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 8px 12px;
            background: var(--bg-container);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            color: var(--text-primary);
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .toggle-switch:hover {
            border-color: var(--primary-red);
        }
        
        .toggle-icon {
            font-size: 16px;
            transition: transform 0.3s ease;
        }
        
        .toggle-text {
            font-weight: 500;
        }
        
        /* Manual Dark Mode Class */
        .dark-mode {
            --bg-body: #1a1a1a;
            --bg-container: #2d2d2d;
            --text-primary: #e0e0e0;
            --text-secondary: #cccccc;
            --border-color: #555555;
            --input-bg: #404040;
            --input-border: #666666;
            --input-text: #e0e0e0;
            --alert-success-bg: #0d3b2a;
            --alert-success-text: #a3cfbb;
            --alert-success-border: #145c3e;
            --section-border: #666666;
            --upload-area-bg: rgba(181, 12, 9, 0.15);
            --upload-area-border: #B50C09;
            --terms-bg: #333333;
            --terms-border: #B50C09;
        }
        
        /* Manual Light Mode Class (overrides system dark) */
        .light-mode {
            --bg-body: #f5f5f5;
            --bg-container: #ffffff;
            --text-primary: #000000;
            --text-secondary: #333333;
            --border-color: #999999;
            --input-bg: #ffffff;
            --input-border: #ced4da;
            --input-text: #495057;
            --alert-success-bg: #d1e7dd;
            --alert-success-text: #0f5132;
            --alert-success-border: #badbcc;
            --section-border: #999999;
            --upload-area-bg: rgba(181, 12, 9, 0.05);
            --upload-area-border: #B50C09;
            --terms-bg: #f8f9fa;
            --terms-border: #B50C09;
        }
        
        /* CSS Variables for Light Mode (Default) */
        :root {
            --primary-red: #B50C09;
            --primary-red-dark: #8a0907;
            --pure-black: #000000;
            --pure-white: #ffffff;
            --bg-body: #f5f5f5;
            --bg-container: #ffffff;
            --text-primary: #000000;
            --text-secondary: #333333;
            --border-color: #999999;
            --input-bg: #ffffff;
            --input-border: #ced4da;
            --input-text: #495057;
            --alert-success-bg: #d1e7dd;
            --alert-success-text: #0f5132;
            --alert-success-border: #badbcc;
            --section-border: #999999;
            --upload-area-bg: rgba(181, 12, 9, 0.05);
            --upload-area-border: #B50C09;
            --terms-bg: #f8f9fa;
            --terms-border: #B50C09;
        }

        /* Dark Mode Variables - Night Grey Theme */
        @media (prefers-color-scheme: dark) {
            :root {
                --bg-body: #1a1a1a;          /* Night Grey - Dark background */
                --bg-container: #2d2d2d;     /* Night Grey - Container background */
                --text-primary: #e0e0e0;     /* Light grey text */
                --text-secondary: #cccccc;   /* Medium grey text */
                --border-color: #555555;     /* Dark grey borders */
                --input-bg: #404040;         /* Dark input background */
                --input-border: #666666;     /* Medium grey borders */
                --input-text: #e0e0e0;       /* Light text in inputs */
                --alert-success-bg: #0d3b2a; /* Dark green for success */
                --alert-success-text: #a3cfbb; /* Light green text */
                --alert-success-border: #145c3e; /* Medium green border */
                --section-border: #666666;   /* Medium grey section borders */
                --upload-area-bg: rgba(181, 12, 9, 0.15); /* Darker red overlay */
                --upload-area-border: #B50C09; /* Red border stays same */
                --terms-bg: #333333;         /* Dark grey for terms */
                --terms-border: #B50C09;     /* Red border stays same */
            }
            
            /* Additional dark mode specific styles */
            .form-control {
                background-color: var(--input-bg) !important;
                border-color: var(--input-border) !important;
                color: var(--input-text) !important;
            }
            
            .form-control::placeholder {
                color: #999999 !important;
            }
            
            .form-check-label {
                color: var(--text-primary) !important;
            }
            
            .text-muted {
                color: #aaaaaa !important;
            }
            
            .alert-light {
                background-color: var(--terms-bg) !important;
                border-color: var(--terms-border) !important;
                color: var(--text-primary) !important;
            }
            
            .form-control:focus {
                background-color: var(--input-bg) !important;
                border-color: var(--primary-red) !important;
                color: var(--input-text) !important;
                box-shadow: 0 0 0 0.25rem rgba(181, 12, 9, 0.25) !important;
            }
            
            select.form-control option {
                background-color: var(--bg-container) !important;
                color: var(--text-primary) !important;
            }
        }

        /* Base Styles using CSS Variables */
        body { 
            background: var(--bg-body); 
            padding: 20px; 
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        .container { 
            max-width: 900px; 
            background: var(--bg-container); 
            border-radius: 10px; 
            padding: 30px; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: background-color 0.3s ease;
        }
        
        .header { 
            background: var(--primary-red); 
            color: var(--pure-white); 
            padding: 20px; 
            border-radius: 10px 10px 0 0; 
        }
        
        .btn-submit { 
            background: var(--primary-red); 
            color: var(--pure-white); 
            padding: 15px; 
            width: 100%; 
            border: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        
        .btn-submit:hover { 
            background: var(--primary-red-dark); 
        }
        
        h3 { 
            color: var(--primary-red); 
            border-bottom: 2px solid var(--section-border); 
            padding-bottom: 10px; 
            margin-top: 30px; 
        }
        
        .required::after { 
            content: " *"; 
            color: var(--primary-red); 
        }
        
        label {
            color: var(--text-primary);
            font-weight: 500;
        }
        
        .form-control {
            background-color: var(--input-bg);
            border-color: var(--input-border);
            color: var(--input-text);
            transition: all 0.3s ease;
        }
        
        .alert-success {
            background-color: var(--alert-success-bg);
            border-color: var(--alert-success-border);
            color: var(--alert-success-text);
        }
        
        .border-danger {
            border-color: var(--upload-area-border) !important;
        }
        
        .text-danger {
            color: var(--primary-red) !important;
        }
        
        .btn-outline-danger {
            color: var(--primary-red);
            border-color: var(--primary-red);
        }
        
        .btn-outline-danger:hover {
            background-color: var(--primary-red);
            color: var(--pure-white);
        }
        
        .alert-light {
            background-color: var(--terms-bg);
            border-color: var(--terms-border);
        }
        
        /* jQuery UI Autocomplete Custom Styling - Override default */
        .ui-autocomplete {
            max-height: 200px !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            background-color: var(--input-bg) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 4px !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
            z-index: 9999 !important;
        }
        
        .ui-autocomplete .ui-menu-item {
            padding: 8px 12px !important;
            font-size: 14px !important;
            color: var(--text-primary) !important;
            cursor: pointer !important;
            border-bottom: 1px solid var(--border-color) !important;
            background-color: var(--input-bg) !important;
        }
        
        .ui-autocomplete .ui-menu-item:last-child {
            border-bottom: none !important;
        }
        
        .ui-autocomplete .ui-menu-item:hover,
        .ui-autocomplete .ui-state-focus,
        .ui-autocomplete .ui-state-active {
            background-color: var(--primary-red) !important;
            color: white !important;
            border-color: var(--primary-red) !important;
        }
        
        .ui-autocomplete .ui-state-hover {
            background-color: var(--primary-red) !important;
            color: white !important;
            border-color: var(--primary-red) !important;
        }
        
        .upload-area-dragover {
            border-color: #8a0907 !important;
            background: rgba(181, 12, 9, 0.15) !important;
        }
        .photo-thumb-remove {
            transition: all 0.2s ease;
        }
        .photo-thumb-remove:hover {
            transform: scale(1.2);
        }
        .ui-helper-hidden-accessible {
            display: none !important;
        }
        
        /* Ensure autocomplete works in dark mode */
        .dark-mode .ui-autocomplete {
            background-color: var(--input-bg) !important;
            border-color: var(--border-color) !important;
        }
        
        .dark-mode .ui-autocomplete .ui-menu-item {
            color: var(--text-primary) !important;
            background-color: var(--input-bg) !important;
            border-color: var(--border-color) !important;
        }
        
        .dark-mode .ui-autocomplete .ui-menu-item:hover,
        .dark-mode .ui-autocomplete .ui-state-focus,
        .dark-mode .ui-autocomplete .ui-state-active {
            background-color: var(--primary-red) !important;
            color: white !important;
        }
        @media (max-width: 768px) {
            .container {
                max-width: 100% !important;
                margin: 10px !important;
                padding: 20px !important;
                border-radius: 5px !important;
            }
            
            .form-section {
                padding: 15px !important;
                margin-bottom: 15px !important;
            }
            
            h2 { font-size: 1.5rem !important; }
            h3 { font-size: 1.2rem !important; }
            
            .btn {
                width: 100% !important;
                margin-bottom: 10px !important;
            }
            
            input, select, textarea {
                font-size: 16px !important;
            }
            
            .row > [class*="col-"] {
                width: 100% !important;
                margin-bottom: 15px !important;
            }
            .form-control, .form-select {
                height: 45px !important;
                font-size: 16px !important;
            }
            
            .form-check-label {
                font-size: 14px !important;
            }
            
            .section-title {
                font-size: 1.8rem !important;
                margin-bottom: 20px !important;
            }
        }
        
        @media (max-width: 480px) {
            .container { padding: 15px !important; }
            h2 { font-size: 1.3rem !important; }
            .dark-mode-toggle { top: 10px !important; right: 10px !important; }
        }
    </style>
    
    <!-- jQuery and jQuery UI for autocomplete -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
</head>
<body>
    <!-- Dark Mode Toggle -->
    <div class="dark-mode-toggle">
        <div class="toggle-switch" id="darkModeToggle">
            <span class="toggle-icon">🌙</span>
            <span class="toggle-text">Dark Mode</span>
        </div>
    </div>
    
    <div class="container">
        <div class="header text-center mb-4">
            <h1><i class="fas fa-file-invoice-dollar me-2"></i>Get a Quote</h1>
            <p>Fill out the form below for a detailed quotation</p>
        </div>
        
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        
        <form action="{{ route('quotation.submit') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            <!-- SECTION 1: YOUR INFORMATION -->
            <h3><i class="fas fa-user me-2"></i>Your Information</h3>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="required">Full Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="required">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="required">Phone</label>
                    <input type="tel" name="phone" class="form-control" required>
                </div>
            </div>
            
            <!-- SECTION 2: VEHICLE INFORMATION -->
            <h3><i class="fas fa-car me-2"></i>Vehicle Information</h3>
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="required">Vehicle Make</label>
                    <input type="text" name="vehicle_make" class="form-control" autocomplete="off" required>
                    <small class="form-text text-muted">Start typing - suggestions will appear</small>
                </div>
                <div class="col-md-4">
                    <label class="required">Vehicle Model</label>
                    <input type="text" name="vehicle_model" class="form-control" autocomplete="off" required>
                    <small class="form-text text-muted">Start typing - suggestions will appear</small>
                </div>
                <div class="col-md-4">
                    <label class="required">Year</label>
                    <select name="vehicle_year" class="form-control" required>
                        <option value="">Select Year</option>
                        @for($year = date('Y') + 1; $year >= 1990; $year--)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-4">
                    <label>License Plate (Optional)</label>
                    <input type="text" name="license_plate" class="form-control" placeholder="ABC 123">
                </div>
                <div class="col-md-4">
                    <label>Vehicle Color (Optional)</label>
                    <input type="text" name="vehicle_color" class="form-control" autocomplete="off" placeholder="e.g., White, Red">
                    <small class="form-text text-muted">Start typing - suggestions will appear</small>
                </div>
                <div class="col-md-4">
                    <label>VIN (Optional)</label>
                    <input type="text" name="vin_number" class="form-control" placeholder="17-character VIN">
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Preferred Date (Optional)</label>
                    <input type="date" name="preferred_date" class="form-control" min="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-6">
                    <label>Preferred Time (Optional)</label>
                    <select name="preferred_time" class="form-control">
                        <option value="">Select Time</option>
                        <option value="morning">Morning (8AM-12PM)</option>
                        <option value="afternoon">Afternoon (1PM-5PM)</option>
                        <option value="evening">Evening (6PM-8PM)</option>
                    </select>
                </div>
            </div>
            
            <!-- SECTION 3: UPLOAD PHOTOS -->
            <h3><i class="fas fa-camera me-2"></i>Upload Photos (Optional)</h3>
            <div class="mb-3">
                <p class="text-muted">Mas madali magbigay ng estimate kapag may larawan ng damage o issue.</p>
                <div class="border border-danger border-2 rounded p-4 text-center" style="background: rgba(181, 12, 9, 0.05);">
                    <i class="fas fa-cloud-upload-alt fa-3x text-danger mb-3"></i>
                    <h5>Click to Upload Photos</h5>
                    <p class="text-muted">Drag & drop or click to select photos (max 3 photos, 5MB each)</p>
                    <input type="file" name="photos[]" multiple accept="image/*" style="display:none" id="photoUpload">
                    <button type="button" class="btn btn-outline-danger" id="btnSelectPhotos">
                        <i class="fas fa-camera me-2"></i>Select Photos
                    </button>
                </div>
                <!-- Photo Preview Area -->
                <div id="photoPreviewContainer" class="row mt-3 g-2"></div>
            </div>
            
            <!-- SECTION 4: SERVICE OPTIONS -->
            <h3><i class="fas fa-tools me-2"></i>Service Options</h3>
            <div class="mb-3">
                <label class="required">Service Type</label>
                <select name="service_type" class="form-control" required>
                    <option value="">Select Service Type</option>
                    <option value="General Maintenance">General Maintenance</option>
                    <option value="Brake Service">Brake Service</option>
                    <option value="Engine Repair">Engine Repair</option>
                    <option value="Transmission">Transmission</option>
                    <option value="Electrical">Electrical</option>
                    <option value="Other">Other Service</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label>Service Checklist (Select all that apply)</label>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input type="checkbox" name="service_checklist[]" value="Oil Change" class="form-check-input">
                            <label class="form-check-label">Oil Change</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="service_checklist[]" value="Brake Pad Replacement" class="form-check-input">
                            <label class="form-check-label">Brake Pad Replacement</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="service_checklist[]" value="Tire Rotation" class="form-check-input">
                            <label class="form-check-label">Tire Rotation</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-check">
                            <input type="checkbox" name="service_checklist[]" value="Battery Replacement" class="form-check-input">
                            <label class="form-check-label">Battery Replacement</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="service_checklist[]" value="AC Repair" class="form-check-input">
                            <label class="form-check-label">AC Repair</label>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" name="service_checklist[]" value="Other" class="form-check-input">
                            <label class="form-check-label">Other (specify in description)</label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SECTION 5: PARTS PREFERENCE -->
            <h3><i class="fas fa-cog me-2"></i>Parts Preference (Optional)</h3>
            <div class="mb-3">
                <div class="form-check">
                    <input type="radio" name="parts_preference" value="oem" class="form-check-input">
                    <label class="form-check-label">OEM / Genuine Parts (higher quality, higher cost)</label>
                </div>
                <div class="form-check">
                    <input type="radio" name="parts_preference" value="aftermarket" class="form-check-input">
                    <label class="form-check-label">Aftermarket / Budget Parts (good value, lower cost)</label>
                </div>
                <div class="form-check">
                    <input type="radio" name="parts_preference" value="no_preference" class="form-check-input" checked>
                    <label class="form-check-label">No Preference (technician will recommend)</label>
                </div>
            </div>
            
            <!-- SECTION 6: BUDGET RANGE -->
            <h3><i class="fas fa-money-bill-wave me-2"></i>Budget Range (Optional)</h3>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label>Minimum Budget (₱)</label>
                    <input type="number" name="budget_min" class="form-control" placeholder="0" min="0">
                </div>
                <div class="col-md-6">
                    <label>Maximum Budget (₱)</label>
                    <input type="number" name="budget_max" class="form-control" placeholder="0" min="0">
                </div>
            </div>
            
            <!-- SECTION 7: SERVICE DESCRIPTION -->
            <h3><i class="fas fa-file-alt me-2"></i>Service Description</h3>
            <div class="mb-3">
                <label class="required">Please describe the service you need in detail</label>
                <textarea name="service_description" class="form-control" rows="3" required></textarea>
            </div>
            
            <!-- SECTION 8: CONSENT -->
            <div class="mb-3">
                <div class="form-check">
                    <input type="checkbox" name="consent_contact" class="form-check-input" required>
                    <label class="form-check-label required">I agree to be contacted regarding this quote</label>
                </div>
            </div>
            
            <!-- SECTION 9: SERVICE TERMS -->
            <h3><i class="fas fa-clipboard-check me-2"></i>Our Service Terms</h3>
            <div class="alert alert-light border border-danger">
                <h5><i class="fas fa-check-circle text-success me-2"></i>What to Expect:</h5>
                <ul class="mb-0">
                    <li><strong>Warranty:</strong> 12 months on labor, 6-12 months on parts</li>
                    <li><strong>Payment Terms:</strong> Deposit may be required for large jobs</li>
                    <li><strong>Estimate Validity:</strong> Quotes valid for 30 days</li>
                    <li><strong>Timeline:</strong> We'll provide estimated completion time</li>
                    <li><strong>Updates:</strong> We'll keep you informed throughout the process</li>
                </ul>
            </div>
            
            <!-- SECTION 10: SUBMIT BUTTON -->
            <button type="submit" class="btn btn-submit">
                <i class="fas fa-paper-plane me-2"></i>Submit Quote Request
            </button>
            
            <!-- SECTION 11: BACK LINK -->
            <div class="text-center mt-4">
                <a href="https://fixitautoservices.com/" class="text-danger">
                    <i class="fas fa-arrow-left me-2"></i>Back to Homepage
                </a>
            </div>
        </form>
    </div>
    
    <script>
        // Photo upload with preview
        const photoInput = document.getElementById('photoUpload');
        const previewContainer = document.getElementById('photoPreviewContainer');
        const btnSelect = document.getElementById('btnSelectPhotos');
        let selectedFiles = [];

        btnSelect.addEventListener('click', function() {
            photoInput.click();
        });

        // Also allow clicking the upload area itself
        const uploadArea = document.querySelector('.border.border-danger.border-2.rounded.p-4.text-center');
        uploadArea.addEventListener('click', function(e) {
            if (e.target.tagName !== 'BUTTON' && e.target.tagName !== 'I') {
                photoInput.click();
            }
        });

        // Drag & drop support
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.borderColor = '#8a0907';
            this.style.background = 'rgba(181, 12, 9, 0.15)';
        });
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.borderColor = '';
            this.style.background = '';
        });
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.borderColor = '';
            this.style.background = '';
            const droppedFiles = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
            if (droppedFiles.length === 0) {
                alert('Please drop image files only.');
                return;
            }
            if (selectedFiles.length + droppedFiles.length > 3) {
                alert('Maximum of 3 photos only. You already have ' + selectedFiles.length + ' selected.');
                return;
            }
            for (let f of droppedFiles) {
                if (f.size > 5 * 1024 * 1024) {
                    alert('"' + f.name + '" is too large. Max 5MB per photo.');
                    return;
                }
            }
            droppedFiles.forEach(f => selectedFiles.push(f));
            renderPreviews();
        });

        photoInput.addEventListener('change', function(e) {
            const newFiles = Array.from(e.target.files);
            const totalCount = selectedFiles.length + newFiles.length;

            if (totalCount > 3) {
                alert('Maximum of 3 photos only. You already have ' + selectedFiles.length + ' selected.');
                photoInput.value = '';
                return;
            }

            // Check file size (5MB each)
            for (let f of newFiles) {
                if (f.size > 5 * 1024 * 1024) {
                    alert('"' + f.name + '" is too large. Max 5MB per photo.');
                    photoInput.value = '';
                    return;
                }
            }

            // Add new files
            newFiles.forEach(f => selectedFiles.push(f));
            renderPreviews();
            // Reset input so same file can be re-selected
            photoInput.value = '';
        });

        function renderPreviews() {
            previewContainer.innerHTML = '';
            selectedFiles.forEach(function(file, index) {
                const reader = new FileReader();
                reader.onload = function(ev) {
                    const col = document.createElement('div');
                    col.className = 'col-4';
                    col.innerHTML = `
                        <div class="position-relative" style="padding-top: 75%;">
                            <img src="${ev.target.result}" 
                                 style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;border-radius:8px;border:2px solid #B50C09;">
                            <button type="button" class="btn btn-danger btn-sm position-absolute" 
                                    style="top:-8px;right:-8px;width:24px;height:24px;border-radius:50%;padding:0;font-size:12px;line-height:1;"
                                    onclick="removePhoto(${index})">&times;</button>
                        </div>
                        <small class="text-muted d-block text-center mt-1" style="font-size:11px;">${file.name.substring(0,15)}${file.name.length>15?'...':''}</small>
                    `;
                    previewContainer.appendChild(col);
                };
                reader.readAsDataURL(file);
            });
        }

        window.removePhoto = function(index) {
            selectedFiles.splice(index, 1);
            renderPreviews();
            rebuildFileInput();
        };

        function rebuildFileInput() {
            // Create a DataTransfer to update the input's files
            const dt = new DataTransfer();
            selectedFiles.forEach(f => dt.items.add(f));
            photoInput.files = dt.files;
        }

        // Override form submit to ensure files are set
        document.querySelector('form').addEventListener('submit', function() {
            rebuildFileInput();
        });
        
        // Budget validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const budgetMin = document.querySelector('input[name="budget_min"]').value;
            const budgetMax = document.querySelector('input[name="budget_max"]').value;
            
            if (budgetMin && budgetMax && parseInt(budgetMin) > parseInt(budgetMax)) {
                e.preventDefault();
                alert('Minimum budget cannot be higher than maximum budget');
                return false;
            }
            
            if (!document.querySelector('input[name="consent_contact"]').checked) {
                e.preventDefault();
                alert('Please agree to be contacted regarding your quote');
                return false;
            }
        });
        
        // Dark Mode Toggle Functionality
        const darkModeToggle = document.getElementById('darkModeToggle');
        const body = document.body;
        
        // Check for saved theme preference or system preference
        const savedTheme = localStorage.getItem('theme');
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        // Set initial theme
        if (savedTheme === 'dark') {
            body.classList.add('dark-mode');
            body.classList.remove('light-mode');
            updateToggleUI(true);
        } else if (savedTheme === 'light') {
            body.classList.remove('dark-mode');
            body.classList.add('light-mode');
            updateToggleUI(false);
        } else if (systemPrefersDark) {
            body.classList.add('dark-mode');
            body.classList.remove('light-mode');
            updateToggleUI(true);
        } else {
            body.classList.remove('dark-mode');
            body.classList.remove('light-mode');
            updateToggleUI(false);
        }
        
        // Toggle dark mode
        darkModeToggle.addEventListener('click', function() {
            const isDarkMode = !body.classList.contains('dark-mode');
            
            if (isDarkMode) {
                body.classList.add('dark-mode');
                body.classList.remove('light-mode');
                localStorage.setItem('theme', 'dark');
            } else {
                body.classList.remove('dark-mode');
                body.classList.add('light-mode');
                localStorage.setItem('theme', 'light');
            }
            
            updateToggleUI(isDarkMode);
        });
        
        // Update toggle UI
        function updateToggleUI(isDarkMode) {
            const icon = darkModeToggle.querySelector('.toggle-icon');
            const text = darkModeToggle.querySelector('.toggle-text');
            
            if (isDarkMode) {
                icon.textContent = '☀️';
                text.textContent = 'Light Mode';
                darkModeToggle.title = 'Switch to light mode';
            } else {
                icon.textContent = '🌙';
                text.textContent = 'Dark Mode';
                darkModeToggle.title = 'Switch to dark mode';
            }
        }
        
        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
            if (!localStorage.getItem('theme')) {
                if (e.matches) {
                    body.classList.add('dark-mode');
                    updateToggleUI(true);
                } else {
                    body.classList.remove('dark-mode');
                    updateToggleUI(false);
                }
            }
        });
        
        // Vehicle Autocomplete Functionality — always uses DB from /api/vehicle-*
        $(document).ready(function() {
            var API_BASE = window.location.origin;

            // Initialize autocomplete for Vehicle Brand
            $('input[name="vehicle_make"]').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: API_BASE + '/api/vehicle-brands',
                        data: { term: request.term },
                        success: function(data) { response(data); },
                        error: function() { response([]); }
                    });
                },
                minLength: 0,
                delay: 150,
                select: function(event, ui) {
                    // Refresh model suggestions when brand changes
                    var modelField = $('input[name="vehicle_model"]');
                    if (modelField.val()) modelField.val('');
                }
            }).focus(function() {
                $(this).autocomplete('search', $(this).val());
            });

            // Initialize autocomplete for Vehicle Model (filters by brand from DB)
            $('input[name="vehicle_model"]').autocomplete({
                source: function(request, response) {
                    var selectedBrand = $('input[name="vehicle_make"]').val();
                    $.ajax({
                        url: API_BASE + '/api/vehicle-models',
                        data: { term: request.term, brand: selectedBrand },
                        success: function(data) { response(data); },
                        error: function() { response([]); }
                    });
                },
                minLength: 0,
                delay: 150
            }).focus(function() {
                $(this).autocomplete('search', $(this).val());
            });

            // Initialize autocomplete for Vehicle Color
            $('input[name="vehicle_color"]').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: API_BASE + '/api/vehicle-colors',
                        data: { term: request.term },
                        success: function(data) { response(data); },
                        error: function() { response([]); }
                    });
                },
                minLength: 0,
                delay: 150
            }).focus(function() {
                $(this).autocomplete('search', $(this).val());
            });
        });
    </script>
</body>
</html>
