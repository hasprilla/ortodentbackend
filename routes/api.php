<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ninths;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\FileUploadController;

Route::resource('ninths', App\Http\Controllers\Ninths::class)->middleware('checkresource');
Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/upload', [FileUploadController::class, 'upload']);
Route::get('files/{filename}', [FileUploadController::class, 'getFile']);
