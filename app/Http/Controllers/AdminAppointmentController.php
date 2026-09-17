<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Vehicle;
use Illuminate\Http\Request;

/**
 * Admin-facing appointment routes under /admin/* (blueprint requirement).
 *
 * The blueprint targets a modern, simple admin appointment flow:
 *   - GET  /admin/appointments            (status-filterable list)
 *   - GET  /admin/appointments/cancelled  (cancelled appointments)
 *   - POST /admin/appointments            (admin creates with customer+vehicle, booking_source=admin)
 *   - DELETE /admin/appointments/{id}
 *
 * This is deliberately small and separate from the legacy public-booking
 * AppointmentController.store (which uses the client-side form schema).
 */
class AdminAppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with(['customer', 'vehicle'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time');

        if ($request->filled('status')) {
            $query->where('appointment_status', $request->get('status'));
        }

        $appointments = $query->get();

        return view('appointments.admin_index', compact('appointments'));
    }

    public function cancelled()
    {
        $appointments = Appointment::with(['customer', 'vehicle'])
            ->where('appointment_status', 'cancelled')
            ->orderByDesc('cancelled_at')
            ->get();

        return view('appointments.admin_index', compact('appointments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'       => 'required|exists:customers,id',
            'vehicle_id'        => 'nullable|exists:vehicles,id',
            'appointment_date'  => 'required|date|after_or_equal:today',
            'appointment_time'  => 'required|date_format:H:i',
        ]);

        $customer = Customer::findOrFail($validated['customer_id']);
        $vehicle  = $validated['vehicle_id'] ? Vehicle::find($validated['vehicle_id']) : null;

        $appointment = Appointment::create([
            'customer_id'         => $customer->id,
            'vehicle_id'          => $validated['vehicle_id'] ?? null,
            'vehicle_description' => $vehicle
                ? trim(($vehicle->year ?? '') . ' ' . $vehicle->make . ' ' . $vehicle->model)
                : null,
            'appointment_date'    => $validated['appointment_date'],
            'appointment_time'    => $validated['appointment_time'],
            'appointment_type'    => 'regular_service',
            'appointment_number'  => Appointment::generateAppointmentNumber(),
            'appointment_status'  => 'scheduled',
            'scheduled_at'        => now(),
            'booking_source'      => 'admin',
        ]);

        // Admin-created appointments follow the same client flow: issue a
        // BookingToken so the appointment can be managed via /booking/{token}
        // (proceed/cancel/reschedule) exactly like a client-proceeded booking.
        \App\Models\BookingToken::generateForAppointment($appointment->id);

        return redirect()->route('admin.appointments.index')
            ->with('success', 'Appointment created for ' . $customer->first_name . ' ' . $customer->last_name . '.');
    }

    public function destroy(Appointment $appointment)
    {
        // Blueprint admin delete removes the appointment entirely (no soft-delete residue).
        $appointment->forceDelete();

        return redirect()->route('admin.appointments.index');
    }
}
