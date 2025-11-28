<?php

use Illuminate\Support\Facades\Route;

Route::prefix('tasks')->group(function () { // must be above the resource route
    Route::post('/validate-task', 'TaskController@validateTask');
    Route::post('/run-manual-task', 'TaskManualRunController@runManualTask');
    Route::get('/recent-failed-jobs-count', 'TaskController@failedJobsLast24HrsCount');
    Route::resource('monitored', 'MonitoredScheduledTaskLogItemController');
    Route::get('/toggle-pause-task/{id}', 'TaskController@togglePauseTask');
    Route::post('/delete-many', 'TaskController@deleteMany');
});

Route::resource('tasks', 'TaskController');
