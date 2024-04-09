<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Middleware\Admin;
use App\Http\Middleware\VerifyJwt;
use Illuminate\Support\Facades\Route;


Route::apiResource('posts', PostController::class);

Route::prefix('admin')->middleware([VerifyJwt::class, Admin::class])->group(function () {
    // Your protected API routes here
    Route::get('/users', function () {
        return 'users';
    });

    Route::post('/products', function () {
        // Logic for products API
    });

    // More routes...
});
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
