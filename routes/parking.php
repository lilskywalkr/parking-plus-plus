<?php

use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\ParkingSpace;


Route::get('/parking', [App\Http\Controllers\Parking::class, 'index'])
    ->middleware(['auth', 'verified'])->name('parking');

Route::get('/parking/{stall_id}/reserve', [App\Http\Controllers\Parking::class, 'edit'])
    ->middleware(['auth', 'verified'])->name('parking.reserve');

Route::patch('/parking/reserve/save', [App\Http\Controllers\Parking::class, 'update'])
    ->middleware(['auth', 'verified'])->name('parking.reserve.save');
