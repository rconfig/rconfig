<?php

use Illuminate\Support\Facades\Route;

Route::resource('commands', 'CommandController');
Route::post('/commands/delete-many', 'CommandController@deleteMany');
