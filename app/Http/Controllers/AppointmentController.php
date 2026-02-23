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
        $query = Appointment::with(['customer', 'vehicle', 'technician', 'advisor'])
            ->latest();
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('appointment_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($q) use ($search) {
                      $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  })
                  ->orWhereHas('vehicle', function($q) use ($search) {
                      $q->where('license_plate', 'like', "%{$search}%")
                        ->orWhere('make', 'like', "%{$search}%")
                        ->orWhere('model', 'like', "%{$search}%");
                  });
            });
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('appointment_status', $request->status);
        }
        
        // Date filter
        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        } elseif ($request->filled('date_range')) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $query->whereBetween('appointment_date', [$dates[0], $dates[1]]);
            }
        } else {
            // Default: show today and upcoming appointments
            $query->whereDate('appointment_date', '>=', Carbon::today());
        }
        
        // Technician filter
        if ($request->filled('technician_id')) {
            $query->where('assigned_technician_id', $request->technician_id);
        }
        
        // Type filter
        if ($request->filled('type')) {
            $query->where('appointment_type', $request->type);
        }
        
        // Priority filter
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }
        
        // Waitlist filter
        if ($request->filled('waitlist')) {
            $query->where('is_waitlist', $request->waitlist == 'yes');
        }
        
        $appointments = $query->paginate(20);
        
        // Get technicians for filter dropdown
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        
        // Get statistics
        $stats = [
            'total' => Appointment::count(),
            'today' => Appointment::today()->count(),
            'upcoming' => Appointment::upcoming(7)->count(),
            'waitlist' => Appointment::waitlist()->count(),
            'no_show' => Appointment::where('appointment_status', 'no_show')->count(),
        ];
        
        return view('appointments.index', compact('appointments', 'technicians', 'stats'));
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
        
        // Pre-select customer if provided
        $selectedCustomer = $request->filled('customer_id') 
            ? Customer::find($request->customer_id)
            : null;
        
        // Pre-select vehicle if provided
        $selectedVehicle = $request->filled('vehicle_id') 
            ? Vehicle::find($request->vehicle_id)
            : null;
        
        return view('appointments.create', compact('customers', 'vehicles', 'technicians', 'advisors', 'selectedCustomer', 'selectedVehicle'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'appointment_type' => 'required|in:regular_service,emergency,inspection,diagnostic,repair,maintenance,tire_service,oil_change,brake_service,other',
            'service_request' => 'nullable|string|max:1000',
            'estimated_duration' => 'nullable|numeric|min:0.5|max:8',
            'estimated_cost' => 'nullable|numeric|min:0',
            'priority' => 'required|in:low,normal,high,emergency',
            'assigned_technician_id' => 'nullable|exists:users,id',
            'service_advisor_id' => 'nullable|exists:users,id',
            'bay_number' => 'nullable|integer|min:1|max:20',
            'customer_notes' => 'nullable|string|max:500',
            'requires_deposit' => 'boolean',
            'deposit_amount' => 'nullable|required_if:requires_deposit,true|numeric|min:0',
            'is_waitlist' => 'boolean',
            'service_types' => 'nullable|array',
            'service_types.*' => 'string|max:100',
            'preferred_communication' => 'nullable|array',
            'preferred_communication.*' => 'in:sms,email,call',
        ]);
        
        // Generate appointment number
        $validated['appointment_number'] = Appointment::generateAppointmentNumber();
        
        // Set default status
        $validated['appointment_status'] = $validated['is_waitlist'] ?? false ? 'scheduled' : 'scheduled';
        
        // Set scheduled timestamp
        $validated['scheduled_at'] = now();
        
        // Set booking source
        $validated['booking_source'] = 'admin_panel';
        
        // Convert arrays to JSON
        if (isset($validated['service_types'])) {
            $validated['service_types'] = json_encode($validated['service_types']);
        }
        
        if (isset($validated['preferred_communication'])) {
            $validated['preferred_communication'] = json_encode($validated['preferred_communication']);
        }
        
        // Create appointment
        $appointment = Appointment::create($validated);
        
        // If not waitlist, check for conflicts
        if (!$appointment->is_waitlist) {
            $this->checkForConflicts($appointment);
        }
        
        return redirect()->route('appointments.show', $appointment)
            ->with('success', 'Appointment created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        $appointment->load(['customer', 'vehicle', 'technician', 'advisor', 'workOrder']);
        
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
        $appointment->load(['customer', 'vehicle']);
        
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
            'estimated_duration' => 'nullable|numeric|min:0.5|max:8',
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
        $date = $request->filled('date') ? Carbon::parse($request->date) : Carbon::today();
        
        // Get appointments for the month
        $appointments = Appointment::with(['customer', 'vehicle', 'technician'])
            ->whereYear('appointment_date', $date->year)
            ->whereMonth('appointment_date', $date->month)
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();
        
        // Group by date for calendar
        $calendarData = [];
        foreach ($appointments as $appointment) {
            $dateKey = $appointment->appointment_date->format('Y-m-d');
            if (!isset($calendarData[$dateKey])) {
                $calendarData[$dateKey] = [];
            }
            $calendarData[$dateKey][] = $appointment;
        }
        
        // Get technicians for filter
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        
        return view('appointments.calendar', compact('date', 'calendarData', 'technicians'));
    }
    
    /**
     * Check-in an appointment.
     */
    public function checkIn(Appointment $appointment)
    {
        if ($appointment->appointment_status !== 'confirmed') {
            return redirect()->back()->with('error', 'Only confirmed appointments can be checked in.');
        }
        
        $appointment->update([
            'appointment_status' => 'checked_in',
            'checked_in_at' => now(),
        ]);
        
        return redirect()->back()->with('success', 'Appointment checked in successfully.');
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
     * Get appointment statistics.
     */
    public function statistics()
    {
        $today = Carbon::today();
        
        // Daily statistics
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
}
