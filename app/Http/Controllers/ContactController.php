<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Admin Inbox — list of Contact Us lead messages.
     */
    public function index(Request $request)
    {
        $query = ContactMessage::query();

        if ($q = $request->get('q')) {
            $query->where(function ($b) use ($q) {
                $b->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('subject', 'like', "%{$q}%")
                  ->orWhere('message', 'like', "%{$q}%");
            });
        }

        $messages = $query->orderByDesc('created_at')->paginate(25);

        return view('inbox.index', compact('messages'))->with('q', $q);
    }

    /**
     * Mark a contact message as read.
     */
    public function markRead(ContactMessage $contactMessage)
    {
        $contactMessage->update(['is_read' => true]);
        return back()->with('success', 'Message marked as read.');
    }

    /**
     * Public contact form -> save lead to app DB.
     * Server-side sanitization: required fields validated + trimmed.
     */
    public function store(Request $request)
    {
        // Honeypot spam protection: bots fill hidden fields. If the hidden
        // 'website' field is populated, silently accept but discard (no save,
        // no admin email) so we don't leak that a bot was detected.
        if (!empty($request->input('website'))) {
            return response()->json(['success' => true]);
        }

        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'email'   => ['required', 'email', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $message = ContactMessage::create([
            'name'    => trim($validated['name']),
            'email'   => trim($validated['email']),
            'phone'   => isset($validated['phone']) ? trim($validated['phone']) : null,
            'subject' => isset($validated['subject']) ? trim($validated['subject']) : null,
            'message' => trim($validated['message']),
        ]);

        // When the static site (contact-send.php) forwards a submission it already
        // emailed admins via Brevo — skip the duplicate email, but still persist
        // the lead and notify staff in-app (navbar bell).
        $skipEmail = $request->boolean('skip_email');

        if (!$skipEmail) {
            // Admin email notification on each new contact submission (Brevo flow,
            // same as the appointment new-booking notification).
            foreach (config('mail.admin_recipients', []) as $adminEmail) {
                \Illuminate\Support\Facades\Mail::to($adminEmail)
                    ->send(new \App\Mail\NewContactAdminNotification($message));
            }
        }

        // In-app staff notification (admin navbar bell) for new contact messages.
        try {
            $staff = \App\Models\User::whereIn('role', ['super_admin', 'admin', 'office_staff'])->get();
            if ($staff->isNotEmpty()) {
                \Illuminate\Support\Facades\Notification::send(
                    $staff,
                    new \App\Notifications\NewContactMessageNotification($message)
                );
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Contact staff notification failed: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'id' => $message->id]);
    }
}
