<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\Auth\ManagerController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactRequestController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth/managers'], function () {
    Route::post('/login', [ManagerController::class, 'login']);
    Route::post('/logout', [ManagerController::class, 'logout'])
        ->middleware('auth:manager');

    Route::get('/me', [ManagerController::class, 'me'])
        ->middleware('auth:manager');
});

Route::group(['prefix' => 'auth/users'], function () {
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/register', [UserController::class, 'register']);
    Route::post('/logout', [UserController::class, 'logout'])
        ->middleware('auth:manager');
});


Route::middleware('auth:manager')->group(function () {

    Route::get('/seos', [SeoController::class, 'index']);
    Route::put('/seos/{id}', [SeoController::class, 'update']);
});

Route::get('/settings', [SettingsController::class, 'index']);
Route::post('/settings', [SettingsController::class, 'update']);
Route::get('/seos/{key}', [SeoController::class, 'show']);



Route::apiResource('categories', CategoryController::class);

Route::get('/categories/{slug}/products', [CategoryController::class, 'products']);

Route::apiResource('products', ProductController::class);
Route::apiResource('orders', OrderController::class);

Route::apiResource('contact-requests', ContactRequestController::class);


Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/', [CartController::class, 'store']);
    Route::put('/items/{cartItemId}', [CartController::class, 'update']);
    Route::delete('/items/{cartItemId}', [CartController::class, 'destroy']);
    Route::post('/clear', [CartController::class, 'clear']);
});
