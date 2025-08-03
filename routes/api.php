<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ninths;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\FileUploadController;

// Rutas públicas
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/register', [RegisteredUserController::class, 'store']);

// Rutas protegidas
Route::middleware(['apiauth', 'throttle:100,1'])->group(function () {
    Route::resource('ninths', Ninths::class)->middleware('checkresource');
    Route::post('/upload', [FileUploadController::class, 'upload']);
    Route::get('files/{filename}', [FileUploadController::class, 'getFile']);
});
