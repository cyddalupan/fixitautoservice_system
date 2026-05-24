<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f4f4f8; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: #fff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 8px 0 0; opacity: 0.9; font-size: 14px; }
        .body { padding: 30px; }
        .details { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .details table { width: 100%; border-collapse: collapse; }
        .details td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; font-size: 14px; }
        .details td:first-child { font-weight: 600; color: #64748b; width: 120px; }
        .details tr:last-child td { border-bottom: none; }
        .badge { display: inline-block; background: #dbeafe; color: #2563eb; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; }
        .footer { background: #f8fafc; padding: 20px 30px; text-align: center; font-size: 13px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
        .footer a { color: #2563eb; text-decoration: none; }
        .btn { display: inline-block; background: #2563eb; color: #fff; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>✅ Appointment Confirmed</h1>
            <p>Your booking at Fix-It Auto Services</p>
        </div>
        <div class="body">
            <p>Hi <strong>{{ $customer->first_name }}</strong>,</p>
            <p>Your appointment has been confirmed. Here are the details:</p>

            <div class="details">
                <table>
                    <tr>
                        <td>Appointment #</td>
                        <td><span class="badge">{{ $appointment->appointment_number }}</span></td>
                    </tr>
                    <tr>
                        <td>Date</td>
                        <td>{{ $appointment->appointment_date->format('l, F d, Y') }}</td>
                    </tr>
                    <tr>
                        <td>Time</td>
                        <td>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</td>
                    </tr>
                    <tr>
                        <td>Service</td>
                        <td>{{ ucwords(str_replace('_', ' ', $appointment->appointment_type)) }}</td>
                    </tr>
                    @if($appointment->vehicle)
                    <tr>
                        <td>Vehicle</td>
                        <td>{{ $appointment->vehicle->year }} {{ $appointment->vehicle->make }} {{ $appointment->vehicle->model }}
                            @if($appointment->vehicle->license_plate)
                            <br><small>Plate: {{ $appointment->vehicle->license_plate }}</small>
                            @endif
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td>Status</td>
                        <td><span style="color:#2563eb;font-weight:600;">Booked by Customer</span></td>
                    </tr>
                </table>
            </div>

            @if($appointment->customer_notes)
            <p><strong>Your notes:</strong><br>{{ $appointment->customer_notes }}</p>
            @endif

            <p style="text-align:center;margin-top:25px;">
                <a href="{{ route('booking.dashboard') }}" class="btn">View My Appointments</a>
            </p>

            <p style="font-size:13px;color:#94a3b8;text-align:center;">
                If you need to reschedule or cancel, please visit your dashboard or contact us.
            </p>
        </div>
        <div class="footer">
            <p><strong>Fix-It Auto Services</strong><br>
            Need help? Call us or reply to this email.</p>
            <p style="margin-top:8px;">&copy; {{ date('Y') }} Fix-It Auto Services. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
