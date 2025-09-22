<?php

use Illuminate\Support\Facades\Route;

Route::get('/stall', [App\Http\Controllers\Stall::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('stall');

Route::post('/stall/checkout/{id?}', [App\Http\Controllers\Stall::class, 'checkout'])
    ->middleware(['auth', 'verified'])
    ->name('stall.checkout');

Route::get('/stall/checkout/success', [App\Http\Controllers\Stall::class, 'success'])
    ->middleware(['auth', 'verified'])
    ->name('stall.checkout.success');

Route::get('/stall/checkout/cancel', [App\Http\Controllers\Stall::class, 'cancel'])
    ->middleware(['auth', 'verified'])
    ->name('stall.checkout.cancel');

Route::post('/webhook', [App\Http\Controllers\Stall::class, 'webhook'])
    ->name('webhook');
