<?php

use App\Http\Controllers\Api\V1\User\General\MainController;
use App\Http\Controllers\Dashboard\{ProductController,
    SubCategoryController,
    TagController,
    UserController,
    CategoryController};
use Illuminate\Support\Facades\Route;


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


    Route::prefix('/api')->group(function () {
        Route::get('states', [MainController::class, 'states'])->name('states');
        Route::get('sub-categories', [MainController::class, 'subCategories'])->name('sub-categories');
    });

});
