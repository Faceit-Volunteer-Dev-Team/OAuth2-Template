<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\EnsureUserIsLoggedIn;

// Those are all endpoitns for OAuth login/logout
Route::controller(\App\Http\Controllers\OauthController::class)->group(function () {
    Route::get('/login', 'login')->name('login');
    Route::get('/login/callback', 'doLogin')->name('doLogin');
    Route::get('/logout', 'logout')->name('logout');
});

// Those are all endpoints for the main application
// The middleware 'auth' is used to protect the routes
Route::middleware([EnsureUserIsLoggedIn::class])->group(function () {
    Route::controller(\App\Http\Controllers\HomeController::class)->group(function () {
        Route::get('/', 'home')->name('home');
    });
});
