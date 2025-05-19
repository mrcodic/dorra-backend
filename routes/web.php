<?php

use App\Http\Controllers\Api\V1\User\General\MainController;
use App\Http\Controllers\Dashboard\{AdminController,
    InvoiceController,
    OrderController,
    PermissionController,
    ProductController,
    ReviewController,
    RoleController,
    ShippingAddressController,
    SubCategoryController,
    TagController,
    TemplateController,
    UserController,
    CategoryController,
    ProfileController,
    DiscountCodeController,
    SettingController
};
use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Rules\Role;

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

    Route::prefix('/admins')->as('admins')->group(function () {
        Route::get('/data', [AdminController::class, 'getData'])->name('.data');
    });
    Route::resource('/admins', AdminController::class);

    Route::group(['prefix' => 'categories', 'as' => 'categories.', 'controller' => CategoryController::class,], function () {
        Route::get('/data', 'getData')->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
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
    });
    Route::resource('/orders', OrderController::class);

    Route::group(['prefix' => 'invoices', 'as' => 'invoices.', 'controller' => InvoiceController::class,], function () {
        Route::get('/data', [InvoiceController::class, 'getData'])->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
    });
    Route::resource('/invoices', InvoiceController::class)->except('show');

    Route::group(['prefix' => 'discount-codes', 'as' => 'discount-codes.', 'controller' => DiscountCodeController::class,], function () {
        Route::get('/data', [DiscountCodeController::class, 'getData'])->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
    });
    Route::resource('/discount-codes', DiscountCodeController::class)->except('show');

    Route::group(['prefix' => 'templates', 'as' => 'templates.', 'controller' => TemplateController::class,], function () {
        Route::get('/data', [TemplateController::class, 'getData'])->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
        Route::post('/publish', 'bulkDelete')->name('publish');
    });
    Route::resource('/templates', TemplateController::class);


    Route::resource('/profile', ProfileController::class)->only(['index', 'update']);

    Route::controller(ReviewController::class)->group(function () {
        Route::delete('reviews/{review}', 'deleteReview')->name('reviews.destroy');
        Route::put('reviews/{review}', 'replyReview')->name('reviews.reply');
    });


    Route::controller(SettingController::class)->group(function () {
        Route::get('settings/details', 'details')->name('settings.details');
        Route::get('settings/payments', 'payments')->name('settings.payments');
          Route::get('settings/notifications', 'notifications')->name('settings.notifications');
    });


    Route::prefix('/api')->group(function () {
        Route::controller(ReviewController::class)->group(function () {
            Route::delete('reviews/{review}', 'deleteReview')->name('reviews.destroy');
            Route::put('reviews/{review}/reply', 'deleteReply')->name('reviews.reply.destroy');
            Route::put('reviews/{review}', 'replyReview')->name('reviews.reply');
        });
        Route::controller(MainController::class)->group(function () {
            Route::get('states', 'states')->name('states');
            Route::get('sub-categories', 'subCategories')->name('sub-categories');
            Route::delete('/media/{media}', 'removeMedia')->name('remove-media');
            Route::post('/media/{resource}', 'addMedia')->name('add-media');

        });
        Route::resource('/shipping-addresses', ShippingAddressController::class)->only(['store', 'update','destroy']);

    });

});
