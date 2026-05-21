<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\TourController;
use App\Http\Controllers\Admin\HotelController;
use App\Http\Controllers\Admin\SeasonPeriodController;
use App\Http\Controllers\Admin\AvailableDateController;

use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CustomerController::class, 'index'])->name('home');
Route::get('/tours/{tour}', [CustomerController::class, 'show'])->name('tours.show');
Route::post('/inquiry', [CustomerController::class, 'inquiry'])->name('inquiry.store');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {

    Route::resource('tours', TourController::class);
    Route::resource('hotels', HotelController::class);
    Route::resource('season-periods', SeasonPeriodController::class);

    // Available Dates Routes
    Route::get('tours/{tour}/dates',[AvailableDateController::class, 'index'])->name('tours.dates.index');
    Route::get('tours/{tour}/dates/create',[AvailableDateController::class, 'create'])->name('tours.dates.create');
    Route::post('tours/{tour}/dates',[AvailableDateController::class, 'store'])->name('tours.dates.store');
    Route::get('tours/{tour}/dates/{date}/edit',[AvailableDateController::class, 'edit'])->name('tours.dates.edit');
    Route::put('tours/{tour}/dates/{date}',[AvailableDateController::class, 'update'])->name('tours.dates.update');
    Route::delete('tours/{tour}/dates/{date}',[AvailableDateController::class, 'destroy'])->name('tours.dates.destroy');
});

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';