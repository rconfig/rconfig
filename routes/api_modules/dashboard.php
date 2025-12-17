<?php

use Illuminate\Support\Facades\Route;

Route::prefix('dashboard')->group(function () {
    Route::get('/sysinfo', 'DashboardController@getSysInfo')->name('api.dashboard.sysinfo');
    Route::get('/backup-task-status', 'DashboardController@getBackUpTaskStatus')->name('api.dashboard.backup-task-status');
    Route::get('/configinfo', 'DashboardController@getConfigInfo')->name('api.dashboard.configinfo');
    Route::get('/queueinfo', 'DashboardController@getQueueInfo')->name('api.dashboard.queueinfo');
    Route::get('/latest-devices', 'DashboardController@getLast5Devices')->name('api.dashboard.latest-devices');
    Route::get('/errored-devices', 'DashboardController@getLast5unreachableDevices')->name('api.dashboard.errored-devices');
    Route::get('/health-latest', 'SystemHealthController@healthLatest')->name('api.dashboard.health-latest');
    Route::get('/time', 'DashboardController@serverTime')->name('api.dashboard.time');
});

Route::resource('/notifications', 'NotificationsController')->only(['index', 'update']);
Route::post('/notifications/mark-all-as-read', 'NotificationsController@markAllAsRead')->name('api.notifications.mark-all-as-read');
