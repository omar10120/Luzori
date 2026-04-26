<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppAPI\AuthController;
use App\Http\Controllers\AppAPI\WalletController;
use App\Http\Controllers\AppAPI\FirebaseAuthController;
use App\Http\Controllers\AppAPI\BookingController;
use App\Http\Controllers\AppAPI\InfoController;
use App\Http\Controllers\AppAPI\PackageController;

/*
|--------------------------------------------------------------------------
| App API Routes (Main Database)
|--------------------------------------------------------------------------
*/

Route::get('info', [InfoController::class, 'index']);

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('social-login', [FirebaseAuthController::class, 'socialLogin']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::post('update-profile', [AuthController::class, 'updateProfile']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('booking')->group(function () {
        Route::post('store', [BookingController::class, 'store']);
        Route::get('list', [BookingController::class, 'list']);
    });

    Route::prefix('packages')->group(function () {
        Route::get('/', [PackageController::class, 'userPurchased']);
        Route::get('{center_id}', [PackageController::class, 'index']);
        Route::get('available/{center_id}', [PackageController::class, 'available']);
        Route::post('store/{center_id}', [PackageController::class, 'store']);
    });
});

Route::middleware('auth:sanctum')->prefix('wallet')->controller(WalletController::class)->group(function () {
    Route::get('balance', 'balance');
    Route::get('payment-methods', 'paymentMethods');
    Route::post('top-up', 'topUp');
    Route::get('transactions', 'transactions');
});
