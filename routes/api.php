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

    Route::get('/app-dir-path', function () {
        return rconfig_appdir_path();
    });

    /* SEARCH ROUTES */
    Route::get('/search', 'QuickSearchController@search');

    Route::resource('categories', 'CategoryController');
    Route::post('/categories/delete-many', 'CategoryController@deleteMany');

    Route::resource('commands', 'CommandController');
    Route::post('/commands/delete-many', 'CommandController@deleteMany');

    Route::resource('vendors', 'VendorController');
    Route::post('/vendors/delete-many', 'VendorController@deleteMany');

    Route::resource('device-models', 'DeviceModelsController');
    Route::post('/device-models/delete-many', 'DeviceModelsController@deleteMany');

    Route::resource('tags', 'TagController');
    Route::post('/tags/delete-many', 'TagController@deleteMany');

    Route::resource('templates', 'TemplateController');
    Route::post('/templates/delete-many', 'TemplateController@deleteMany');

    Route::get('/import-github-templates', 'TemplateGithubController@import_github_templates');
    Route::get('/test-template-repo-connection', 'TemplateGithubController@test_github_repo_connection');
    Route::get('/list-template-repo-folders', 'TemplateGithubController@list_template_repo_folders');
    Route::post('/list-repo-folders-contents', 'TemplateGithubController@list_repo_folders_contents');
    Route::post('/get-template-file-contents', 'TemplateGithubController@get_template_file_contents');
    Route::get('/get-default-template', 'TemplateController@getDefaultTemplate');

    /* DEVICES SPECIFIC ROUTES*/
    Route::get('/get-device-models', 'DeviceController@getDeviceModels');

    /* TASKS ROUTES */
    Route::prefix('tasks')->group(function () {
        Route::post('/delete-many', 'TaskController@deleteMany');
        Route::get('/toggle-pause-task/{id}', 'TaskController@togglePauseTask');
        Route::post('/validate-task', 'TaskController@validateTask');
        Route::post('/run-manual-task', 'TaskManualRunController@runManualTask');
        Route::get('/recent-failed-jobs-count', 'TaskController@failedJobsLast24HrsCount');
        Route::resource('monitored', 'MonitoredScheduledTaskLogItemController');
    });
    Route::resource('tracked-jobs', 'TrackedJobController')->only(['show']);
    Route::resource('tasks', 'TaskController');

    /* CONFIGS ROUTES */
    Route::resource('configs', 'ConfigController')->only(['index', 'show', 'destroy']);
    Route::resource('reports', 'TaskReportController')->only(['index', 'show', 'destroy']);
    Route::prefix('configs')->group(function () {
        Route::get('all-by-deviceid/{id}/{status}', 'ConfigController@getAllById');
        Route::get('latest-by-deviceid/{id}', 'ConfigController@getLatestById');
        Route::get('view-config/{id}', 'ConfigController@getSingleConfigFile');
        Route::get('distinct-commands/{id}', 'ConfigController@getDistinctCommands');
        Route::post('/search', 'ConfigSearchController@search');
    });

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
