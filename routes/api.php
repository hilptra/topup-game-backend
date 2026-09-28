<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function() {
    Route::post('register', RegisterController::class);
    Route::post('login', [LoginController::class, 'login'])->middleware('throttle:5,1');

    Route::middleware('auth:sanctum')->group(function() {
        Route::post('logout',[LoginController::class,'logout']);
    });
});

Route::middleware('auth:sanctum')->group(function() {
    Route::get('me', [ProfileController::class, 'show']);
});