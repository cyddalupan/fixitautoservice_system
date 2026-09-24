<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Repair Quotation {{ $inspection->appointment->appointment_number ?? '#'.$inspection->id }}</title>
</head>
<body style="margin:0;">
    @include('partials.repair-quotation')
</body>
</html>
