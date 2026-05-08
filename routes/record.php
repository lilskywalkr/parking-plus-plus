<?php

use App\Http\Controllers\RecordController;
use Illuminate\Support\Facades\Route;

Route::get('/record', [RecordController::class, 'index'])
    ->middleware(['auth', 'verified', 'admin'])
    ->name('record');

Route::get('/record/filter', [RecordController::class, 'filter'])
    ->middleware(['auth', 'verified', 'admin'])
    ->name('record.filter');
