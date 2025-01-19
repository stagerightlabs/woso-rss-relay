<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', HomeController::class)->name('home');
Route::view('/about', 'about')->name('about');
Route::get('feed/{site}', FeedController::class)->name('feed');
