<?php

use App\Http\Controllers\ActivitiesController;
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
Route::get('/activities', [ActivitiesController::class, 'listActivities'])->name('list')->middleware([VerifyJwt::class]);
Route::get('/homeworks', [ActivitiesController::class, 'listActivities'])->name('list')->middleware([VerifyJwt::class]);
Route::get('/activity/{id}', [ActivitiesController::class, 'getActivity'])->name('get')->middleware([VerifyJwt::class]);
Route::post('/submitWorkFiles/{id}', [ActivitiesController::class, 'submitWorkFiles'])->name('submitWorkFiles')->middleware([VerifyJwt::class]);
Route::get('/submitstatus/{id}', [ActivitiesController::class, 'submitStatus'])->name('submitStatus')->middleware([VerifyJwt::class]);
Route::post('/activities/create', [ActivitiesController::class, 'create'])->name('create')->middleware([VerifyJwt::class]);
Route::post('/activities/create/file', [ActivitiesController::class, 'createFile'])->name('create')->middleware([VerifyJwt::class]);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');
