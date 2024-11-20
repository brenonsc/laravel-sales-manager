<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(['middleware' => 'api'], function () {
    Route::post('login', [AuthController::class, 'login']) -> name('login');
    Route::post('signup', [AuthController::class, 'signup']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh_token', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
});

Route::group(['middleware' => 'api', 'prefix' => 'clients'], function (){
    Route::get('', [ClientController::class, 'index']);
    Route::get('{id}/sales', [ClientController::class, 'show']);
    Route::get('{id}/sales/{year}/{month}', [ClientController::class, 'getSalesByMonthAndYear']);
    Route::post('', [ClientController::class, 'store']);
    Route::put('{id}', [ClientController::class, 'update']);
    Route::delete('{id}', [ClientController::class, 'delete']);
});

Route::group(['middleware' => 'api', 'prefix' => 'products'], function (){
    Route::get('', [ProductController::class, 'index']);
    Route::get('{id}', [ProductController::class, 'show']);
    Route::post('', [ProductController::class, 'store']);
    Route::put('{id}', [ProductController::class, 'update']);
    Route::delete('{id}', [ProductController::class, 'delete']);
});

Route::group(['middleware' => 'api', 'prefix' => 'sales'], function (){
    Route::get('', [SaleController::class, 'index']);
    Route::post('', [SaleController::class, 'store']);
});
