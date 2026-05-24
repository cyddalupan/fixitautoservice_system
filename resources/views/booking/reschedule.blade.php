<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Appointment – Fix-It Auto Services</title>
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
        .container { max-width: 700px; margin: 30px auto; padding: 0 20px; }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #fef3c7, #fde68a);
            padding: 18px 24px;
            border-bottom: 1px solid #fcd34d;
            font-weight: 700;
            font-size: 16px;
            color: #92400e;
        }
        .card-header i { color: #d97706; margin-right: 8px; }
        .card-body { padding: 24px; }
        .current-details {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .current-details dt { font-size: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
        .current-details dd { font-weight: 600; color: #0f172a; margin-bottom: 8px; }
        .form-label { font-weight: 600; font-size: 13px; color: #475569; margin-bottom: 4px; }
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 14px;
            border: 2px solid #e2e8f0;
            font-size: 14px;
        }
        .form-control:focus, .form-select:focus { border-color: #cc0000; box-shadow: 0 0 0 3px rgba(204,0,0,0.15); }
        .time-slot-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
            gap: 8px;
            margin-top: 12px;
        }
        .time-slot {
            padding: 8px;
            text-align: center;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            color: #64748b;
        }
        .time-slot:hover:not(.unavailable) { border-color: #cc0000; background: #fce4e4; color: #cc0000; }
        .time-slot.selected { border-color: #cc0000; background: #cc0000; color: #fff; }
        .time-slot.unavailable { opacity: 0.35; cursor: not-allowed; background: #f8fafc; text-decoration: line-through; }
        .btn-primary {
            background: #cc0000;
            border: none;
            border-radius: 12px;
            padding: 14px 32px;
            font-weight: 600;
            font-size: 15px;
        }
        .btn-primary:hover { background: #990000; }
        .btn-secondary {
            background: #e2e8f0; border: none; border-radius: 10px; padding: 8px 16px; font-weight: 600; font-size: 13px; color: #475569;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('booking.dashboard') }}">
                FIX-IT <small>AUTO SERVICES</small>
            </a>
            <a href="{{ route('booking.dashboard') }}" class="nav-link">
                <i class="fas fa-arrow-left me-1"></i>Dashboard
            </a>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <i class="fas fa-sync-alt"></i> Reschedule Appointment
            </div>
            <div class="card-body">
                <div class="current-details">
                    <div class="row">
                        <div class="col-4">
                            <dt>Current Date</dt>
                            <dd>{{ $appointment->appointment_date->format('M d, Y') }}</dd>
                        </div>
                        <div class="col-4">
                            <dt>Current Time</dt>
                            <dd>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</dd>
                        </div>
                        <div class="col-4">
                            <dt>Service</dt>
                            <dd>{{ ucwords(str_replace('_', ' ', $appointment->appointment_type)) }}</dd>
                        </div>
                    </div>
                </div>

                <form id="rescheduleForm">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">

                    <div class="mb-3">
                        <label class="form-label">New Date</label>
                        <input type="text" id="datePicker" class="form-control" placeholder="Pick a new date..." readonly>
                        <input type="hidden" name="appointment_date" id="appointmentDate">
                    </div>

                    <div id="timeSlotsSection" style="display:none;">
                        <label class="form-label">Available Time Slots</label>
                        <div id="timeSlotGrid" class="time-slot-grid"></div>
                        <div id="noSlotsMessage" class="text-center text-muted py-3" style="display:none;">
                            <i class="fas fa-clock"></i> No available slots for this date.
                        </div>
                        <input type="hidden" name="appointment_time" id="appointmentTime">
                    </div>

                    <div class="mt-4 d-flex gap-2 justify-content-center">
                        <a href="{{ route('booking.dashboard') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" id="submitBtn" class="btn btn-primary">
                            <i class="fas fa-check me-1"></i>Confirm Reschedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const WORKING_HOURS = @json($workingHours);
        const SLOT_DURATION = {{ $slotDuration }};
        const MAX_DAYS_AHEAD = {{ $maxDaysAhead }};
        const DAY_MAP = {
            'monday': 'Monday', 'tuesday': 'Tuesday', 'wednesday': 'Wednesday',
            'thursday': 'Thursday', 'friday': 'Friday', 'saturday': 'Saturday', 'sunday': 'Sunday'
        };

        const today = new Date();
        const maxDate = new Date();
        maxDate.setDate(maxDate.getDate() + MAX_DAYS_AHEAD);

        const enabledDays = Object.entries(WORKING_HOURS)
            .filter(([k, v]) => v.enabled)
            .map(([k]) => ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'].indexOf(k));

        const datePicker = flatpickr('#datePicker', {
            minDate: today,
            maxDate: maxDate,
            dateFormat: 'Y-m-d',
            enable: d => enabledDays.includes(d.getDay()),
            onChange: function(_, dateStr) {
                document.getElementById('appointmentDate').value = dateStr;
                document.getElementById('appointmentTime').value = '';
                loadTimeSlots(dateStr);
            }
        });

        function loadTimeSlots(dateStr) {
            const section = document.getElementById('timeSlotsSection');
            const grid = document.getElementById('timeSlotGrid');
            const noSlots = document.getElementById('noSlotsMessage');

            section.style.display = 'block';
            grid.innerHTML = '<div class="text-center py-3"><div class="spinner-border text-primary" role="status"></div></div>';
            noSlots.style.display = 'none';

            const dayName = DAY_MAP[new Date(dateStr).toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase()];
            const dayConfig = WORKING_HOURS[dayName];
            if (!dayConfig || !dayConfig.enabled) {
                grid.innerHTML = '';
                noSlots.style.display = 'block';
                noSlots.querySelector('p').textContent = 'Shop is closed on this day.';
                return;
            }

            fetch(`/booking/api/slots?date=${dateStr}`, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    grid.innerHTML = '';
                    const slots = data.slots || [];
                    const available = slots.filter(s => s.available);
                    if (available.length === 0) {
                        noSlots.style.display = 'block';
                        noSlots.querySelector('p').textContent = 'All slots booked. Choose another date.';
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
                .catch(() => {
                    grid.innerHTML = '';
                    noSlots.style.display = 'block';
                    noSlots.querySelector('p').textContent = 'Error loading slots.';
                });
        }

        document.getElementById('rescheduleForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const date = document.getElementById('appointmentDate').value;
            const time = document.getElementById('appointmentTime').value;

            if (!date || !time) {
                Swal.fire({ icon: 'warning', title: 'Incomplete', text: 'Please select a new date and time.' });
                return;
            }

            document.getElementById('submitBtn').disabled = true;
            document.getElementById('submitBtn').innerHTML = '<span class="spinner-border spinner-border-sm"></span> Updating...';

            fetch('{{ route("booking.reschedule.update", $appointment->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    appointment_date: date,
                    appointment_time: time,
                })
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('submitBtn').disabled = false;
                document.getElementById('submitBtn').innerHTML = '<i class="fas fa-check me-1"></i>Confirm Reschedule';

                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Rescheduled!',
                        text: data.message,
                        timer: 2000
                    }).then(() => window.location.href = '{{ route("booking.dashboard") }}');
                } else {
                    Swal.fire({ icon: 'error', title: 'Failed', text: data.message });
                }
            })
            .catch(() => {
                document.getElementById('submitBtn').disabled = false;
                document.getElementById('submitBtn').innerHTML = '<i class="fas fa-check me-1"></i>Confirm Reschedule';
                Swal.fire({ icon: 'error', title: 'Error', text: 'Network error.' });
            });
        });
    </script>
</body>
</html>
