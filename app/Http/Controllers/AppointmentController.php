<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Vehicle;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Get appointments by status for tabs
        $scheduledAppointments = Appointment::with(['customer', 'technician'])
            ->whereIn('appointment_status', ['scheduled', 'confirmed'])
            ->whereDate('appointment_date', '>=', Carbon::today())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();
        
        $arrivedAppointments = Appointment::with(['customer', 'technician'])
            ->where('appointment_status', 'checked_in')
            ->whereDate('appointment_date', '>=', Carbon::today()->subDays(7))
            ->orderBy('checked_in_at', 'desc')
            ->get();
        
        $cancelledAppointments = Appointment::with(['customer'])
            ->where('appointment_status', 'cancelled')
            ->whereDate('appointment_date', '>=', Carbon::today()->subDays(30))
            ->orderBy('cancelled_at', 'desc')
            ->get();
        
        $convertedAppointments = Appointment::with(['customer', 'estimate', 'workOrder'])
            ->whereHas('estimate')
            ->orWhereHas('workOrder')
            ->whereDate('appointment_date', '>=', Carbon::today()->subDays(30))
            ->orderBy('appointment_date', 'desc')
            ->get();
        
        // Get statistics for tabs
        $stats = [
            'scheduled' => $scheduledAppointments->count(),
            'arrived' => $arrivedAppointments->count(),
            'cancelled' => $cancelledAppointments->count(),
            'converted' => $convertedAppointments->count(),
        ];
        
        return view('appointments.index', compact(
            'scheduledAppointments',
            'arrivedAppointments',
            'cancelledAppointments',
            'convertedAppointments',
            'stats'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        $vehicles = Vehicle::with('customer')->get();
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $advisors = User::where('role', 'service_advisor')->where('is_active', true)->get();
        
        // Pre-select vehicle if provided
        $selectedVehicle = $request->filled('vehicle_id') 
            ? Vehicle::find($request->vehicle_id)
            : null;
        
        // Pre-select customer if provided OR get from selected vehicle
        if ($request->filled('customer_id')) {
            $selectedCustomer = Customer::find($request->customer_id);
        } elseif ($selectedVehicle && $selectedVehicle->customer) {
            // If vehicle is provided but customer isn't, get customer from vehicle
            $selectedCustomer = $selectedVehicle->customer;
        } else {
            $selectedCustomer = null;
        }
        
        return view('appointments.create', compact('customers', 'vehicles', 'technicians', 'advisors', 'selectedCustomer', 'selectedVehicle'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_description' => 'required|string|max:255',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'service_type' => 'required|in:oil_change,tire_rotation,brake_service,engine_diagnostic,transmission,ac_service,general_maintenance,emergency',
            'description' => 'nullable|string|max:1000',
            'estimated_cost' => 'nullable|numeric|min:0',
            'priority' => 'required|in:low,normal,high,urgent',
            'assigned_to' => 'nullable|exists:users,id',
        ]);
        
        // Generate appointment number
        $validated['appointment_number'] = Appointment::generateAppointmentNumber();
        
        // Set default status
        $validated['appointment_status'] = 'scheduled';
        
        // Set scheduled timestamp
        $validated['scheduled_at'] = now();
        
        // Set booking source
        $validated['booking_source'] = 'admin_panel';
        
        // Map service_type form values to database appointment_type values
        $serviceTypeMapping = [
            'oil_change' => 'oil_change',
            'tire_rotation' => 'tire_service',
            'brake_service' => 'brake_service',
            'engine_diagnostic' => 'diagnostic',
            'transmission' => 'repair', // Map to repair since transmission isn't in ENUM
            'ac_service' => 'repair', // Map to repair since ac_service isn't in ENUM
            'general_maintenance' => 'maintenance',
            'emergency' => 'emergency',
        ];
        
        $appointmentType = $serviceTypeMapping[$validated['service_type']] ?? 'regular_service';
        
        // Map form fields to database fields
        $appointmentData = [
            'customer_id' => $validated['customer_id'],
            'vehicle_description' => $validated['vehicle_description'],
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'],
            'appointment_type' => $appointmentType,
            'service_request' => $validated['description'] ?? null,
            'estimated_cost' => $validated['estimated_cost'] ?? null,
            'priority' => $validated['priority'],
            'assigned_technician_id' => $validated['assigned_to'] ?? null,
            'appointment_number' => $validated['appointment_number'],
            'appointment_status' => $validated['appointment_status'],
            'scheduled_at' => $validated['scheduled_at'],
            'booking_source' => $validated['booking_source'],
        ];
        
        // Create appointment
        $appointment = Appointment::create($appointmentData);
        
        // Record vehicle description in history
        if (!empty($validated['vehicle_description'])) {
            \App\Models\VehicleHistory::findOrCreate($validated['vehicle_description'])->incrementUse();
        }
        
        // Update service progress
        \App\Services\ServiceProgressService::updateFromAppointment($appointment);
        
        // Check for conflicts
        $this->checkForConflicts($appointment);
        
        return redirect()->route('appointments.show', $appointment)
            ->with('success', 'Appointment created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        $appointment->load(['customer', 'technician', 'advisor', 'workOrder', 'serviceProgress']);
        
        // Get similar appointments for this customer
        $customerAppointments = Appointment::where('customer_id', $appointment->customer_id)
            ->where('id', '!=', $appointment->id)
            ->orderBy('appointment_date', 'desc')
            ->limit(5)
            ->get();
        
        // Get available time slots for rescheduling
        $availableSlots = $this->getAvailableTimeSlots($appointment->appointment_date);
        
        return view('appointments.show', compact('appointment', 'customerAppointments', 'availableSlots'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Appointment $appointment)
    {
        $appointment->load(['customer']);
        
        $customers = Customer::where('is_active', true)->orderBy('first_name')->get();
        $vehicles = Vehicle::with('customer')->get();
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $advisors = User::where('role', 'service_advisor')->where('is_active', true)->get();
        
        return view('appointments.edit', compact('appointment', 'customers', 'vehicles', 'technicians', 'advisors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i',
            'appointment_type' => 'required|in:regular_service,emergency,inspection,diagnostic,repair,maintenance,tire_service,oil_change,brake_service,other',
            'appointment_status' => 'required|in:scheduled,confirmed,checked_in,in_progress,completed,cancelled,no_show,rescheduled',
            'service_request' => 'nullable|string|max:1000',
            'estimated_duration' => 'nullable|numeric',
            'estimated_cost' => 'nullable|numeric|min:0',
            'priority' => 'required|in:low,normal,high,emergency',
            'assigned_technician_id' => 'nullable|exists:users,id',
            'service_advisor_id' => 'nullable|exists:users,id',
            'bay_number' => 'nullable|integer|min:1|max:20',
            'bay_status' => 'nullable|in:available,occupied,maintenance',
            'customer_notes' => 'nullable|string|max:500',
            'requires_deposit' => 'boolean',
            'deposit_amount' => 'nullable|numeric|min:0',
            'deposit_status' => 'nullable|in:pending,paid,refunded,forfeited',
            'is_waitlist' => 'boolean',
            'waitlist_position' => 'nullable|integer|min:1',
            'service_types' => 'nullable|array',
            'service_types.*' => 'string|max:100',
            'preferred_communication' => 'nullable|array',
            'preferred_communication.*' => 'in:sms,email,call',
        ]);
        
        // Update status timestamps
        if ($validated['appointment_status'] !== $appointment->appointment_status) {
            $statusField = $validated['appointment_status'] . '_at';
            if (in_array($validated['appointment_status'], ['confirmed', 'checked_in', 'started', 'completed', 'cancelled'])) {
                $validated[$statusField] = now();
            }
        }
        
        // Convert arrays to JSON
        if (isset($validated['service_types'])) {
            $validated['service_types'] = json_encode($validated['service_types']);
        }
        
        if (isset($validated['preferred_communication'])) {
            $validated['preferred_communication'] = json_encode($validated['preferred_communication']);
        }
        
        $appointment->update($validated);
        
        return redirect()->route('appointments.show', $appointment)
            ->with('success', 'Appointment updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        
        return redirect()->route('appointments.index')
            ->with('success', 'Appointment deleted successfully.');
    }
    
    /**
     * Calendar view for appointments.
     */
    public function calendar(Request $request)
    {
        $date = $request->filled("date") ? \Carbon\Carbon::parse($request->date) : \Carbon\Carbon::today();
        
        // Get appointments for the month
        $appointments = \App\Models\Appointment::with(["customer", "technician"])
            ->whereYear("appointment_date", $date->year)
            ->whereMonth("appointment_date", $date->month)
            ->orderBy("appointment_date")
            ->orderBy("appointment_time")
            ->get();
        
        // Group by date for calendar
        $calendarData = [];
        foreach ($appointments as $appointment) {
            $dateKey = $appointment->appointment_date->format("Y-m-d");
            if (!isset($calendarData[$dateKey])) {
                $calendarData[$dateKey] = [];
            }
            $calendarData[$dateKey][] = $appointment;
        }
        
        // Get technicians for filter
        $technicians = \App\Models\User::where("role", "technician")->where("is_active", true)->get();
        
        return view("appointments.calendar", compact("date", "calendarData", "technicians"));
    }
    public function checkIn(Appointment $appointment)
    {
        if ($appointment->appointment_status !== 'confirmed') {
            return redirect()->back()->with('error', 'Only confirmed appointments can be checked in.');
        }
        
        // Update appointment status
        $appointment->update([
            'appointment_status' => 'checked_in',
            'checked_in_at' => now(),
        ]);
        
        // Get customer name safely
        $customerName = 'Customer';
        if ($appointment->customer) {
            $customerName = $appointment->customer->full_name;
        } elseif ($appointment->customer_id) {
            $customer = \App\Models\Customer::find($appointment->customer_id);
            if ($customer) {
                $customerName = $customer->full_name;
            }
        }
        
        // Create vehicle inspection for the appointment
        // Note: vehicle_id may be null since appointments now use vehicle_description
        $inspection = \App\Models\VehicleInspection::create([
            'appointment_id' => $appointment->id,
            'customer_id' => $appointment->customer_id,
            'vehicle_id' => $appointment->vehicle_id, // May be null
            'service_advisor_id' => $appointment->service_advisor_id,
            'inspection_type' => 'pre_service',
            'inspection_status' => 'draft',
            'inspection_name' => 'Pre-Service Inspection for ' . $customerName,
            'customer_concerns' => $appointment->service_request,
            'inspection_started_at' => now(),
            'created_by' => auth()->id(),
        ]);
        
        // If appointment has a service_id, link it to the inspection
        if ($appointment->service_id) {
            $inspection->update(['service_id' => $appointment->service_id]);
        }
        
        // Redirect to the newly created vehicle inspection
        return redirect()->route('inspections.show', $inspection->id)
            ->with('success', 'Appointment checked in successfully. Vehicle inspection created.');
    }
    
    /**
     * Start an appointment.
     */
    public function start(Appointment $appointment)
    {
        if (!in_array($appointment->appointment_status, ['checked_in', 'confirmed'])) {
            return redirect()->back()->with('error', 'Appointment must be checked in or confirmed to start.');
        }
        
        $appointment->update([
            'appointment_status' => 'in_progress',
            'started_at' => now(),
            'bay_status' => 'occupied',
        ]);
        
        return redirect()->back()->with('success', 'Appointment started successfully.');
    }
    
    /**
     * Complete an appointment.
     */
    public function complete(Appointment $appointment)
    {
        if ($appointment->appointment_status !== 'in_progress') {
            return redirect()->back()->with('error', 'Only appointments in progress can be completed.');
        }
        
        $appointment->update([
            'appointment_status' => 'completed',
            'completed_at' => now(),
            'bay_status' => 'available',
        ]);
        
        return redirect()->back()->with('success', 'Appointment completed successfully.');
    }
    
    /**
     * Cancel an appointment.
     */
    public function cancel(Appointment $appointment, Request $request)
    {
        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ]);
        
        $appointment->update([
            'appointment_status' => 'cancelled',
            'cancelled_at' => now(),
            'customer_notes' => $appointment->customer_notes . "\n\nCancellation Reason: " . $validated['cancellation_reason'],
            'bay_status' => 'available',
        ]);
        
        return redirect()->back()->with('success', 'Appointment cancelled successfully.');
    }
    
    /**
     * Mark as no-show.
     */
    public function markNoShow(Appointment $appointment)
    {
        $appointment->update([
            'appointment_status' => 'no_show',
            'last_no_show_at' => now(),
            'no_show_count' => $appointment->no_show_count + 1,
            'bay_status' => 'available',
        ]);
        
        return redirect()->back()->with('success', 'Appointment marked as no-show.');
    }
    
    /**
     * Reschedule an appointment.
     */
    public function reschedule(Appointment $appointment, Request $request)
    {
        $validated = $request->validate([
            'new_date' => 'required|date|after_or_equal:today',
            'new_time' => 'required|date_format:H:i',
            'reschedule_reason' => 'nullable|string|max:500',
        ]);
        
        $oldDateTime = $appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time;
        
        $appointment->update([
            'appointment_date' => $validated['new_date'],
            'appointment_time' => $validated['new_time'],
            'appointment_status' => 'rescheduled',
            'customer_notes' => $appointment->customer_notes . "\n\nRescheduled from: " . $oldDateTime . 
                               "\nReschedule Reason: " . ($validated['reschedule_reason'] ?? 'Not specified'),
        ]);
        
        return redirect()->back()->with('success', 'Appointment rescheduled successfully.');
    }
    
    /**
     * Convert waitlist to regular appointment.
     */
    public function convertFromWaitlist(Appointment $appointment)
    {
        if (!$appointment->is_waitlist) {
            return redirect()->back()->with('error', 'This is not a waitlist appointment.');
        }
        
        $appointment->update([
            'is_waitlist' => false,
            'waitlist_converted_at' => now(),
            'appointment_status' => 'scheduled',
        ]);
        
        return redirect()->back()->with('success', 'Waitlist appointment converted to regular appointment.');
    }
    
    /**
     * Send reminder for appointment.
     */
    public function sendReminder(Appointment $appointment, Request $request)
    {
        $validated = $request->validate([
            'reminder_type' => 'required|in:sms,email,both',
        ]);
        
        // In production, this would integrate with SMS/email services
        $updates = [];
        
        if (in_array($validated['reminder_type'], ['sms', 'both'])) {
            $updates['sms_reminder_sent'] = true;
        }
        
        if (in_array($validated['reminder_type'], ['email', 'both'])) {
            $updates['email_reminder_sent'] = true;
        }
        
        $updates['reminder_sent_at'] = now();
        
        $appointment->update($updates);
        
        return redirect()->back()->with('success', 'Reminder sent successfully.');
    }
    
    /**
     * Send confirmation for appointment.
     */
    public function sendConfirmation(Appointment $appointment)
    {
        $appointment->update([
            'confirmation_sent' => true,
            'confirmation_sent_at' => now(),
        ]);
        
        return redirect()->back()->with('success', 'Confirmation sent successfully.');
    }
    
    /**
     * Get available time slots for a date.
     */
    private function getAvailableTimeSlots($date, $duration = 1)
    {
        // Define business hours
        $businessHours = [
            'start' => '08:00',
            'end' => '18:00',
            'lunch_start' => '12:00',
            'lunch_end' => '13:00',
        ];
        
        // Get existing appointments for the date
        $existingAppointments = Appointment::whereDate('appointment_date', $date)
            ->whereNotIn('appointment_status', ['cancelled', 'no_show'])
            ->orderBy('appointment_time')
            ->get(['appointment_time', 'estimated_duration']);
        
        // Generate time slots
        $slots = [];
        $currentTime = strtotime($businessHours['start']);
        $endTime = strtotime($businessHours['end']);
        $lunchStart = strtotime($businessHours['lunch_start']);
        $lunchEnd = strtotime($businessHours['lunch_end']);
        
        while ($currentTime + ($duration * 3600) <= $endTime) {
            // Skip lunch break
            if ($currentTime >= $lunchStart && $currentTime < $lunchEnd) {
                $currentTime = $lunchEnd;
                continue;
            }
            
            $slotStart = date('H:i', $currentTime);
            $slotEnd = date('H:i', $currentTime + ($duration * 3600));
            
            // Check if slot conflicts with existing appointments
            $hasConflict = false;
            foreach ($existingAppointments as $appointment) {
                $appointmentStart = strtotime($appointment->appointment_time);
                $appointmentEnd = strtotime($appointment->appointment_time) + ($appointment->estimated_duration * 3600);
                
                if ($currentTime < $appointmentEnd && ($currentTime + ($duration * 3600)) > $appointmentStart) {
                    $hasConflict = true;
                    break;
                }
            }
            
            if (!$hasConflict) {
                $slots[] = [
                    'start' => $slotStart,
                    'end' => $slotEnd,
                    'formatted' => date('g:i A', $currentTime) . ' - ' . date('g:i A', $currentTime + ($duration * 3600)),
                ];
            }
            
            // Move to next slot (30-minute intervals)
            $currentTime += 1800; // 30 minutes
        }
        
        return $slots;
    }
    
    /**
     * Check for scheduling conflicts.
     */
    private function checkForConflicts(Appointment $appointment)
    {
        $conflicts = Appointment::whereDate('appointment_date', $appointment->appointment_date)
            ->where('id', '!=', $appointment->id)
            ->whereNotIn('appointment_status', ['cancelled', 'no_show'])
            ->where(function($query) use ($appointment) {
                // Check for technician conflict
                if ($appointment->assigned_technician_id) {
                    $query->orWhere('assigned_technician_id', $appointment->assigned_technician_id);
                }
                
                // Check for bay conflict
                if ($appointment->bay_number) {
                    $query->orWhere('bay_number', $appointment->bay_number);
                }
            })
            ->get();
        
        foreach ($conflicts as $conflict) {
            $conflictStart = strtotime($conflict->appointment_time);
            $conflictEnd = $conflictStart + ($conflict->estimated_duration * 3600);
            $appointmentStart = strtotime($appointment->appointment_time);
            $appointmentEnd = $appointmentStart + ($appointment->estimated_duration * 3600);
            
            if ($appointmentStart < $conflictEnd && $appointmentEnd > $conflictStart) {
                // Conflict found - in production, would send notification
                // For now, just log it
                \Log::warning('Appointment conflict detected', [
                    'appointment_id' => $appointment->id,
                    'conflict_id' => $conflict->id,
                    'technician_conflict' => $appointment->assigned_technician_id == $conflict->assigned_technician_id,
                    'bay_conflict' => $appointment->bay_number == $conflict->bay_number,
                ]);
            }
        }
    }
    
    /**
     * AJAX: Check in appointment and create vehicle inspection.
     */
    public function ajaxCheckIn(Request $request, $id)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            
            if (!in_array($appointment->appointment_status, ['scheduled', 'confirmed'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only scheduled or confirmed appointments can be checked in.'
                ], 400);
            }
            
            // Update appointment status
            $appointment->update([
                'appointment_status' => 'checked_in',
                'checked_in_at' => now(),
            ]);
            
            // Get customer name safely
            $customerName = 'Customer';
            if ($appointment->customer) {
                $customerName = $appointment->customer->full_name;
            } elseif ($appointment->customer_id) {
                $customer = \App\Models\Customer::find($appointment->customer_id);
                if ($customer) {
                    $customerName = $customer->full_name;
                }
            }
            
            // Create vehicle inspection for the appointment
            // Note: vehicle_id may be null since appointments now use vehicle_description
            // Get or create a vehicle for the customer
            $vehicleId = $appointment->vehicle_id;
            if (!$vehicleId) {
                // Try to find any vehicle for this customer
                $customerVehicle = \App\Models\Vehicle::where('customer_id', $appointment->customer_id)->first();
                if ($customerVehicle) {
                    $vehicleId = $customerVehicle->id;
                } else {
                    // Create a default vehicle for the customer
                    $defaultVehicle = \App\Models\Vehicle::create([
                        'customer_id' => $appointment->customer_id,
                        'make' => 'Unknown',
                        'model' => 'Vehicle',
                        'year' => date('Y'),
                        'license_plate' => 'TEMP-' . $appointment->id,
                    ]);
                    $vehicleId = $defaultVehicle->id;
                }
            }
            
            $inspection = \App\Models\VehicleInspection::create([
                'appointment_id' => $appointment->id,
                'customer_id' => $appointment->customer_id,
                'vehicle_id' => $vehicleId, // Now guaranteed to have a value
                'service_advisor_id' => $appointment->service_advisor_id,
                'inspection_type' => 'pre_service',
                'inspection_status' => 'draft',
                'inspection_name' => 'Pre-Service Inspection for ' . $customerName,
                'customer_concerns' => $appointment->service_request,
                'inspection_started_at' => now(),
                'created_by' => auth()->id(),
                // Required fields with NOT NULL constraint
                'total_items_checked' => 0,
                'items_passed' => 0,
                'items_failed' => 0,
                'items_attention_needed' => 0,
                'items_not_applicable' => 0,
                'has_safety_concerns' => 0,
                'has_urgent_issues' => 0,
                'has_critical_issues' => 0,
                'requires_customer_approval' => 1,
                'customer_approved' => 0,
                'has_upsell_opportunities' => 0,
            ]);
            
            // If appointment has a service_id, link it to the inspection
            if ($appointment->service_id) {
                $inspection->update(['service_id' => $appointment->service_id]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Appointment checked in successfully. Vehicle inspection created.',
                'appointment_id' => $appointment->id,
                'appointment_number' => $appointment->appointment_number,
                'new_status' => 'checked_in',
                'new_status_label' => 'Arrived',
                'new_status_color' => 'warning',
                'checked_in_at' => $appointment->checked_in_at->format('M d, Y g:i A'),
                'inspection_id' => $inspection->id,
                'inspection_created' => true,
                'inspection_url' => route('inspections.show', $inspection->id),
                'inspection_edit_url' => route('inspections.edit', $inspection->id),
                'redirect_message' => 'Redirecting to vehicle inspection...',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check in appointment: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * AJAX: Cancel appointment.
     */
    public function ajaxCancel(Request $request, $id)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            
            $validated = $request->validate([
                'cancellation_reason' => 'nullable|string|max:500',
            ]);
            
            $updateData = [
                'appointment_status' => 'cancelled',
                'cancelled_at' => now(),
                'bay_status' => 'available',
            ];
            
            // Only add cancellation_reason if provided
            if (!empty($validated['cancellation_reason'])) {
                $updateData['cancellation_reason'] = $validated['cancellation_reason'];
                $updateData['customer_notes'] = $appointment->customer_notes . "\n\nCancellation Reason: " . $validated['cancellation_reason'];
            }
            
            $appointment->update($updateData);
            
            return response()->json([
                'success' => true,
                'message' => 'Appointment cancelled successfully.',
                'appointment_id' => $appointment->id,
                'appointment_number' => $appointment->appointment_number,
                'new_status' => 'cancelled',
                'new_status_label' => 'Cancelled',
                'new_status_color' => 'danger',
                'cancelled_at' => $appointment->cancelled_at->format('M d, Y g:i A'),
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel appointment: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * AJAX: Restore cancelled appointment.
     */
    public function ajaxRestore(Request $request, $id)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            
            // Check if appointment is actually cancelled
            if ($appointment->appointment_status !== 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Appointment is not cancelled. Current status: ' . $appointment->appointment_status
                ], 400);
            }
            
            // Restore to scheduled status
            $appointment->update([
                'appointment_status' => 'scheduled',
                'cancelled_at' => null,
                'cancellation_reason' => null,
                'bay_status' => 'available',
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Appointment restored successfully.',
                'appointment_id' => $appointment->id,
                'appointment_number' => $appointment->appointment_number,
                'new_status' => 'scheduled',
                'new_status_label' => 'Scheduled',
                'new_status_color' => 'primary',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore appointment: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * AJAX: Mark as no show.
     */
    public function ajaxMarkNoShow(Request $request, $id)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            
            $appointment->update([
                'appointment_status' => 'no_show',
                'last_no_show_at' => now(),
                'no_show_count' => $appointment->no_show_count + 1,
                'bay_status' => 'available',
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Appointment marked as no-show.',
                'appointment_id' => $appointment->id,
                'appointment_number' => $appointment->appointment_number,
                'new_status' => 'no_show',
                'new_status_label' => 'No Show',
                'new_status_color' => 'dark',
                'no_show_count' => $appointment->no_show_count,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark as no-show: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get appointment statistics.
     */
    /**
     * Get calendar data in JSON format for FullCalendar.
     */
    public function calendarData(Request $request)
    {
        $start = $request->filled("start") ? Carbon::parse($request->start) : Carbon::now()->startOfMonth();
        $end = $request->filled("end") ? Carbon::parse($request->end) : Carbon::now()->endOfMonth();
        
        // Get appointments within date range WITH related models
        // ONLY show appointments that appear in Scheduled or Cancelled tabs (per user request)
        // Scheduled tab shows: scheduled OR confirmed status
        // Cancelled tab shows: cancelled status
        $appointments = Appointment::with(["customer", "technician", "vehicle", "estimate", "workOrder", "invoice", "payments"])
            ->whereBetween("appointment_date", [$start, $end])
            ->whereIn("appointment_status", ["scheduled", "confirmed", "cancelled"])
            ->orderBy("appointment_date")
            ->orderBy("appointment_time")
            ->get();
        
        // Format events for FullCalendar
        $events = [];
        foreach ($appointments as $appointment) {
            // Determine the workflow status for color coding
            $workflowStatus = $this->getWorkflowStatus($appointment);
            
            // DEBUG: Log workflow status
            \Log::debug("Appointment #{$appointment->id} workflow status: {$workflowStatus}", [
                'has_estimate' => $appointment->estimate ? 'YES' : 'NO',
                'has_work_order' => $appointment->workOrder ? 'YES' : 'NO',
                'has_invoice' => $appointment->invoice ? 'YES' : 'NO',
                'payment_count' => $appointment->payments ? $appointment->payments->count() : 0,
            ]);
            
            $event = [
                "id" => $appointment->id,
                "title" => $this->getEventTitle($appointment),
                "start" => $appointment->appointment_date->format("Y-m-d") . "T" . $appointment->appointment_time,
                "end" => $appointment->appointment_date->format("Y-m-d") . "T" . $this->calculateEndTime($appointment->appointment_time),
                "className" => $workflowStatus,
                "extendedProps" => [
                    "number" => $appointment->appointment_number,
                    "customer_name" => $appointment->customer ? $appointment->customer->full_name : "Walk-in Customer",
                    "customer_phone" => $appointment->customer ? $appointment->customer->phone : null,
                    "status" => $appointment->appointment_status,
                    "status_display" => ucfirst(str_replace("_", " ", $appointment->appointment_status)),
                    "workflow_status" => $workflowStatus,
                    "workflow_status_display" => $this->getWorkflowStatusDisplay($workflowStatus),
                    "vehicle_info" => $this->getVehicleInfo($appointment),
                    "service_type" => $appointment->service_type,
                    "notes" => $appointment->notes,
                    "technician_name" => $appointment->technician ? $appointment->technician->name : "Not Assigned",
                    "tooltip" => $this->getEventTooltip($appointment)
                ]
            ];
            
            // Store original workflow status for tooltip
            $originalWorkflowStatus = $workflowStatus;
            
            // Add warning class if appointment is within 3 days
            // WARNING OVERRIDES workflow colors for urgency
            if ($this->isWithinThreeDays($appointment)) {
                $event["className"] = "fc-event-warning";
                // Keep original workflow status in extended props for tooltip
                $event["extendedProps"]["workflow_status"] = $originalWorkflowStatus;
                $event["extendedProps"]["workflow_status_display"] = $this->getWorkflowStatusDisplay($originalWorkflowStatus) . " (Within 3 days)";
            }
            
            $events[] = $event;
        }
        
        return response()->json(["events" => $events]);
    }
    
    /**
     * Determine workflow status for color coding.
     */
    private function getWorkflowStatus($appointment)
    {
        // Check if has work order (repair order)
        if ($appointment->workOrder) {
            // Check if has invoice AND payments (GREEN)
            if ($appointment->invoice && $appointment->payments && $appointment->payments->count() > 0) {
                return "fc-event-job-order-paid"; // GREEN (repair order with invoices & payments)
            }
            return "fc-event-repair-order"; // LIGHT GREY (repair order without payments)
        }
        
        // Check if has estimate (DARK GREY)
        if ($appointment->estimate) {
            return "fc-event-estimate"; // DARK GREY
        }
        
        // Default: Scheduled appointment (BLACK BORDER, WHITE BACKGROUND)
        return "fc-event-scheduled-default";
    }
    
    /**
     * Get display text for workflow status.
     */
    private function getWorkflowStatusDisplay($workflowStatus)
    {
        $statusMap = [
            "fc-event-scheduled-default" => "Scheduled Appointment",
            "fc-event-estimate" => "Estimate Created",
            "fc-event-repair-order" => "Repair Order (No Payments)",
            "fc-event-job-order-paid" => "Repair Order with Invoices & Payments"
        ];
        
        return $statusMap[$workflowStatus] ?? "Scheduled";
    }
    
    /**
     * Get event title for calendar.
     */
    private function getEventTitle($appointment)
    {
        $customerName = $appointment->customer ? $appointment->customer->first_name : "Walk-in";
        
        // Format time (e.g., "9:00 AM")
        $time = date('g:i A', strtotime($appointment->appointment_time));
        
        // Get service type abbreviation
        $serviceType = $this->getServiceTypeAbbreviation($appointment->service_type);
        
        // Clean calendar title: Time + Customer + Service
        // Example: "9:00 AM - John - Oil Change"
        return "{$time} - {$customerName}" . ($serviceType ? " - {$serviceType}" : "");
    }
    
    /**
     * Get abbreviated service type for calendar display.
     */
    private function getServiceTypeAbbreviation($serviceType)
    {
        $abbreviations = [
            'regular_service' => 'Service',
            'emergency' => 'Emergency',
            'inspection' => 'Inspection',
            'diagnostic' => 'Diagnostic',
            'repair' => 'Repair',
            'maintenance' => 'Maintenance',
            'tire_service' => 'Tires',
            'oil_change' => 'Oil Change',
            'brake_service' => 'Brakes',
            'other' => 'Other'
        ];
        
        return $abbreviations[$serviceType] ?? $serviceType;
    }
    
    /**
     * Calculate end time for calendar event.
     */
    private function calculateEndTime($time)
    {
        // Default 1 hour duration
        $start = Carbon::parse($time);
        $end = $start->copy()->addHour();
        return $end->format("H:i:s");
    }
    
    /**
     * Get vehicle information.
     */
    private function getVehicleInfo($appointment)
    {
        if ($appointment->vehicle) {
            $vehicle = $appointment->vehicle;
            return $vehicle->year . " " . $vehicle->make . " " . $vehicle->model;
        }
        
        if ($appointment->customer && $appointment->customer->vehicles->count() > 0) {
            $vehicle = $appointment->customer->vehicles->first();
            return $vehicle->year . " " . $vehicle->make . " " . $vehicle->model;
        }
        
        return "Vehicle info not available";
    }
    
    /**
     * Get event tooltip.
     */
    private function getEventTooltip($appointment)
    {
        $customerName = $appointment->customer ? $appointment->customer->full_name : "Walk-in Customer";
        $time = Carbon::parse($appointment->appointment_time)->format("g:i A");
        $status = ucfirst(str_replace("_", " ", $appointment->appointment_status));
        $appointmentNumber = $appointment->appointment_number;
        
        // Get workflow status for display
        $workflowStatus = $this->getWorkflowStatus($appointment);
        $workflowStatusDisplay = $this->getWorkflowStatusDisplay($workflowStatus);
        
        // Improved tooltip with more details
        $tooltip = "Appointment #: " . $appointmentNumber . "\n";
        $tooltip .= "Customer: " . $customerName . "\n";
        $tooltip .= "Time: " . $time . "\n";
        $tooltip .= "Status: " . $status . "\n";
        $tooltip .= "Workflow: " . $workflowStatusDisplay . "\n";
        
        // Add vehicle info if available
        $vehicleInfo = $this->getVehicleInfo($appointment);
        if ($vehicleInfo) {
            $tooltip .= "Vehicle: " . $vehicleInfo . "\n";
        }
        
        // Add service type
        if ($appointment->service_type) {
            $tooltip .= "Service: " . ucfirst(str_replace('_', ' ', $appointment->service_type)) . "\n";
        }
        
        // REMOVED: Color coding explanation (per user request)
        // Tooltip now only shows appointment details, not color legend
        
        return $tooltip;
    }
    
    public function statistics()
    {
        $today = Carbon::today();
        $dailyStats = [
            'total' => Appointment::whereDate('appointment_date', $today)->count(),
            'scheduled' => Appointment::whereDate('appointment_date', $today)->where('appointment_status', 'scheduled')->count(),
            'confirmed' => Appointment::whereDate('appointment_date', $today)->where('appointment_status', 'confirmed')->count(),
            'in_progress' => Appointment::whereDate('appointment_date', $today)->where('appointment_status', 'in_progress')->count(),
            'completed' => Appointment::whereDate('appointment_date', $today)->where('appointment_status', 'completed')->count(),
            'cancelled' => Appointment::whereDate('appointment_date', $today)->where('appointment_status', 'cancelled')->count(),
            'no_show' => Appointment::whereDate('appointment_date', $today)->where('appointment_status', 'no_show')->count(),
        ];
        
        // Weekly statistics
        $weekStart = $today->copy()->startOfWeek();
        $weekEnd = $today->copy()->endOfWeek();
        
        $weeklyStats = [
            'total' => Appointment::whereBetween('appointment_date', [$weekStart, $weekEnd])->count(),
            'by_type' => Appointment::whereBetween('appointment_date', [$weekStart, $weekEnd])
                ->groupBy('appointment_type')
                ->selectRaw('appointment_type, count(*) as count')
                ->pluck('count', 'appointment_type'),
            'by_status' => Appointment::whereBetween('appointment_date', [$weekStart, $weekEnd])
                ->groupBy('appointment_status')
                ->selectRaw('appointment_status, count(*) as count')
                ->pluck('count', 'appointment_status'),
        ];
        
        // Monthly statistics
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();
        
        $monthlyStats = [
            'total' => Appointment::whereBetween('appointment_date', [$monthStart, $monthEnd])->count(),
            'revenue' => Appointment::whereBetween('appointment_date', [$monthStart, $monthEnd])
                ->where('appointment_status', 'completed')
                ->sum('estimated_cost'),
            'avg_duration' => Appointment::whereBetween('appointment_date', [$monthStart, $monthEnd])
                ->where('appointment_status', 'completed')
                ->avg('estimated_duration'),
        ];
        
        // Technician performance
        $technicianStats = User::where('role', 'technician')
            ->where('is_active', true)
            ->withCount(['appointments as completed_appointments' => function($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('appointment_date', [$monthStart, $monthEnd])
                      ->where('appointment_status', 'completed');
            }])
            ->withAvg(['appointments as avg_duration' => function($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('appointment_date', [$monthStart, $monthEnd])
                      ->where('appointment_status', 'completed');
            }], 'estimated_duration')
            ->get();
        
        return view('appointments.statistics', compact('dailyStats', 'weeklyStats', 'monthlyStats', 'technicianStats'));
    }
    /**
     * Check if appointment is within 3 days.
     */
    private function isWithinThreeDays($appointment)
    {
        $appointmentDate = \Carbon\Carbon::parse($appointment->appointment_date);
        $today = \Carbon\Carbon::today();
        
        $daysDifference = $today->diffInDays($appointmentDate, false); // false = not absolute
        
        // Return true if appointment is within 3 days (0-3 days away)
        return $daysDifference >= 0 && $daysDifference <= 3;
    }
}
