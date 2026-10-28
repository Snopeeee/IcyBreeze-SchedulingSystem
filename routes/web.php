<?php

use App\Http\Controllers\AdminAppointmentController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminCustomerController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\AdminServiceController;
use App\Http\Controllers\AdminSubscriptionController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PayPalPaymentController;
use App\Http\Controllers\AdminIntegrationController;
use App\Http\Controllers\AdminFaqController;
use App\Http\Controllers\CustomerAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('/services', fn () => app(SiteController::class)->page('services'))->name('services');
Route::get('/how-it-works', fn () => app(SiteController::class)->page('how-it-works'))->name('how');
Route::get('/coverage', fn () => app(SiteController::class)->page('coverage'))->name('coverage');
Route::get('/faq', fn () => app(SiteController::class)->page('faq'))->name('faq');
Route::get('/contact', fn () => app(SiteController::class)->page('contact'))->name('contact');

Route::prefix('account')->name('customer.')->group(function () {
    Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [CustomerAuthController::class, 'register'])->middleware('throttle:5,1')->name('register.store');
    Route::middleware('auth')->group(function () {
        Route::get('/', [CustomerAuthController::class, 'dashboard'])->name('dashboard');
        Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('logout');
    });
});

Route::get('/book', [BookingController::class, 'create'])->name('booking.create');
Route::get('/book/availability', [BookingController::class, 'availability'])
    ->middleware('throttle:60,1')
    ->name('booking.availability');
Route::post('/book', [BookingController::class, 'store'])->middleware('throttle:8,1')->name('booking.store');
Route::get('/book/confirmation/{reference}', [BookingController::class, 'success'])->name('booking.success');
Route::get('/manage-booking/{token}', [BookingController::class, 'manage'])->name('booking.manage');
Route::post('/manage-booking/{token}/cancel', [BookingController::class, 'cancel'])->middleware('throttle:5,1')->name('booking.cancel');
Route::post('/manage-booking/{token}/paypal', [PayPalPaymentController::class, 'start'])->middleware('throttle:5,1')->name('paypal.start');
Route::get('/manage-booking/{token}/paypal/return', [PayPalPaymentController::class, 'complete'])->middleware('throttle:8,1')->name('paypal.return');
Route::get('/manage-booking/{token}/paypal/cancel', [PayPalPaymentController::class, 'cancel'])->name('paypal.cancel');
Route::get('/subscriptions', [SubscriptionController::class, 'create'])->name('subscriptions.create');
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
        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
        Route::patch('/payments/{payment}', [AdminPaymentController::class, 'update'])->name('payments.update');
        Route::post('/payments/{payment}/verify-paypal', [AdminPaymentController::class, 'reconcile'])->middleware('throttle:10,1')->name('payments.verify-paypal');
        Route::post('/payments/{payment}/switch-to-cash', [AdminPaymentController::class, 'switchToCash'])->middleware('throttle:10,1')->name('payments.switch-to-cash');
        Route::get('/services', [AdminServiceController::class, 'index'])->name('services.index');
        Route::patch('/services/{service}', [AdminServiceController::class, 'update'])->name('services.update');
        Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
        Route::get('/integrations', [AdminIntegrationController::class, 'index'])->name('integrations.index');
        Route::post('/integrations/verify-service-area', [AdminIntegrationController::class, 'verifyServiceArea'])->middleware('throttle:1,1')->name('integrations.verify-service-area');
        Route::resource('faqs', AdminFaqController::class)->except('show');
        Route::post('/appointments/{appointment}/lookup-address', [AdminIntegrationController::class, 'lookupAddress'])->middleware('throttle:10,1')->name('appointments.lookup-address');
        Route::get('/subscriptions', [AdminSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::patch('/subscriptions/{subscription}', [AdminSubscriptionController::class, 'update'])->name('subscriptions.update');
    });
});
