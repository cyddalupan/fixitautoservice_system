<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class BookingAuth
{
    /**
     * Handle an incoming request.
     *
     * Accepts either:
     * - Authenticated portal user (email/password login)
     * - Session-based customer ID (magic link token)
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check portal guard first
        if (Auth::guard('portal')->check()) {
            $user = Auth::guard('portal')->user();
            if (!$user->is_active) {
                Auth::guard('portal')->logout();
                return redirect()->route('booking.login')
                    ->with('error', 'Your account has been deactivated.');
            }
            return $next($request);
        }

        // Check session-based auth (magic link)
        if ($request->session()->has('booking_customer_id')) {
            return $next($request);
        }

        // Not authenticated
        return redirect()->route('booking.login')
            ->with('error', 'Please log in to access your bookings.');
    }
}
