<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\TourController;
use App\Http\Controllers\Admin\HotelController;
use App\Http\Controllers\Admin\SeasonPeriodController;
use App\Http\Controllers\Admin\TravelPeriodController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Frontend\BookingController as FrontendBookingController;
use App\Http\Controllers\Admin\InquiryController as AdminInquiryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Frontend\InquiryController as FrontendInquiryController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// Customer routes
Route::get('/', [CustomerController::class, 'index'])->name('home');
Route::get('/tours', [CustomerController::class, 'tours'])->name('tours.index');
Route::get('/tours/{tour}', [CustomerController::class, 'show'])->name('tours.show');
Route::post('/inquiry', [FrontendInquiryController::class, 'store'])
    ->name('inquiry.store');
Route::get('/inquiry/success/{inquiry}', [FrontendInquiryController::class, 'success'])
    ->name('inquiry.success');
Route::get('/booking/{tour}', [FrontendBookingController::class, 'create'])
    ->name('booking.create');

Route::post('/booking/{tour}', [FrontendBookingController::class, 'store'])
    ->name('booking.store');
Route::get('/booking/success/{booking}', [FrontendBookingController::class, 'success'])
    ->name('booking.success');

Route::get('/payment/success', [FrontendBookingController::class, 'paymentSuccess'])
    ->name('payment.success');
Route::get('/payment/{token}', [FrontendBookingController::class, 'paymentShow'])
    ->name('payment.show');
Route::post('/payment/{token}', [FrontendBookingController::class, 'paymentUpload'])
    ->name('payment.upload');

Route::get('/booking/cancel/{token}', [FrontendBookingController::class, 'cancelShow'])
    ->name('booking.cancel.show');
Route::post('/booking/cancel/{token}', [FrontendBookingController::class, 'cancelSubmit'])
    ->name('booking.cancel.submit');
Route::get('/booking/cancel/{token}/success', [FrontendBookingController::class, 'cancelSuccess'])
    ->name('booking.cancel.success');

// Dashboard
Route::get('/dashboard', [App\Http\Controllers\Admin\DashboardController::class, '__invoke'])
    ->middleware(['auth', 'verified', 'prevent-cache'])
    ->name('dashboard');

// Admin routes
Route::middleware(['auth', 'prevent-cache'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('tours', TourController::class);
    Route::get('hotels-by-location', [HotelController::class, 'byLocation'])->name('hotels.by-location');
    Route::resource('hotels', HotelController::class);
    Route::resource('season-periods', SeasonPeriodController::class);
    Route::resource('bookings', BookingController::class);
    Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    Route::resource('inquiries', AdminInquiryController::class)->only(['index', 'show', 'update', 'destroy']);

    // Travel Periods
    Route::get('tours/{tour}/travel-periods', [TravelPeriodController::class, 'index'])->name('tours.travel-periods.index');
    Route::get('tours/{tour}/travel-periods/create', [TravelPeriodController::class, 'create'])->name('tours.travel-periods.create');
    Route::post('tours/{tour}/travel-periods', [TravelPeriodController::class, 'store'])->name('tours.travel-periods.store');
    Route::get('tours/{tour}/travel-periods/{travel_period}/edit', [TravelPeriodController::class, 'edit'])->name('tours.travel-periods.edit');
    Route::put('tours/{tour}/travel-periods/{travel_period}', [TravelPeriodController::class, 'update'])->name('tours.travel-periods.update');
    Route::delete('tours/{tour}/travel-periods/{travel_period}', [TravelPeriodController::class, 'destroy'])->name('tours.travel-periods.destroy');

    // User management (Admin-only)
    Route::middleware(['admin'])->group(function () {
        Route::resource('users', UserController::class);
    });
});

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


require __DIR__.'/auth.php';