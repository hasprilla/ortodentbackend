<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Ninths;


Route::resource('ninths', App\Http\Controllers\Ninths::class)->middleware('checkresource');
