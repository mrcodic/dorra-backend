<?php

use App\Http\Controllers\Api\V1\User\{
    General\MainController,
    };
use Illuminate\Support\Facades\Route;


Route::get('country-codes',[MainController::class, 'countryCodes']);

Route::middleware('auth:web,sanctum')->group(function () {

    Route::get('countries',[MainController::class, 'countries']);
    Route::get('categories',[MainController::class, 'categories']);

});

Route::get('states',[MainController::class, 'states'])->name('states');
Route::get('sub-categories',[MainController::class, 'subCategories'])->name('sub-categories');

