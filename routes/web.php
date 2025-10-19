<?php

use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserDataController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Unit management routes
Route::prefix('api/units')->group(function () {
    Route::get('/categories', [UnitController::class, 'getCategories']);
    Route::get('/all', [UnitController::class, 'getAllUnits']);
    Route::get('/category/{category}', [UnitController::class, 'getUnitsByCategory']);
    Route::get('/info/{unit}', [UnitController::class, 'getUnitInfo']);
    Route::get('/scales/{category}', [UnitController::class, 'getScaleOptions']);
    Route::get('/balance/{category}', [UnitController::class, 'getBalanceInfo']);
    Route::get('/selection-data', [UnitController::class, 'getUnitSelectionData']);
    
    Route::post('/process-selection', [UnitController::class, 'processUnitSelection']);
    Route::post('/calculate-price', [UnitController::class, 'calculateTotalPrice']);
    Route::post('/convert', [UnitController::class, 'convertQuantity']);
    Route::post('/validate', [UnitController::class, 'validateQuantity']);
});

// User data management routes (API endpoints)
Route::middleware('auth')->prefix('api/user-data')->group(function () {
    Route::get('/export', [UserDataController::class, 'export']);
    Route::post('/import', [UserDataController::class, 'import']);
    Route::delete('/delete-account', [UserDataController::class, 'deleteAccount']);
});
