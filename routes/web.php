<?php

Route::get('/', 'WelcomeController@index');
Route::group(['middleware' => 'auth'], function () {
    Route::get('message', [
        'name' => 'message',
        'callback' => 'MessageController@index'
    ]);
    Route::get('upload', [
        'name' => 'upload',
        'callback' => 'PaperController@create'
    ]);
    Route::post('upload', [
        'callback' => 'PaperController@store'
    ]);
    Route::put('paper/{id}', 'PaperController@update');
    Route::get('download/{file}', 'WelcomeController@download')->name('download');
});
Route::get('user/{id}', [
    'name' => 'user',
    'callback' => 'UserController@show'
]);
Route::post('user/{id}/edit', [
    'name' => 'user.edit',
    'callback' => 'UserController@edit'
]);
Route::get('search', [
    'name' => 'search',
    'callback' => 'SearchController@index'
]);
Route::get('paper/{id}', [
    'name' => 'paper.show',
    'callback' => 'PaperController@show' 
]);
Route::group(['middleware' => 'authenticate'], function () {
    Route::get('login', [
        'name' => 'login',
        'callback' => 'AuthController@login'
    ]);
    Route::post('login', [
        'callback' => 'AuthController@loginAction'
    ]);
    Route::get('register', [
        'name' => 'register',
        'callback' => 'AuthController@register'
    ]);
    Route::post('register', [
        'callback' => 'AuthController@registerAction' 
    ]);
    Route::get('forgot', [
        'name' => 'forgot',
        'callback' => 'AuthController@forgot'
    ]);
    Route::post('forgot', [
        'callback' => 'AuthController@forgotAction'
    ]);
    Route::get('recovery', [
        'name' => 'recovery',
        'callback' => 'AuthController@recovery'
    ]);
    Route::post('recovery', [
        'callback' => 'AuthController@recoveryAction'
    ]);
});
Route::get('logout', [
    'name' => 'logout',
    'callback' => 'AuthController@logout'
]);
Route::post('update_picture/{id}', [
    'name' => 'update_picture',
    'callback' => 'UserController@editPicture'
]);
Route::group([
    'prefix' => 'dashboard',
    'middleware' => ['auth', 'auth.admin']
], function () {
    Route::get('/', 'DashboardController@index')->name('dashboard');
    Route::get('users', 'DashboardController@users')->name('dashboard.users');
    Route::get('papers', 'DashboardController@papers')->name('dashboard.papers');
    Route::put('users/{id}', 'DashboardController@usersEdit')->name('dashboard.users.edit');
    Route::delete('users/{id}', 'DashboardController@usersDelete');
    Route::post('users', 'DashboardController@usersCreate');
    Route::post('users/{id}', 'DashboardController@usersAdmin')->name('dashboard.users.admin');
});