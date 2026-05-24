<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment – Fix-It Auto Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="https://fixitautoservices.com/favicon.svg">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }
        .navbar {
            background: linear-gradient(135deg, #cc0000, #8b0000);
            padding: 12px 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .navbar-brand { color: #fff !important; font-weight: 800; font-size: 20px; letter-spacing: -0.5px; }
        .navbar-brand small { font-weight: 400; font-size: 10px; opacity: 0.7; display: block; letter-spacing: 2px; }
        .nav-link { color: rgba(255,255,255,0.85) !important; font-size: 14px; }
        .container { max-width: 800px; margin: 30px auto; padding: 0 20px; }
        .step-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .step-header {
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            padding: 18px 24px;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 700;
            font-size: 16px;
            color: #0f172a;
        }
        .step-header .step-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            background: #cc0000;
            color: #fff;
            border-radius: 50%;
            font-size: 13px;
            font-weight: 700;
            margin-right: 10px;
        }
        .step-body { padding: 24px; }
        .form-label { font-weight: 600; font-size: 13px; color: #475569; margin-bottom: 4px; }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 14px;
            border: 2px solid #e2e8f0;
            font-size: 14px;
            transition: all 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #cc0000;
            box-shadow: 0 0 0 3px rgba(204,0,0,0.15);
        }
        .time-slot-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            gap: 8px;
            margin-top: 12px;
        }
        .time-slot {
            padding: 10px 8px;
            text-align: center;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            color: #64748b;
            background: #fff;
        }
        .time-slot:hover:not(.unavailable) {
            border-color: #cc0000;
            background: #fce4e4;
            color: #cc0000;
        }
        .time-slot.selected {
            border-color: #cc0000;
            background: #cc0000;
            color: #fff;
        }
        .time-slot.unavailable {
            opacity: 0.35;
            cursor: not-allowed;
            background: #f8fafc;
            text-decoration: line-through;
        }
        .time-slot.loading {
            opacity: 0.5;
            pointer-events: none;
        }
        .btn-primary {
            background: #cc0000;
            border: none;
            border-radius: 12px;
            padding: 14px 32px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.2s;
        }
        .btn-primary:hover { background: #990000; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(204,0,0,0.3); }
        .btn-primary:disabled { opacity: 0.6; }
        .btn-secondary {
            background: #e2e8f0;
            border: none;
            border-radius: 10px;
            padding: 8px 16px;
            font-weight: 600;
            font-size: 13px;
            color: #475569;
        }
        .btn-secondary:hover { background: #cbd5e1; }
        .alert { border-radius: 12px; font-size: 14px; }
        .loader {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #e2e8f0;
            border-top: 3px solid #cc0000;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,0.5);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }
        .spinner-overlay.active { display: flex; }
        .spinner-box {
            background: #fff;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 25px 60px rgba(0,0,0,0.2);
        }
        .spinner-box .loader { width: 48px; height: 48px; border-width: 4px; }
        .spinner-box p { margin-top: 16px; font-weight: 600; color: #475569; }
        .vehicle-card {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 14px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .vehicle-card:hover { border-color: #cc0000; }
        .vehicle-card.selected { border-color: #cc0000; background: #fce4e4; }
        .vehicle-card input[type="radio"] { display: none; }
        .vehicle-card label { cursor: pointer; width: 100%; margin: 0; }
        .empty-state { text-align: center; padding: 40px 20px; color: #94a3b8; }
        .empty-state i { font-size: 36px; margin-bottom: 8px; }
        @media (max-width: 600px) {
            .container { margin: 20px auto; padding: 0 12px; }
            .step-body { padding: 16px; }
            .time-slot-grid { grid-template-columns: repeat(auto-fill, minmax(75px, 1fr)); }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('booking.dashboard') }}">
                FIX-IT <small>AUTO SERVICES</small>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('booking.dashboard') }}" class="nav-link">
                    <i class="fas fa-arrow-left me-1"></i>Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container">
        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        <form id="bookingForm">
            @csrf

            <!-- STEP 1: Service -->
            <div class="step-card">
                <div class="step-header">
                    <span class="step-num">1</span> Select Service
                </div>
                <div class="step-body">
                    <select name="service_type" id="serviceType" class="form-select" required>
                        <option value="">Choose a service...</option>
                        @foreach($services as $service)
                            <option value="{{ $service->key }}">{{ $service->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- STEP 2: Vehicle -->
            <div class="step-card">
                <div class="step-header">
                    <span class="step-num">2</span> Vehicle
                </div>
                <div class="step-body">
                    @if($vehicles->count() > 0)
                        <p class="text-muted" style="font-size:13px;margin-bottom:12px;">Select a saved vehicle or add a new one</p>
                        <div class="row g-2 mb-3" id="vehicleCards">
                            @foreach($vehicles as $v)
                                <div class="col-md-6">
                                    <div class="vehicle-card" data-vehicle-id="{{ $v->id }}">
                                        <label>
                                            <input type="radio" name="vehicle_id" value="{{ $v->id }}">
                                            <strong>{{ $v->year }} {{ $v->make }} {{ $v->model }}</strong>
                                            @if($v->license_plate)
                                                <br><small class="text-muted"><i class="fas fa-id-card me-1"></i>{{ $v->license_plate }}</small>
                                            @endif
                                            @if($v->color)
                                                <br><small class="text-muted"><i class="fas fa-palette me-1"></i>{{ $v->color }}</small>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="divider" style="text-align:center;margin:16px 0;color:#94a3b8;font-size:12px;">
                            — or add a new vehicle —
                        </div>
                    @endif

                    <div class="form-check mb-3">
                        <input type="checkbox" id="newVehicleToggle" name="new_vehicle" value="1" class="form-check-input">
                        <label class="form-check-label fw-semibold" for="newVehicleToggle" style="font-size:14px;">
                            <i class="fas fa-plus-circle me-1 text-primary"></i> Add a new vehicle
                        </label>
                    </div>

                    <div id="newVehicleFields" style="display:none;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Make / Brand</label>
                                <input type="text" name="vehicle_make" class="form-control" placeholder="e.g. Toyota">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Model</label>
                                <input type="text" name="vehicle_model" class="form-control" placeholder="e.g. Vios">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Year</label>
                                <input type="number" name="vehicle_year" class="form-control" placeholder="2024" min="1900" max="2030">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Color</label>
                                <input type="text" name="vehicle_color" class="form-control" placeholder="e.g. White">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Plate No.</label>
                                <input type="text" name="vehicle_plate" class="form-control" placeholder="ABC-123">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- STEP 3: Date & Time -->
            <div class="step-card">
                <div class="step-header">
                    <span class="step-num">3</span> Choose Date & Time
                </div>
                <div class="step-body">
                    <div class="mb-3">
                        <label class="form-label">Select a Date</label>
                        <input type="text" id="datePicker" class="form-control" placeholder="Pick a date..." readonly>
                        <input type="hidden" name="appointment_date" id="appointmentDate">
                    </div>
                    <div id="timeSlotsSection" style="display:none;">
                        <label class="form-label">Available Time Slots</label>
                        <div id="timeSlotLoader" style="text-align:center;padding:20px;">
                            <div class="loader"></div>
                            <p class="text-muted mt-2" style="font-size:13px;">Loading available slots...</p>
                        </div>
                        <div id="timeSlotGrid" class="time-slot-grid"></div>
                        <div id="noSlotsMessage" class="empty-state" style="display:none;">
                            <i class="fas fa-clock"></i>
                            <p>No available slots for this date.</p>
                        </div>
                        <input type="hidden" name="appointment_time" id="appointmentTime">
                    </div>
                </div>
            </div>

            <!-- STEP 4: Notes -->
            <div class="step-card">
                <div class="step-header">
                    <span class="step-num">4</span> Notes (Optional)
                </div>
                <div class="step-body">
                    <textarea name="customer_notes" class="form-control" rows="3"
                        placeholder="Tell us about any specific concerns or requests..."></textarea>
                </div>
            </div>

            <!-- Submit -->
            <div style="text-align:center;margin-bottom:30px;">
                <button type="submit" id="submitBtn" class="btn btn-primary btn-lg">
                    <i class="fas fa-calendar-check me-2"></i>Confirm Booking
                </button>
            </div>
        </form>
    </div>

    <!-- Spinner overlay -->
    <div class="spinner-overlay" id="spinnerOverlay">
        <div class="spinner-box">
            <div class="loader"></div>
            <p>Processing your booking...</p>
        </div>
    </div>

    <div style="text-align:center;padding:10px 20px 30px;color:#94a3b8;font-size:12px;">
        &copy; {{ date('Y') }} Fix-It Auto Services
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // ─── Config ───────────────────────────────────────
        const WORKING_HOURS = @json($workingHours);
        const SLOT_DURATION = {{ $slotDuration }};
        const MAX_DAYS_AHEAD = {{ $maxDaysAhead }};
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        // Translate day keys to PHP day names
        const DAY_MAP = {
            'monday': 'Monday', 'tuesday': 'Tuesday', 'wednesday': 'Wednesday',
            'thursday': 'Thursday', 'friday': 'Friday', 'saturday': 'Saturday', 'sunday': 'Sunday'
        };

        // ─── Vehicle Card Selection ──────────────────────
        document.querySelectorAll('.vehicle-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.vehicle-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                const radio = this.querySelector('input[type="radio"]');
                if (radio) radio.checked = true;
                // Untick new vehicle
                document.getElementById('newVehicleToggle').checked = false;
                document.getElementById('newVehicleFields').style.display = 'none';
            });
        });

        document.getElementById('newVehicleToggle').addEventListener('change', function() {
            document.getElementById('newVehicleFields').style.display = this.checked ? 'block' : 'none';
            if (this.checked) {
                document.querySelectorAll('.vehicle-card').forEach(c => c.classList.remove('selected'));
                document.querySelectorAll('input[name="vehicle_id"]').forEach(r => r.checked = false);
            }
        });

        // ─── Date Picker ─────────────────────────────────
        const today = new Date();
        const maxDate = new Date();
        maxDate.setDate(maxDate.getDate() + MAX_DAYS_AHEAD);

        // Build enabled days array (only enabled working days)
        const enabledDays = Object.entries(WORKING_HOURS)
            .filter(([key, val]) => val.enabled)
            .map(([key]) => {
                const idx = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
                return idx.indexOf(key);
            });

        const datePicker = flatpickr('#datePicker', {
            minDate: today,
            maxDate: maxDate,
            dateFormat: 'Y-m-d',
            enable: [
                function(date) {
                    const day = date.getDay(); // 0=Sun, 1=Mon...
                    return enabledDays.includes(day);
                }
            ],
            locale: {
                firstDayOfWeek: 1
            },
            onChange: function(selectedDates, dateStr) {
                document.getElementById('appointmentDate').value = dateStr;
                document.getElementById('appointmentTime').value = '';
                loadTimeSlots(dateStr);
            },
            placeholder: 'Pick an available date...'
        });

        // ─── Time Slots ──────────────────────────────────
        function loadTimeSlots(dateStr) {
            const section = document.getElementById('timeSlotsSection');
            const loader = document.getElementById('timeSlotLoader');
            const grid = document.getElementById('timeSlotGrid');
            const noSlots = document.getElementById('noSlotsMessage');

            section.style.display = 'block';
            loader.style.display = 'block';
            grid.innerHTML = '';
            noSlots.style.display = 'none';

            const dayName = DAY_MAP[new Date(dateStr).toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase()];
            const dayConfig = WORKING_HOURS[dayName];

            if (!dayConfig || !dayConfig.enabled) {
                loader.style.display = 'none';
                noSlots.style.display = 'block';
                noSlots.querySelector('p').textContent = 'The shop is closed on this day.';
                return;
            }

            fetch(`/booking/api/slots?date=${dateStr}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN }
            })
            .then(r => r.json())
            .then(data => {
                loader.style.display = 'none';
                const slots = data.slots || [];

                if (slots.length === 0) {
                    noSlots.style.display = 'block';
                    return;
                }

                const available = slots.filter(s => s.available);
                if (available.length === 0) {
                    noSlots.style.display = 'block';
                    noSlots.querySelector('p').textContent = 'All time slots are booked for this date. Please choose another.';
                    return;
                }

                slots.forEach(slot => {
                    const div = document.createElement('div');
                    div.className = 'time-slot' + (slot.available ? '' : ' unavailable');
                    div.textContent = slot.display;
                    div.dataset.time = slot.time;

                    if (slot.available) {
                        div.addEventListener('click', function() {
                            document.querySelectorAll('.time-slot').forEach(el => el.classList.remove('selected'));
                            this.classList.add('selected');
                            document.getElementById('appointmentTime').value = this.dataset.time;
                        });
                    }

                    grid.appendChild(div);
                });
            })
            .catch(err => {
                loader.style.display = 'none';
                noSlots.style.display = 'block';
                noSlots.querySelector('p').textContent = 'Error loading time slots. Please try again.';
                console.error('Slot load error:', err);
            });
        }

        // ─── Form Submission ─────────────────────────────
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const service = document.getElementById('serviceType').value;
            const date = document.getElementById('appointmentDate').value;
            const time = document.getElementById('appointmentTime').value;

            if (!service) {
                Swal.fire({ icon: 'warning', title: 'Select Service', text: 'Please select a service type.' });
                return;
            }
            if (!date) {
                Swal.fire({ icon: 'warning', title: 'Select Date', text: 'Please choose a date.' });
                return;
            }
            if (!time) {
                Swal.fire({ icon: 'warning', title: 'Select Time', text: 'Please select a time slot.' });
                return;
            }

            // Check vehicle selection
            const selectedVehicle = document.querySelector('input[name="vehicle_id"]:checked');
            const addNewVehicle = document.getElementById('newVehicleToggle').checked;

            if (!selectedVehicle && !addNewVehicle) {
                Swal.fire({ icon: 'warning', title: 'Select Vehicle', text: 'Please select or add a vehicle.' });
                return;
            }

            if (addNewVehicle) {
                const make = document.querySelector('input[name="vehicle_make"]').value.trim();
                const model = document.querySelector('input[name="vehicle_model"]').value.trim();
                const year = document.querySelector('input[name="vehicle_year"]').value.trim();
                if (!make || !model || !year) {
                    Swal.fire({ icon: 'warning', title: 'Vehicle Details', text: 'Please fill in make, model, and year for the new vehicle.' });
                    return;
                }
            }

            // Show spinner
            document.getElementById('spinnerOverlay').classList.add('active');
            document.getElementById('submitBtn').disabled = true;

            const formData = new FormData(this);

            fetch('/booking/api/create', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('spinnerOverlay').classList.remove('active');
                document.getElementById('submitBtn').disabled = false;

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Booking Confirmed! 🎉',
                        html: `
                            <p>Appointment #${data.appointment.number}</p>
                            <p><strong>${data.appointment.date}</strong> at <strong>${data.appointment.time}</strong></p>
                        `,
                        confirmButtonColor: '#cc0000',
                        confirmButtonText: 'View My Appointments'
                    }).then(() => {
                        window.location.href = '{{ route('booking.dashboard') }}';
                    });
                } else {
                    const msg = data.message || 'Booking failed. Please try again.';
                    const errors = data.errors;
                    let errorHtml = msg;
                    if (errors) {
                        errorHtml += '<br><ul style="text-align:left;margin-top:8px;">';
                        Object.values(errors).flat().forEach(e => { errorHtml += `<li>${e}</li>`; });
                        errorHtml += '</ul>';
                    }
                    Swal.fire({ icon: 'error', title: 'Booking Failed', html: errorHtml });
                }
            })
            .catch(err => {
                document.getElementById('spinnerOverlay').classList.remove('active');
                document.getElementById('submitBtn').disabled = false;
                Swal.fire({ icon: 'error', title: 'Error', text: 'Network error. Please try again.' });
                console.error('Booking error:', err);
            });
        });
    </script>
</body>
</html>
