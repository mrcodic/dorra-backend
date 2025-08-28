<?php



use App\Http\Middleware\LocalizationMiddleware;
use App\Http\Controllers\Api\V1\User\{Auth\LoginController,
    Auth\LogoutController,
    Auth\OtpController,
    Auth\RegisterController,
    Auth\ResetPasswordController,
    Cart\CartController,
    Category\CategoryController,
    CMS\LandingController,
    Design\DesignController,
    Folder\FolderController,
    Invitation\InvitationController,
    Order\OrderController,
    Payment\PaymentController,
    Product\ProductController,
    Profile\PasswordController,
    Profile\ProfileController,
    Profile\UserNotificationTypeController,
    SavedItems\SaveController,
    ShippingAddress\ShippingAddressController,
    Team\TeamController,
    Template\TemplateController,
    Review\ReviewController
};
use App\Http\Controllers\Shared\CommentController;
use App\Http\Controllers\Shared\General\MainController;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

//Route::middleware(LocalizationMiddleware::class)->group(function () {
Route::post('contact-us', [MainController::class, 'contactUs'])->name('contact-us');
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
Route::controller(ProductController::class)->group(function () {
    Route::get('product-types', 'productTypes');
    Route::get('products/{product}/quantities', 'getQuantities');
});
Route::apiResource('products', ProductController::class)->only(['index', 'show']);


Route::controller(DesignController::class)->prefix('designs/')->group(function () {
    Route::post('{design}/teams', 'assignToTeam');
    Route::post('bulk-restore', 'bulkRestore');
    Route::post('bulk-delete', 'bulkDelete');
    Route::post('bulk-force-delete', 'bulkForceDelete');
    Route::get('owners', 'owners');
    Route::post('design-finalization', 'designFinalization');
});
Route::get('/design-versions/{design_version}', [DesignController::class, 'getDesignVersions']);
Route::apiResource('/designs', DesignController::class)->except(['destroy']);

Route::controller(CartController::class)->group(function () {
    Route::get('/cart-info', 'cartInfo');
    Route::post('/carts/apply-discount', 'applyDiscount');
    Route::get('/carts/remove-discount', 'removeDiscount');
    Route::delete('/carts', 'destroy');
    Route::get('carts/{item}/price-details', 'priceDetails');
    Route::put('carts/{item}/price-details', 'updatePriceDetails');
    Route::post('carts/{item}/add-quantity', 'addQuantity');

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
        Route::delete('{folder}/designs/bulk-delete', 'bulkDeleteDesigns');
        Route::post('bulk-force-delete', 'bulkForceDelete');
        Route::post('bulk-restore', 'bulkRestore');
    });

    Route::apiResource('folders', FolderController::class)->except(['destroy']);

    Route::apiResource('teams', TeamController::class)->except(['update']);
    Route::prefix('teams/')->controller(TeamController::class)->group(function () {
        Route::post('{team}/designs', 'assignToDesign');
        Route::delete('{team}/designs/bulk-delete', 'bulkDeleteDesigns');
        Route::post('bulk-delete', 'bulkDelete');
        Route::post('bulk-force-delete', 'bulkForceDelete');
        Route::post('bulk-restore', 'bulkRestore');
    });

    Route::post('reviews', [ReviewController::class,'store']);


    Route::get('trash', [MainController::class, 'trash'])->name('trash');

    Route::apiResource('orders', OrderController::class)->only(['index', 'show']);
    Route::get('tags', [MainController::class, 'tags'])->name('tags');

    Route::post('buy-order-again', [PaymentController::class, 'buyOrderAgain']);
});

Route::get('templates', [TemplateController::class, 'index']);
Route::get('templates/{template}', [TemplateController::class, 'show']);

Route::controller(PaymentController::class)->group(function () {
    Route::get('payment-methods', 'paymentMethods');
    Route::post('buy-order-again', 'buyOrderAgain');
    Route::post('payment/callback', 'handleCallback');
    Route::get('payment/redirect', 'handleRedirect');
});

Route::prefix('invitations/')->controller(InvitationController::class)->group(function () {
    Route::post('send', 'send')->name('invitation.send');
    Route::get('accept', 'accept')
        ->name('invitation.accept')
        ->middleware('signed');
});

Route::prefix("landing/")->controller(LandingController::class)->group(function () {
    Route::get('carousels', 'carousels');
    Route::get('settings/visibility-sections', 'visibilitySections');
    Route::get('settings/statistics', 'statistics');
    Route::get('partners', 'partners');
    Route::get('reviews-with-images', 'reviewsWithImages');
    Route::get('reviews-without-images', 'reviewsWithoutImages');
    Route::get('faqs', 'faqs');
});
Route::get('reviews/{product_id}', [ReviewController::class,'show']);
Route::get('reviews-statistics/{reviewable_id}', [ReviewController::class,'statistics']);

//});


