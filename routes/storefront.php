<?php
use App\Http\Controllers\Customer\AccountController;
use App\Http\Controllers\Customer\NotificationController;
use App\Http\Controllers\Customer\ReturnController;
use App\Http\Controllers\Customer\WalletController;
use App\Http\Controllers\Customer\WishlistReviewController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\ProductController;
use Illuminate\Support\Facades\Route;
Route::get('/products',[ProductController::class,'index'])->name('products.index');
Route::get('/products/{product:slug}',[ProductController::class,'show'])->name('products.show');
Route::get('/cart',[CartController::class,'index'])->name('cart.index');
Route::post('/cart/add',[CartController::class,'add'])->name('cart.add');
Route::post('/cart/update',[CartController::class,'update'])->name('cart.update');
Route::get('/cart/remove/{key}',[CartController::class,'remove'])->name('cart.remove');
Route::middleware('auth')->group(function(){
 Route::get('/checkout',[CheckoutController::class,'index'])->name('checkout.index');
 Route::post('/checkout/place',[CheckoutController::class,'place'])->name('checkout.place');
 Route::get('/checkout/success/{order}',[CheckoutController::class,'success'])->name('checkout.success');
 Route::prefix('account')->name('customer.')->group(function(){
  Route::get('/',[AccountController::class,'dashboard'])->name('dashboard');
  Route::get('/orders',[AccountController::class,'orders'])->name('orders');
  Route::get('/orders/{order}',[AccountController::class,'order'])->name('orders.show');
  Route::post('/orders/{order}/cancel',[AccountController::class,'cancel'])->name('orders.cancel');
  Route::get('/returns',[ReturnController::class,'index'])->name('returns');
  Route::get('/orders/{order}/return',[ReturnController::class,'create'])->name('returns.create');
  Route::post('/returns',[ReturnController::class,'store'])->name('returns.store');
  Route::post('/returns/{return}/cancel',[ReturnController::class,'cancel'])->name('returns.cancel');
  Route::get('/notifications',[NotificationController::class,'index'])->name('notifications');
  Route::post('/notifications/{notification}/read',[NotificationController::class,'read'])->name('notifications.read');
  Route::post('/notifications/read-all',[NotificationController::class,'readAll'])->name('notifications.read-all');
  Route::get('/wallet',[WalletController::class,'index'])->name('wallet');
  Route::get('/wishlist',[WishlistReviewController::class,'wishlist'])->name('wishlist');
  Route::post('/wishlist/{product}',[WishlistReviewController::class,'add'])->name('wishlist.add');
  Route::delete('/wishlist/{product}',[WishlistReviewController::class,'remove'])->name('wishlist.remove');
  Route::post('/products/{product}/review',[WishlistReviewController::class,'storeReview'])->name('reviews.store');
 });
});
