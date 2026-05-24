<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed – Fix-It Auto Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="https://fixitautoservices.com/favicon.svg">
    <style>
        body {
            font-family: 'Segoe UI', -apple-system, sans-serif;
            background: linear-gradient(135deg, #8b0000, #cc0000);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            width: 100%;
            max-width: 480px;
            border: none;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.2);
            overflow: hidden;
            text-align: center;
        }
        .card-check {
            background: linear-gradient(135deg, #8b0000, #cc0000);
            padding: 40px 30px;
            color: #fff;
        }
        .card-check .icon { font-size: 64px; }
        .card-check h1 { font-size: 24px; font-weight: 700; margin-top: 12px; }
        .card-check p { opacity: 0.85; font-size: 14px; margin-top: 4px; }
        .card-body { padding: 30px; }
        .details { background: #f8f8f8; border: 1px solid #e0e0e0; border-radius: 12px; padding: 16px; margin: 16px 0; text-align: left; }
        .details table { width: 100%; border-collapse: collapse; }
        .details td { padding: 8px 0; border-bottom: 1px solid #e0e0e0; font-size: 14px; }
        .details td:first-child { font-weight: 600; color: #64748b; width: 100px; }
        .details tr:last-child td { border-bottom: none; }
        .btn-primary {
            background: #cc0000;
            border: none;
            border-radius: 12px;
            padding: 14px 28px;
            font-weight: 600;
            width: 100%;
        }
        .btn-primary:hover { background: #990000; }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-check">
            <div class="icon">✅</div>
            <h1>Booking Confirmed!</h1>
            <p>Your appointment has been scheduled.</p>
        </div>
        <div class="card-body">
            @if(isset($appointment))
            <div class="details">
                <table>
                    <tr>
                        <td>Appt #</td>
                        <td><strong>{{ $appointment->appointment_number }}</strong></td>
                    </tr>
                    <tr>
                        <td>Date</td>
                        <td><strong>{{ $appointment->appointment_date->format('l, F d, Y') }}</strong></td>
                    </tr>
                    <tr>
                        <td>Time</td>
                        <td><strong>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</strong></td>
                    </tr>
                    <tr>
                        <td>Service</td>
                        <td>{{ ucwords(str_replace('_', ' ', $appointment->appointment_type)) }}</td>
                    </tr>
                    @if($appointment->vehicle)
                    <tr>
                        <td>Vehicle</td>
                        <td>{{ $appointment->vehicle->year }} {{ $appointment->vehicle->make }} {{ $appointment->vehicle->model }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <p class="text-muted" style="font-size:13px;">
                <i class="fas fa-envelope me-1"></i>
                A confirmation email has been sent to <strong>{{ $appointment->customer->email ?? 'your email' }}</strong>.
            </p>
            @endif

            <a href="{{ route('booking.dashboard') }}" class="btn btn-primary">
                <i class="fas fa-arrow-right me-2"></i>Go to My Appointments
            </a>
        </div>
    </div>
</body>
</html>
