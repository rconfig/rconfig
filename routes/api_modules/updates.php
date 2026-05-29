<?php

use Illuminate\Support\Facades\Route;

Route::get('/version-check', 'UpdateController@check')->name('api.version-check');
