<?php

use Illuminate\Support\Facades\Route;

Route::get('/auth/providers', function () {
    // used to check if auth providers are configured and return their status to the login page for SSO buttons
    return response()->json([
        'microsoft' => ! empty(config('services.microsoft.client_id')),
        'okta' => ! empty(config('services.okta.client_id')),
        'google' => ! empty(config('services.google.client_id')),
        'saml2' => ! empty(config('services.saml2.metadata')),
    ]);
});

Route::namespace('Api')->middleware('auth:api')->group(function () {

    /* UPDATES ROUTES */

    Route::resource('tracked-jobs', 'TrackedJobController')->only(['show']);

    /* SETTINGS ROUTES */
    loadRoutesFrom('/api_modules/settings.php');
    
    /* INTEGRATIONS ROUTES */
    loadRoutesFrom('/api_modules/integrations.php');

    /* USERS ROUTES */
    loadRoutesFrom('/api_modules/users.php');

    /* DASHBOARD ROUTES */
    loadRoutesFrom('/api_modules/dashboard.php');

    /* DEVICES ROUTES */
    loadRoutesFrom('/api_modules/devices.php');

    /* DEVICE MODELS ROUTES */
    loadRoutesFrom('/api_modules/device_models.php');

    /* CATEGORY ROUTES */
    loadRoutesFrom('/api_modules/category.php');

    /* COMMANDS ROUTES */
    loadRoutesFrom('/api_modules/commands.php');

    /* VENDORS ROUTES */
    loadRoutesFrom('/api_modules/vendors.php');

    /* TAGS ROUTES */
    loadRoutesFrom('/api_modules/tags.php');

    /* TASKS ROUTES */
    loadRoutesFrom('/api_modules/tasks.php');

    /* TASKS REPORTS ROUTES */
    loadRoutesFrom('/api_modules/tasks_reports.php');

    /* TEMPLATE ROUTES */
    loadRoutesFrom('/api_modules/templates.php');

    /* CONFIGS ROUTES */
    loadRoutesFrom('/api_modules/configs.php');

    Route::get('/app-dir-path', function () {
        return rconfig_appdir_path();
    });

    /* SEARCH ROUTES */
    Route::get('/search', 'QuickSearchController@search');

    Route::get('/license-info', 'LicenseInfoController@index');

    Route::resource('activitylogs', 'ActivityLogController')->only(['index', 'show', 'destroy']);
    Route::post('/activitylogs/delete-many', 'ActivityLogController@deleteMany');

    Route::prefix('activitylogs')->group(function () {
        Route::get('last5/{deviceid}', 'ActivityLogController@getLast5');
        Route::get('device-stats/{deviceid}', 'ActivityLogController@showStatsByDeviceId');
        Route::get('clear-logs/{deviceid}', 'ActivityLogController@clearLogsByDeviceId');
    });

    Route::get('/onboarding/steps', 'UserOnboardingController@getSteps');

    Route::resource('device-credentials', 'DeviceCredentialsController');
    Route::post('/device-credentials/delete-many', 'DeviceCredentialsController@deleteMany');
});
