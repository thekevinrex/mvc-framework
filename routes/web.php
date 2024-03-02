<?php

use PhpVueBridge\Support\Facades\Route;


Route::get('/', [\App\Http\Controllers\HomeController::class, 'index']);

Route::get('/home', [\App\Http\Controllers\HomeController::class, 'home'])->name('home');