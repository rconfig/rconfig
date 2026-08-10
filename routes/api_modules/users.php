<?php

use App\Http\Controllers\Api\NotificationEnumsController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserNotificationPreferencesController;
use Illuminate\Support\Facades\Route;

/*
 * Self service account endpoints. These act on the authenticated user. The {userid}
 * segments are kept so existing clients keep working, but they are not trusted: the
 * controller resolves the account from the session, never from the URL.
 */
Route::controller(UserController::class)->prefix('user')->group(function () {
    Route::get('/profile', 'profile');
    Route::post('/update-profile/{userid}', 'updateProfile');
    Route::post('/set-notification-status/{userid}', 'setNotificationStatus');
    Route::post('/{userid}/setLocale', 'setLocale');
    Route::post('/add-external-link/{id}', 'addExternalLink');
    Route::get('/get-external-links/{id}', 'getExternalLinks');
    Route::post('/remove-external-link/', 'removeExternalLink');
    Route::post('/{userid}/change-password', 'changePassword');

    Route::get('/notification-preferences', [UserNotificationPreferencesController::class, 'index']);
    Route::patch('/notification-preferences', [UserNotificationPreferencesController::class, 'update']);
});

/*
 * Approving an SSO account decides whether someone can sign in at all, so it is
 * account administration rather than self service.
 */
Route::post('/user/set-socialite-approval-status/{userid}', [UserController::class, 'setSocialiteApprovalStatus'])
    ->middleware('admin');

/* User administration. Admin only: these act on arbitrary accounts. */
Route::middleware('admin')->group(function () {
    Route::resource('users', 'UserController');
    Route::post('/users/delete-many', 'UserController@deleteMany');
});

Route::get('/notification-enums', [NotificationEnumsController::class, 'index']);
