<?php

use App\Http\Controllers\Dashboard\{AdminController,
    CategoryController,
    DiscountCodeController,
    FaqController,
    InvoiceController,
    LocationController,
    MessageController,
    MockupController,
    OfferController,
    OrderController,
    PermissionController,
    ProductController,
    ProductSpecificationController,
    ProfileController,
    ReviewController,
    RoleController,
    SettingController,
    ShippingAddressController,
    SubCategoryController,
    TagController,
    TemplateController,
    UserController
};
use App\Http\Controllers\Shared\CommentController;
use App\Http\Controllers\Shared\General\MainController;
use App\Http\Controllers\Shared\LibraryAssetController;
use Illuminate\Support\Facades\Route;


Route::view('/login/social', 'dashboard.auth.social-login');
Route::view('confirm-password', 'dashboard.auth.confirm-password');

Route::middleware('auth')->group(function () {
    Route::get('states', [MainController::class, 'states'])->name('states');
    Route::view('/', 'dashboard.index')->name('dashboard');

    Route::group(['prefix' => 'users', 'as' => 'users.', 'controller' => UserController::class,], function () {
        Route::get('/data', 'getData')->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        Route::put('{id}/change-password', [UserController::class, 'changePassword'])->name('change-password');
        Route::get('/billing/{user}', [UserController::class, 'billing'])->name('billing');
        Route::get('/search', [UserController::class, 'search'])->name('search');
    });
    Route::resource('/users', UserController::class);

    Route::prefix('/admins')->controller(AdminController::class)->as('admins.')->group(function () {
        Route::get('/data', 'getData')->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
    });
    Route::resource('/admins', AdminController::class);

    Route::group(['prefix' => 'categories', 'as' => 'categories.', 'controller' => CategoryController::class,], function () {
        Route::get('/data', 'getData')->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        Route::get('/search', 'search')->name('search');
        Route::post('/landing', 'addToLanding')->name('landing');
        Route::post('/landing/remove-category', 'removeFromLanding')->name('landing.remove');
    });
    Route::delete('categories/bulk-delete', [CategoryController::class, 'bulkDelete'])->name('bulk-delete');

    Route::resource('/categories', CategoryController::class);

    Route::group(['prefix' => 'sub-categories', 'as' => 'sub-categories.', 'controller' => SubCategoryController::class,], function () {
        Route::get('/data', 'getData')->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
    });
    Route::resource('/sub-categories', SubCategoryController::class);

    Route::group(['prefix' => 'products', 'as' => 'products.', 'controller' => ProductController::class,], (function () {
        Route::get('/data', [ProductController::class, 'getData'])->name('data');
        Route::get('/search', [ProductController::class, 'search'])->name('search');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
    }));
    Route::resource('/products', ProductController::class);

    Route::group(['prefix' => 'tags', 'as' => 'tags.', 'controller' => TagController::class,], (function () {
        Route::get('/data', [TagController::class, 'getData'])->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
    }));
    Route::resource('/tags', TagController::class);

    Route::group(['prefix' => 'roles', 'as' => 'roles.', 'controller' => RoleController::class,], function () {
        Route::get('/data', [RoleController::class, 'getData'])->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
    });
    Route::resource('/roles', RoleController::class)->except(['show']);

    Route::group(['prefix' => 'permissions', 'as' => 'permissions.', 'controller' => PermissionController::class,], function () {
        Route::get('/data', [PermissionController::class, 'getData'])->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');

    });
    Route::resource('/permissions', PermissionController::class)->except(['show']);

    Route::group(['prefix' => 'orders', 'as' => 'orders.', 'controller' => OrderController::class,], function () {
        Route::get('/data', [OrderController::class, 'getData'])->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        Route::get('/pdf', 'downloadPDF')->name('pdf');
    });
    Route::resource('/orders', OrderController::class);

    Route::group(['prefix' => 'invoices', 'as' => 'invoices.', 'controller' => InvoiceController::class,], function () {
        Route::get('/data', [InvoiceController::class, 'getData'])->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
    });
    Route::resource('/invoices', InvoiceController::class)->except('show');

    Route::group(['prefix' => 'faqs', 'as' => 'faqs.', 'controller' => FaqController::class,], function () {
        Route::get('/data', [FaqController::class, 'getData'])->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
    });
    Route::resource('/faqs', FaqController::class)->except('show');

    Route::group(['prefix' => 'messages', 'as' => 'messages.', 'controller' => MessageController::class,], function () {
        Route::get('/data', [MessageController::class, 'getData'])->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
    });
    Route::resource('/messages', MessageController::class)->except('show');

    Route::group(['prefix' => 'offers', 'as' => 'offers.', 'controller' => OfferController::class,], function () {
        Route::get('/data', [OfferController::class, 'getData'])->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
    });
    Route::resource('/offers', OfferController::class)->except('show');

    Route::group(['prefix' => 'locations', 'as' => 'locations.', 'controller' => LocationController::class,], function () {
        Route::get('/data', 'getData')->name('data');
        Route::get('/dashboard', 'dashboard')->name('dashboard');
        Route::get('/search', 'search')->name('search');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
    });
    Route::resource('/logistics', LocationController::class)->except('show');

    Route::group(['prefix' => 'discount-codes', 'as' => 'discount-codes.', 'controller' => DiscountCodeController::class,], function () {
        Route::get('/data', [DiscountCodeController::class, 'getData'])->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        Route::post('/generate-export', 'generateAndExport')->name('generate.export');
        Route::post('/export', 'export')->name('export');
    });
    Route::resource('/discount-codes', DiscountCodeController::class)->except('show');

    Route::group(['prefix' => 'templates', 'as' => 'templates.', 'controller' => TemplateController::class,], function () {
        Route::get('/data', [TemplateController::class, 'getData'])->name('data');
        Route::get('/search', 'search')->name('search');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        Route::post('/landing', 'addToLanding')->name('landing');
        Route::post('/landing/remove-category', 'removeFromLanding')->name('landing.remove');
    });
    Route::post('/store-templates', [TemplateController::class, 'storeAndRedirect'])->name('templates.redirect.store');

    Route::put('/product-templates/change-status/{id}', [TemplateController::class, 'changeStatus'])->name("product-templates.change-status");
    Route::resource('/product-templates', TemplateController::class);


    Route::resource('/profile', ProfileController::class)->only(['index', 'update']);

    Route::controller(ReviewController::class)->group(function () {
        Route::delete('reviews/{review}', 'deleteReview')->name('reviews.destroy');
        Route::put('reviews/{review}', 'replyReview')->name('reviews.reply');
    });


    Route::controller(SettingController::class)->prefix('settings')->group(function () {
        Route::get('/details', 'details')->name('settings.details');
        Route::get('/payments', 'payments')->name('settings.payments');
        Route::get('/website', 'website')->name('settings.website');
        Route::get('/notifications', 'notifications')->name('settings.notifications');

        Route::post('/carousels/{carousel?}', 'createOrUpdateCarousel')->name('carousels.update');

        Route::delete('carousels/{carousel}', 'removeCarousel')->name('carousels.remove');

        Route::put('landing-sections', 'landingSections')->name('landing-sections.update');

        Route::put('statistics', 'updateStatisticsSection')->name('statistics-section.update');

        Route::post('partners', 'uploadPartners')->name('partners.create');
        Route::delete('partners/{partner}', 'removePartner')->name('partners.remove');

        Route::delete('reviews/{review}', 'removeReview')->name('reviews.remove');
        Route::post('reviews-with-images', 'storeReviewsWithImages')->name('reviews-images.create');
        Route::post('reviews', 'storeReviews')->name('reviews.create');

    });

    Route::group(['prefix' => 'mockups', 'as' => 'mockups.', 'controller' => MockupController::class,], function () {
        Route::get('/data', 'getData')->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
    });
    Route::resource('/mockups', MockupController::class);

    Route::apiResource('/products', ProductController::class)->only(['show', 'index']);


});
Route::prefix('api/v1/')->group(function () {

    Route::controller(ReviewController::class)->group(function () {
        Route::delete('reviews/{review}', 'deleteReview')->name('reviews.destroy');
        Route::put('reviews/{ review}/reply', 'deleteReply')->name('reviews.reply.destroy');
        Route::put('reviews/{review}', 'replyReview')->name('reviews.reply');
    });

    Route::controller(MainController::class)->group(function () {
        Route::get('states', 'states')->name('states');
        Route::get('sub-categories', 'subCategories')->name('sub-categories');
        Route::get('template-types', 'templateTypes')->name('template-types');
        Route::get('tags', 'tags')->name('tags');
        Route::get('units', 'units')->name('units');
        Route::delete('media/{media}', 'removeMedia')->name('remove-media');
        Route::post('media/{resource}', 'addMedia')->name('add-media');
        Route::post('dimensions', 'storeDimension')->name('dimensions.store');
        Route::get('admin-check', 'adminCheck')->name('admin-check');
    });

    Route::prefix("orders/")->controller(OrderController::class)->as("orders.")->group(function () {
        Route::post("step1", 'storeStep1')->name('step1');
        Route::post("step2", 'storeStep2')->name('step2');
        Route::post("template-customizations", 'templateCustomizations')->name('template.customizations');
        Route::post("apply-discount-code", 'applyDiscountCode')->name('apply-discount-code');
        Route::post("step4", 'storeStep4')->name('step4');
        Route::post("step5", 'storeStep5')->name('step5');
        Route::post("step6", 'storeStep6')->name('step6');
        Route::put('orders/{order}/edit-shipping-addresses', 'editShippingAddresses')->name('edit-shipping-addresses');
        Route::delete('orders/{orderId}/designs/{designId}', 'deleteDesign')->name('designs.delete');


    });
    Route::apiResource('templates', TemplateController::class)->only(['store', 'show', 'destroy']);
    Route::patch('templates/{template}', [TemplateController::class, 'updateEditorData']);

    Route::get('templates', [TemplateController::class, 'getProductTemplates'])->name("templates.products");
    Route::get('template-assets', [TemplateController::class, 'templateAssets'])->name("templates.assets");
    Route::post('template-assets', [TemplateController::class, 'storeTemplateAssets'])->name("store.templates.assets");

    Route::apiResource('library-assets', LibraryAssetController::class)->only(['store', 'index']);

    Route::resource('shipping-addresses', ShippingAddressController::class)->only(['store', 'update', 'destroy']);

    Route::post('product-specifications', ProductSpecificationController::class)->name('products.specifications.create');
    Route::get('product-specifications/{product}', [ProductSpecificationController::class, 'getProductSpecs'])->name('products.specifications');

    Route::apiResource('comments', CommentController::class)->only(['store', 'index', 'destroy']);

    Route::controller(MockupController::class)->group(function () {
        Route::get('mockups', 'index');
        Route::get('mockups/{mockup}', 'showAndUpdateRecent');
        Route::get('recent-mockups', 'recentMockups');
        Route::get('mockup-types', 'mockupTypes');
        Route::patch('mockups/{mockup}', 'updateEditorData');
        Route::delete('recent-mockups/{mockup}', 'destroyRecentMockup');
    });

    Route::post('check-product-type', [TemplateController::class, 'checkProductTypeInEditor']);
    Route::post('addMedia', [MainController::class, 'addMedia'])->name("media.store");
    Route::delete('/media/{media}', [MainController::class, 'removeMedia'])->name("media.destroy");

});
