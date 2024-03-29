<?php

use Illuminate\Support\Facades\Route;

Route::namespace('Api')->middleware('auth:api')->group(function () {

    Route::get('/app-dir-path', function () {
        return rconfig_appdir_path();
    });


    /* DASHBOARD ROUTES */
    Route::prefix('dashboard')->group(function () {
        Route::get('/sysinfo', 'DashboardController@getSysInfo')->name('api.dashboard.sysinfo');
        Route::get('/configinfo', 'DashboardController@getConfigInfo')->name('api.dashboard.configinfo');
        Route::get('/health-latest', 'SystemHealthController@healthLatest')->name('api.dashboard.health-latest');
    });

    /* DEVICES ROUTES */
    Route::resource('devices', 'DeviceController');
    Route::prefix('device')->group(function () {
        Route::post('/download-now', 'ConfigActionsController@downloadNow');
        Route::post('/purge-failed-configs', 'ConfigActionsController@purgeFailed');
        Route::get('/all-device-names', 'DeviceController@allDeviceNames');
    });
    Route::resource('categories', 'CategoryController');
    Route::resource('commands', 'CommandController');
    Route::resource('vendors', 'VendorController');
    Route::resource('tags', 'TagController');
    Route::resource('templates', 'TemplateController');
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

    /* SETTINGS RESOURCE ROUTES*/
    Route::prefix('settings')->group(function () {
        Route::resource('/settings', 'SettingBannerController')->only(['show']);
        Route::resource('/banner', 'SettingBannerController')->only(['show', 'update']);
        Route::resource('/timezone', 'SettingTimezoneController')->only(['show', 'update']);
        Route::resource('/email', 'SettingEmailController')->only(['show', 'update']);
        Route::resource('/debug', 'SettingDebugController')->only(['show', 'update']);
        Route::resource('/support-info', 'SettingsSupportInfoController')->only(['index']);

        /* SETTINGS SPECIFIC ROUTES*/
        Route::get('/get-timezone-list', 'SettingTimezoneController@getTimezoneList');
        Route::get('/test-email', 'SettingEmailController@TestMail');
        Route::get('/test-notification', 'SettingEmailController@TestMailNotifications');
    });

    Route::get('/license-info', 'LicenseInfoController@index');

    Route::resource('activitylogs', 'ActivityLogController')->only(['index', 'show', 'destroy']);
    Route::prefix('activitylogs')->group(function () {
        Route::get('last5/{deviceid}', 'ActivityLogController@getLast5');
        Route::get('device-stats/{deviceid}', 'ActivityLogController@showStatsByDeviceId');
        Route::get('clear-logs/{deviceid}', 'ActivityLogController@clearLogsByDeviceId');
    });

    Route::resource('users', 'UserController');
});
