<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\MidtransController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'title' => 'Dashboard'
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth'])->group(function () {
    Route::post('/booking', [BookingController::class, 'store']);
    Route::get('/booking/payment/{id}', [MidtransController::class, 'payment'])->name('booking.payment');
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);
    Route::get('/bookings', [BookingController::class, 'index'])->name('booking.index');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('booking.create');
});
Route::post('/midtrans/callback', [MidtransController::class, 'handleCallback']);


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
