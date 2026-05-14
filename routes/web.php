<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// ─────────────────────────────────────────
// ROOT
// ─────────────────────────────────────────
Route::get('/', function () {
    if (Auth::check()) {
        return Auth::user()->role === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('client.index');
    }
    return redirect()->route('client.index');
});


// ─────────────────────────────────────────
// AUTH ROUTES — POST only (popup-driven)
// ─────────────────────────────────────────
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleController;

Route::middleware('guest')->group(function () {
    Route::post('/login',    [AuthController::class, 'login'])->name('login.post');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    
    // Google OAuth Routes
    Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('google.login');
    Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);
});

// Required by Laravel's auth middleware as the named 'login' route
Route::get('/login', fn() => redirect()->route('client.index'))->name('login');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');


// ─────────────────────────────────────────
// CLIENT — public + auth combined
// IMPORTANT: specific routes MUST come before /{id} wildcard
// ─────────────────────────────────────────
use App\Http\Controllers\ClientController;
use App\Http\Controllers\TourBookingController;
use App\Http\Controllers\PaymentController;

Route::prefix('client')->name('client.')->group(function () {

    // ── Public routes ──
    Route::get('/', [ClientController::class, 'index'])->name('index');

    // ── Auth-required routes (must be before /{id} wildcard) ──
    Route::middleware('auth')->group(function () {

        // Booking
        Route::get('/booking',                [TourBookingController::class, 'create'])->name('booking.create');
        Route::post('/book',                  [TourBookingController::class, 'store'])->name('book');
        Route::get('/my-bookings',            [TourBookingController::class, 'myBookings'])->name('bookings');
        Route::patch('/bookings/{id}/cancel', [TourBookingController::class, 'cancel'])->name('bookings.cancel');

        // Stripe Payment
        Route::post('/payment/checkout', [PaymentController::class, 'checkout'])->name('payment.checkout');
        Route::post('/payment/resume/{id}', [PaymentController::class, 'resume'])->name('payment.resume');
        Route::get('/payment/success',   [PaymentController::class, 'success'])->name('payment.success');
        Route::get('/payment/cancel',    [PaymentController::class, 'cancel'])->name('payment.cancel');

        // My Payments
        Route::get('/my-payments', [PaymentController::class, 'myPayments'])->name('payments');
    });

    // ── Wildcard — MUST be last inside this prefix group ──
    Route::get('/{id}', [ClientController::class, 'show'])->name('show');
});


// ─────────────────────────────────────────
// ADMIN
// ─────────────────────────────────────────
use App\Http\Controllers\AdminController;

Route::middleware(['auth', 'can:isAdmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn() => redirect()->route('admin.dashboard'));
    Route::get('/dashboard',              [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/bookings',               [AdminController::class, 'bookings'])->name('bookings');
    Route::patch('/bookings/{id}/status', [AdminController::class, 'updateBookingStatus'])->name('bookings.status');
    Route::get('/users',                  [AdminController::class, 'users'])->name('users');
    Route::get('/audit-logs/{type}',      [AdminController::class, 'getAuditLogs'])->name('audit_logs');

    // Notifications
    Route::get('/notifications', [AdminController::class, 'getNotifications'])->name('notifications.index');
    Route::post('/notifications/mark-as-read', [AdminController::class, 'markNotificationsAsRead'])->name('notifications.markAsRead');

    // Senior Validation
    Route::get('/bookings/{id}/validate', [AdminController::class, 'showValidationPage'])->name('bookings.validate');
    Route::post('/bookings/{id}/validate', [AdminController::class, 'validateSeniorBooking'])->name('bookings.validate.post');
});


// ─────────────────────────────────────────
// TOURS — admin CRUD
// Note: /create must come before /{id}
// ─────────────────────────────────────────
use App\Http\Controllers\TourController;

Route::middleware(['auth', 'can:isAdmin'])->group(function () {
    Route::get('/tours/create',        [TourController::class, 'create'])->name('tours.create');
    Route::post('/tours',              [TourController::class, 'store'])->name('tours.store');
    Route::get('/tours/{id}/edit',     [TourController::class, 'edit'])->name('tours.edit');
    Route::put('/tours/{id}',          [TourController::class, 'update'])->name('tours.update');
    Route::delete('/tours/{id}',       [TourController::class, 'destroy'])->name('tours.destroy');
    Route::patch('/tours/{id}/toggle', [TourController::class, 'toggle'])->name('tours.toggle');
    Route::get('/tours',               [TourController::class, 'index'])->name('tours.index');
    Route::get('/tours/{id}',          [TourController::class, 'show'])->name('tours.show');
});


// ─────────────────────────────────────────
// TOUR IMAGES
// ─────────────────────────────────────────
use App\Http\Controllers\TourImageController;

Route::middleware(['auth', 'can:isAdmin'])->group(function () {
    Route::delete('/tour-images/{id}', [TourImageController::class, 'destroy'])->name('tour_images.destroy');
});


// ─────────────────────────────────────────
// TOUR SCHEDULES
// Note: /create must come before /{id}
// ─────────────────────────────────────────
use App\Http\Controllers\TourScheduleController;

Route::middleware(['auth', 'can:isAdmin'])->group(function () {
    Route::get('/tour-schedules/create',    [TourScheduleController::class, 'create'])->name('tour_schedules.create');
    Route::post('/tour-schedules',          [TourScheduleController::class, 'store'])->name('tour_schedules.store');
    Route::get('/tour-schedules/{id}/edit', [TourScheduleController::class, 'edit'])->name('tour_schedules.edit');
    Route::put('/tour-schedules/{id}',      [TourScheduleController::class, 'update'])->name('tour_schedules.update');
    Route::delete('/tour-schedules/{id}',   [TourScheduleController::class, 'destroy'])->name('tour_schedules.destroy');
    Route::get('/tour-schedules',           [TourScheduleController::class, 'index'])->name('tour_schedules.index');
    Route::get('/tour-schedules/{id}',      [TourScheduleController::class, 'show'])->name('tour_schedules.show');
});


// ─────────────────────────────────────────
// PAYMENTS — admin view only
// ─────────────────────────────────────────
Route::middleware(['auth', 'can:isAdmin'])->group(function () {
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
});


// ─────────────────────────────────────────
// USERS — admin
// ─────────────────────────────────────────
use App\Http\Controllers\UserController;

Route::middleware(['auth', 'can:isAdmin'])->prefix('users')->group(function () {
    Route::get('/',        [UserController::class, 'index']);
    Route::get('/{id}',    [UserController::class, 'show']);
    Route::post('/',       [UserController::class, 'store']);
    Route::put('/{id}',    [UserController::class, 'update']);
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('users.destroy');
});