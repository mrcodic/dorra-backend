<?php


use App\Http\Controllers\Dashboard\MockupController;
use App\Http\Controllers\Dashboard\TemplateController;
use App\Http\Controllers\Shared\CommentController;
use App\Http\Controllers\Shared\LibraryAssetController;
use App\Http\Controllers\Api\V1\User\{Auth\LoginController,
    Auth\LogoutController,
    Auth\OtpController,
    Auth\RegisterController,
    Auth\ResetPasswordController,
    Cart\CartController,
    Category\CategoryController,
    Design\DesignController,
    Folder\FolderController,
    General\MainController,
    Order\OrderController,
    Product\ProductController,
    Profile\PasswordController,
    Profile\ProfileController,
    Profile\UserNotificationTypeController,
    SaveController,
    ShippingAddress\ShippingAddressController};
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

Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::get('sub-categories', [MainController::class, 'subCategories']);

Route::apiResource('products', ProductController::class)->only(['index', 'show']);
Route::get('templates', [TemplateController::class, 'getProductTemplates'])->name("templates.products");


Route::controller(CartController::class)->group(function () {
    Route::get('/cart-info', 'cartInfo');
    Route::post('/carts/apply-discount', 'applyDiscount');
    Route::delete('/carts', 'destroy');
});
Route::apiResource('/carts', CartController::class)->only(['store', 'index']);

Route::controller(OrderController::class)->group(function () {
    Route::post('checkout', 'checkout');
    Route::get('locations', 'searchLocations');
    Route::get('track-order/{order}', 'trackOrder');
    Route::get('order-statuses', 'orderStatuses');
});

Route::apiResource('shipping-addresses', ShippingAddressController::class);


Route::middleware('auth:sanctum')->group(function () {

    Route::post('logout', LogoutController::class);

    Route::group(['prefix' => 'profile', 'controller' => ProfileController::class], function () {
        Route::get('/', 'show');
        Route::put('/', 'update');
        Route::delete('/disconnect-account/{accountId}', 'disconnectAccount');
    });

    Route::delete('/media/{media}', [MainController::class, 'removeMedia']);
    Route::put('password/update', PasswordController::class);
    Route::get('notification-types', UserNotificationTypeController::class);

    Route::controller(SaveController::class)->group(function () {
        Route::post('toggle-save', 'toggleSave');
        Route::delete('bulk-delete-saved', 'destroyBulk');
    });


    Route::controller(DesignController::class)->prefix('designs/')->group(function () {
        Route::post('bulk-delete', 'bulkDelete');
        Route::get('owners', 'owners');
        Route::get('{design}/price-details', 'priceDetails');
        Route::post('{design}/add-quantity', 'addQuantity');
        Route::get('{design}/quantities', 'getQuantities');
        Route::post('design-finalization', 'designFinalization');
    });
    Route::get('/design-versions/{design_version}', [DesignController::class,'getDesignVersions']);
    Route::apiResource('/designs', DesignController::class)->except(['destroy']);

    Route::get('states', [MainController::class, 'states']);
    Route::get('countries', [MainController::class, 'countries']);

    Route::apiResource('comments', CommentController::class)->only(['store', 'index', 'destroy']);

    Route::post('designs/assign-to-folder', [FolderController::class,'assignDesignsToFolder']);
    Route::post('folders/bulk-delete', [FolderController::class,'bulkDelete']);
    Route::apiResource('folders', FolderController::class)->only(['store', 'index']);

});


Route::apiResource('templates', TemplateController::class)->only(['store', 'show', 'update', 'destroy']);
Route::apiResource('library-assets', LibraryAssetController::class)->only(['store', 'index']);
Route::get('template-types', [MainController::class, 'templateTypes'])->name('template-types');
Route::get('tags', [MainController::class, 'tags'])->name('tags');
Route::get('units', [MainController::class, 'units'])->name('units');
Route::delete('/media/{media}', [MainController::class, 'removeMedia'])->name('remove-media');
Route::post("orders/template-customizations", [\App\Http\Controllers\Dashboard\OrderController::class, 'templateCustomizations'])->name('template.customizations');
Route::get('template-assets', [TemplateController::class, 'templateAssets'])->name("templates.assets");
Route::post('template-assets', [TemplateController::class, 'storeTemplateAssets'])->name("store.templates.assets");
Route::apiResource('library-assets', LibraryAssetController::class)->only(['store', 'index']);
Route::get('mockups', [MockupController::class,'index']);
Route::get('mockup-types', [MockupController::class,'mockupTypes']);
Route::delete('mockups/{mockup}', [MockupController::class,'destroy']);

Route::get('mockups/{mockup}', [MockupController::class,'show']);

