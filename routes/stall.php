<?php

use Illuminate\Support\Facades\Route;

Route::get('/stall', [App\Http\Controllers\StallController::class, 'index'])
    ->middleware(['auth', 'verified', 'user'])
    ->name('stall');

Route::post('/stall/checkout/{id?}', [App\Http\Controllers\StallController::class, 'checkout'])
    ->middleware(['auth', 'verified', 'user'])
    ->name('stall.checkout');

Route::get('/stall/checkout/success', [App\Http\Controllers\StallController::class, 'success'])
    ->middleware(['auth', 'verified', 'user'])
    ->name('stall.checkout.success');

Route::get('/stall/checkout/cancel', [App\Http\Controllers\StallController::class, 'cancel'])
    ->middleware(['auth', 'verified', 'user'])
    ->name('stall.checkout.cancel');

Route::post('/stripe/webhook', [App\Http\Controllers\StallController::class, 'webhook'])
    ->name('stripe.webhook');
