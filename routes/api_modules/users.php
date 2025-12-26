<?php

use App\Http\Controllers\Api\NotificationEnumsController;
use App\Http\Controllers\Api\UserNotificationPreferencesController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::controller(UserController::class)->prefix('user')->group(function () {
    Route::post('/update-profile/{userid}', 'updateProfile');
    Route::post('/set-notification-status/{userid}', 'setNotificationStatus');
    Route::post('/set-socialite-approval-status/{userid}', 'setSocialiteApprovalStatus');
    Route::post('/{userid}/setLocale', 'UserController@setLocale');
    Route::post('/add-external-link/{id}', 'addExternalLink');
    Route::get('/get-external-links/{id}', 'getExternalLinks');
    Route::post('/remove-external-link/', 'removeExternalLink');
    Route::post('/add-external-link/{id}', 'UserController@addExternalLink');
    Route::get('/get-external-links/{id}', 'UserController@getExternalLinks');
    Route::post('/remove-external-link/', 'UserController@removeExternalLink');
    Route::post('/{userid}/change-password', 'UserController@changePassword');

    Route::get('/notification-preferences', [UserNotificationPreferencesController::class, 'index']);
    Route::patch('/notification-preferences', [UserNotificationPreferencesController::class, 'update']);
});

Route::resource('users', 'UserController');
Route::post('/users/delete-many', 'UserController@deleteMany');

// Route::resource('users-activity-log', 'UserLogActivityController')->only(['index', 'show']);
// Route::post('/users-activity-log/delete-many', 'UserLogActivityController@deleteMany');

Route::get('/notification-enums', [NotificationEnumsController::class, 'index']);