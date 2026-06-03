<?php

use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopeeFeeController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
});

Route::middleware(['auth', 'verified'])->group(function(){
    Route::get('/backoffice', function(){
        return Inertia::render('Backoffice/Dashboard');
    })->name('dashboard');

    Route::get('/backoffice/Orders/Order', function(){
        return Inertia::render('Backoffice/Orders/Order');
    })->name('order');

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
