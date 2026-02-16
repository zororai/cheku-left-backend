<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Owner\LicenseController;
use App\Http\Controllers\Owner\SalesController;
use App\Http\Controllers\Owner\StaffController;
use App\Http\Controllers\Owner\StockSessionController;
use App\Http\Controllers\SuperAdmin\ButcherShopController;
use App\Http\Controllers\SuperAdmin\UnlockCodeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

// Handle GET requests to /logout (redirect to home)
Route::get('logout', function () {
    return redirect('/');
});

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::resource('butcher-shops', ButcherShopController::class);
    Route::post('butcher-shops/{butcherShop}/toggle-status', [ButcherShopController::class, 'toggleStatus'])
        ->name('butcher-shops.toggle-status');
    Route::post('butcher-shops/{butcherShop}/extend-subscription', [ButcherShopController::class, 'extendSubscription'])
        ->name('butcher-shops.extend-subscription');
    Route::resource('unlock-codes', UnlockCodeController::class)->only(['index', 'create', 'store', 'destroy']);
});

Route::middleware(['auth', 'verified', 'role:owner,manager'])->prefix('owner')->name('owner.')->group(function () {
    Route::resource('staff', StaffController::class)->except(['show']);
    Route::post('staff/{staff}/toggle-status', [StaffController::class, 'toggleStatus'])
        ->name('staff.toggle-status');
    Route::get('sales', [SalesController::class, 'index'])->name('sales.index');
    Route::get('sales/{sale}', [SalesController::class, 'show'])->name('sales.show');
    Route::get('stock-sessions', [StockSessionController::class, 'index'])->name('stock-sessions.index');
    Route::get('stock-sessions/{stockSession}', [StockSessionController::class, 'show'])->name('stock-sessions.show');
    Route::get('license', [LicenseController::class, 'index'])->name('license.index');
});

require __DIR__.'/settings.php';
