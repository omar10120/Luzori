<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppAPI\AuthController;
use App\Http\Controllers\AppAPI\WalletController;
use App\Http\Controllers\AppAPI\FirebaseAuthController;

/*
|--------------------------------------------------------------------------
| App API Routes (Main Database)
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('social-login', [FirebaseAuthController::class, 'socialLogin']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->prefix('wallet')->controller(WalletController::class)->group(function () {
    Route::get('balance', 'balance');
    Route::get('payment-methods', 'paymentMethods');
    Route::post('top-up', 'topUp');
    Route::get('transactions', 'transactions');
});
