<?php

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

Route::get('/backoffice', function () {
    return Inertia::render('Backoffice/Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/backoffice/order', function(){
    return Inertia::render('Backoffice/Orders/Order');
})->middleware(['auth', 'verified'])->name('order');

Route::get('/backoffice/configuration/shopee-fee', function(){
    return Inertia::render('Backoffice/Configuration/ShopeeFee');
})->middleware(['auth', 'verified'])->name('ShopeeFee');

Route::middleware(['auth', 'verified'])->group(function(){
    Route::get('/backoffice/configuration/shopee-fee-create', [ShopeeFeeController::class, 'index'])->name('ShopeeFee');
    // Route::get('/backoffice/configuration/shopee-fee-create', [ShopeeFeeController::class, 'create'])->name('ShopeeFee');
    Route::post('/backoffice/configuration/shopee-fee-create', [ShopeeFeeController::class, 'store'])->name('ShopeeFeePost');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
