<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EventController;

Route::post('/events', [EventController::class, 'store']);
Route::get('/health', [EventController::class, 'health']);
