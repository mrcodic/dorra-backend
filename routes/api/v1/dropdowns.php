<?php

use App\Http\Controllers\Api\V1\User\{
    General\MainController,
    };
use Illuminate\Support\Facades\Route;


Route::get('country-codes',[MainController::class, 'countryCodes']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('countries',[MainController::class, 'countries']);
    Route::get('states',[MainController::class, 'states']);

});


