<?php

use App\Http\Controllers\Dashboard\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/dashboard', 'dashboard.index')->name('dashboard');

Route::get('users/data',[UserController::class,'getData'])->name('users.data');
Route::resource('users', UserController::class);
