<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\{
    AuthController,
    BillingCompanyController,
    DashboardController,
    SellerController,
    BuyerController,
    ContractController,
    CourierController,
    GardenInvoiceController,
    LogisticCompanyController,
    OfferListController,
    SampleController,
    TeaController,
    UserController
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
        
         // User Management (NEW)
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('{id}', [UserController::class, 'show'])->name('show');
            Route::get('{id}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('{id}', [UserController::class, 'update'])->name('update');
            Route::delete('{id}', [UserController::class, 'destroy'])->name('destroy');
            
            // Additional user routes
            Route::patch('{id}/status', [UserController::class, 'updateStatus'])->name('update-status');
            Route::post('bulk-action', [UserController::class, 'bulkAction'])->name('bulk-action');
            Route::get('export', [UserController::class, 'export'])->name('export');
        });
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
      
      Route::get('{id}/manage-attachments', [BuyerController::class, 'manageAttachments'])->name('manage-attachments');
                  Route::post('{id}/attachments', [BuyerController::class, 'uploadAttachments'])->name('upload-attachments');
        Route::get('{id}/attachments', [BuyerController::class, 'getAttachments'])->name('get-attachments');
        Route::delete('{buyerId}/attachments/{attachmentId}', [BuyerController::class, 'deleteAttachment'])->name('delete-attachment');
        Route::get('{buyerId}/attachments/{attachmentId}/download', [BuyerController::class, 'downloadAttachment'])->name('attachments.download');
        Route::get('{buyerId}/attachments/{attachmentId}/preview', [BuyerController::class, 'previewAttachment'])->name('attachments.preview');
        Route::post('{buyerId}/attachments/{attachmentId}/verify', [BuyerController::class, 'verifyAttachment'])->name('verify-attachment');
        Route::put('{buyerId}/attachments/{attachmentId}', [BuyerController::class, 'updateAttachment'])->name('update-attachment');
   
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
      
        
      
        
      
          Route::prefix('samples')->name('samples.')->group(function () {
        Route::get('/', [SampleController::class, 'index'])->name('index');
        Route::get('create', [SampleController::class, 'create'])->name('create');
        Route::post('/', [SampleController::class, 'store'])->name('store');
        Route::get('{id}', [SampleController::class, 'show'])->name('show');
        Route::get('{id}/edit', [SampleController::class, 'edit'])->name('edit');
        Route::put('{id}', [SampleController::class, 'update'])->name('update');
        Route::delete('{id}', [SampleController::class, 'destroy'])->name('destroy');
        
        // Evaluation routes (Module 2.2)
        Route::get('{id}/evaluate', [SampleController::class, 'evaluate'])->name('evaluate');
        Route::post('{id}/evaluate', [SampleController::class, 'storeEvaluation'])->name('store-evaluation');
        Route::post('{id}/start-evaluation', [SampleController::class, 'startEvaluation'])->name('start-evaluation');
        
        // Special views
        Route::get('pending-evaluations', [SampleController::class, 'pendingEvaluations'])->name('pending-evaluations');
        Route::get('evaluated-samples', [SampleController::class, 'evaluatedSamples'])->name('evaluated');
        Route::get('approved-samples', [SampleController::class, 'approvedSamples'])->name('approved');
        Route::get('tasting-report', [SampleController::class, 'tastingReport'])->name('tasting-report');
        
        // Bulk operations
        Route::get('bulk-upload', [SampleController::class, 'bulkUpload'])->name('bulk-upload');
        Route::post('bulk-upload', [SampleController::class, 'processBulkUpload'])->name('process-bulk-upload');
        Route::get('export', [SampleController::class, 'export'])->name('export');
   
           // Buyer Assignment routes (Module 2.3)
        Route::get('ready-for-assignment', [SampleController::class, 'readyForAssignment'])->name('ready-for-assignment');
        Route::get('assigned-samples', [SampleController::class, 'assignedSamples'])->name('assigned-samples');
        Route::get('awaiting-dispatch', [SampleController::class, 'awaitingDispatch'])->name('awaiting-dispatch');
        
        Route::get('{id}/assign-buyers', [SampleController::class, 'assignToBuyers'])->name('assign-buyers');
        Route::post('{id}/assign-buyers', [SampleController::class, 'storeBuyerAssignments'])->name('store-buyer-assignments');
        
        // AJAX routes for assignment management
        Route::patch('assignments/{assignmentId}/dispatch-status', [SampleController::class, 'updateDispatchStatus'])->name('update-dispatch-status');
        Route::delete('assignments/{assignmentId}', [SampleController::class, 'removeAssignment'])->name('remove-assignment');
   
Route::get('{id}/transfer', [SampleController::class, 'showTransferForm'])->name('transfer-form');
Route::post('{id}/transfer', [SampleController::class, 'transferToBatch'])->name('transfer-to-batch');
Route::get('{id}/transfer-history', [SampleController::class, 'transferHistory'])->name('transfer-history');

// All transfers listing
Route::get('transfers', [SampleController::class, 'transfers'])->name('transfers');
    });

    Route::prefix('batches')->name('batches.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\BatchController::class, 'index'])->name('index');
    Route::get('{id}', [App\Http\Controllers\Admin\BatchController::class, 'show'])->name('show');
    Route::delete('{id}', [App\Http\Controllers\Admin\BatchController::class, 'destroy'])->name('destroy');
    
    // Batch creation and management
    Route::post('create-for-date', [App\Http\Controllers\Admin\BatchController::class, 'createBatchesForDate'])->name('create-for-date');
    Route::post('rebuild-for-date', [App\Http\Controllers\Admin\BatchController::class, 'rebuildBatchesForDate'])->name('rebuild-for-date');
    Route::get('date-statistics', [App\Http\Controllers\Admin\BatchController::class, 'getDateStatistics'])->name('date-statistics');
    
    // Batch status management
    Route::patch('{id}/status', [App\Http\Controllers\Admin\BatchController::class, 'updateStatus'])->name('update-status');
    
    // Export and data
    Route::get('export', [App\Http\Controllers\Admin\BatchController::class, 'export'])->name('export');
    Route::get('overview-data', [App\Http\Controllers\Admin\BatchController::class, 'getOverviewData'])->name('overview-data');
    Route::get('{id}/export-samples', [App\Http\Controllers\Admin\BatchController::class, 'exportBatchSamples'])->name('export-samples');

    // Add these routes inside the batches route group in web.php
    
    // Batch evaluation routes
    Route::get('{id}/evaluation', [App\Http\Controllers\Admin\BatchController::class, 'showEvaluationForm'])->name('evaluation-form');
    Route::post('{id}/evaluation', [App\Http\Controllers\Admin\BatchController::class, 'storeEvaluation'])->name('store-evaluation');
    Route::get('{id}/evaluation-results', [App\Http\Controllers\Admin\BatchController::class, 'showEvaluationResults'])->name('evaluation-results');
      Route::get('{id}/initiate-testing', [App\Http\Controllers\Admin\BatchController::class, 'initiateBatchTesting'])->name('initiate-testing');
    Route::post('{id}/initiate-testing', [App\Http\Controllers\Admin\BatchController::class, 'initiateBatchTesting'])->name('initiate-testing');
    Route::get('{id}/sample-testing', [App\Http\Controllers\Admin\BatchController::class, 'showSampleTesting'])->name('sample-testing');
    Route::post('{id}/sample-testing', [App\Http\Controllers\Admin\BatchController::class, 'storeSampleTestingResult'])->name('store-sample-testing');
    Route::get('{id}/testing-results', [App\Http\Controllers\Admin\BatchController::class, 'showTestingResults'])->name('testing-results');
    
});

     Route::resource('pocs', App\Http\Controllers\Admin\PocController::class);
    Route::patch('pocs/{id}/toggle-status', [App\Http\Controllers\Admin\PocController::class, 'toggleStatus'])->name('pocs.toggle-status');
    
    // Tea Master Routes
    // Route::resource('teas', App\Http\Controllers\Admin\TeaController::class);
    // Route::patch('teas/{id}/toggle-status', [App\Http\Controllers\Admin\TeaController::class, 'toggleStatus'])->name('teas.toggle-status');
    
    // Garden Master Routes
    Route::resource('gardens', App\Http\Controllers\Admin\GardenController::class);
    Route::patch('gardens/{id}/toggle-status', [App\Http\Controllers\Admin\GardenController::class, 'toggleStatus'])->name('gardens.toggle-status');
    

      Route::group(['prefix' => 'gardens'], function () {
        Route::get('{id}/manage-attachments', [App\Http\Controllers\Admin\GardenController::class, 'manageAttachments'])
            ->name('gardens.manage-attachments');
        Route::post('{id}/attachments', [App\Http\Controllers\Admin\GardenController::class, 'uploadAttachments'])
            ->name('gardens.upload-attachments');
        Route::get('{id}/attachments', [App\Http\Controllers\Admin\GardenController::class, 'getAttachments'])
            ->name('gardens.get-attachments');
        Route::delete('{gardenId}/attachments/{attachmentId}', [App\Http\Controllers\Admin\GardenController::class, 'deleteAttachment'])
            ->name('gardens.delete-attachment');
        Route::get('{gardenId}/attachments/{attachmentId}/download', [App\Http\Controllers\Admin\GardenController::class, 'downloadAttachment'])
            ->name('gardens.attachments.download');
        Route::get('{gardenId}/attachments/{attachmentId}/preview', [App\Http\Controllers\Admin\GardenController::class, 'previewAttachment'])
            ->name('gardens.attachments.preview');
        Route::post('{gardenId}/attachments/{attachmentId}/verify', [App\Http\Controllers\Admin\GardenController::class, 'verifyAttachment'])
            ->name('gardens.verify-attachment');
        Route::put('{gardenId}/attachments/{attachmentId}', [App\Http\Controllers\Admin\GardenController::class, 'updateAttachment'])
            ->name('gardens.update-attachment');
    });
    
    // Garden Invoice Management Routes
    Route::group(['prefix' => 'gardens/{garden}'], function () {
        
        
        // Invoice CRUD routes
        Route::get('/invoices', [GardenInvoiceController::class, 'index'])
            ->name('gardens.invoices.index');
            
        Route::get('/invoices/create', [GardenInvoiceController::class, 'create'])
            ->name('gardens.invoices.create');
            
        Route::post('/invoices', [GardenInvoiceController::class, 'store'])
            ->name('gardens.invoices.store');
            
        Route::get('/invoices/{invoice}', [GardenInvoiceController::class, 'show'])
            ->name('gardens.invoices.show');
            
        Route::get('/invoices/{invoice}/edit', [GardenInvoiceController::class, 'edit'])
            ->name('gardens.invoices.edit');
            
        Route::put('/invoices/{invoice}', [GardenInvoiceController::class, 'update'])
            ->name('gardens.invoices.update');
            
        Route::delete('/invoices/{invoice}', [GardenInvoiceController::class, 'destroy'])
            ->name('gardens.invoices.destroy');
        
        // Invoice status management routes
        Route::patch('/invoices/{invoice}/finalize', [GardenInvoiceController::class, 'finalize'])
            ->name('gardens.invoices.finalize');
            
        Route::patch('/invoices/{invoice}/cancel', [GardenInvoiceController::class, 'cancel'])
            ->name('gardens.invoices.cancel');
    });



     Route::prefix('teas')->name('teas.')->group(function () {
           // New tea grading system routes
        Route::get('tea-types-by-category', [TeaController::class, 'getTeaTypesByCategory'])->name('tea-types-by-category');
        Route::get('grade-codes-by-tea-type', [TeaController::class, 'getGradeCodesByTeaType'])->name('grade-codes-by-tea-type');
        Route::get('existing-grade-codes', [TeaController::class, 'getExistingGradeCodesByTeaTypes'])->name('existing-grade-codes');
        Route::get('filtered-teas', [TeaController::class, 'getFilteredTeas'])->name('filtered-teas');
        Route::post('filtered-teas-multiple', [TeaController::class, 'getFilteredTeasMultiple'])->name('filtered-teas-multiple');
        Route::post('validate-grading', [TeaController::class, 'validateGrading'])->name('validate-grading');
        Route::get('grading-options', [TeaController::class, 'getGradingOptions'])->name('grading-options');
        Route::get('search', [TeaController::class, 'search'])->name('search');
        Route::get('statistics', [TeaController::class, 'getStatistics'])->name('statistics');
        Route::post('bulk-action', [TeaController::class, 'bulkAction'])->name('bulk-action');
        Route::get('export', [TeaController::class, 'export'])->name('export');
   
       
        Route::get('/', [TeaController::class, 'index'])->name('index');
        Route::get('create', [TeaController::class, 'create'])->name('create');
        Route::post('/', [TeaController::class, 'store'])->name('store');
        Route::get('{id}', [TeaController::class, 'show'])->name('show');
        Route::get('{id}/edit', [TeaController::class, 'edit'])->name('edit');
        Route::put('{id}', [TeaController::class, 'update'])->name('update');
        Route::delete('{id}', [TeaController::class, 'destroy'])->name('destroy');
        
     });
      

     Route::prefix('billing-companies')->name('billing-companies.')->group(function () {
        Route::get('/', [BillingCompanyController::class, 'index'])->name('index');
        Route::get('create', [BillingCompanyController::class, 'create'])->name('create');
        Route::post('/', [BillingCompanyController::class, 'store'])->name('store');
        Route::get('{id}', [BillingCompanyController::class, 'show'])->name('show');
        Route::get('{id}/edit', [BillingCompanyController::class, 'edit'])->name('edit');
        Route::put('{id}', [BillingCompanyController::class, 'update'])->name('update');
        Route::delete('{id}', [BillingCompanyController::class, 'destroy'])->name('destroy');
        
        Route::patch('{id}/status', [BillingCompanyController::class, 'updateStatus'])->name('update-status');
        Route::post('{id}/shipping-address', [BillingCompanyController::class, 'addShippingAddress'])->name('add-shipping-address');
        Route::get('by-type', [BillingCompanyController::class, 'getByType'])->name('by-type');
        Route::post('bulk-action', [BillingCompanyController::class, 'bulkAction'])->name('bulk-action');
        Route::get('export', [BillingCompanyController::class, 'export'])->name('export');
    });

    // Transporter Branches Management (from previous artifacts)
    Route::prefix('transporter-branches')->name('transporter-branches.')->group(function () {
        Route::get('/', [TransporterBranchController::class, 'index'])->name('index');
        Route::get('create', [TransporterBranchController::class, 'create'])->name('create');
        Route::post('/', [TransporterBranchController::class, 'store'])->name('store');
        Route::get('{id}', [TransporterBranchController::class, 'show'])->name('show');
        Route::get('{id}/edit', [TransporterBranchController::class, 'edit'])->name('edit');
        Route::put('{id}', [TransporterBranchController::class, 'update'])->name('update');
        Route::delete('{id}', [TransporterBranchController::class, 'destroy'])->name('destroy');
        
        Route::patch('{id}/status', [TransporterBranchController::class, 'updateStatus'])->name('update-status');
        Route::get('by-company/{companyId}', [TransporterBranchController::class, 'getByCompany'])->name('by-company');
        Route::get('by-city/{city}', [TransporterBranchController::class, 'getByCity'])->name('by-city');
        Route::post('{id}/service-route', [TransporterBranchController::class, 'addServiceRoute'])->name('add-service-route');
        Route::delete('{branchId}/service-route/{routeId}', [TransporterBranchController::class, 'removeServiceRoute'])->name('remove-service-route');
    });


    Route::prefix('sales-register')->name('sales-register.')->group(function () {
    Route::get('/', [App\Http\Controllers\Admin\SalesRegisterController::class, 'index'])->name('index');
    Route::get('create', [App\Http\Controllers\Admin\SalesRegisterController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\Admin\SalesRegisterController::class, 'store'])->name('store');
    Route::get('{id}', [App\Http\Controllers\Admin\SalesRegisterController::class, 'show'])->name('show');
    Route::get('{id}/edit', [App\Http\Controllers\Admin\SalesRegisterController::class, 'edit'])->name('edit');
    Route::put('{id}', [App\Http\Controllers\Admin\SalesRegisterController::class, 'update'])->name('update');
    Route::delete('{id}', [App\Http\Controllers\Admin\SalesRegisterController::class, 'destroy'])->name('destroy');
    
    // Status management
    Route::post('{id}/approve', [App\Http\Controllers\Admin\SalesRegisterController::class, 'approve'])->name('approve');
    Route::post('{id}/reject', [App\Http\Controllers\Admin\SalesRegisterController::class, 'reject'])->name('reject');
    
    // Reports and export
    Route::get('report', [App\Http\Controllers\Admin\SalesRegisterController::class, 'report'])->name('report');
    Route::get('export', [App\Http\Controllers\Admin\SalesRegisterController::class, 'export'])->name('export');
});
    //   Route::get('tea-types-by-category', [TeaController::class, 'getTeaTypesByCategory'])
    //      ->name('tea-types-by-category');
    
    // Route::get('grade-codes-by-tea-type', [TeaController::class, 'getGradeCodesByTeaType'])
    //      ->name('grade-codes-by-tea-type');
    
    // Route::get('filtered-teas', [TeaController::class, 'getFilteredTeas'])
    //      ->name('filtered-teas');


        Route::post('{id}/add-to-sales-register', [SampleController::class, 'addToSalesRegister'])->name('samples.add-to-sales-register');
    });

      Route::resource('document-types', App\Http\Controllers\Admin\DocumentTypeController::class);
    Route::patch('document-types/{documentType}/toggle-status', [App\Http\Controllers\Admin\DocumentTypeController::class, 'toggleStatus'])
        ->name('document-types.toggle-status');
    Route::get('document-types-active', [App\Http\Controllers\Admin\DocumentTypeController::class, 'getActive'])
        ->name('document-types.active');


         Route::resource('offer-lists', OfferListController::class)->except(['show']);
    
    Route::controller(OfferListController::class)->group(function () {
        Route::get('offer-lists/{id}', 'show')->name('offer-lists.show');
        Route::get('offer-lists-import/form', 'importForm')->name('offer-lists.import.form');
        Route::post('offer-lists-import', 'import')->name('offer-lists.import');
        Route::get('offer-lists-export', 'export')->name('offer-lists.export');
        Route::get('offer-lists-template/download', 'downloadTemplate')->name('offer-lists.template');
    });
});

/*
|--------------------------------------------------------------------------
| API Routes for AJAX calls
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Fallback Route
|--------------------------------------------------------------------------
*/

Route::fallback(function () {
    return view('errors.404');
});