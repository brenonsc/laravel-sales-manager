<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['middleware' => 'api', 'prefix' => 'auth'], function () {
    Route::post('login', 'App\Http\Controllers\AuthController@login') -> name('login');
    Route::post('signup', 'App\Http\Controllers\AuthController@signup');
    Route::post('logout', 'App\Http\Controllers\AuthController@logout');
    Route::post('refresh', 'App\Http\Controllers\AuthController@refresh');
    Route::get('me', 'App\Http\Controllers\AuthController@me');
});

Route::group(['middleware' => 'api'], function (){
    Route::get('clients', 'App\Http\Controllers\ClientController@index');
    Route::post('clients', 'App\Http\Controllers\ClientController@create');
    Route::put('clients/{id}', 'App\Http\Controllers\ClientController@update');
    Route::delete('clients/{id}', 'App\Http\Controllers\ClientController@destroy');
});
