<?php

use App\Http\Controllers\AdminAppointmentController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminCustomerController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\AdminServiceController;
use App\Http\Controllers\AdminSubscriptionController;
use App\Http\Controllers\AdminTechnicianController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TechnicianAppointmentController;
use App\Http\Controllers\TechnicianAuthController;
use App\Http\Controllers\TechnicianDashboardController;
use App\Http\Controllers\TechnicianLocationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('/services', fn () => app(SiteController::class)->page('services'))->name('services');
Route::get('/how-it-works', fn () => app(SiteController::class)->page('how-it-works'))->name('how');
Route::get('/coverage', fn () => app(SiteController::class)->page('coverage'))->name('coverage');
Route::get('/faq', fn () => app(SiteController::class)->page('faq'))->name('faq');
Route::get('/contact', fn () => app(SiteController::class)->page('contact'))->name('contact');

Route::get('/book', [BookingController::class, 'create'])->name('booking.create');
Route::post('/book', [BookingController::class, 'store'])->middleware('throttle:8,1')->name('booking.store');
Route::get('/book/confirmation/{reference}', [BookingController::class, 'success'])->name('booking.success');
Route::get('/manage-booking/{token}', [BookingController::class, 'manage'])->name('booking.manage');
Route::post('/manage-booking/{token}/cancel', [BookingController::class, 'cancel'])->middleware('throttle:5,1')->name('booking.cancel');
Route::get('/subscriptions', [SubscriptionController::class, 'create'])->name('subscriptions.create');
Route::post('/subscriptions', [SubscriptionController::class, 'store'])->middleware('throttle:6,1')->name('subscriptions.store');
Route::get('/subscriptions/confirmation/{reference}', [SubscriptionController::class, 'success'])->name('subscriptions.success');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/', AdminDashboardController::class)->name('dashboard');
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/appointments', [AdminAppointmentController::class, 'index'])->name('appointments.index');
        Route::get('/appointments/{appointment}', [AdminAppointmentController::class, 'show'])->name('appointments.show');
        Route::patch('/appointments/{appointment}/status', [AdminAppointmentController::class, 'updateStatus'])->name('appointments.status');
        Route::patch('/appointments/{appointment}/reschedule', [AdminAppointmentController::class, 'reschedule'])->name('appointments.reschedule');
        Route::patch('/appointments/{appointment}/assign', [AdminAppointmentController::class, 'assign'])->name('appointments.assign');
        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::patch('/payments/{payment}', [AdminPaymentController::class, 'update'])->name('payments.update');
        Route::get('/services', [AdminServiceController::class, 'index'])->name('services.index');
        Route::patch('/services/{service}', [AdminServiceController::class, 'update'])->name('services.update');
        Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
        Route::get('/subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::patch('/subscriptions/{subscription}', [AdminSubscriptionController::class, 'update'])->name('subscriptions.update');
        Route::get('/technicians', [AdminTechnicianController::class, 'index'])->name('technicians.index');
        Route::post('/technicians', [AdminTechnicianController::class, 'store'])->name('technicians.store');
        Route::patch('/technicians/{technician}', [AdminTechnicianController::class, 'update'])->name('technicians.update');
    });
});

Route::prefix('technician')->name('technician.')->group(function () {
    Route::get('/login', [TechnicianAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [TechnicianAuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
    Route::middleware('technician')->group(function () {
        Route::get('/', TechnicianDashboardController::class)->name('dashboard');
        Route::post('/logout', [TechnicianAuthController::class, 'logout'])->name('logout');
        Route::post('/location', [TechnicianLocationController::class, 'store'])->middleware('throttle:30,1')->name('location.store');
        Route::patch('/appointments/{appointment}/status', [TechnicianAppointmentController::class, 'updateStatus'])->name('appointments.status');
    });
});
