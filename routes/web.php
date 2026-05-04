<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\TourController;
use App\Http\Controllers\Admin\HotelController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin routes
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('tours', TourController::class);
    Route::resource('hotels', HotelController::class);
});

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';