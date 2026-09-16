<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppAPI\AuthController;
use App\Http\Controllers\AppAPI\WalletController;
use App\Http\Controllers\AppAPI\FirebaseAuthController;
use App\Http\Controllers\AppAPI\BookingController;
use App\Http\Controllers\AppAPI\InfoController;
use App\Http\Controllers\AppAPI\PackageController;
use App\Http\Controllers\AppAPI\PaymentController;
use App\Http\Controllers\AppAPI\NotificationController;
use App\Http\Controllers\AppAPI\FavoriteCenterController;
use App\Http\Controllers\AppAPI\CenterReviewController;
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
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    
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

    
    Route::prefix('payment')->group(function () {
        Route::post('create-session', [PaymentController::class, 'createSession']);
    });

    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteCenterController::class, 'index']);
        Route::post('/', [FavoriteCenterController::class, 'store']);
        Route::post('toggle', [FavoriteCenterController::class, 'toggle']);
        Route::get('{center_id}/check', [FavoriteCenterController::class, 'check']);
        Route::delete('{center_id}', [FavoriteCenterController::class, 'destroy']);
    });

    Route::prefix('reviews')->group(function () {
        Route::get('/', [CenterReviewController::class, 'index']);
        Route::post('/', [CenterReviewController::class, 'store']);
        Route::get('center/{center_id}', [CenterReviewController::class, 'byCenter']);
        Route::get('{center_id}/mine', [CenterReviewController::class, 'mine']);
        Route::put('{center_id}', [CenterReviewController::class, 'update']);
        Route::delete('{center_id}', [CenterReviewController::class, 'destroy']);
    });

    Route::get('getnotification', [NotificationController::class, 'get']);
    Route::post('notification/seen', [NotificationController::class, 'seen']);

    Route::prefix('account')->group(function () {
        Route::post('delete', [AuthController::class, 'deleteAccount']);
    });
});

Route::middleware('auth:sanctum')->prefix('wallet')->controller(WalletController::class)->group(function () {
    Route::get('balance', 'balance');
    Route::get('payment-methods', 'paymentMethods');
    Route::post('top-up', 'topUp');
    Route::post('buy', 'buy');
    Route::get('transactions', 'transactions');
});
