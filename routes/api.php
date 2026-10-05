<?php

use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Auth Routes
Route::prefix('auth')->group(function() {

    // Register
    Route::post('register', RegisterController::class);

    // Login
    Route::post('login', [LoginController::class, 'login'])->middleware('throttle:5,1');

    // Logout
    Route::middleware('auth:sanctum')->group(function() {
        Route::post('logout',[LoginController::class,'logout']);
    });

    // Forgot & Reset Password
    Route::post('forgot-password', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:3,1');
    Route::post('reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:3,1');
    
});

// Verification Routes
Route::get('email/verify/{id}/{hash}',[EmailVerificationController::class, 'verify'])
    ->middleware(['signed','throttle:6,1'])
    ->name('verification.verify');

Route::post('email/resend',[EmailVerificationController::class, 'resend'])
    ->middleware(['auth:sanctum','throttle:6,1'])
    ->name('verification.resend');


Route::middleware('auth:sanctum')->group(function() {
    Route::get('me', [ProfileController::class, 'show']);

    // Update Profile
    Route::put('profile', [ProfileController::class, 'update']);
    
    // Change Password
    Route::put('profile/password', [ProfileController::class, 'changePassword']);

    // Order
    Route::post('games/{game}/validate-account', [OrderController::class, 'validateAccount']);
    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{order}', [OrderController::class, 'show']);

    // Payment
    Route::post('payments', [PaymentController::class, 'store']);
});

// Game Routes
Route::get('games',[GameController::class,'index']);
Route::get('games/{slug}',[GameController::class,'show']);

// Product Routes
Route::get('products',[ProductController::class,'index']);

// Webhook
Route::post('webhooks/payment', [PaymentWebhookController::class, 'handle']);

