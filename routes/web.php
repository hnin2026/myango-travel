<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\TourController;
use App\Http\Controllers\Admin\HotelController;
use App\Http\Controllers\Admin\SeasonPeriodController;
use App\Http\Controllers\Admin\AvailableDateController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\InquiryController as AdminInquiryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Frontend\InquiryController as FrontendInquiryController;
use Illuminate\Support\Facades\Route;

// Customer routes
Route::get('/', [CustomerController::class, 'index'])->name('home');
Route::get('/tours/{tour}', [CustomerController::class, 'show'])->name('tours.show');
Route::post('/inquiry', [FrontendInquiryController::class, 'store'])
    ->name('inquiry.store');

// Dashboard
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('tours', TourController::class);
    Route::resource('hotels', HotelController::class);
    Route::resource('season-periods', SeasonPeriodController::class);
    Route::resource('bookings', BookingController::class);
    Route::get('/inquiries', [AdminInquiryController::class, 'index'])->name('inquiries.index');
    Route::get('/inquiries/{inquiry}', [AdminInquiryController::class, 'show'])->name('inquiries.show');

    // Available Dates
    Route::get('tours/{tour}/dates', [AvailableDateController::class, 'index'])->name('tours.dates.index');
    Route::get('tours/{tour}/dates/create', [AvailableDateController::class, 'create'])->name('tours.dates.create');
    Route::post('tours/{tour}/dates', [AvailableDateController::class, 'store'])->name('tours.dates.store');
    Route::get('tours/{tour}/dates/{date}/edit', [AvailableDateController::class, 'edit'])->name('tours.dates.edit');
    Route::put('tours/{tour}/dates/{date}', [AvailableDateController::class, 'update'])->name('tours.dates.update');
    Route::delete('tours/{tour}/dates/{date}', [AvailableDateController::class, 'destroy'])->name('tours.dates.destroy');
});

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';