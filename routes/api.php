<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ButcherShopController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\LicenseController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\StockSessionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SuperAdmin\ButcherShopController as SuperAdminButcherShopController;
use App\Http\Controllers\Api\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\Api\SuperAdmin\PlanController as SuperAdminPlanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Super Admin routes
    Route::prefix('admin')->middleware('role:super_admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [SuperAdminDashboardController::class, 'index']);
        Route::get('/subscriptions/overview', [SuperAdminDashboardController::class, 'subscriptionOverview']);
        Route::get('/revenue/report', [SuperAdminDashboardController::class, 'revenueReport']);
        Route::get('/payments', [SuperAdminDashboardController::class, 'platformPayments']);

        // Plans management
        Route::apiResource('plans', SuperAdminPlanController::class);

        // Butcher shops management
        Route::apiResource('butcher-shops', SuperAdminButcherShopController::class);
        Route::post('/butcher-shops/{butcherShop}/suspend', [SuperAdminButcherShopController::class, 'suspend']);
        Route::post('/butcher-shops/{butcherShop}/activate', [SuperAdminButcherShopController::class, 'activate']);
        Route::post('/butcher-shops/{butcherShop}/extend', [SuperAdminButcherShopController::class, 'extendSubscription']);
        Route::post('/butcher-shops/{butcherShop}/change-plan', [SuperAdminButcherShopController::class, 'changePlan']);
        Route::post('/butcher-shops/{butcherShop}/reset-api-key', [SuperAdminButcherShopController::class, 'resetApiKey']);
        Route::get('/butcher-shops/{butcherShop}/sales', [SuperAdminButcherShopController::class, 'sales']);
        Route::post('/butcher-shops/{butcherShop}/payments', [SuperAdminButcherShopController::class, 'recordPayment']);
    });

    // Owner routes (shop management)
    Route::middleware(['role:owner', 'subscription'])->group(function () {
        Route::get('/shop', [ButcherShopController::class, 'show']);
        Route::put('/shop', [ButcherShopController::class, 'update']);
        Route::post('/shop/generate-api-key', [ButcherShopController::class, 'generateApiKey']);
        Route::get('/shop/subscription', [ButcherShopController::class, 'subscriptionStatus']);

        // User management (managers/cashiers)
        Route::apiResource('users', UserController::class);
        Route::post('/users/{user}/toggle-active', [UserController::class, 'toggleActive']);
    });

    // Owner & Manager routes (products)
    Route::middleware(['role:owner,manager', 'subscription'])->group(function () {
        Route::apiResource('products', ProductController::class);
    });

    // All butcher shop roles (sales, products, stock, devices, license)
    Route::middleware(['role:owner,manager,cashier', 'subscription'])->group(function () {
        // Products
        Route::get('/products', [ProductController::class, 'index']);
        Route::post('/products/sync', [ProductController::class, 'sync']);

        // Sales
        Route::get('/sales', [SaleController::class, 'index']);
        Route::get('/sales/{sale}', [SaleController::class, 'show']);
        Route::post('/sales', [SaleController::class, 'store']);
        Route::post('/sales/sync', [SaleController::class, 'sync']);

        // Stock Sessions
        Route::get('/stock-sessions', [StockSessionController::class, 'index']);
        Route::post('/stock-sessions/open', [StockSessionController::class, 'open']);
        Route::post('/stock-sessions/close', [StockSessionController::class, 'close']);

        // Device Registration
        Route::post('/devices/register', [DeviceController::class, 'register']);

        // License
        Route::get('/license/status', [LicenseController::class, 'status']);
        Route::post('/license/unlock', [LicenseController::class, 'unlock']);
    });

    // Reports (owner & manager only)
    Route::middleware(['role:owner,manager', 'subscription'])->group(function () {
        Route::get('/reports/sales', [SaleController::class, 'report']);
    });
});
