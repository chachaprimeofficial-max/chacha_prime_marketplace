<?php

use App\Http\Controllers\Customer\WalletController;
use App\Http\Controllers\Storefront\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

Route::middleware('auth')->prefix('account')->name('customer.')->group(function () {
    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet');
});
