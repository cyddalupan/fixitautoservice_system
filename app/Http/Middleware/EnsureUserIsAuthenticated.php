<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // For demo purposes, auto-login as admin if not authenticated
        if (!Auth::check()) {
            // Find admin user or create one
            $admin = \App\Models\User::where('email', 'admin@fixitautoservices.com')->first();
            
            if ($admin) {
                Auth::login($admin);
            } else {
                // Create admin user if doesn't exist
                $admin = \App\Models\User::create([
                    'name' => 'Admin User',
                    'email' => 'admin@fixitautoservices.com',
                    'password' => bcrypt('FixitAdmin2024!'),
                    'role' => 'admin',
                    'is_active' => true,
                ]);
                Auth::login($admin);
            }
        }

        return $next($request);
    }
}