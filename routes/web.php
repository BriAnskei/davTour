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

// Redirect for /payments to appropriate role-based route
Route::get('/payments', function () {
    if (Auth::check()) {
        return Auth::user()->role === 'admin'
            ? redirect()->route('admin.payments.index')
            : redirect()->route('client.payments');
    }
    return redirect()->route('login');
})->middleware('auth');

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

        // Notifications
        Route::get('/notifications', [TourBookingController::class, 'getNotifications'])->name('notifications.index');
        Route::post('/notifications/mark-as-read', [TourBookingController::class, 'markNotificationsAsRead'])->name('notifications.markAsRead');

        // Booking
        Route::get('/booking',                [TourBookingController::class, 'create'])->name('booking.create');
        Route::post('/book',                  [TourBookingController::class, 'store'])->name('book');
        Route::get('/my-bookings',            [TourBookingController::class, 'myBookings'])->name('bookings');
        Route::get('/bookings/{id}',          [TourBookingController::class, 'show'])->name('bookings.show');
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
// ADMIN CONSOLIDATED
// ─────────────────────────────────────────
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TourController;
use App\Http\Controllers\TourScheduleController;
use App\Http\Controllers\TourImageController;
use App\Http\Controllers\UserController;

Route::middleware(['auth', 'can:isAdmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn() => redirect()->route('admin.dashboard'));
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Bookings
    Route::prefix('bookings')->name('bookings.')->group(function() {
        Route::get('/',               [AdminController::class, 'bookings'])->name('index');
        Route::get('/archive',        [AdminController::class, 'archivedBookings'])->name('archive');
        Route::patch('/{id}/archive', [AdminController::class, 'toggleBookingArchive'])->name('archive.toggle');
        Route::patch('/{id}/status',  [AdminController::class, 'updateBookingStatus'])->name('status');
        Route::get('/{id}/validate',  [AdminController::class, 'showValidationPage'])->name('validate');
        Route::post('/{id}/validate', [AdminController::class, 'validateSeniorBooking'])->name('validate.post');
    });

    // Tours
    Route::prefix('tours')->name('tours.')->group(function() {
        Route::get('/',                [TourController::class, 'index'])->name('index');
        Route::get('/archive',         [TourController::class, 'archivedTours'])->name('archive');
        Route::get('/create',          [TourController::class, 'create'])->name('create');
        Route::post('/',               [TourController::class, 'store'])->name('store');
        Route::get('/{id}',            [TourController::class, 'show'])->name('show');
        Route::get('/{id}/edit',       [TourController::class, 'edit'])->name('edit');
        Route::put('/{id}',            [TourController::class, 'update'])->name('update');
        Route::delete('/{id}',         [TourController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/toggle',   [TourController::class, 'toggle'])->name('toggle');
        Route::patch('/{id}/archive',  [TourController::class, 'toggleArchive'])->name('archive.toggle');
    });

    // Tour Images
    Route::delete('/tour-images/{id}', [TourImageController::class, 'destroy'])->name('tour_images.destroy');

    // Tour Schedules
    Route::prefix('tour-schedules')->name('tour_schedules.')->group(function() {
        Route::get('/',               [TourScheduleController::class, 'index'])->name('index');
        Route::get('/archive',        [TourScheduleController::class, 'archivedSchedules'])->name('archive');
        Route::get('/create',         [TourScheduleController::class, 'create'])->name('create');
        Route::post('/',              [TourScheduleController::class, 'store'])->name('store');
        Route::get('/{id}',           [TourScheduleController::class, 'show'])->name('show');
        Route::get('/{id}/edit',      [TourScheduleController::class, 'edit'])->name('edit');
        Route::put('/{id}',           [TourScheduleController::class, 'update'])->name('update');
        Route::delete('/{id}',        [TourScheduleController::class, 'destroy'])->name('destroy');
        Route::patch('/{id}/archive', [TourScheduleController::class, 'toggleArchive'])->name('archive.toggle');
    });

    // Payments
    Route::prefix('payments')->name('payments.')->group(function() {
        Route::get('/',               [PaymentController::class, 'index'])->name('index');
        Route::get('/archive',        [PaymentController::class, 'archivedPayments'])->name('archive');
        Route::patch('/{id}/archive', [PaymentController::class, 'toggleArchive'])->name('archive.toggle');
        Route::delete('/{id}',        [PaymentController::class, 'destroy'])->name('destroy');
        Route::get('/export-pdf',     [PaymentController::class, 'exportPdf'])->name('export_pdf');
    });

    // Users
    Route::prefix('users')->name('users.')->group(function() {
        Route::get('/',        [AdminController::class, 'users'])->name('index');
        Route::post('/',       [UserController::class, 'store'])->name('store');
        Route::get('/{id}',    [UserController::class, 'show'])->name('show');
        Route::put('/{id}',    [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');
    });

    Route::get('/audit-logs/{type}',      [AdminController::class, 'getAuditLogs'])->name('audit_logs');

    // Notifications
    Route::get('/notifications', [AdminController::class, 'getNotifications'])->name('notifications.index');
    Route::post('/notifications/mark-as-read', [AdminController::class, 'markNotificationsAsRead'])->name('notifications.markAsRead');
});
