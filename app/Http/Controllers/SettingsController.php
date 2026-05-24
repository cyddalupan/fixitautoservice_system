<?php

namespace App\Http\Controllers;

use App\Models\SmtpSetting;
use App\Models\MagicLinkLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $smtp = SmtpSetting::first();
        $magicLinkLogs = MagicLinkLog::with('customer')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('settings.index', [
            'smtp' => $smtp,
            'magicLinkLogs' => $magicLinkLogs,
        ]);
    }

    /**
     * GET /api/settings/smtp — retrieve current SMTP config (without password).
     */
    public function getSmtp()
    {
        $smtp = SmtpSetting::first();

        if (!$smtp) {
            return response()->json([
                'mail_host' => 'smtp.gmail.com',
                'mail_port' => 587,
                'mail_from_address' => 'noreply@app.fixitautoservices.com',
                'mail_from_name' => 'Fix-It Auto Services',
                'mail_username' => '',
                'has_password' => false,
                'mail_encryption' => 'tls',
                'is_active' => false,
            ]);
        }

        return response()->json([
            'id' => $smtp->id,
            'mail_host' => $smtp->mail_host,
            'mail_port' => $smtp->mail_port,
            'mail_from_address' => $smtp->mail_from_address,
            'mail_from_name' => $smtp->mail_from_name,
            'mail_username' => $smtp->mail_username,
            'has_password' => $smtp->hasPassword(),
            'mail_encryption' => $smtp->mail_encryption,
            'is_active' => $smtp->is_active,
        ]);
    }

    /**
     * POST /api/settings/smtp — save/update SMTP config.
     */
    public function saveSmtp(Request $request)
    {
        $validated = $request->validate([
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer|min:1|max:65535',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:500',
            'mail_encryption' => ['required', Rule::in(['tls', 'ssl', 'none'])],
            'is_active' => 'boolean',
        ]);

        $smtp = SmtpSetting::first();

        if (!$smtp) {
            $smtp = new SmtpSetting();
        }

        $smtp->mail_host = $validated['mail_host'];
        $smtp->mail_port = $validated['mail_port'];
        $smtp->mail_from_address = $validated['mail_from_address'];
        $smtp->mail_from_name = $validated['mail_from_name'];
        $smtp->mail_username = $validated['mail_username'] ?? null;
        $smtp->mail_encryption = $validated['mail_encryption'];
        $smtp->is_active = $request->boolean('is_active');

        // Only update password if a new one was submitted
        if (!empty($validated['mail_password'])) {
            $smtp->setPasswordAttribute($validated['mail_password']);
        }

        $smtp->save();

        Log::info('SMTP settings updated by user: ' . (Auth::user()->email ?? 'unknown'));

        return response()->json([
            'success' => true,
            'message' => 'SMTP settings saved successfully.',
            'data' => [
                'id' => $smtp->id,
                'mail_host' => $smtp->mail_host,
                'mail_port' => $smtp->mail_port,
                'mail_from_address' => $smtp->mail_from_address,
                'mail_from_name' => $smtp->mail_from_name,
                'mail_username' => $smtp->mail_username,
                'has_password' => $smtp->hasPassword(),
                'mail_encryption' => $smtp->mail_encryption,
                'is_active' => $smtp->is_active,
            ],
        ]);
    }

    /**
     * POST /api/settings/test-email — send a test email using current SMTP config.
     */
    public function sendTestEmail(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        $smtp = SmtpSetting::first();

        if (!$smtp || !$smtp->is_active || !$smtp->hasPassword()) {
            return response()->json([
                'success' => false,
                'message' => 'SMTP is not configured or not active. Please save and activate SMTP settings first.',
            ], 422);
        }

        // Apply SMTP config to Laravel mail system
        $smtp->applyMailConfig();

        try {
            Mail::raw(
                "This is a test email from Fix-It Auto Services.\n\nYour SMTP configuration is working correctly.\n\nSent at: " . now()->format('Y-m-d H:i:s'),
                function ($message) use ($request) {
                    $message->to($request->test_email)
                        ->subject('Test Email — Fix-It Auto Services SMTP');
                }
            );

            Log::info('Test email sent successfully to: ' . $request->test_email . ' by user: ' . (Auth::user()->email ?? 'unknown'));

            return response()->json([
                'success' => true,
                'message' => 'Test email sent successfully to ' . $request->test_email . '.',
            ]);
        } catch (\Exception $e) {
            Log::error('Test email failed to: ' . $request->test_email . ' - Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send test email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/magic-link/logs — retrieve magic link request logs with pagination.
     */
    public function getMagicLinkLogs(Request $request)
    {
        $query = MagicLinkLog::with('customer');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search by email
        if ($request->filled('search')) {
            $query->where('email', 'like', '%' . $request->search . '%');
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 20));

        return response()->json($logs);
    }

    /**
     * POST /api/magic-link/logs — create a new magic link log entry (for the BookingController).
     */
    public function saveMagicLinkLog(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'email' => 'required|email',
            'token' => 'required|string|max:100',
            'status' => 'required|in:success,failed',
            'error_message' => 'nullable|string|max:1000',
        ]);

        $log = MagicLinkLog::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Magic link request logged.',
            'data' => $log,
        ]);
    }
}
