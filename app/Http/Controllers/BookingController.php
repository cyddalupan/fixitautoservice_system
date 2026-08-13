<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BookingToken;
use App\Models\BookingSetting;
use App\Models\Customer;
use App\Models\PortalUser;
use App\Models\ServiceType;
use App\Models\Vehicle;
use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\MagicLinkLog;
use App\Models\PortalMessage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * Show the booking portal homepage (login / magic link landing).
     */
    public function index()
    {
        return view('booking.portal');
    }

    /**
     * Show login form.
     */
    public function showLogin()
    {
        return view('booking.login');
    }

    /**
     * Handle login (email + password).
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $portalUser = PortalUser::where('email', $validated['email'])
            ->where('is_active', true)
            ->first();

        if (!$portalUser || !Hash::check($validated['password'], $portalUser->password)) {
            return back()->withErrors(['email' => 'Invalid email or password.'])->onlyInput('email');
        }

        $portalUser->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        auth('portal')->login($portalUser);

        return redirect()->route('booking.dashboard');
    }

    /**
     * Show registration form.
     */
    public function showRegister()
    {
        return view('booking.register');
    }

    /**
     * Handle registration (create portal user linked to existing customer).
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:portal_users,email',
            'password' => 'required|min:6|confirmed',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        // Check if customer already exists with this email
        $customer = Customer::where('email', $validated['email'])->first();

        // If no customer with this email, check if one with this phone exists
        if (!$customer) {
            $customer = Customer::where('phone', $validated['phone'])->first();
        }

        // Still no customer? Create one
        if (!$customer) {
            $customer = Customer::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'is_active' => true,
                'customer_since' => now(),
            ]);
        }

        // Create portal user (auto-verified since they registered directly)
        $portalUser = PortalUser::create([
            'customer_id' => $customer->id,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        auth('portal')->login($portalUser);

        return redirect()->route('booking.dashboard');
    }

    /**
     * Customer booking dashboard.
     */
    public function dashboard()
    {
        $customer = auth('portal')->user()->customer;
        $appointments = Appointment::where('customer_id', $customer->id)
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->paginate(10);

        return view('booking.dashboard', compact('customer', 'appointments'));
    }

    /**
     * Show the booking form (create new appointment).
     */
    public function showBookingForm()
    {
        $customer = auth('portal')->user()->customer;
        $vehicles = Vehicle::where('customer_id', $customer->id)->where('is_active', true)->get();
        $services = ServiceType::orderBy('sort_order')->get();
        $workingHours = BookingSetting::getWorkingHours();
        $slotDuration = BookingSetting::getSlotDuration();
        $maxDaysAhead = BookingSetting::getMaxDaysAhead();

        return view('booking.create', compact(
            'customer', 'vehicles', 'services',
            'workingHours', 'slotDuration', 'maxDaysAhead'
        ));
    }

    /**
     * Handle magic link token access (no login required).
     */
    public function magicLink(Request $request)
    {
        $token = $request->token;

        if (!$token) {
            return redirect()->route('booking.login')
                ->with('error', 'Invalid or missing booking token.');
        }

        $bookingToken = BookingToken::findValid($token);

        if (!$bookingToken) {
            return redirect()->route('booking.login')
                ->with('error', 'This booking link has expired or is invalid. Please log in.');
        }

        // Log in the portal user if exists, or create a session for this customer
        $portalUser = PortalUser::where('customer_id', $bookingToken->customer_id)->first();

        if ($portalUser) {
            auth('portal')->login($portalUser);
        } else {
            // Store customer ID in session for token-based booking
            session(['booking_customer_id' => $bookingToken->customer_id]);
            session(['booking_token_id' => $bookingToken->id]);
        }

        $bookingToken->markAsUsed();

        return redirect()->route('booking.create')
            ->with('success', 'Welcome! You can now book your appointment.');
    }

    /**
     * Show create booking page (also accessible via magic link).
     */
    public function showCreate()
    {
        // Determine customer from auth or session
        $customerId = $this->resolveCustomerId();
        if (!$customerId) {
            return redirect()->route('booking.login')
                ->with('error', 'Please log in to book an appointment.');
        }

        $customer = Customer::findOrFail($customerId);
        $vehicles = Vehicle::where('customer_id', $customerId)->where('is_active', true)->get();
        $services = ServiceType::orderBy('sort_order')->get();
        $workingHours = BookingSetting::getWorkingHours();
        $slotDuration = BookingSetting::getSlotDuration();
        $maxDaysAhead = BookingSetting::getMaxDaysAhead();

        return view('booking.create', compact(
            'customer', 'vehicles', 'services',
            'workingHours', 'slotDuration', 'maxDaysAhead'
        ));
    }

    /**
     * API: Get active vehicle brands for the public booking form autocomplete.
     */
    public function apiVehicleBrands(Request $request)
    {
        $term = trim((string) $request->get('term', ''));

        $brands = VehicleBrand::where('is_active', true)
            ->when($term !== '', function ($q) use ($term) {
                return $q->where('name', 'LIKE', '%' . $term . '%');
            })
            ->orderBy('name')
            ->pluck('name')
            ->values();

        return response()->json($brands);
    }

    /**
     * API: Get active vehicle models (optionally filtered by brand) for the
     * public booking form autocomplete.
     */
    public function apiVehicleModels(Request $request)
    {
        $term = trim((string) $request->get('term', ''));
        $brand = trim((string) $request->get('brand', ''));

        $query = VehicleModel::where('is_active', true);

        if ($brand !== '') {
            $query->whereHas('brand', function ($q) use ($brand) {
                return $q->where('name', $brand);
            });
        }

        if ($term !== '') {
            $query->where('name', 'LIKE', '%' . $term . '%');
        }

        $models = $query->orderBy('name')->pluck('name')->values();

        return response()->json($models);
    }

    /**
     * API: Get available time slots for a given date.
     */
    public function getAvailableSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $date = Carbon::parse($request->date);
        $dayOfWeek = strtolower($date->format('l'));
        $workingHours = BookingSetting::getWorkingHours();
        $slotDuration = BookingSetting::getSlotDuration();

        // Check if the day is enabled
        $dayConfig = $workingHours[$dayOfWeek] ?? null;
        if (!$dayConfig || !$dayConfig['enabled']) {
            return response()->json([
                'slots' => [],
                'message' => 'No service available on ' . $date->format('l'),
            ]);
        }

        $startTime = Carbon::parse($dayConfig['start']);
        $endTime = Carbon::parse($dayConfig['end']);

        // Get existing appointments for this date that are not cancelled/no-show
        $existingBookings = Appointment::whereDate('appointment_date', $date)
            ->whereNotIn('appointment_status', ['cancelled', 'no_show'])
            ->get()
            ->map(function ($apt) {
                return Carbon::parse($apt->appointment_time)->format('H:i');
            })
            ->values()
            ->toArray();

        // Generate all possible slots
        $slots = [];
        $current = clone $startTime;

        while ($current < $endTime) {
            $timeStr = $current->format('H:i');
            $isBooked = in_array($timeStr, $existingBookings);

            $slots[] = [
                'time' => $timeStr,
                'display' => $current->format('g:i A'),
                'available' => !$isBooked,
            ];

            $current->addMinutes($slotDuration);
        }

        return response()->json([
            'date' => $date->format('Y-m-d'),
            'slots' => $slots,
            'working_hours' => [
                'start' => $dayConfig['start'],
                'end' => $dayConfig['end'],
            ],
        ]);
    }

    /**
     * API: Create a new booking (appointment).
     */
    public function createBooking(Request $request)
    {
        // Rate limiting
        $key = 'booking_' . $request->ip();
        // Apply validation
        $validator = Validator::make($request->all(), [
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'appointment_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'service_type' => 'required|string|max:255',
            'customer_notes' => 'nullable|string|max:2000',
            // New vehicle fields (if adding a new vehicle)
            'new_vehicle' => 'nullable|boolean',
            'vehicle_make' => 'required_if:new_vehicle,1|string|max:255',
            'vehicle_model' => 'required_if:new_vehicle,1|string|max:255',
            'vehicle_year' => 'required_if:new_vehicle,1|integer|min:1900|max:2030',
            'vehicle_plate' => 'nullable|string|max:255',
            'vehicle_color' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Resolve customer
        $customerId = $this->resolveCustomerId();
        if (!$customerId) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required.',
            ], 401);
        }

        $customer = Customer::findOrFail($customerId);
        $date = Carbon::parse($request->appointment_date);
        $time = $request->appointment_time;

        // Validate slot availability
        $dayOfWeek = strtolower($date->format('l'));
        $workingHours = BookingSetting::getWorkingHours();
        $dayConfig = $workingHours[$dayOfWeek] ?? null;

        if (!$dayConfig || !$dayConfig['enabled']) {
            return response()->json([
                'success' => false,
                'message' => 'Bookings are not available on ' . $date->format('l') . '.',
            ], 400);
        }

        if ($time < $dayConfig['start'] || $time >= $dayConfig['end']) {
            return response()->json([
                'success' => false,
                'message' => 'Selected time is outside working hours.',
            ], 400);
        }

        // Check for double booking
        $existing = Appointment::whereDate('appointment_date', $date)
            ->where('appointment_time', $time)
            ->whereNotIn('appointment_status', ['cancelled', 'no_show'])
            ->exists();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'This time slot is no longer available. Please choose another.',
            ], 409);
        }

        // Check for duplicate vehicle plate — same plate cannot have overlapping active appointments
        $vehicleId = $request->vehicle_id;
        $plateToCheck = null;
        
        if ($request->boolean('new_vehicle') && $request->filled('vehicle_plate')) {
            // New vehicle — check if another vehicle with same plate already has an active appointment
            $plateToCheck = $request->vehicle_plate;
            $conflictingVehicle = Vehicle::where('license_plate', $plateToCheck)->first();
            if ($conflictingVehicle) {
                $conflictingAppointments = Appointment::where('vehicle_id', $conflictingVehicle->id)
                    ->whereDate('appointment_date', $date)
                    ->whereNotIn('appointment_status', ['cancelled', 'no_show', 'completed'])
                    ->exists();
                if ($conflictingAppointments) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This vehicle (plate: ' . $plateToCheck . ') already has an existing appointment on ' . $date->format('M d, Y') . '. Each vehicle can only have one active appointment per day.',
                    ], 409);
                }
            }
        } elseif (!$request->boolean('new_vehicle') && $vehicleId) {
            // Existing vehicle — check if it already has an active appointment on this date
            $conflictingAppointments = Appointment::where('vehicle_id', $vehicleId)
                ->whereDate('appointment_date', $date)
                ->whereNotIn('appointment_status', ['cancelled', 'no_show', 'completed'])
                ->exists();
            if ($conflictingAppointments) {
                return response()->json([
                    'success' => false,
                    'message' => 'This vehicle already has an existing appointment on ' . $date->format('M d, Y') . '. Each vehicle can only have one active appointment per day.',
                ], 409);
            }
        }

        // Handle vehicle
        $vehicleId = $request->vehicle_id;
        if ($request->boolean('new_vehicle')) {
            $vehicle = Vehicle::create([
                'customer_id' => $customerId,
                'make' => $request->vehicle_make,
                'model' => $request->vehicle_model,
                'year' => $request->vehicle_year,
                'license_plate' => $request->vehicle_plate,
                'color' => $request->vehicle_color,
                'is_active' => true,
            ]);
            $vehicleId = $vehicle->id;
        }

        // Create the appointment
        $appointment = Appointment::create([
            'customer_id' => $customerId,
            'vehicle_id' => $vehicleId,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'appointment_type' => $request->service_type,
            'appointment_status' => 'customer_booked',
            'service_request' => $request->customer_notes,
            'booking_source' => 'website',
            'customer_notes' => $request->customer_notes,
            'scheduled_at' => now(),
            'estimated_duration' => 1.0, // 1 hour default
        ]);

        // Generate confirmation token
        $confirmationToken = BookingToken::generateForCustomer($customerId, 72);

        // Try to send email confirmation (log if fails)
        try {
            \Illuminate\Support\Facades\Mail::to($customer->email)->send(new \App\Mail\BookingConfirmation($appointment, $customer));
        } catch (\Exception $e) {
            // Email sending is best-effort; log but don't fail
            \Illuminate\Support\Facades\Log::warning('Booking confirmation email failed: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Your appointment has been booked successfully!',
            'appointment' => [
                'id' => $appointment->id,
                'number' => $appointment->appointment_number,
                'date' => $appointment->appointment_date->format('M d, Y'),
                'time' => Carbon::parse($appointment->appointment_time)->format('g:i A'),
                'status' => 'customer_booked',
            ],
            'confirmation_token' => $confirmationToken->token,
            'reschedule_token' => route('booking.reschedule', ['appointment' => $appointment->id]),
        ]);
    }

    /**
     * Show reschedule form.
     */
    public function showReschedule(Appointment $appointment)
    {
        $customerId = $this->resolveCustomerId();
        if (!$customerId || $appointment->customer_id !== $customerId) {
            return redirect()->route('booking.login')
                ->with('error', 'Unauthorized.');
        }

        $customer = Customer::find($customerId);
        $vehicles = Vehicle::where('customer_id', $customerId)->where('is_active', true)->get();
        $services = ServiceType::orderBy('sort_order')->get();
        $workingHours = BookingSetting::getWorkingHours();
        $slotDuration = BookingSetting::getSlotDuration();
        $maxDaysAhead = BookingSetting::getMaxDaysAhead();

        return view('booking.reschedule', compact(
            'appointment', 'customer', 'vehicles', 'services',
            'workingHours', 'slotDuration', 'maxDaysAhead'
        ));
    }

    /**
     * Handle reschedule submission.
     */
    public function updateReschedule(Request $request, Appointment $appointment)
    {
        $customerId = $this->resolveCustomerId();
        if (!$customerId || $appointment->customer_id !== $customerId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'appointment_date' => 'required|date_format:Y-m-d|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
        ]);

        $date = Carbon::parse($validated['appointment_date']);
        $time = $validated['appointment_time'];

        // Check availability
        $existing = Appointment::where('id', '!=', $appointment->id)
            ->whereDate('appointment_date', $date)
            ->where('appointment_time', $time)
            ->whereNotIn('appointment_status', ['cancelled', 'no_show'])
            ->exists();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'This time slot is no longer available.',
            ], 409);
        }

        $appointment->update([
            'appointment_date' => $date,
            'appointment_time' => $time,
            'appointment_status' => 'rescheduled',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment rescheduled successfully!',
        ]);
    }

    /**
     * Cancel an appointment.
     */
    public function cancelAppointment(Request $request, Appointment $appointment)
    {
        $customerId = $this->resolveCustomerId();
        if (!$customerId || $appointment->customer_id !== $customerId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $appointment->update([
            'appointment_status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled successfully.',
        ]);
    }

    /**
     * Show confirmation page.
     */
    public function showConfirmation(Request $request)
    {
        $appointmentId = $request->session()->get('booking_confirmation_id');
        if (!$appointmentId) {
            return redirect()->route('booking.dashboard');
        }

        $appointment = Appointment::with('customer', 'vehicle')->findOrFail($appointmentId);

        return view('booking.confirmation', compact('appointment'));
    }

    /**
     * Logout.
     */
    public function logout()
    {
        auth('portal')->logout();
        session()->forget(['booking_customer_id', 'booking_token_id']);
        return redirect()->route('booking.portal');
    }

    /**
     * Resolve customer ID from auth or session.
     */
    private function resolveCustomerId(): ?int
    {
        if (auth('portal')->check()) {
            return auth('portal')->user()->customer_id;
        }

        // Check token from API header (standalone frontend)
        $token = request()->header('X-Booking-Token');
        if ($token) {
            try {
                $decoded = json_decode(decrypt($token), true);
                if ($decoded && isset($decoded['customer_id']) && $decoded['expires_at'] > time()) {
                    return $decoded['customer_id'];
                }
            } catch (\Exception $e) {
                // Invalid or expired token
            }
        }

        return session('booking_customer_id');
    }

    /**
     * Get customer vehicles (API).
     */
    public function getVehicles()
    {
        $customerId = $this->resolveCustomerId();
        if (!$customerId) {
            return response()->json(['success' => false, 'message' => 'Auth required'], 401);
        }

        $vehicles = Vehicle::where('customer_id', $customerId)
            ->where('is_active', true)
            ->get(['id', 'make', 'model', 'year', 'color', 'license_plate']);

        return response()->json(['success' => true, 'vehicles' => $vehicles]);
    }

    /**
     * API: Login via JSON (used by standalone frontend on fixitautoservices.com).
     */
    public function apiLogin(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $portalUser = PortalUser::where('email', $validated['email'])
            ->where('is_active', true)
            ->first();

        if (!$portalUser || !Hash::check($validated['password'], $portalUser->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $portalUser->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        auth('portal')->login($portalUser);

        // Generate a session-based token for the frontend
        $token = encrypt(json_encode([
            'customer_id' => $portalUser->customer_id,
            'portal_user_id' => $portalUser->id,
            'expires_at' => now()->addDays(7)->timestamp,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token' => $token,
            'customer' => [
                'id' => $portalUser->customer_id,
                'email' => $portalUser->email,
            ],
        ]);
    }

    /**
     * API: Register via JSON (used by standalone frontend).
     */
    public function apiRegister(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:portal_users,email',
            'password' => 'required|min:6',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        // Find or create customer
        $customer = Customer::where('email', $validated['email'])->first()
            ?? Customer::where('phone', $validated['phone'])->first();

        if (!$customer) {
            $customer = Customer::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'is_active' => true,
                'customer_since' => now(),
            ]);
        }

        $portalUser = PortalUser::create([
            'customer_id' => $customer->id,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        auth('portal')->login($portalUser);

        $token = encrypt(json_encode([
            'customer_id' => $customer->id,
            'portal_user_id' => $portalUser->id,
            'expires_at' => now()->addDays(7)->timestamp,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully.',
            'token' => $token,
            'customer' => [
                'id' => $customer->id,
                'email' => $portalUser->email,
            ],
        ]);
    }

    /**
     * API: Logout via JSON.
     */
    public function apiLogout()
    {
        auth('portal')->logout();
        return response()->json([
            'success' => true,
            'message' => 'Logged out.',
        ]);
    }

    /**
     * API: Verify a magic link token and return a session token.
     */
    public function apiMagicVerify(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        $bookingToken = BookingToken::findValid($validated['token']);

        if (!$bookingToken) {
            return response()->json([
                'success' => false,
                'message' => 'This link has expired or is invalid. Please request a new magic link.',
            ], 400);
        }

        // Find or log in the portal user
        $portalUser = PortalUser::where('customer_id', $bookingToken->customer_id)->first();
        if ($portalUser) {
            auth('portal')->login($portalUser);
        } else {
            session(['booking_customer_id' => $bookingToken->customer_id]);
        }

        $bookingToken->markAsUsed();

        // Generate token for SPA
        $token = encrypt(json_encode([
            'customer_id' => $bookingToken->customer_id,
            'portal_user_id' => $portalUser ? $portalUser->id : null,
            'expires_at' => now()->addDays(7)->timestamp,
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'token' => $token,
        ]);
    }

    /**
     * API: Request a magic link (sent via email).
     */
    public function apiRequestMagicLink(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
        ]);

        $customer = Customer::where('email', $validated['email'])->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'No account found with that email address.',
            ], 404);
        }

        // Generate a token and build the magic link URL (on the main domain)
        $token = BookingToken::generateForCustomer($customer->id, 2);
        $magicUrl = 'https://fixitautoservices.com/booking/magic?token=' . $token->token;

        // Log the magic link request
        $logData = [
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'token' => $token->token,
        ];

        // Try to send email
        try {
            \Illuminate\Support\Facades\Mail::to($customer->email)->send(new \App\Mail\BookingMagicLink($customer, $magicUrl));
            $logData['status'] = 'success';
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Magic link email failed: ' . $e->getMessage());
            $logData['status'] = 'failed';
            $logData['error_message'] = $e->getMessage();
            // Even if email fails, return the magic link URL for testing
        }

        try {
            \App\Models\MagicLinkLog::create($logData);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to log magic link request: ' . $e->getMessage());
        }

        // For the standalone SPA: return the magic link as well
        // (In production, remove this from response)
        return response()->json([
            'success' => true,
            'message' => 'Magic link sent to your email.',
            '_dev_magic_url' => $magicUrl, // Remove in production
        ]);
    }

    /**
     * API: Get customer data for the booking portal (JSON).
     */
    public function apiCustomerData()
    {
        $customerId = $this->resolveCustomerId();
        if (!$customerId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $customer = Customer::with(['vehicles' => function ($q) {
            $q->where('is_active', true);
        }])->find($customerId);

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found'], 404);
        }

        $services = ServiceType::orderBy('sort_order')->get();

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
            ],
            'vehicles' => $customer->vehicles->map(fn($v) => [
                'id' => $v->id,
                'make' => $v->make,
                'model' => $v->model,
                'year' => $v->year,
                'color' => $v->color,
                'license_plate' => $v->license_plate,
            ]),
            'services' => $services->map(fn($s) => [
                'id' => $s->id,
                'key' => $s->key,
                'name' => $s->name,
                'icon' => $s->icon ?? '🔧',
                'description' => $s->description,
            ]),
        ]);
    }

    /**
     * API: Get authenticated customer's appointments list.
     */
    public function apiCustomerAppointments()
    {
        $customerId = $this->resolveCustomerId();
        if (!$customerId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $appointments = Appointment::with('vehicle')
            ->where('customer_id', $customerId)
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'reference' => $a->appointment_number,
                'date' => $a->appointment_date ? $a->appointment_date->format('Y-m-d') : null,
                'date_display' => $a->appointment_date ? $a->appointment_date->format('M d, Y') : null,
                'time' => $a->appointment_time ? Carbon::parse($a->appointment_time)->format('H:i') : null,
                'time_display' => $a->appointment_time ? Carbon::parse($a->appointment_time)->format('g:i A') : null,
                'service_type' => $a->appointment_type,
                'status' => $a->appointment_status,
                'status_display' => $this->customerStatusLabel($a->appointment_status),
                'notes' => $a->customer_notes,
                'vehicle' => $a->vehicle ? $a->vehicle->make . ' ' . $a->vehicle->model . ' (' . $a->vehicle->license_plate . ')' : 'Unknown',
            ]);

        return response()->json([
            'success' => true,
            'appointments' => $appointments,
        ]);
    }

    /**
     * Convert internal status to customer-facing label.
     */
    private function customerStatusLabel($status)
    {
        $labels = [
            'customer_booked' => 'Booked by Customer',
            'scheduled' => 'Scheduled',
            'confirmed' => 'Confirmed',
            'checked_in' => 'Checked In',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'no_show' => 'No Show',
            'rescheduled' => 'Rescheduled',
        ];
        return $labels[$status] ?? ucfirst(str_replace('_', ' ', $status));
    }

    /**
     * API: Create booking from the public-facing portal (JSON endpoint).
     */
    public function apiCreateBooking(Request $request)
    {
        $customerId = $this->resolveCustomerId();
        if (!$customerId) {
            return response()->json(['success' => false, 'message' => 'Not authenticated'], 401);
        }

        $validated = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id', function ($attr, $value, $fail) use ($customerId) {
                if (!\App\Models\Vehicle::where('id', $value)->where('customer_id', $customerId)->exists()) {
                    $fail('The selected vehicle does not belong to you.');
                }
            }],
            'service_type' => 'required|string',
            'appointment_date' => 'required|date_format:Y-m-d',
            'appointment_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:500',
        ]);

        // Check for duplicate booking (same time slot)
        $existing = Appointment::where('customer_id', $customerId)
            ->whereDate('appointment_date', $validated['appointment_date'])
            ->where('appointment_time', $validated['appointment_time'] . ':00')
            ->whereNotIn('appointment_status', ['cancelled', 'no_show'])
            ->exists();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a booking at this time. Please choose a different time.',
            ], 409);
        }

        // Check if vehicle already has any active transaction
        $vehicleId = $validated['vehicle_id'];

        // Active appointment check (any date)
        $activeAppointment = Appointment::where('vehicle_id', $vehicleId)
            ->whereNotIn('appointment_status', ['cancelled', 'no_show', 'completed'])
            ->exists();

        if ($activeAppointment) {
            return response()->json([
                'success' => false,
                'message' => 'This vehicle already has an active appointment. Please contact the shop to manage existing bookings.',
            ], 409);
        }

        // Active work order check
        $activeWorkOrder = \App\Models\WorkOrder::where('vehicle_id', $vehicleId)
            ->whereNotIn('work_order_status', ['cancelled', 'completed', 'released'])
            ->exists();

        if ($activeWorkOrder) {
            return response()->json([
                'success' => false,
                'message' => 'This vehicle is currently being serviced. Please contact the shop to schedule additional work.',
            ], 409);
        }

        // Active estimate check
        $activeEstimate = \App\Models\Estimate::where('vehicle_id', $vehicleId)
            ->whereNotIn('status', ['cancelled', 'rejected', 'converted', 'expired'])
            ->exists();

        if ($activeEstimate) {
            return response()->json([
                'success' => false,
                'message' => 'This vehicle has a pending estimate. Please contact the shop to proceed before booking.',
            ], 409);
        }

        $vehicle = Vehicle::findOrFail($validated['vehicle_id']);

        $appointment = Appointment::create([
            'customer_id' => $customerId,
            'vehicle_id' => $validated['vehicle_id'],
            'appointment_number' => Appointment::generateAppointmentNumber(),
            'appointment_date' => $validated['appointment_date'],
            'appointment_time' => $validated['appointment_time'] . ':00',
            'appointment_type' => $validated['service_type'],
            'appointment_status' => 'customer_booked',
            'customer_notes' => $validated['notes'] ?? null,
            'booking_source' => 'website',
            'booking_ip' => $request->ip(),
            'scheduled_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment booked successfully!',
            'appointment' => [
                'id' => $appointment->id,
                'reference' => $appointment->appointment_number,
                'date' => $appointment->appointment_date,
                'time' => $appointment->appointment_time,
                'service' => $validated['service_type'],
                'status' => 'Booked by Customer',
                'vehicle' => $vehicle->make . ' ' . $vehicle->model . ' (' . $vehicle->license_plate . ')',
            ],
        ]);
    }

    // ========================================================================
    // CUSTOMER APPOINTMENT TRACKING
    // ========================================================================

    /**
     * Map internal status to customer-facing tracking step.
     */
    private function mapTrackingStep(string $status): array
    {
        $steps = [
            'booked'       => ['label' => 'Booked',       'icon' => '📋', 'order' => 0],
            'confirmed'    => ['label' => 'Confirmed',    'icon' => '✅', 'order' => 1],
            'in_progress'  => ['label' => 'In Progress',  'icon' => '🔧', 'order' => 2],
            'completed'    => ['label' => 'Completed',    'icon' => '✔️',  'order' => 3],
            'ready'        => ['label' => 'Ready for Pickup', 'icon' => '🚗', 'order' => 4],
        ];

        $statusMap = [
            'customer_booked' => 'booked',
            'scheduled'       => 'booked',
            'confirmed'       => 'confirmed',
            'checked_in'      => 'in_progress',
            'in_progress'     => 'in_progress',
            'completed'       => 'completed',
            'ready_for_pickup' => 'ready',
        ];

        $stepKey = $statusMap[$status] ?? 'booked';
        return [
            'step_key' => $stepKey,
            'step'     => $steps[$stepKey] ?? $steps['booked'],
            'all_steps' => array_values($steps),
        ];
    }

    /**
     * Get a friendly status description for the customer.
     */
    private function getStatusDescription(string $status): string
    {
        $descriptions = [
            'customer_booked' => 'Your appointment request has been received. We will review it shortly.',
            'scheduled'       => 'Your appointment is scheduled. We look forward to serving you!',
            'confirmed'       => 'Your appointment has been confirmed. We\'ll be ready for you on the scheduled date.',
            'checked_in'      => 'You have checked in. Your vehicle will be taken in for service soon.',
            'in_progress'     => 'Your vehicle is currently being serviced. We\'ll notify you when it\'s ready.',
            'completed'       => 'Your service has been completed. Your vehicle is ready for pick-up!',
            'cancelled'       => 'This appointment has been cancelled.',
            'rescheduled'     => 'This appointment has been rescheduled.',
            'no_show'         => 'This appointment was marked as missed. Please contact us to reschedule.',
        ];

        return $descriptions[$status] ?? 'Your appointment is being processed.';
    }

    /**
     * API: Track an appointment (customer-facing, safe view).
     */
    public function apiTrackAppointment(Appointment $appointment)
    {
        $customerId = $this->resolveCustomerId();
        if (!$customerId) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        // Security: verify customer owns this appointment
        if ($appointment->customer_id !== $customerId) {
            return response()->json(['success' => false, 'message' => 'Appointment not found.'], 404);
        }

        $appointment->load('vehicle', 'customer');

        $tracking = $this->mapTrackingStep($appointment->appointment_status);

        // Build customer-safe response
        $safeData = [
            'id'               => $appointment->appointment_number ?? 'APT' . $appointment->id,
            'date'             => $appointment->appointment_date ? $appointment->appointment_date->format('M d, Y') : null,
            'time'             => $appointment->appointment_time ? Carbon::parse($appointment->appointment_time)->format('g:i A') : null,
            'service_type'     => ucwords(str_replace('_', ' ', $appointment->appointment_type)),
            'status'           => $appointment->appointment_status,
            'status_label'     => $tracking['step']['label'],
            'status_icon'      => $tracking['step']['icon'],
            'status_description' => $this->getStatusDescription($appointment->appointment_status),
            'tracking_steps'   => $tracking['all_steps'],
            'current_step_key' => $tracking['step_key'],
            'vehicle'          => $appointment->vehicle ? [
                'make'         => $appointment->vehicle->make,
                'model'        => $appointment->vehicle->model,
                'year'         => $appointment->vehicle->year,
                'color'        => $appointment->vehicle->color,
                'license_plate'=> $appointment->vehicle->license_plate,
            ] : null,
            'customer_notes'   => $appointment->customer_notes,
            'cost'             => [
                'estimated'    => $appointment->estimated_cost ? (float) $appointment->estimated_cost : null,
                'final'        => null, // Only available when completed, fetched from invoice
            ],
            'can_reschedule'   => in_array($appointment->appointment_status, ['customer_booked', 'scheduled', 'confirmed']),
            'can_cancel'       => in_array($appointment->appointment_status, ['customer_booked', 'scheduled', 'confirmed']),
            'is_cancelled'     => $appointment->appointment_status === 'cancelled',
            'is_rescheduled'   => $appointment->appointment_status === 'rescheduled',
        ];

        // If completed, try to get final cost from invoice
        if ($appointment->appointment_status === 'completed') {
            $invoice = $appointment->invoice;
            if ($invoice) {
                $safeData['cost']['final'] = (float) $invoice->total_amount;
            } else {
                // Fallback: use estimated cost as final
                $safeData['cost']['final'] = $appointment->estimated_cost ? (float) $appointment->estimated_cost : null;
            }
        }

        return response()->json([
            'success' => true,
            'appointment' => $safeData,
        ]);
    }

    /**
     * API: Get customer-safe updates/messages for an appointment.
     */
    public function apiTrackAppointmentMessages(Appointment $appointment)
    {
        $customerId = $this->resolveCustomerId();
        if (!$customerId) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        // Security: verify customer owns this appointment
        if ($appointment->customer_id !== $customerId) {
            return response()->json(['success' => false, 'message' => 'Appointment not found.'], 404);
        }

        // Fetch messages sent to this customer (via portal_messages table)
        $messages = PortalMessage::where('customer_id', $customerId)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get(['id', 'subject', 'message', 'message_type', 'created_at'])
            ->map(fn($m) => [
                'id'      => $m->id,
                'subject' => $m->subject,
                'message' => $m->message,
                'type'    => $m->message_type,
                'date'    => $m->created_at->format('M d, Y g:i A'),
            ]);

        return response()->json([
            'success'  => true,
            'messages' => $messages,
        ]);
    }

    /**
     * API: Cancel an appointment (customer-facing).
     */
    public function apiCancelAppointment(Appointment $appointment)
    {
        $customerId = $this->resolveCustomerId();
        if (!$customerId) {
            return response()->json(['success' => false, 'message' => 'Authentication required.'], 401);
        }

        // Security: verify customer owns this appointment
        if ($appointment->customer_id !== $customerId) {
            return response()->json(['success' => false, 'message' => 'Appointment not found.'], 404);
        }

        // Only allow cancellation if status allows it
        if (!in_array($appointment->appointment_status, ['customer_booked', 'scheduled', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'This appointment cannot be cancelled at its current stage.',
            ], 422);
        }

        $appointment->update([
            'appointment_status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment has been cancelled successfully.',
        ]);
    }
}
