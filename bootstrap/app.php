<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register aliases
        $middleware->alias([
            'portal.auth' => \App\Http\Middleware\PortalAuth::class,
            'booking.auth' => \App\Http\Middleware\BookingAuth::class,
            'cors' => \App\Http\Middleware\Cors::class,
        ]);

        // Exempt booking API routes from CSRF for cross-origin requests
        $middleware->validateCsrfTokens(except: [
            '/quotation-submit',
            '/booking/api/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
