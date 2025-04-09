<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\MidtransController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'title' => 'Dashboard'
    ]);
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])->middleware(['auth'])->name('dashboard');


Route::middleware(['auth'])->group(function () {
    Route::post('/booking', [BookingController::class, 'store']);
    Route::get('/booking/payment/{id}', [MidtransController::class, 'payment'])->name('booking.payment');
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);
    Route::get('/bookings', [BookingController::class, 'index'])->name('booking.index');

    Route::get('/bookings/create', [BookingController::class, 'create'])->name('booking.create');
    Route::put('/bookings/{id}', [BookingController::class, 'update'])->name('bookings.update');

    Route::post('/reviews', [ReviewController::class, 'store']);

    Route::middleware(['role:admin|superadmin'])->prefix('dashboard')->group(function () {
        Route::resource('services', ServiceController::class)->except('update');
        Route::patch('services/{service}', [ServiceController::class, 'update']);
        Route::get('/services/{service}/audits', [ServiceController::class, 'audits'])->name('dashboard.services.audits');

        Route::get('/bookings', [BookingController::class, 'dashboardindex'])->name('dashboard.bookings');
        Route::get('/bookings/{booking}/audits', [BookingController::class, 'audits'])->name('dashboard.bookings.audits');
        Route::patch('/bookings/{id}/status', [BookingController::class, 'updateStatus'])->name('bookings.updateStatus');

        Route::resource('reviews', ReviewController::class)->except(['store']);
        Route::get('/reviews/{review}/audits', [ReviewController::class, 'audits'])->name('dashboard.reviews.audits');

        Route::resource('usermanagement', UserController::class)->except('update', 'destroy');
        Route::patch('usermanagement/{user}', [UserController::class, 'update']);
        Route::delete('usermanagement/{user}', [UserController::class, 'destroy']);

        Route::post('/excel/export', [ExcelController::class, 'export'])->name('excel.export');
        Route::post('/excel/import', [ExcelController::class, 'import'])->name('excel.import');
        Route::get('/get-columns', [ExcelController::class, 'getColumns'])->name('excel.columns');

        Route::get('/excel', function () {
            return Inertia::render('dashboard/excel/Index');
        })->name('excel');

    });
    Route::middleware(['role:superadmin'])->prefix('dashboard')->group(function () {
        Route::resource('roles', RoleController::class);
    });
});

Route::post('/midtrans/callback', [MidtransController::class, 'handleCallback']);

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
