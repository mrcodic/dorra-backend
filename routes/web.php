<?php

use App\Http\Controllers\Dashboard\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/dashboard', 'dashboard.index')->name('dashboard');

Route::prefix('/users')->as('users')->group(function () {
    Route::get('/data',[UserController::class,'getData'])->name('.data');
    Route::get('/billing/{user}', [UserController::class,'billing'])->name('.billing');


});
Route::resource('/users', UserController::class);
