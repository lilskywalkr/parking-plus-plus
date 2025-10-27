<?php

use App\Http\Controllers\RecordController;
use Illuminate\Support\Facades\Route;

Route::get('/record', [RecordController::class, 'index'])
    ->middleware(['auth', 'verified', 'admin'])
    ->name('record');

Route::get('/search', [RecordController::class, 'search'])
    ->middleware(['auth', 'verified', 'admin'])
    ->name('record.search');
