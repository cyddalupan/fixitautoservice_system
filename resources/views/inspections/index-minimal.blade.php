<!DOCTYPE html>
<html>
<head>
    <title>Vehicle Inspections - Minimal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h1>Vehicle Inspections - Minimal Test</h1>
        
        @if($inspections->count() > 0)
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Vehicle</th>
                        <th>Type</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inspections as $inspection)
                    <tr>
                        <td>{{ $inspection->id }}</td>
                        <td>
                            @if($inspection->customer)
                                {{ $inspection->customer->first_name }}
                            @else
                                No customer
                            @endif
                        </td>
                        <td>
                            @if($inspection->vehicle)
                                {{ $inspection->vehicle->make }}
                            @else
                                No vehicle
                            @endif
                        </td>
                        <td>{{ $inspection->inspection_type }}</td>
                        <td>{{ $inspection->inspection_status }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No inspections found.</p>
        @endif
    </div>
</body>
</html>