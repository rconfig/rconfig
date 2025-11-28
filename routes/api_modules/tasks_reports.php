<?php

use Illuminate\Support\Facades\Route;

Route::resource('reports', 'TaskReportController')->only(['index', 'show', 'destroy']);
Route::post('reports/delete-many', 'TaskReportController@deleteMany');
