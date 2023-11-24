<?php


use app\core\facades\Route;

Route::get('/', [\app\App\Http\Controllers\HomeController::class, 'index'])->middleware('asdas:asas');

Route::get('/home', [\app\App\Http\Controllers\HomeController::class, 'home'])->name('home');