<?php

namespace App\Http\Controllers;

use App\Models\PortalUser;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerPortalController extends Controller
{
    /**
     * Show the portal login form.
     */
    public function showLoginForm()
    {
        return view('portal.auth.login');
    }

    /**
     * Handle portal login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (Auth::guard('portal')->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            // Update last login
            $user = Auth::guard('portal')->user();
            $user->update([
                'last_login_at' => now(),
                'last_login_ip' => $request->ip(),
            ]);

            return redirect()->intended(route('portal.dashboard'))
                ->with('success', 'Welcome back to your customer portal!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Handle portal logout.
     */
    public function logout(Request $request)
    {
        Auth::guard('portal')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Show the portal registration form.
     */
    public function showRegistrationForm()
    {
        return view('portal.auth.register');
    }

    /**
     * Handle portal registration.
     */
    public function register(Request $request)
    {
        $request->validate([
            'customer_email' => 'required|email',
            'customer_phone' => 'required',
            'password' => 'required|confirmed|min:8',
            'terms' => 'required|accepted',
        ]);

        // Find customer by email or phone
        $customer = Customer::where('email', $request->customer_email)
            ->orWhere('phone', $request->customer_phone)
            ->first();

        if (!$customer) {
            return back()->withErrors([
                'customer_email' => 'No customer found with this email or phone. Please contact the shop to register.',
            ])->withInput();
        }

        // Check if portal user already exists
        if (PortalUser::where('customer_id', $customer->id)->exists()) {
            return back()->withErrors([
                'customer_email' => 'A portal account already exists for this customer. Please login instead.',
            ])->withInput();
        }

        // Create portal user
        $portalUser = PortalUser::create([
            'customer_id' => $customer->id,
            'email' => $request->customer_email,
            'password' => Hash::make($request->password),
            'verification_token' => Str::random(60),
            'is_active' => true,
        ]);

        // Auto-login the user
        Auth::guard('portal')->login($portalUser);

        return redirect()->route('portal.dashboard')
            ->with('success', 'Your customer portal account has been created successfully!');
    }

    /**
     * Show portal dashboard.
     */
    public function dashboard()
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        // Get recent data
        $recentAppointments = $customer->appointments()
            ->with(['vehicle', 'technician'])
            ->orderBy('appointment_date', 'desc')
            ->limit(5)
            ->get();
            
        $recentWorkOrders = $customer->workOrders()
            ->with(['vehicle', 'technician'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        $recentInspections = $customer->vehicleInspections()
            ->with(['vehicle', 'technician'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        $unreadMessages = 0; // Placeholder for now
        $pendingServiceRequests = 0; // Placeholder for now
        $sharedDocuments = 0; // Placeholder for now

        return view('portal.dashboard', compact(
            'customer',
            'recentAppointments',
            'recentWorkOrders',
            'recentInspections',
            'unreadMessages',
            'pendingServiceRequests',
            'sharedDocuments'
        ));
    }

    /**
     * Show customer profile.
     */
    public function profile()
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        return view('portal.profile', compact('customer'));
    }

    /**
     * Update customer profile.
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|email|unique:customers,email,' . $customer->id,
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:50',
            'zip_code' => 'nullable|string|max:20',
        ]);
        
        $customer->update($validated);
        
        // Update portal user email if changed
        if ($customer->email !== $user->email) {
            $user->update(['email' => $customer->email]);
        }
        
        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Show customer vehicles.
     */
    public function vehicles()
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        $vehicles = $customer->vehicles()->with(['serviceRecords'])->get();
        
        return view('portal.vehicles.index', compact('vehicles', 'customer'));
    }

    /**
     * Show vehicle details.
     */
    public function showVehicle($id)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $vehicle = $customer->vehicles()->findOrFail($id);
        $vehicle->load(['serviceRecords', 'appointments', 'workOrders', 'inspections']);
        
        return view('portal.vehicles.show', compact('vehicle'));
    }

    /**
     * Show customer appointments.
     */
    public function appointments()
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $appointments = $customer->appointments()
            ->with(['vehicle', 'technician'])
            ->orderBy('appointment_date', 'desc')
            ->paginate(20);
        
        return view('portal.appointments.index', compact('appointments', 'customer'));
    }

    /**
     * Show appointment details.
     */
    public function showAppointment($id)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $appointment = $customer->appointments()->findOrFail($id);
        $appointment->load(['vehicle', 'technician', 'workOrder']);
        
        return view('portal.appointments.show', compact('appointment'));
    }

    /**
     * Show customer work orders.
     */
    public function workOrders()
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $workOrders = $customer->workOrders()
            ->with(['vehicle', 'technician'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('portal.work-orders.index', compact('workOrders', 'customer'));
    }

    /**
     * Show work order details.
     */
    public function showWorkOrder($id)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $workOrder = $customer->workOrders()->findOrFail($id);
        $workOrder->load(['vehicle', 'technician', 'items', 'tasks', 'appointment']);
        
        return view('portal.work-orders.show', compact('workOrder'));
    }

    /**
     * Show customer inspections.
     */
    public function inspections()
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $inspections = $customer->vehicleInspections()
            ->with(['vehicle', 'technician'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('portal.inspections.index', compact('inspections', 'customer'));
    }

    /**
     * Show inspection details.
     */
    public function showInspection($id)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $inspection = $customer->vehicleInspections()->findOrFail($id);
        $inspection->load(['vehicle', 'technician', 'items', 'template']);
        
        return view('portal.inspections.show', compact('inspection'));
    }

    /**
     * Show customer documents.
     */
    public function documents()
    {
        $user = Auth::guard('portal')->user();
        
        $documents = []; // Placeholder for now
        
        return view('portal.documents.index', compact('documents'));
    }

    /**
     * Show customer messages.
     */
    public function messages()
    {
        $user = Auth::guard('portal')->user();
        
        $messages = []; // Placeholder for now
        
        return view('portal.messages.index', compact('messages'));
    }

    /**
     * Show customer service requests.
     */
    public function serviceRequests()
    {
        $user = Auth::guard('portal')->user();
        
        $serviceRequests = []; // Placeholder for now
        
        return view('portal.service-requests.index', compact('serviceRequests'));
    }

    /**
     * Show customer loyalty program.
     */
    public function loyalty()
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $loyaltyPoints = $customer->loyaltyPoints()->orderBy('created_at', 'desc')->paginate(20);
        $currentBalance = $customer->loyaltyPoints()->sum('points');
        
        return view('portal.loyalty.index', compact('loyaltyPoints', 'currentBalance'));
    }

    /**
     * Redeem loyalty points.
     */
    public function redeemLoyaltyPoints(Request $request)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $request->validate([
            'points' => 'required|integer|min:100',
            'reward_type' => 'required|in:discount,certificate,product',
        ]);
        
        $currentBalance = $customer->loyaltyPoints()->sum('points');
        
        if ($request->points > $currentBalance) {
            return back()->withErrors(['points' => 'Insufficient loyalty points.']);
        }
        
        // Create redemption transaction
        $customer->loyaltyPoints()->create([
            'transaction_type' => 'redeemed',
            'points' => -$request->points,
            'description' => 'Redeemed for ' . $request->reward_type,
            'balance_after' => $currentBalance - $request->points,
        ]);
        
        // TODO: Process reward based on type
        
        return redirect()->route('portal.loyalty')
            ->with('success', 'Loyalty points redeemed successfully!');
    }

    /**
     * Show loyalty rewards.
     */
    public function loyaltyRewards()
    {
        $rewards = [
            ['points' => 100, 'reward' => '$10 Service Credit', 'type' => 'discount'],
            ['points' => 250, 'reward' => 'Free Oil Change', 'type' => 'service'],
            ['points' => 500, 'reward' => 'Free Tire Rotation', 'type' => 'service'],
            ['points' => 1000, 'reward' => '$100 Gift Certificate', 'type' => 'certificate'],
        ];
        
        return view('portal.loyalty.rewards', compact('rewards'));
    }

    /**
     * Show customer billing.
     */
    public function billing()
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        return view('portal.billing.index', compact('customer'));
    }

    /**
     * Show customer invoices.
     */
    public function invoices()
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $invoices = $customer->invoices()
            ->with(['workOrder', 'vehicle'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('portal.billing.invoices', compact('invoices', 'customer'));
    }

    /**
     * Show invoice details.
     */
    public function showInvoice($id)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $invoice = $customer->invoices()->findOrFail($id);
        $invoice->load(['workOrder', 'vehicle', 'items', 'payments']);
        
        return view('portal.billing.invoice-show', compact('invoice'));
    }

    /**
     * Pay invoice.
     */
    public function payInvoice(Request $request, $id)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $invoice = $customer->invoices()->findOrFail($id);
        
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->balance_due,
            'payment_method' => 'required|in:credit_card,debit_card,bank_transfer',
            'card_number' => 'required_if:payment_method,credit_card,debit_card',
            'expiry_date' => 'required_if:payment_method,credit_card,debit_card',
            'cvv' => 'required_if:payment_method,credit_card,debit_card',
        ]);
        
        // Create payment record
        $payment = $invoice->payments()->create([
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'transaction_id' => 'PORTAL-' . time() . '-' . $invoice->id,
            'status' => 'completed',
            'notes' => 'Paid via customer portal',
        ]);
        
        // Update invoice status
        $invoice->update([
            'amount_paid' => $invoice->amount_paid + $request->amount,
            'balance_due' => $invoice->balance_due - $request->amount,
            'status' => $invoice->balance_due - $request->amount <= 0 ? 'paid' : 'partial',
        ]);
        
        return redirect()->route('portal.invoices.show', $invoice)
            ->with('success', 'Payment processed successfully!');
    }

    /**
     * Show payment methods.
     */
    public function paymentMethods()
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $paymentMethods = $customer->paymentMethods()->where('is_active', true)->get();
        
        return view('portal.billing.payment-methods', compact('paymentMethods', 'customer'));
    }

    /**
     * Store payment method.
     */
    public function storePaymentMethod(Request $request)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $request->validate([
            'card_type' => 'required|in:visa,mastercard,amex,discover',
            'card_number' => 'required|digits:16',
            'expiry_month' => 'required|digits:2|between:1,12',
            'expiry_year' => 'required|digits:4|min:' . date('Y'),
            'card_holder' => 'required|string|max:100',
            'is_default' => 'boolean',
        ]);
        
        // Mask card number for storage
        $maskedNumber = '****-****-****-' . substr($request->card_number, -4);
        
        $paymentMethod = $customer->paymentMethods()->create([
            'card_type' => $request->card_type,
            'card_number' => $maskedNumber,
            'expiry_month' => $request->expiry_month,
            'expiry_year' => $request->expiry_year,
            'card_holder' => $request->card_holder,
            'is_default' => $request->has('is_default'),
            'is_active' => true,
        ]);
        
        // If this is set as default, update others
        if ($request->has('is_default')) {
            $customer->paymentMethods()
                ->where('id', '!=', $paymentMethod->id)
                ->update(['is_default' => false]);
        }
        
        return redirect()->route('portal.payment-methods')
            ->with('success', 'Payment method added successfully!');
    }

    /**
     * Delete payment method.
     */
    public function deletePaymentMethod($id)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $paymentMethod = $customer->paymentMethods()->findOrFail($id);
        
        // Don't allow deletion if it's the only payment method
        if ($customer->paymentMethods()->count() <= 1) {
            return back()->withErrors(['error' => 'Cannot delete the only payment method.']);
        }
        
        $paymentMethod->delete();
        
        return redirect()->route('portal.payment-methods')
            ->with('success', 'Payment method deleted successfully!');
    }

    /**
     * Show document details.
     */
    public function showDocument($id)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $document = $customer->portalDocuments()
            ->where('is_shared', true)
            ->findOrFail($id);
        
        // Mark as viewed
        if (!$document->viewed_at) {
            $document->update(['viewed_at' => now()]);
        }
        
        return view('portal.documents.show', compact('document'));
    }

    /**
     * Download document.
     */
    public function downloadDocument($id)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $document = $customer->portalDocuments()
            ->where('is_shared', true)
            ->findOrFail($id);
        
        // Mark as downloaded
        if (!$document->downloaded_at) {
            $document->update(['downloaded_at' => now()]);
        }
        
        $path = storage_path('app/' . $document->file_path);
        
        if (!file_exists($path)) {
            abort(404, 'File not found.');
        }
        
        return response()->download($path, $document->file_name);
    }

    /**
     * Show message details.
     */
    public function showMessage($id)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $message = $customer->portalMessages()->findOrFail($id);
        
        // Mark as read
        if (!$message->is_read) {
            $message->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
        
        return view('portal.messages.show', compact('message'));
    }

    /**
     * Mark message as read.
     */
    public function markMessageAsRead($id)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $message = $customer->portalMessages()->findOrFail($id);
        
        if (!$message->is_read) {
            $message->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }
        
        return response()->json(['success' => true]);
    }

    /**
     * Send message to shop.
     */
    public function sendMessage(Request $request)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $request->validate([
            'subject' => 'required|string|max:200',
            'message' => 'required|string',
            'message_type' => 'required|in:question,service_inquiry,appointment_request,general',
        ]);
        
        $message = $customer->portalMessages()->create([
            'subject' => $request->subject,
            'message' => $request->message,
            'message_type' => $request->message_type,
            'is_read' => false,
            'requires_action' => in_array($request->message_type, ['appointment_request', 'service_inquiry']),
            'action_type' => $request->message_type === 'appointment_request' ? 'schedule_appointment' : null,
        ]);
        
        // TODO: Send notification to shop staff
        
        return redirect()->route('portal.messages.show', $message)
            ->with('success', 'Message sent successfully!');
    }

    /**
     * Show service request details.
     */
    public function showServiceRequest($id)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $serviceRequest = $customer->portalServiceRequests()
            ->with(['vehicle'])
            ->findOrFail($id);
        
        return view('portal.service-requests.show', compact('serviceRequest'));
    }

    /**
     * Create service request.
     */
    public function createServiceRequest()
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $vehicles = $customer->vehicles()->get();
        
        return view('portal.service-requests.create', compact('vehicles', 'customer'));
    }

    /**
     * Store service request.
     */
    public function storeServiceRequest(Request $request)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $request->validate([
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'request_type' => 'required|in:maintenance,repair,diagnostic,quote,other',
            'description' => 'required|string',
            'urgency' => 'required|in:routine,soon,urgent',
            'preferred_date' => 'nullable|date',
            'preferred_time' => 'nullable|string',
        ]);
        
        $serviceRequest = $customer->portalServiceRequests()->create([
            'vehicle_id' => $request->vehicle_id,
            'request_type' => $request->request_type,
            'description' => $request->description,
            'urgency' => $request->urgency,
            'preferred_date' => $request->preferred_date,
            'preferred_time' => $request->preferred_time,
            'status' => 'pending',
        ]);
        
        // TODO: Send notification to shop staff
        
        return redirect()->route('portal.service-requests.show', $serviceRequest)
            ->with('success', 'Service request submitted successfully!');
    }

    /**
     * Cancel service request.
     */
    public function cancelServiceRequest($id)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $serviceRequest = $customer->portalServiceRequests()->findOrFail($id);
        
        if ($serviceRequest->status !== 'pending') {
            return back()->withErrors(['error' => 'Only pending service requests can be cancelled.']);
        }
        
        $serviceRequest->update([
            'status' => 'cancelled',
            'completed_at' => now(),
        ]);
        
        return redirect()->route('portal.service-requests.show', $serviceRequest)
            ->with('success', 'Service request cancelled successfully!');
    }

    /**
     * Show customer reviews.
     */
    public function reviews()
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $reviews = $customer->portalReviews()
            ->with(['appointment', 'workOrder'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('portal.reviews.index', compact('reviews', 'customer'));
    }

    /**
     * Create review.
     */
    public function createReview()
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        // Get recent appointments and work orders that can be reviewed
        $recentAppointments = $customer->appointments()
            ->where('status', 'completed')
            ->whereDoesntHave('portalReviews')
            ->orderBy('appointment_date', 'desc')
            ->limit(10)
            ->get();
        
        $recentWorkOrders = $customer->workOrders()
            ->where('status', 'completed')
            ->whereDoesntHave('portalReviews')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return view('portal.reviews.create', compact('recentAppointments', 'recentWorkOrders', 'customer'));
    }

    /**
     * Store review.
     */
    public function storeReview(Request $request)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $request->validate([
            'rating' => 'required|integer|between:1,5',
            'review_text' => 'nullable|string|max:1000',
            'appointment_id' => 'nullable|exists:appointments,id',
            'work_order_id' => 'nullable|exists:work_orders,id',
            'is_anonymous' => 'boolean',
        ]);
        
        // Check if review already exists for this appointment/work order
        if ($request->appointment_id) {
            $existingReview = $customer->portalReviews()
                ->where('appointment_id', $request->appointment_id)
                ->first();
            
            if ($existingReview) {
                return back()->withErrors(['error' => 'You have already reviewed this appointment.']);
            }
        }
        
        if ($request->work_order_id) {
            $existingReview = $customer->portalReviews()
                ->where('work_order_id', $request->work_order_id)
                ->first();
            
            if ($existingReview) {
                return back()->withErrors(['error' => 'You have already reviewed this work order.']);
            }
        }
        
        $review = $customer->portalReviews()->create([
            'appointment_id' => $request->appointment_id,
            'work_order_id' => $request->work_order_id,
            'rating' => $request->rating,
            'review_text' => $request->review_text,
            'review_ratings' => json_encode([
                'quality' => $request->quality_rating ?? $request->rating,
                'timeliness' => $request->timeliness_rating ?? $request->rating,
                'communication' => $request->communication_rating ?? $request->rating,
                'value' => $request->value_rating ?? $request->rating,
            ]),
            'is_anonymous' => $request->has('is_anonymous'),
            'is_approved' => false, // Needs admin approval
        ]);
        
        return redirect()->route('portal.reviews.show', $review)
            ->with('success', 'Review submitted successfully! It will be visible after approval.');
    }

    /**
     * Show customer preferences.
     */
    public function preferences()
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $preferences = $user->portalPreferences()->firstOrCreate([]);
        
        return view('portal.profile.preferences', compact('preferences', 'customer'));
    }

    /**
     * Update preferences.
     */
    public function updatePreferences(Request $request)
    {
        $user = Auth::guard('portal')->user();
        
        $request->validate([
            'notification_email_appointments' => 'required|in:immediate,24_hours,weekly,never',
            'notification_email_reminders' => 'required|in:immediate,24_hours,weekly,never',
            'notification_email_promotions' => 'required|in:immediate,24_hours,weekly,never',
            'notification_sms_appointments' => 'required|in:immediate,24_hours,weekly,never',
            'notification_sms_reminders' => 'required|in:immediate,24_hours,weekly,never',
            'notification_sms_promotions' => 'required|in:immediate,24_hours,weekly,never',
            'receive_service_reminders' => 'boolean',
            'receive_promotional_offers' => 'boolean',
            'receive_birthday_offers' => 'boolean',
            'receive_newsletter' => 'boolean',
            'preferred_contact_method' => 'required|in:email,sms,both',
            'preferred_communication_time' => 'required|in:morning,afternoon,evening,anytime',
        ]);
        
        $preferences = $user->portalPreferences()->firstOrCreate([]);
        $preferences->update($request->all());
        
        return redirect()->route('portal.profile.preferences')
            ->with('success', 'Preferences updated successfully!');
    }

    /**
     * Create appointment.
     */
    public function createAppointment()
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $vehicles = $customer->vehicles()->get();
        
        return view('portal.appointments.create', compact('vehicles', 'customer'));
    }

    /**
     * Store appointment.
     */
    public function storeAppointment(Request $request)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'service_type' => 'required|string|max:100',
            'appointment_date' => 'required|date|after:today',
            'appointment_time' => 'required|string',
            'description' => 'nullable|string|max:500',
            'urgency' => 'required|in:routine,urgent',
        ]);
        
        // Check if vehicle belongs to customer
        $vehicle = $customer->vehicles()->find($request->vehicle_id);
        if (!$vehicle) {
            return back()->withErrors(['vehicle_id' => 'Invalid vehicle selected.']);
        }
        
        $appointment = $customer->appointments()->create([
            'vehicle_id' => $request->vehicle_id,
            'service_type' => $request->service_type,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'description' => $request->description,
            'urgency' => $request->urgency,
            'status' => 'pending',
            'source' => 'portal',
        ]);
        
        // TODO: Send notification to shop staff
        
        return redirect()->route('portal.appointments.show', $appointment)
            ->with('success', 'Appointment requested successfully! We will confirm shortly.');
    }

    /**
     * Cancel appointment.
     */
    public function cancelAppointment($id)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $appointment = $customer->appointments()->findOrFail($id);
        
        if (!in_array($appointment->status, ['pending', 'scheduled'])) {
            return back()->withErrors(['error' => 'Only pending or scheduled appointments can be cancelled.']);
        }
        
        $appointment->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'Cancelled by customer via portal',
        ]);
        
        return redirect()->route('portal.appointments.show', $appointment)
            ->with('success', 'Appointment cancelled successfully!');
    }

    /**
     * Reschedule appointment.
     */
    public function rescheduleAppointment(Request $request, $id)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $appointment = $customer->appointments()->findOrFail($id);
        
        if (!in_array($appointment->status, ['pending', 'scheduled'])) {
            return back()->withErrors(['error' => 'Only pending or scheduled appointments can be rescheduled.']);
        }
        
        $request->validate([
            'appointment_date' => 'required|date|after:today',
            'appointment_time' => 'required|string',
            'reason' => 'nullable|string|max:200',
        ]);
        
        $appointment->update([
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'status' => 'rescheduled',
            'rescheduled_at' => now(),
            'reschedule_reason' => $request->reason,
        ]);
        
        return redirect()->route('portal.appointments.show', $appointment)
            ->with('success', 'Appointment rescheduled successfully!');
    }

    /**
     * Show vehicle service history.
     */
    public function vehicleServiceHistory($id)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $vehicle = $customer->vehicles()->findOrFail($id);
        
        $serviceHistory = [
            'appointments' => $vehicle->appointments()->orderBy('appointment_date', 'desc')->get(),
            'workOrders' => $vehicle->workOrders()->orderBy('created_at', 'desc')->get(),
            'inspections' => $vehicle->inspections()->orderBy('created_at', 'desc')->get(),
            'serviceRecords' => $vehicle->serviceRecords()->orderBy('service_date', 'desc')->get(),
        ];
        
        return view('portal.vehicles.service-history', compact('vehicle', 'serviceHistory'));
    }

    /**
     * Show inspection report.
     */
    public function inspectionReport($id)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $inspection = $customer->vehicleInspections()->findOrFail($id);
        $inspection->load(['vehicle', 'technician', 'items', 'template']);
        
        return view('portal.inspections.report', compact('inspection'));
    }

    /**
     * Approve inspection.
     */
    public function approveInspection($id)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $inspection = $customer->vehicleInspections()->findOrFail($id);
        
        if ($inspection->status !== 'completed') {
            return back()->withErrors(['error' => 'Only completed inspections can be approved.']);
        }
        
        $inspection->update([
            'customer_approved' => true,
            'customer_approved_at' => now(),
        ]);
        
        return redirect()->route('portal.inspections.show', $inspection)
            ->with('success', 'Inspection approved successfully!');
    }

    /**
     * Approve work order estimate.
     */
    public function approveWorkOrderEstimate($id)
    {
        $user = Auth::guard('portal')->user();
        $customer = $user->customer;
        
        $workOrder = $customer->workOrders()->findOrFail($id);
        
        if ($workOrder->status !== 'estimate_pending') {
            return back()->withErrors(['error' => 'Only estimates pending approval can be approved.']);
        }
        
        $workOrder->update([
            'status' => 'estimate_approved',
            'estimate_approved_at' => now(),
            'estimate_approved_by' => 'customer_portal',
        ]);
        
        return redirect()->route('portal.work-orders.show', $workOrder)
            ->with('success', 'Work order estimate approved successfully!');
    }

    /**
     * Show forgot password form.
     */
    public function showForgotPasswordForm()
    {
        return view('portal.auth.forgot-password');
    }

    /**
     * Send reset link email.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $portalUser = PortalUser::where('email', $request->email)->first();
        
        if ($portalUser) {
            // Generate reset token
            $token = Str::random(60);
            $portalUser->update(['verification_token' => $token]);
            
            // TODO: Send reset email
            // Mail::to($portalUser->email)->send(new PortalPasswordReset($token));
        }
        
        // Always return success to prevent email enumeration
        return back()->with('success', 'If an account exists with that email, a password reset link has been sent.');
    }

    /**
     * Show reset password form.
     */
    public function showResetPasswordForm($token)
    {
        $portalUser = PortalUser::where('verification_token', $token)->first();
        
        if (!$portalUser) {
            return redirect()->route('portal.login')
                ->withErrors(['error' => 'Invalid or expired reset token.']);
        }
        
        return view('portal.auth.reset-password', compact('token'));
    }

    /**
     * Reset password.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);
        
        $portalUser = PortalUser::where('email', $request->email)
            ->where('verification_token', $request->token)
            ->first();
        
        if (!$portalUser) {
            return back()->withErrors(['email' => 'Invalid token or email.']);
        }
        
        $portalUser->update([
            'password' => Hash::make($request->password),
            'verification_token' => null,
        ]);
        
        return redirect()->route('portal.login')
            ->with('success', 'Password reset successfully! Please login with your new password.');
    }

    /**
     * Verify email.
     */
    public function verifyEmail($token)
    {
        $portalUser = PortalUser::where('verification_token', $token)->first();
        
        if (!$portalUser) {
            return redirect()->route('portal.login')
                ->withErrors(['error' => 'Invalid verification token.']);
        }
        
        $portalUser->update([
            'email_verified_at' => now(),
            'verification_token' => null,
        ]);
        
        // Auto-login the user
        Auth::guard('portal')->login($portalUser);
        
        return redirect()->route('portal.dashboard')
            ->with('success', 'Email verified successfully! Welcome to your customer portal.');
    }
}
