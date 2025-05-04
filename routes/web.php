<?php

use App\Http\Controllers\Api\V1\User\General\MainController;
use App\Http\Controllers\Dashboard\{OrderController,
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

    Route::prefix('/categories')->as('categories')->group(function () {
        Route::get('/data', [CategoryController::class, 'getData'])->name('.data');
    });
    Route::resource('/categories', CategoryController::class);

    Route::prefix('/sub-categories')->as('sub-categories')->group(function () {
        Route::get('/data', [SubCategoryController::class, 'getData'])->name('.data');
    });
    Route::resource('/sub-categories', SubCategoryController::class);

    Route::prefix('/products')->as('products')->group(function () {
        Route::get('/data', [ProductController::class, 'getData'])->name('.data');
    });
    Route::resource('/products', ProductController::class);

    Route::prefix('/tags')->as('tags')->group(function () {
        Route::get('/data', [TagController::class, 'getData'])->name('.data');
    });
    Route::resource('/tags', TagController::class);

    Route::prefix('/roles')->as('roles')->group(function () {
        Route::get('/data', [RoleController::class, 'getData'])->name('.data');
    });
    Route::resource('/roles', RoleController::class)->except(['show']);

    Route::prefix('/permissions')->as('permissions')->group(function () {
        Route::get('/data', [PermissionController::class, 'getData'])->name('.data');
    });
    Route::resource('/permissions', PermissionController::class)->except(['show']);

    Route::prefix('/orders')->as('orders')->group(function () {
        Route::get('/data', [OrderController::class, 'getData'])->name('.data');
    });
    Route::resource('/orders', OrderController::class)->except(['show']);

    Route::resource('/profile',ProfileController::class)->only(['index','update']);


    Route::prefix('/api')->group(function () {
        Route::controller(MainController::class)->group(function () {
            Route::get('states', 'states')->name('states');
            Route::get('sub-categories', 'subCategories')->name('sub-categories');
            Route::delete('/media/{media}', 'removeMedia')->name('remove-media');
        });
    });

});
