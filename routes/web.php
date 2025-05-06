<?php

use App\Http\Controllers\Api\V1\User\General\MainController;
use App\Http\Controllers\Dashboard\{AdminController,
    InvoiceController,
    OrderController,
    PermissionController,
    ProductController,
    RoleController,
    SubCategoryController,
    TagController,
    UserController,
    CategoryController,
    ProfileController};
use Illuminate\Support\Facades\Route;
use Laravel\Jetstream\Rules\Role;

Route::view('/login/social', 'dashboard.auth.social-login');
Route::view('confirm-password', 'dashboard.auth.confirm-password');

Route::middleware('auth')->group(function () {
    Route::get('states', [MainController::class, 'states'])->name('states');
    Route::view('/', 'dashboard.index')->name('dashboard');

    Route::prefix('/users')->as('users')->group(function () {
        Route::get('/data', [UserController::class, 'getData'])->name('.data');
        Route::get('/billing/{user}', [UserController::class, 'billing'])->name('.billing');
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

    Route::group(['prefix' => 'sub-categories', 'as' => 'sub-categories.', 'controller' => SubCategoryController::class,],function () {
        Route::get('/data', 'getData')->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
    });
    Route::resource('/sub-categories', SubCategoryController::class);

    Route::group(['prefix' => 'products', 'as' => 'products.', 'controller' => ProductController::class,],(function () {
        Route::get('/data', [ProductController::class, 'getData'])->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
    }));
    Route::resource('/products', ProductController::class);

    Route::group(['prefix' => 'tags', 'as' => 'tags.', 'controller' => TagController::class,],(function () {
        Route::get('/data', [TagController::class, 'getData'])->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
    }));
    Route::resource('/tags', TagController::class);

    Route::group(['prefix' => 'roles', 'as' => 'roles.', 'controller' => RoleController::class,],function () {
        Route::get('/data', [RoleController::class, 'getData'])->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
    });
    Route::resource('/roles', RoleController::class)->except(['show']);

    Route::group(['prefix' => 'permissions', 'as' => 'permissions.', 'controller' => PermissionController::class,],function () {
        Route::get('/data', [PermissionController::class, 'getData'])->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');

    });
    Route::resource('/permissions', PermissionController::class)->except(['show']);

    Route::group(['prefix' => 'orders', 'as' => 'orders.', 'controller' => OrderController::class,],function () {
        Route::get('/data', [OrderController::class, 'getData'])->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
    });
    Route::resource('/orders', OrderController::class);

    Route::group(['prefix' => 'invoices', 'as' => 'invoices.', 'controller' => InvoiceController::class,],function () {
        Route::get('/data', [InvoiceController::class, 'getData'])->name('data');
        Route::post('/bulk-delete', 'bulkDelete')->name('bulk-delete');
    });
    Route::resource('/invoices', OrderController::class)->except('show');


    Route::resource('/profile', ProfileController::class)->only(['index', 'update']);


    Route::prefix('/api')->group(function () {
        Route::controller(MainController::class)->group(function () {
            Route::get('states', 'states')->name('states');
            Route::get('sub-categories', 'subCategories')->name('sub-categories');
            Route::delete('/media/{media}', 'removeMedia')->name('remove-media');
        });
    });

});
