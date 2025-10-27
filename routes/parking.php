<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Parking\ParkingSpaceAvailabilityChangeController;
use App\Http\Controllers\Parking\ParkingSpaceReservationController;


Route::get('/parking', [\App\Http\Controllers\Parking\ParkingController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('parking');

Route::get('/parking/{stall_id}/reserve', [\App\Http\Controllers\Parking\ParkingController::class, 'edit'])
    ->middleware(['auth', 'verified'])->name('parking.reserve');

Route::patch('/parking/reserve/save', ParkingSpaceReservationController::class)
    ->middleware(['auth', 'verified', 'user'])->name('parking.reserve.save');

Route::patch('/parking/block/save', ParkingSpaceAvailabilityChangeController::class)
    ->middleware(['auth', 'verified', 'admin'])->name('parking.block.save');
