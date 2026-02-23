<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PortalAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated via portal guard
        if (!Auth::guard('portal')->check()) {
            return redirect()->route('portal.login')->with('error', 'Please login to access the customer portal.');
        }

        // Check if portal user is active
        $user = Auth::guard('portal')->user();
        if (!$user->is_active) {
            Auth::guard('portal')->logout();
            return redirect()->route('portal.login')->with('error', 'Your account has been deactivated. Please contact support.');
        }

        // Update last activity
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        return $next($request);
    }
}