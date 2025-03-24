<?php

use App\Http\Controllers\Api\User\V1\{Auth\LoginController, Auth\RegisterController, Otp\OtpController};
use Illuminate\Support\Facades\Route;

Route::middleware('guest:sanctum')->group(function () {

    Route::post('send-otp',[OtpController::class,'sendOtp']);
    Route::post('register',RegisterController::class);
    Route::prefix('login')->controller(LoginController::class)->group(function () {
        Route::post('/',LoginController::class);
        Route::post('/google','loginWithGoogle');
//        Route::post('/google/callback','loginWithGoogle');
//        Route::post('/apple/callback','loginWithApple');
    });

});

Route::middleware('auth:sanctum')->group(function () {

    Route::get('test',function(){
        return 'test';
    });

});


