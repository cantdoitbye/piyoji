<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{
    AuthController,
    DashboardController,
    SellerController,
    BuyerController,
    ContractController,
    CourierController,
    LogisticCompanyController
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function () {
    
    // Authentication routes (if using custom admin authentication)
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    
    // Protected Admin Routes
    Route::middleware(['auth:admin'])->group(function () {
        
        // Dashboard
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/', [DashboardController::class, 'index']);
        
        // Sellers Management
        Route::prefix('sellers')->name('sellers.')->group(function () {
            Route::get('/', [SellerController::class, 'index'])->name('index');
            Route::get('create', [SellerController::class, 'create'])->name('create');
            Route::post('/', [SellerController::class, 'store'])->name('store');
            Route::get('{id}', [SellerController::class, 'show'])->name('show');
            Route::get('{id}/edit', [SellerController::class, 'edit'])->name('edit');
            Route::put('{id}', [SellerController::class, 'update'])->name('update');
            Route::delete('{id}', [SellerController::class, 'destroy'])->name('destroy');
            
            // Additional seller routes
            Route::patch('{id}/status', [SellerController::class, 'updateStatus'])->name('update-status');
            Route::post('bulk-action', [SellerController::class, 'bulkAction'])->name('bulk-action');
            Route::get('export', [SellerController::class, 'export'])->name('export');
            Route::get('by-tea-grade', [SellerController::class, 'getByTeaGrade'])->name('by-tea-grade');
        });
        
        // Buyers Management
        Route::prefix('buyers')->name('buyers.')->group(function () {
            Route::get('/', [BuyerController::class, 'index'])->name('index');
            Route::get('create', [BuyerController::class, 'create'])->name('create');
            Route::post('/', [BuyerController::class, 'store'])->name('store');
            Route::get('{id}', [BuyerController::class, 'show'])->name('show');
            Route::get('{id}/edit', [BuyerController::class, 'edit'])->name('edit');
            Route::put('{id}', [BuyerController::class, 'update'])->name('update');
            Route::delete('{id}', [BuyerController::class, 'destroy'])->name('destroy');
            
            // Additional buyer routes
            Route::patch('{id}/status', [BuyerController::class, 'updateStatus'])->name('update-status');
            Route::post('bulk-action', [BuyerController::class, 'bulkAction'])->name('bulk-action');
            Route::get('export', [BuyerController::class, 'export'])->name('export');
            Route::get('by-type', [BuyerController::class, 'getByType'])->name('by-type');
            Route::get('by-tea-grade', [BuyerController::class, 'getByTeaGrade'])->name('by-tea-grade');
        });
        
        // Courier Services Management
        Route::prefix('couriers')->name('couriers.')->group(function () {
            Route::get('/', [CourierController::class, 'index'])->name('index');
            Route::get('create', [CourierController::class, 'create'])->name('create');
            Route::post('/', [CourierController::class, 'store'])->name('store');
            Route::get('{id}', [CourierController::class, 'show'])->name('show');
            Route::get('{id}/edit', [CourierController::class, 'edit'])->name('edit');
            Route::put('{id}', [CourierController::class, 'update'])->name('update');
            Route::delete('{id}', [CourierController::class, 'destroy'])->name('destroy');
            
            // Additional courier routes
            Route::patch('{id}/status', [CourierController::class, 'updateStatus'])->name('update-status');
            Route::post('{id}/test-api', [CourierController::class, 'testApi'])->name('test-api');
            Route::post('bulk-action', [CourierController::class, 'bulkAction'])->name('bulk-action');
            Route::get('export', [CourierController::class, 'export'])->name('export');
            Route::get('by-service-area', [CourierController::class, 'getByServiceArea'])->name('by-service-area');
            Route::post('{id}/tracking-url', [CourierController::class, 'generateTrackingUrl'])->name('tracking-url');
        });
        
      Route::prefix('logistics')->name('logistics.')->group(function () {
        Route::get('/', [LogisticCompanyController::class, 'index'])->name('index');
        Route::get('create', [LogisticCompanyController::class, 'create'])->name('create');
        Route::post('/', [LogisticCompanyController::class, 'store'])->name('store');
        Route::get('{id}', [LogisticCompanyController::class, 'show'])->name('show');
        Route::get('{id}/edit', [LogisticCompanyController::class, 'edit'])->name('edit');
        Route::put('{id}', [LogisticCompanyController::class, 'update'])->name('update');
        Route::delete('{id}', [LogisticCompanyController::class, 'destroy'])->name('destroy');
        
        // Additional logistic company routes
        Route::patch('{id}/status', [LogisticCompanyController::class, 'updateStatus'])->name('update-status');
        Route::post('bulk-action', [LogisticCompanyController::class, 'bulkAction'])->name('bulk-action');
        Route::get('export', [LogisticCompanyController::class, 'export'])->name('export');
        Route::get('by-region', [LogisticCompanyController::class, 'getByRegion'])->name('by-region');
        Route::get('by-route', [LogisticCompanyController::class, 'getByRoute'])->name('by-route');
        Route::get('by-state', [LogisticCompanyController::class, 'getByState'])->name('by-state');
        Route::post('{id}/calculate-cost', [LogisticCompanyController::class, 'calculateShippingCost'])->name('calculate-cost');
    });
    
    // Contract Management (Module 1.5)
    Route::prefix('contracts')->name('contracts.')->group(function () {
        Route::get('/', [ContractController::class, 'index'])->name('index');
        Route::get('create', [ContractController::class, 'create'])->name('create');
        Route::post('/', [ContractController::class, 'store'])->name('store');
        Route::get('{id}', [ContractController::class, 'show'])->name('show');
        Route::get('{id}/edit', [ContractController::class, 'edit'])->name('edit');
        Route::put('{id}', [ContractController::class, 'update'])->name('update');
        Route::delete('{id}', [ContractController::class, 'destroy'])->name('destroy');
        
        // Contract status management
        Route::patch('{id}/status', [ContractController::class, 'updateStatus'])->name('update-status');
        Route::post('{id}/activate', [ContractController::class, 'activate'])->name('activate');
        Route::post('{id}/cancel', [ContractController::class, 'cancel'])->name('cancel');
        Route::post('{id}/expire', [ContractController::class, 'expire'])->name('expire');
        
        // Bulk actions and utilities
        Route::post('bulk-action', [ContractController::class, 'bulkAction'])->name('bulk-action');
        Route::get('export', [ContractController::class, 'export'])->name('export');
        Route::post('{id}/upload-file', [ContractController::class, 'uploadFile'])->name('upload-file');
        
        // Data retrieval routes
        Route::get('expiry-alerts', [ContractController::class, 'getExpiryAlerts'])->name('expiry-alerts');
        Route::post('send-expiry-alerts', [ContractController::class, 'sendExpiryAlerts'])->name('send-expiry-alerts');
        Route::get('by-tea-grade', [ContractController::class, 'getByTeaGrade'])->name('by-tea-grade');
        Route::get('by-seller', [ContractController::class, 'getBySeller'])->name('by-seller');
        Route::get('get-price', [ContractController::class, 'getPrice'])->name('get-price');
        Route::get('tea-grades-by-seller', [ContractController::class, 'getTeaGradesBySeller'])->name('tea-grades-by-seller');
        Route::get('performance-data', [ContractController::class, 'getPerformanceData'])->name('performance-data');
        
        // Contract Items Management
        Route::prefix('{contractId}/items')->name('items.')->group(function () {
            Route::get('/', [ContractController::class, 'getItems'])->name('index');
            Route::post('/', [ContractController::class, 'addItem'])->name('store');
            Route::put('{itemId}', [ContractController::class, 'updateItem'])->name('update');
            Route::delete('{itemId}', [ContractController::class, 'deleteItem'])->name('destroy');
            Route::patch('{itemId}/status', [ContractController::class, 'updateItemStatus'])->name('update-status');
        });
    });
      
        
      
        
      
        
      
        
    });
});

/*
|--------------------------------------------------------------------------
| API Routes for AJAX calls
|--------------------------------------------------------------------------
*/

Route::prefix('api/admin')->name('api.admin.')->middleware(['auth:admin'])->group(function () {
    
    // Sellers API
    Route::prefix('sellers')->name('sellers.')->group(function () {
        Route::get('search', [SellerController::class, 'search'])->name('search');
        Route::get('select-options', [SellerController::class, 'getForSelect'])->name('select-options');
    });
    
    // Buyers API
    Route::prefix('buyers')->name('buyers.')->group(function () {
        Route::get('search', [BuyerController::class, 'search'])->name('search');
        Route::get('select-options', [BuyerController::class, 'getForSelect'])->name('select-options');
    });
    
    // Couriers API
    Route::prefix('couriers')->name('couriers.')->group(function () {
        Route::get('search', [CourierController::class, 'search'])->name('search');
        Route::get('select-options', [CourierController::class, 'getForSelect'])->name('select-options');
    });
    
});

/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
*/

Route::fallback(function () {
    return view('errors.404');
});