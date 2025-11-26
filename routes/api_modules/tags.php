<?php

use Illuminate\Support\Facades\Route;

Route::resource('tags', 'TagController');
Route::post('/tags/delete-many', 'TagController@deleteMany');
