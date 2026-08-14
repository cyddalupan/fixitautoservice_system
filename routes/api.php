<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Booking Public API Routes (loaded under /api prefix automatically)
|--------------------------------------------------------------------------
|
| These are called via reverse proxy from fixitautoservices.com.
| The proxy forwards /booking/api/* -> app.fixitautoservices.com/api/booking/*
|
*/

Route::prefix('booking')->group(function () {

    // ===== No-auth endpoints =====
    Route::get('/available-slots', [BookingController::class, 'getAvailableSlots']);

    // Vehicle brand/model autocomplete for the public booking form
    // (fixitautoservices.com/booking/ static form)
    Route::get('/vehicle-brands', [BookingController::class, 'apiVehicleBrands']);
    Route::get('/vehicle-models', [BookingController::class, 'apiVehicleModels']);

    // ===== Auth endpoints =====
    Route::post('/login', [BookingController::class, 'apiLogin']);
    Route::post('/register', [BookingController::class, 'apiRegister']);
    Route::post('/logout', [BookingController::class, 'apiLogout']);
    Route::post('/magic', [BookingController::class, 'apiRequestMagicLink']);
    Route::post('/magic-verify', [BookingController::class, 'apiMagicVerify']);

    // ===== Customer endpoints =====
    Route::get('/customer-data', [BookingController::class, 'apiCustomerData']);
    Route::get('/vehicles', [BookingController::class, 'getVehicles']);
    Route::post('/customer-booking', [BookingController::class, 'apiCreateBooking']);
    Route::get('/appointments', [BookingController::class, 'apiCustomerAppointments']);

    // ===== Customer Appointment Tracking =====
    Route::get('/track/{appointment}', [BookingController::class, 'apiTrackAppointment']);
    Route::get('/track/{appointment}/messages', [BookingController::class, 'apiTrackAppointmentMessages']);
    Route::post('/track/{appointment}/proceed', [BookingController::class, 'apiProceedAppointment']);
    Route::post('/track/{appointment}/cancel', [BookingController::class, 'apiCancelAppointment']);
    Route::post('/track/{appointment}/reschedule', [BookingController::class, 'apiRescheduleAppointment']);
});

// ===== Contact Us public lead endpoint (posts from fixitautoservices.com) =====
Route::post('/contact/save', [ContactController::class, 'store']);
