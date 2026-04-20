<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'user'])->group(function () {
    Route::get('/stall', [App\Http\Controllers\StallController::class, 'index'])->name('stall');

    Route::post('/stall/checkout/{id?}', [App\Http\Controllers\StallController::class, 'checkout'])->name('stall.checkout');

    Route::get('/stall/checkout/success', [App\Http\Controllers\StallController::class, 'success'])->name('stall.checkout.success');

    Route::get('/stall/checkout/cancel', [App\Http\Controllers\StallController::class, 'cancel'])->name('stall.checkout.cancel');
});

Route::post('/stripe/webhook', [App\Http\Controllers\StallController::class, 'webhook'])
    ->name('stripe.webhook');
