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
        .details td:first-child { font-weight: 600; color: #64748b; width: 130px; }
        .details tr:last-child td { border-bottom: none; }
        .badge { display: inline-block; background: #dbeafe; color: #2563eb; padding: 4px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; }
        .footer { background: #f8fafc; padding: 20px 30px; text-align: center; font-size: 13px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
        .footer a { color: #2563eb; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔔 New Appointment</h1>
            <p>A customer just booked a service at Fix-It Auto Services</p>
        </div>
        <div class="body">
            <p>Hi Admin,<br>A new appointment has been booked. Here are the customer and service details:</p>

            <div class="details">
                <table>
                    <tr>
                        <td>Appointment #</td>
                        <td><span class="badge">{{ $appointment->appointment_number }}</span></td>
                    </tr>
                    @if($customer && $customer->first_name)
                    <tr>
                        <td>Customer</td>
                        <td>{{ $customer->first_name }} {{ $customer->last_name ?? '' }}</td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>{{ $customer->email }}</td>
                    </tr>
                    <tr>
                        <td>Phone</td>
                        <td>{{ $customer->phone ?? '—' }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Date</td>
                        <td>{{ $appointment->appointment_date ? $appointment->appointment_date->format('l, F d, Y') : '—' }}</td>
                    </tr>
                    <tr>
                        <td>Time</td>
                        <td>{{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') : '—' }}</td>
                    </tr>
                    <tr>
                        <td>Service</td>
                        <td>{{ ucwords(str_replace('_', ' ', $appointment->appointment_type)) }}</td>
                    </tr>
                    @if($appointment->service_request)
                    <tr>
                        <td>Notes</td>
                        <td>{{ $appointment->service_request }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Status</td>
                        <td><span style="color:#2563eb;font-weight:600;">scheduled</span></td>
                    </tr>
                </table>
            </div>

            <p style="font-size:13px;color:#94a3b8;text-align:center;">
                Please review and confirm this appointment in the admin dashboard.
            </p>
        </div>
        <div class="footer">
            <p><strong>Fix-It Auto Services</strong><br>
            System-generated notification for new appointment bookings.</p>
            <p style="margin-top:8px;">&copy; {{ date('Y') }} Fix-It Auto Services. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
