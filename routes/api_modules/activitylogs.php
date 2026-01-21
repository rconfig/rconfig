<?php

use Illuminate\Support\Facades\Route;

Route::prefix('activitylogs')->group(function () {
    Route::get('last5/{deviceid}', 'ActivityLogController@getLast5');
    Route::get('Last5Api/{deviceid}', 'ActivityLogController@getLast5Api');
    Route::get('device-stats/{deviceid}', 'ActivityLogController@showStatsByDeviceId');
    Route::get('device-stats-api/{deviceid}', 'ActivityLogController@showStatsByDeviceIdApi');
    Route::get('clear-logs/{deviceid}', 'ActivityLogController@clearLogsByDeviceId');
    Route::post('archive-logs', 'ActivityLogController@archiveLogs');
});
Route::resource('activitylogs', 'ActivityLogController')->only(['index', 'show', 'destroy']);
Route::post('/activitylogs/delete-many', 'ActivityLogController@deleteMany');
