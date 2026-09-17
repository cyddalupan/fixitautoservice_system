<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background: #f4f4f8; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); color: #fff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 8px 0 0; opacity: 0.9; font-size: 14px; }
        .body { padding: 30px; }
        .details { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .details table { width: 100%; border-collapse: collapse; }
        .details td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; font-size: 14px; vertical-align: top; }
        .details td:first-child { font-weight: 600; color: #64748b; width: 130px; }
        .details tr:last-child td { border-bottom: none; }
        .footer { background: #f8fafc; padding: 20px 30px; text-align: center; font-size: 13px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
        .footer a { color: #2563eb; text-decoration: none; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📩 New Contact Message</h1>
            <p>Someone submitted the Contact Us form on the Fix-It Auto Services website</p>
        </div>
        <div class="body">
            <p>Hi Admin,<br>A new contact message has been received. Here are the details:</p>

            <div class="details">
                <table>
                    <tr>
                        <td>Name</td>
                        <td>{{ $contact->name }}</td>
                    </tr>
                    <tr>
                        <td>Email</td>
                        <td>{{ $contact->email }}</td>
                    </tr>
                    @if($contact->phone)
                    <tr>
                        <td>Phone</td>
                        <td>{{ $contact->phone }}</td>
                    </tr>
                    @endif
                    @if($contact->subject)
                    <tr>
                        <td>Subject</td>
                        <td>{{ $contact->subject }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Message</td>
                        <td>{!! nl2br(e($contact->message)) !!}</td>
                    </tr>
                    <tr>
                        <td>Received</td>
                        <td>{{ $contact->created_at ? $contact->created_at->format('l, F d, Y g:i A') : now()->format('l, F d, Y g:i A') }}</td>
                    </tr>
                </table>
            </div>

            <p style="font-size:13px;color:#94a3b8;text-align:center;">
                Please review and respond to this lead in the admin Inbox.
            </p>
        </div>
        <div class="footer">
            <p><strong>Fix-It Auto Services</strong><br>
            System-generated notification for new contact submissions.</p>
            <p style="margin-top:8px;">&copy; {{ date('Y') }} Fix-It Auto Services. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
