<?php

use Illuminate\Support\Facades\Route;

Route::resource('vendors', 'VendorController');
Route::post('/vendors/delete-many', 'VendorController@deleteMany');
