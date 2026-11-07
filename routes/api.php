<?php

use App\Http\Controllers\Api\AdminRuleController;
use App\Http\Controllers\Api\ApplianceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CalculationController;
use App\Http\Controllers\Api\HouseholdController;
use App\Http\Controllers\Api\TariffController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| WATTWISE Centralized REST API Routes
|--------------------------------------------------------------------------
| Shared across Web and Mobile applications
*/

// Public Authentication Endpoints
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Reference Data (Public / Shared)
Route::get('/tariffs', [TariffController::class, 'index']);
Route::get('/appliance-categories', [ApplianceController::class, 'categories']);

// Protected Resource Endpoints
Route::middleware('auth:sanctum')->group(function () {
    // Authenticated User Profile
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Households Management
    Route::apiResource('households', HouseholdController::class);

    // Household Appliances Inventory
    Route::get('/households/{household}/appliances', [ApplianceController::class, 'index']);
    Route::post('/households/{household}/appliances', [ApplianceController::class, 'store']);
    Route::put('/appliances/{appliance}', [ApplianceController::class, 'update']);
    Route::delete('/appliances/{appliance}', [ApplianceController::class, 'destroy']);

    // Energy Calculation & Expert System Inference
    Route::post('/households/{household}/calculate', [CalculationController::class, 'calculate']);
    Route::post('/households/{household}/analyze', [CalculationController::class, 'analyze']);
    Route::get('/households/{household}/periods', [CalculationController::class, 'periods']);
    Route::get('/periods/{period}', [CalculationController::class, 'showPeriod']);

    // Actual Meter Readings & Variance Comparison
    Route::post('/households/{household}/actual-readings', [CalculationController::class, 'recordActualReading']);
    Route::get('/households/{household}/actual-readings', [CalculationController::class, 'actualReadings']);

    // Administrator Expert System Rule Management
    Route::prefix('admin')->group(function () {
        Route::apiResource('rules', AdminRuleController::class);
    });
});
