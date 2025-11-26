<?php

use App\Http\Controllers\Api\DeviceModelController;
use Illuminate\Support\Facades\Route;

// Main CRUD routes
Route::prefix('device-models')->group(function () {
    Route::get('/', [DeviceModelController::class, 'index']); // GET /api/device-models
    Route::get('/{id}', [DeviceModelController::class, 'show']); // GET /api/device-models/{id}
    Route::post('/', [DeviceModelController::class, 'store']); // POST /api/device-models
    Route::patch('/{deviceModel:id}', [DeviceModelController::class, 'update']); // PATCH /api/device-models/{id}
    Route::delete('/{deviceModel:id}', [DeviceModelController::class, 'destroy']); // DELETE /api/device-models/{id}
    Route::get('/{deviceModel:id}/devices', [DeviceModelController::class, 'devices']); // GET /api/device-models/{id}/devices

    // delete-many
    Route::post('/delete-many', [DeviceModelController::class, 'deleteMany']); // DELETE /api/device-models/delete-many
});

/* LEGACY DEVICE MODEL ROUTES - for backward compatibility */
Route::post('/add-device-model', [DeviceModelController::class, 'store']);
Route::get('/get-device-models', [DeviceModelController::class, 'index']);
