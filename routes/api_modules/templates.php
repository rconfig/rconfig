<?php

use Illuminate\Support\Facades\Route;

Route::resource('templates', 'TemplateController');
Route::get('/import-github-templates', 'TemplateGithubController@import_github_templates');
Route::get('/test-template-repo-connection', 'TemplateGithubController@test_github_repo_connection');
Route::get('/list-template-repo-folders', 'TemplateGithubController@list_template_repo_folders');
Route::post('/list-repo-folders-contents', 'TemplateGithubController@list_repo_folders_contents');
Route::post('/get-template-file-contents', 'TemplateGithubController@get_template_file_contents');
Route::get('/get-default-template', 'TemplateController@getDefaultTemplate');
Route::post('/reformat-template', 'TemplateController@reformatTemplateFile');
Route::post('/templates/delete-many', 'TemplateController@deleteMany');
