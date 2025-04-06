<?php

use App\Http\Controllers\Api\V1\User\{Auth\LoginController,
    Auth\OtpController,
    Auth\RegisterController,
    Auth\ResetPasswordController,
    General\MainController,
    Profile\PasswordController,
    Profile\ProfileController,
    Profile\ShippingAddressController,
    Profile\UserNotificationTypeController};
use Illuminate\Support\Facades\Route;

Route::prefix('register')->group(function () {
    Route::post('/send-otp', [OtpController::class, 'sendRegistrationOtp']);
    Route::post('/', RegisterController::class);
});

Route::prefix('password')->group(function () {
    Route::post('/send-otp', [OtpController::class, 'sendPasswordResetOtp']);
    Route::post('/confirm-otp', [OtpController::class, 'confirmPasswordResetOtp']);
    Route::post('reset', ResetPasswordController::class);
});


Route::prefix('login')->controller(LoginController::class)->group(function () {
    Route::post('/', LoginController::class);
    Route::post('/google', 'loginWithGoogle');
    Route::get('/google/callback', 'googleCallback');
    Route::get('/apple/callback', 'appleCallback');
});


Route::middleware('auth:sanctum')->group(function () {

    Route::group(['prefix' => 'profile', 'controller' => ProfileController::class], function () {
        Route::get('/', 'show');
        Route::put('/', 'update');
        Route::delete('/disconnect-account/{accountId}', 'disconnectAccount');
    });
    Route::delete('/media/{media}', [MainController::class, 'removeMedia']);
    Route::put('password/update', PasswordController::class);
    Route::get('notification-types',UserNotificationTypeController::class);

    Route::apiResource('shipping-addresses', ShippingAddressController::class)->only(['index', 'store', 'update', 'destroy']);


});


