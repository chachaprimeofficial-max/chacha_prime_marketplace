<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard',[DashboardController::class,'index'])->name('dashboard');
    Route::get('/products',[ProductController::class,'index'])->name('products.index');
    Route::get('/products/create',[ProductController::class,'create'])->name('products.create');
    Route::post('/products',[ProductController::class,'store'])->name('products.store');
    Route::get('/products/{product}/edit',[ProductController::class,'edit'])->name('products.edit');
    Route::put('/products/{product}',[ProductController::class,'update'])->name('products.update');
    Route::post('/products/{product}/variations',[ProductController::class,'variationStore'])->name('products.variations.store');
    Route::delete('/products/{product}/variations/{variation}',[ProductController::class,'variationDelete'])->name('products.variations.delete');
    Route::post('/products/{product}/inventory',[ProductController::class,'inventory'])->name('products.inventory');
    Route::post('/products/{product}/media',[ProductController::class,'mediaStore'])->name('products.media.store');
    Route::delete('/products/{product}/media/{media}',[ProductController::class,'mediaDelete'])->name('products.media.delete');
});
