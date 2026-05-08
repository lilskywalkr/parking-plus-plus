<?php

use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Route;

Route::get('/statistics', [StatisticsController::class, 'index'])
    ->name('statistics')
    ->middleware(['auth', 'verified', 'admin']);

Route::get('/statistics/show', [StatisticsController::class, 'show'])
    ->name('statistics.show')
    ->middleware(['auth', 'verified', 'admin']);

