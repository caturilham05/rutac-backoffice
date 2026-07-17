<?php

use App\Http\Controllers\Auth\AuthMarketplaceController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ShopeeFeeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redis;

Route::get('/redis-test', function () {
    Redis::set('test', now()->toDateTimeString());

    return Redis::get('test');
});

Route::get('/', function () {
    return Inertia::render('Home', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::get('/auth-marketplace', [AuthMarketplaceController::class, 'auth_shopee']);
Route::get('/callback', [AuthMarketplaceController::class, 'callback'])->name('shopee.callback');

Route::middleware(['auth', 'verified'])->group(function(){
    Route::get('/backoffice', function(){
        return Inertia::render('Backoffice/Dashboard');
    })->name('dashboard');

    Route::get('/backoffice/Orders/Order', function(){
        return Inertia::render('Backoffice/Orders/Order');
    })->name('order');

    Route::get('/backoffice/purchases/purchases-list', [PurchaseController::class, 'index'])->name('purchases.list');
    Route::get('/backoffice/purchases/purchases-create', [PurchaseController::class, 'create'])->name('purchases.create');
    Route::post('/backoffice/purchases/purchases-list', [PurchaseController::class, 'store'])->name('purchases.store');
    Route::get('/backoffice/purchases/purchases-products', [PurchaseController::class, 'indexPurchaseProducts'])->name('purchases.products');

    Route::get('/backoffice/products/product-list', [ProductController::class, 'index'])->name('products');
    Route::get('/backoffice/products/product-list/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/backoffice/products/product-list/create', [ProductController::class, 'store'])->name('products.store');
    Route::get('/backoffice/products/product-list/edit/{id}', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/backoffice/products/product-list/edit/{id}', [ProductController::class, 'put'])->name('products.put');
    Route::delete('/backoffice/products/product-list/{product}', [ProductController::class, 'delete'])->name('products.delete');

    Route::get('/backoffice/products/product-category', [ProductCategoryController::class, 'index'])->name('product_category');
    Route::post('/backoffice/products/product-category', [ProductCategoryController::class, 'store'])->name('product_category.store');
    Route::get('/backoffice/products/product-category/edit/{id}', [ProductCategoryController::class, 'edit'])->name('product_category.edit');
    Route::put('/backoffice/products/product-category/edit/{id}', [ProductCategoryController::class, 'put'])->name('product_category.put');
    Route::delete('/backoffice/products/product-category/{categories}', [ProductCategoryController::class, 'delete'])->name('product_category.delete');

    Route::get('/backoffice/marketplace', [MarketplaceController::class, 'index'])->name('marketplace');
    Route::post('/backoffice/marketplace', [MarketplaceController::class, 'store'])->name('marketplace.store');
    Route::get('/backoffice/marketplace/edit/{id}', [MarketplaceController::class, 'edit'])->name('marketplace.edit');
    Route::put('/backoffice/marketplace/edit/{id}', [MarketplaceController::class, 'put'])->name('marketplace.put');
    Route::delete('/backoffice/marketplace/{marketplace}', [MarketplaceController::class, 'delete'])->name('marketplace.delete');

    Route::get('/backoffice/configuration/shopee-fee-create', [ShopeeFeeController::class, 'index'])->name('ShopeeFee');
    Route::post('/backoffice/configuration/shopee-fee-create', [ShopeeFeeController::class, 'store'])->name('ShopeeFeePost');
    Route::get('/backoffice/configuration/shopee-fee-create/edit/{id}', [ShopeeFeeController::class, 'edit'])->name('shopeeFee.edit');
    Route::put('/backoffice/configuration/shopee-fee-create/edit/{id}', [ShopeeFeeController::class, 'put'])->name('shopeeFee.put');
    Route::delete('/backoffice/configuration/shopee-fee-create/{configFee}', [ShopeeFeeController::class, 'delete'])->name('shopeeFee.delete');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
