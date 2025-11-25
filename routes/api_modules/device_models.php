<?php

use App\Http\Controllers\Api\DeviceModelsController;
use Illuminate\Support\Facades\Route;

// Main CRUD routes
Route::prefix('device-models')->group(function () {
    Route::get('/', [DeviceModelsController::class, 'index']); // GET /api/device-models
    Route::get('/{id}', [DeviceModelsController::class, 'show']); // GET /api/device-models/{slug}
    Route::post('/', [DeviceModelsController::class, 'store']); // POST /api/device-models
    Route::get('/{deviceModel:slug}/devices', [DeviceModelsController::class, 'devices']); // GET /api/device-models/{slug}/devices

    // delete-many
    Route::post('/delete-many', [DeviceModelsController::class, 'deleteMany']); // DELETE /api/device-models/delete-many
});

/* LEGACY DEVICE MODEL ROUTES - for backward compatibility */
Route::post('/add-device-model', [DeviceModelsController::class, 'store']);
Route::get('/get-device-models', [DeviceModelsController::class, 'index']);
