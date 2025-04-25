<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DynamicExcelController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SubscriptionManagementController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\TenantController;
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
// }


Route::middleware(['auth', 'global.role:superadmin'])->group(function () {
    Route::get('/users', [UserManagementController::class, 'index'])->name('user.management');
    Route::patch('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');

    Route::get('/subscriptions', [SubscriptionManagementController::class, 'index'])->name('subscriptionmanagement.index');
    Route::post('/subscription/assign', [SubscriptionManagementController::class, 'assign'])->name('subscription.assign');
    Route::post('/subscription/cancel', [SubscriptionManagementController::class, 'cancel'])->name('subscription.cancel');
    Route::post('/subscription/extend', [SubscriptionManagementController::class, 'extend'])->name('subscription.extend');
});

Route::middleware(['auth', 'subscription.limits'])->group(function () {

    Route::middleware('tenant.or.global:owner,superadmin,admin')->group(function () {
        Route::get('/tenants', [TenantController::class, 'index'])->name('tenants.index');
        Route::post('/tenants', [TenantController::class, 'store'])->name('tenants.store');
        Route::get('/tenants/{tenant}/users', [TenantController::class, 'users'])->name('tenants.users');
        Route::post('/tenants/invite', [TenantController::class, 'invite'])->name('tenants.invite');
        Route::patch('/tenants/{tenant}', [TenantController::class, 'update'])->name('tenants.update');
        Route::delete('/tenants/{tenant}', [TenantController::class, 'destroy'])->name('tenants.destroy');
    });

    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
    Route::put('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

    Route::get('/excel/models', [DynamicExcelController::class, 'availableModels']);
    Route::post('/excel/export', [DynamicExcelController::class, 'export']);
    Route::post('/excel/import', [DynamicExcelController::class, 'import']);
    Route::get('/excel/download/{filename}', [DynamicExcelController::class, 'downloadExport']);
    Route::get('/excel', [DynamicExcelController::class, 'index']);
});

Route::middleware('auth')->group(function () {
    Route::get('/subscription', [SubscriptionController::class, 'index'])->name('subscription.index');
    Route::post('/subscription/select', [SubscriptionController::class, 'select'])->name('subscription.select');
});









require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
