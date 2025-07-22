<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SampleController;

/*
|--------------------------------------------------------------------------
| API Routes for Mobile App
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for mobile app users.
| These routes are loaded by the RouteServiceProvider and assigned 
| to the "api" middleware group.
|
*/

/*
|--------------------------------------------------------------------------
| Public API Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    
   
    
    // Authentication endpoints
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
    });
});

/*
|--------------------------------------------------------------------------
| Protected API Routes (Authentication Required)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware(['auth:sanctum'])->group(function () {
    
    // Authentication endpoints
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('profile', [AuthController::class, 'profile']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::post('refresh-token', [AuthController::class, 'refreshToken']);
        Route::post('check-permission', [AuthController::class, 'checkPermission']);
        
    });


        Route::get('sellers', [SampleController::class, 'sellers']);

     Route::prefix('samples')->name('samples.')->group(function () {
        Route::get('/', [SampleController::class, 'index'])->name('index');
        Route::post('/', [SampleController::class, 'store'])->name('store');
        Route::get('{id}', [SampleController::class, 'show'])->name('show');
        Route::put('{id}', [SampleController::class, 'update'])->name('update');
        Route::delete('{id}', [SampleController::class, 'destroy'])->name('destroy');
        
        // Evaluation endpoints (Module 2.2)
        // Route::get('{id}/evaluate', [SampleController::class, 'evaluate'])->name('evaluate');
        Route::post('{id}/start-evaluation', [SampleController::class, 'startEvaluation'])->name('start-evaluation');
        Route::post('{id}/submit-evaluation', [SampleController::class, 'submitEvaluation'])->name('submit-evaluation');
        
        // Special endpoints
        Route::get('pending-evaluations', [SampleController::class, 'pendingEvaluations'])->name('pending-evaluations');
        Route::get('evaluated-samples', [SampleController::class, 'evaluatedSamples'])->name('evaluated');
        Route::get('approved-samples', [SampleController::class, 'approvedSamples'])->name('approved');
        Route::get('top-scoring', [SampleController::class, 'topScoringSamples'])->name('top-scoring');
        
        // Utility endpoints
        Route::get('statistics', [SampleController::class, 'statistics'])->name('statistics');
        Route::get('search', [SampleController::class, 'search'])->name('search');
        Route::get('sellers', [SampleController::class, 'getSellers'])->name('sellers');
        Route::get('tea-grades', [SampleController::class, 'getTeaGrades'])->name('tea-grades');
    });
    
   
    
   
});

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
*/

function getAvailableModules($user): array
{
    $modules = [];

    if ($user->hasPermission('view_samples') || $user->hasPermission('manage_samples')) {
        $modules[] = [
            'name' => 'Sample Management',
            'icon' => 'vial',
            'permission' => $user->hasPermission('manage_samples') ? 'manage' : 'view',
            'description' => 'Manage tea samples and evaluations'
        ];
    }

    if ($user->hasPermission('view_sellers')) {
        $modules[] = [
            'name' => 'Sellers',
            'icon' => 'store',
            'permission' => 'view',
            'description' => 'View seller information'
        ];
    }

    if ($user->hasPermission('view_buyers')) {
        $modules[] = [
            'name' => 'Buyers',
            'icon' => 'users',
            'permission' => 'view',
            'description' => 'View buyer information'
        ];
    }

    if ($user->hasPermission('manage_dispatch')) {
        $modules[] = [
            'name' => 'Dispatch',
            'icon' => 'shipping-fast',
            'permission' => 'manage',
            'description' => 'Manage sample dispatch'
        ];
    }

    if ($user->hasPermission('view_reports')) {
        $modules[] = [
            'name' => 'Reports',
            'icon' => 'chart-bar',
            'permission' => 'view',
            'description' => 'View system reports'
        ];
    }

    return $modules;
}