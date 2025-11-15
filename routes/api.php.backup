<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SaleController;

// Route de test (publique)
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

// Routes d'authentification (publiques)
Route::controller(AuthController::class)->group(function () {
    Route::post('/login', 'login');
    Route::post('/register', 'register');
});

// ========================================
// TOUTES LES ROUTES PROTÉGÉES
// ========================================
Route::middleware('auth:sanctum')->group(function () {
    
    // Logout (nécessite auth)
    Route::post('/logout', [AuthController::class, 'logout']);

    // PRODUITS
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::get('/{id}', [ProductController::class, 'show']);
        Route::post('/', [ProductController::class, 'store']);
        Route::put('/{id}', [ProductController::class, 'update']);
        Route::delete('/{id}', [ProductController::class, 'destroy']);
    });

    // CATÉGORIES
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{id}', [CategoryController::class, 'show']);
        Route::post('/', [CategoryController::class, 'store']);
        Route::put('/{id}', [CategoryController::class, 'update']);
        Route::delete('/{id}', [CategoryController::class, 'destroy']);
    });

    // FOURNISSEURS
    Route::prefix('suppliers')->group(function () {
        Route::get('/', [SupplierController::class, 'index']);
        Route::get('/{id}', [SupplierController::class, 'show']);
        Route::post('/', [SupplierController::class, 'store']);
        Route::put('/{id}', [SupplierController::class, 'update']);
        Route::delete('/{id}', [SupplierController::class, 'destroy']);
    });

    // COMMANDES FOURNISSEURS
    Route::prefix('orders')->group(function () {
        Route::get('/', [PurchaseOrderController::class, 'index']);
        Route::get('/{id}', [PurchaseOrderController::class, 'show']);
        Route::post('/', [PurchaseOrderController::class, 'store']);
        Route::put('/{id}', [PurchaseOrderController::class, 'update']);
        Route::delete('/{id}', [PurchaseOrderController::class, 'destroy']);
    });

    // MAGASINS
    Route::prefix('stores')->group(function () {
        Route::get('/', [StoreController::class, 'index']);
        Route::get('/{id}', [StoreController::class, 'show']);
        Route::post('/', [StoreController::class, 'store']);
        Route::put('/{id}', [StoreController::class, 'update']);
        Route::delete('/{id}', [StoreController::class, 'destroy']);
    });

    // VENTES
    Route::prefix('sales')->group(function () {
        Route::get('/', [SaleController::class, 'index']);
        Route::get('/{id}', [SaleController::class, 'show']);
        Route::post('/', [SaleController::class, 'store']);
        Route::put('/{id}', [SaleController::class, 'update']);
        Route::delete('/{id}', [SaleController::class, 'destroy']);
    });

    // RAPPORTS
    Route::prefix('reports')->group(function () {
        Route::get('/overview', [ReportsController::class, 'overview']);
        Route::get('/profit-vs-revenue', [ReportsController::class, 'profitabilityTrend']);
        Route::get('/best-products', [ReportsController::class, 'bestSellingProducts']);
        Route::get('/best-categories', [ReportsController::class, 'bestSellingCategories']);
    });

    // DASHBOARD & STATS
    Route::get('/dashboard', [DashboardController::class, 'summary']);
    Route::get('/stats/sales_vs_purchases', [DashboardController::class, 'salesVsPurchases']);
    Route::get('/stats/top-products', [DashboardController::class, 'topProducts']);
    Route::get('/stats/low-stock', [DashboardController::class, 'lowStock']);
    Route::get('/stats/order_summary', [DashboardController::class, 'orderSummary']);
});