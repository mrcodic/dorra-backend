<?php

use App\Http\Controllers\Api\V1\User\{Auth\LoginController,
    Auth\OtpController,
    Auth\RegisterController,
    Auth\ResetPasswordController,
    Category\CategoryController,
    General\MainController,
    Product\ProductController,
    Profile\PasswordController,
    Profile\ProfileController,
    Profile\UserNotificationTypeController,
    ShippingAddress\ShippingAddressController};
use Illuminate\Support\Facades\Route;

Route::get('country-codes',[MainController::class, 'countryCodes']);

Route::post('/otp/expiration-time', [OtpController::class, 'getExpirationTimeOtp']);
Route::prefix('register')->group(function () {
    Route::post('/otp/send', [OtpController::class, 'sendRegistrationOtp']);
    Route::post('/', RegisterController::class);
});

Route::prefix('password')->group(function () {
    Route::post('/otp/send', [OtpController::class, 'sendPasswordResetOtp']);
    Route::post('/otp/confirm', [OtpController::class, 'confirmPasswordResetOtp']);
    Route::post('reset', ResetPasswordController::class);
});


Route::prefix('login')->controller(LoginController::class)->group(function () {
    Route::post('/', LoginController::class);
    Route::get('/google', 'redirectToGoogle');
    Route::get('/google/callback', 'handleGoogleCallback');
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

    Route::apiResource('shipping-addresses', ShippingAddressController::class)->except('show');
    Route::apiResource('products',ProductController::class)->only(['index','show']);

    Route::get('sub-categories',[MainController::class, 'subCategories']);
    Route::apiResource('categories',CategoryController::class)->only(['index','show']);

    Route::get('states',[MainController::class, 'states']);
    Route::get('countries',[MainController::class, 'countries']);
});


