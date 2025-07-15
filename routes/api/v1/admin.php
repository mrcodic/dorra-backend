<?php


use App\Http\Controllers\Api\V1\User\{General\MainController,};
use App\Http\Controllers\Dashboard\MockupController;
use App\Http\Controllers\Dashboard\TemplateController;
use App\Http\Controllers\Shared\LibraryAssetController;
use Illuminate\Support\Facades\Route;



Route::get('templates', [TemplateController::class, 'getProductTemplates'])->name("templates.products");
Route::apiResource('templates', TemplateController::class)->only(['store', 'show', 'update', 'destroy']);
Route::get('show-template/{template}', [TemplateController::class,'showTemplate']);
Route::apiResource('library-assets', LibraryAssetController::class)->only(['store', 'index']);
Route::get('template-types', [MainController::class, 'templateTypes'])->name('template-types');
Route::get('tags', [MainController::class, 'tags'])->name('tags');
Route::get('units', [MainController::class, 'units'])->name('units');
Route::delete('/media/{media}', [MainController::class, 'removeMedia'])->name('remove-media');
Route::post("orders/template-customizations", [\App\Http\Controllers\Dashboard\OrderController::class, 'templateCustomizations'])->name('template.customizations');
Route::post("convert-fabric-json", [MainController::class, 'convertFabricJson']);
Route::get('template-assets', [TemplateController::class, 'templateAssets'])->name("templates.assets");
Route::post('template-assets', [TemplateController::class, 'storeTemplateAssets'])->name("store.templates.assets");
Route::apiResource('library-assets', LibraryAssetController::class)->only(['store', 'index']);
Route::get('mockups', [MockupController::class, 'index']);
Route::get('mockup-types', [MockupController::class, 'mockupTypes']);
Route::delete('mockups/{mockup}', [MockupController::class, 'destroy']);
Route::get('mockups/{mockup}', [MockupController::class, 'showAndUpdateRecent']);

