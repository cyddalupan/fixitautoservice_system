<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard – Fix-It Auto Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="https://fixitautoservices.com/favicon.svg">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        .nav-link { color: rgba(255,255,255,0.85) !important; font-weight: 500; font-size: 14px; }
        .nav-link:hover { color: #fff !important; }
        .container { max-width: 960px; margin: 30px auto; padding: 0 20px; }
        .welcome-card {
            background: linear-gradient(135deg, #cc0000, #8b0000);
            border-radius: 20px;
            padding: 30px;
            color: #fff;
            margin-bottom: 24px;
            box-shadow: 0 4px 15px rgba(204,0,0,0.2);
        }
        .welcome-card h1 { font-size: 24px; font-weight: 700; }
        .welcome-card p { opacity: 0.85; font-size: 14px; margin: 4px 0 0; }
        .stat-card {
            background: #fff;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            text-align: center;
            transition: all 0.2s;
        }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
        .stat-card i { font-size: 28px; color: #cc0000; }
        .stat-card .num { font-size: 28px; font-weight: 800; color: #0f172a; margin-top: 8px; }
        .stat-card .label { font-size: 13px; color: #64748b; }
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            overflow: hidden;
        }
        .card-header {
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 20px;
            font-weight: 700;
            font-size: 16px;
        }
        .appointment-item {
            border-left: 4px solid #cc0000;
            padding: 16px 20px;
            margin-bottom: 8px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.03);
            transition: all 0.2s;
        }
        .appointment-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
        .appointment-item .date { font-weight: 700; font-size: 16px; color: #0f172a; }
        .appointment-item .time { color: #64748b; font-size: 14px; }
        .appointment-item .service { font-size: 13px; color: #475569; margin-top: 4px; }
        .badge-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge.customer_booked { background: #fce4e4; color: #cc0000; }
        .badge.cancelled { background: #fee2e2; color: #dc2626; }
        .badge.rescheduled { background: #fef3c7; color: #d97706; }
        .badge.scheduled, .badge.confirmed { background: #d1fae5; color: #cc0000; }
        .badge.completed { background: #ccfbf1; color: #0d9488; }
        .btn-sm { border-radius: 10px; font-size: 12px; padding: 6px 14px; font-weight: 600; }
        .empty-state { text-align: center; padding: 40px 20px; color: #94a3b8; }
        .empty-state i { font-size: 48px; margin-bottom: 12px; }
        .empty-state p { font-size: 14px; }
        .btn-primary {
            background: #cc0000;
            border: none;
            border-radius: 12px;
            padding: 10px 24px;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-primary:hover { background: #990000; transform: translateY(-1px); }
        .btn-outline-danger { border-radius: 10px; font-size: 12px; padding: 6px 14px; font-weight: 600; }
        .pagination { justify-content: center; }
        @media (max-width: 600px) {
            .welcome-card { padding: 20px; }
            .welcome-card h1 { font-size: 20px; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                FIX-IT <small>AUTO SERVICES</small>
            </a>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('booking.create') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-plus me-1"></i>New Booking
                </a>
                <form method="POST" action="{{ route('booking.logout') }}" class="d-inline">
                    @csrf
                    <button class="nav-link btn btn-link" type="submit">
                        <i class="fas fa-sign-out-alt"></i> Sign Out
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success d-flex align-items-center gap-2" style="border-radius:12px;">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger d-flex align-items-center gap-2" style="border-radius:12px;">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        <div class="welcome-card">
            <h1><i class="fas fa-hand-wave me-2"></i>Welcome, {{ $customer->first_name }}!</h1>
            <p>{{ $customer->email }}</p>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-4">
                <div class="stat-card">
                    <i class="fas fa-calendar-check"></i>
                    <div class="num">{{ $appointments->total() }}</div>
                    <div class="label">Total Appointments</div>
                </div>
            </div>
            <div class="col-4">
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <div class="num">{{ $appointments->whereIn('appointment_status', ['customer_booked', 'scheduled', 'confirmed'])->count() }}</div>
                    <div class="label">Upcoming</div>
                </div>
            </div>
            <div class="col-4">
                <div class="stat-card">
                    <i class="fas fa-car"></i>
                    <div class="num">{{ $customer->total_vehicles }}</div>
                    <div class="label">Vehicles</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="fas fa-list me-2"></i>My Appointments
                <a href="{{ route('booking.create') }}" class="btn btn-primary btn-sm float-end">
                    <i class="fas fa-plus me-1"></i>Book New
                </a>
            </div>
            <div style="padding:12px;">
                @forelse($appointments as $apt)
                    <div class="appointment-item d-flex justify-content-between align-items-start">
                        <div>
                            <div class="date">{{ $apt->appointment_date->format('M d, Y') }}</div>
                            <div class="time">
                                <i class="far fa-clock me-1"></i>
                                {{ \Carbon\Carbon::parse($apt->appointment_time)->format('g:i A') }}
                                @if($apt->vehicle)
                                    &middot; {{ $apt->vehicle->make }} {{ $apt->vehicle->model }} ({{ $apt->vehicle->year }})
                                @endif
                            </div>
                            <div class="service">
                                <i class="fas fa-tools me-1"></i>
                                {{ ucwords(str_replace('_', ' ', $apt->appointment_type)) }}
                                @if($apt->appointment_number)
                                    &middot; #{{ $apt->appointment_number }}
                                @endif
                            </div>
                            @if($apt->customer_notes)
                                <div class="text-muted mt-1" style="font-size:12px;">
                                    <i class="fas fa-comment me-1"></i>{{ \Illuminate\Support\Str::limit($apt->customer_notes, 60) }}
                                </div>
                            @endif
                        </div>
                        <div class="text-end" style="min-width:120px;">
                            <span class="badge badge-status {{ $apt->appointment_status }}">
                                {{ ucwords(str_replace('_', ' ', $apt->appointment_status)) }}
                            </span>
                            @if(in_array($apt->appointment_status, ['customer_booked', 'scheduled', 'confirmed']))
                                <div class="mt-2 d-flex gap-1 justify-content-end">
                                    <a href="{{ route('booking.reschedule', $apt->id) }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-sync-alt me-1"></i>Reschedule
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger cancel-btn" data-id="{{ $apt->id }}" data-number="{{ $apt->appointment_number }}">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="empty-state">
                        <i class="fas fa-calendar-times"></i>
                        <p>No appointments yet. Book your first service today!</p>
                        <a href="{{ route('booking.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Book Now
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        @if($appointments->hasPages())
            <div class="mt-3">
                {{ $appointments->links() }}
            </div>
        @endif
    </div>

    <div style="text-align:center;padding:20px;color:#94a3b8;font-size:12px;">
        &copy; {{ date('Y') }} Fix-It Auto Services &middot;
        <a href="https://fixitautoservices.com" style="color:#94a3b8;">Website</a>
    </div>

    <script>
        document.querySelectorAll('.cancel-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const num = this.dataset.number;
                Swal.fire({
                    title: 'Cancel Appointment?',
                    text: `Are you sure you want to cancel appointment #${num}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, cancel it',
                    cancelButtonText: 'Keep it'
                }).then(result => {
                    if (result.isConfirmed) {
                        fetch(`/booking/appointments/${id}/cancel`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Cancelled!',
                                    text: data.message,
                                    timer: 2000
                                }).then(() => location.reload());
                            } else {
                                Swal.fire({ icon: 'error', title: 'Error', text: data.message });
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
