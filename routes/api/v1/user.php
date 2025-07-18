<?php


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
    Invitation\InvitationController,
    Order\OrderController,
    Payment\PaymentController,
    Product\ProductController,
    Profile\PasswordController,
    Profile\ProfileController,
    Profile\UserNotificationTypeController,
    SavedItems\SaveController,
    ShippingAddress\ShippingAddressController,
    Template\TemplateController};
use App\Http\Controllers\Shared\CommentController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;


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

Route::get('product-types', [ProductController::class, 'productTypes']);
Route::apiResource('products', ProductController::class)->only(['index', 'show']);

Route::controller(DesignController::class)->prefix('designs/')->group(function () {
    Route::post('bulk-restore', 'bulkRestore');
    Route::post('bulk-delete', 'bulkDelete');
    Route::post('bulk-force-delete', 'bulkForceDelete');
    Route::get('owners', 'owners');
    Route::get('{design}/price-details', 'priceDetails');
    Route::post('{design}/add-quantity', 'addQuantity');
    Route::get('{design}/quantities', 'getQuantities');
    Route::post('design-finalization', 'designFinalization');
});
Route::get('/design-versions/{design_version}', [DesignController::class, 'getDesignVersions']);
Route::apiResource('/designs', DesignController::class)->except(['destroy']);

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
Route::get('states', [MainController::class, 'states']);
Route::get('countries', [MainController::class, 'countries']);
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
        Route::get('saved-items', 'savedItems');
        Route::post('toggle-save', 'toggleSave');
        Route::delete('bulk-delete-saved', 'destroyBulk');
    });



    Route::apiResource('comments', CommentController::class)->only(['store', 'index', 'destroy']);

    Route::post('designs/assign-to-folder', [FolderController::class, 'assignDesignsToFolder']);
    Route::prefix('folders/')->controller(FolderController::class)->group(function () {
        Route::post('bulk-delete', 'bulkDelete');
        Route::post('bulk-force-delete', 'bulkForceDelete');
        Route::post('bulk-restore', 'bulkRestore');
    });

    Route::apiResource('folders', FolderController::class)->except(['destroy']);

    Route::prefix('invitations/')->controller(InvitationController::class)->group(function () {
        Route::post('send', 'send')->name('invitation.send');
        Route::get('accept', 'accept')
            ->name('invitation.accept')
            ->middleware('signed');
    });

    Route::get('trash', [MainController::class, 'trash'])->name('trash');

    Route::apiResource('orders', OrderController::class)->only(['index', 'show']);


});
Route::get('templates',[TemplateController::class, 'index']);

Route::controller(PaymentController::class)->group(function () {
    Route::get('payment-methods','paymentMethods');
    Route::post('get-payment-link','getPaymentLink');
    Route::post('payment/callback','handleCallback');
    Route::get('payment/redirect','handleRedirect');
});
