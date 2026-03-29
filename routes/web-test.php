<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppointmentController;

// SIMPLE TEST ROUTE - NO MIDDLEWARE, NO GROUPS
Route::get('/test-appointments-simple', function() {
    return 'SIMPLE TEST: Working at ' . date('Y-m-d H:i:s');
});

// APPOINTMENTS RESOURCE ROUTE (SIMPLIFIED)
Route::resource('appointments-test', AppointmentController::class);

// ORIGINAL APPOINTMENTS ROUTE (EXACT COPY)
Route::get('/appointments-original', [AppointmentController::class, 'index'])->name('appointments.index');
