<?php

use App\Http\Controllers\Auth\AuthMarketplaceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductDiscountController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ShopeeAdsController;
use App\Http\Controllers\ShopeeController;
use App\Http\Controllers\ShopeeFeeController;
use App\Models\Marketplace;
use App\Services\Shopee\ShopeeServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/redis-session', function () {
    return Redis::scan(0, [
        'match' => '*',
        'count' => 100,
    ]);
});

Route::get('/redis-flush', function () {
    Redis::flushdb();

    return 'Redis database cleared.';
});

Route::get('/', function () {
    return Inertia::render('Home', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::middleware(['auth', 'verified'])->prefix('backoffice')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('orders')->controller(OrderController::class)->group(function () {
        Route::get('/order-list', 'index')->name('order');
        Route::get('/order-sync', 'orderSync')->name('order.sync');
    });

    Route::controller(AuthMarketplaceController::class)->group(function () {
        Route::get('/auth-shopee', 'auth_shopee')->name('shopee.auth');
        Route::get('/callback-shopee', 'callback')->name('shopee.callback');
        Route::get('/refresh-shopee', 'refresh_token_shopee')->name('shopee.refresh');
        Route::get('/{marketplace}/cancel-shopee', 'cancel_shopee')->name('shopee.cancel');
        Route::get('/{marketplace}/callback-cancel-shopee', 'callback_cancel')->name('shopee.callback_cancel');
    });

    Route::controller(ShopeeController::class)->group(function () {
        Route::get('/{marketplace}/shopee-get-products', 'shopeeGetProducts')->name('shopee.get_products');
        Route::get('/configuration/{marketplace}/ads-shopee', 'shopeeAds')->name('shopee.ads');
        Route::post('/configuration/{marketplace}/ads-shopee', 'shopeeAdsEdit')->name('shopee.ads.edit');
        Route::post('/{marketplace}/order-sync', 'orderSync')->name('shopee.order.get');
    });

    Route::get('/{marketplace}/shopee-discounts', function (Marketplace $marketplace, Request $request, ShopeeServices $shopee) {
        $filters = $request->validate([
            'discount_status' => ['sometimes', 'in:upcoming,ongoing,expired'],
            'page_no' => ['sometimes', 'integer', 'min:1'],
            'page_size' => ['sometimes', 'integer', 'between:1,100'],
        ]);

        return $shopee->getDiscountList(
            $marketplace->access_token,
            $marketplace->app_key,
            $marketplace->marketplace_id,
            $marketplace->shop_id,
            $filters['discount_status'] ?? 'ongoing',
            $filters['page_no'] ?? 1,
            $filters['page_size'] ?? 100,
        );
    })->name('shopee.discounts');

    Route::get('/{marketplace}/shopee-discount', function (Marketplace $marketplace, ShopeeServices $shopee) {
        return $shopee->getDiscount(
            $marketplace->access_token,
            $marketplace->app_key,
            $marketplace->marketplace_id,
            $marketplace->shop_id,
            493849005015225,
        );
    })->name('shopee.discount');

    Route::prefix('purchases')->controller(PurchaseController::class)->group(function () {
        Route::get('purchases-list', 'index')->name('purchases.list');
        Route::get('purchases-create', 'create')->name('purchases.create');
        Route::post('purchases-list', 'store')->name('purchases.store');
        Route::get('purchases-products', 'indexPurchaseProducts')->name('purchases.products');
    });

    Route::prefix('products')->group(function () {
        Route::get('product-discounts', [ProductDiscountController::class, 'index'])->name('product_discounts');
        Route::post('product-discounts/sync', [ProductDiscountController::class, 'sync'])->name('product_discounts.sync');
        Route::get('product-discounts/{productDiscount:discount_id}', [ProductDiscountController::class, 'show'])->name('product_discounts.show');

        Route::controller(ProductController::class)->group(function () {
            Route::get('product-list', 'index')->name('products');
            Route::get('product-list/create', 'create')->name('products.create');
            Route::post('product-list/create', 'store')->name('products.store');
            Route::get('product-list/edit/{id}', 'edit')->name('products.edit');
            Route::put('product-list/edit/{id}', 'put')->name('products.put');
            Route::delete('product-list/{product}', 'delete')->name('products.delete');
        });

        Route::controller(ProductCategoryController::class)->group(function () {
            Route::get('product-category', 'index')->name('product_category');
            Route::post('product-category', 'store')->name('product_category.store');
            Route::get('product-category/edit/{id}', 'edit')->name('product_category.edit');
            Route::put('product-category/edit/{id}', 'put')->name('product_category.put');
            Route::delete('product-category/{categories}', 'delete')->name('product_category.delete');
        });
    });

    Route::controller(MarketplaceController::class)->prefix('marketplace')->group(function () {
        Route::get('/', 'index')->name('marketplace');
        Route::post('/', 'store')->name('marketplace.store');
        Route::match(['get', 'post'], 'edit/{id}', 'edit')->name('marketplace.edit');
        Route::put('edit/{id}', 'put')->name('marketplace.put');
        Route::match(['delete', 'post'], '{id}', 'delete')->name('marketplace.delete');
    });

    Route::prefix('configuration')->controller(ShopeeFeeController::class)->group(function () {
        Route::get('shopee-fee-create', 'index')->name('ShopeeFee');
        Route::post('shopee-fee-create', 'store')->name('ShopeeFeePost');
        Route::get('shopee-fee-create/edit/{id}', 'edit')->name('shopeeFee.edit');
        Route::put('shopee-fee-create/edit/{id}', 'put')->name('shopeeFee.put');
        Route::delete('shopee-fee-create/{configFee}', 'delete')->name('shopeeFee.delete');
    });

    Route::get('/configuration/ads-shopee', [ShopeeAdsController::class, 'index'])->name('shopee.ads.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
