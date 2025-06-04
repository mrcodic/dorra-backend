<?php


use App\Http\Controllers\Dashboard\TemplateController;
use App\Http\Controllers\Shared\LibraryAssetController;
use App\Http\Controllers\Api\V1\User\{Auth\LoginController,
    Auth\OtpController,
    Auth\RegisterController,
    Auth\ResetPasswordController,
    Category\CategoryController,
    Design\DesignController,
    General\MainController,
    Product\ProductController,
    Profile\PasswordController,
    Profile\ProfileController,
    Profile\UserNotificationTypeController,
    SaveController,
    ShippingAddress\ShippingAddressController,
  };
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;

Route::get('country-codes', [MainController::class, 'countryCodes']);

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
    Route::get('/google', 'redirectToGoogle')->middleware(EnsureFrontendRequestsAreStateful::class);
    Route::get('/google/callback', 'handleGoogleCallback')->middleware(EnsureFrontendRequestsAreStateful::class);
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
    Route::get('notification-types', UserNotificationTypeController::class);

    Route::apiResource('shipping-addresses', ShippingAddressController::class);

    Route::apiResource('products', ProductController::class)->only(['index', 'show']);
    Route::controller(SaveController::class)->group(function () {
        Route::post('toggle-save', 'toggleSave');
        Route::delete('bulk-delete-saved', 'destroyBulk');
    });


    Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

    Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
    Route::get('sub-categories', [MainController::class, 'subCategories']);

    Route::get('states', [MainController::class, 'states']);
    Route::get('countries', [MainController::class, 'countries']);
//    Route::apiResource('library-assets', LibraryAssetController::class)->only(['store', 'index']);
//    Route::apiResource('/designs', DesignController::class)->except(['destroy']);
//    Route::get('/design-versions/{design_version}', [DesignController::class, 'getDesignVersions']);

});



Route::apiResource('/designs', DesignController::class)->except(['destroy']);
Route::get('/design-versions/{design_version}', [DesignController::class, 'getDesignVersions']);

Route::apiResource('templates', TemplateController::class)->only(['store', 'show', 'update']);
Route::get('templates', [TemplateController::class, 'getProductTemplates'])->name("templates.products");
Route::apiResource('library-assets', LibraryAssetController::class)->only(['store', 'index']);
Route::get('template-types', [MainController::class,'templateTypes'])->name('template-types');
Route::get('tags', [MainController::class,'tags'])->name('tags');
Route::get('units', [MainController::class,'units'])->name('units');
Route::delete('/media/{media}', [MainController::class,'removeMedia'])->name('remove-media');
