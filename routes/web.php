<?php

use App\Http\Controllers\FeedController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', HomeController::class);
Route::get('feed/{site}', FeedController::class)->name('feed');
