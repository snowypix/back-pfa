<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Middleware\VerifyJwt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
Route::apiResource('posts', PostController::class);

Route::middleware(VerifyJwt::class)->get('/protected', function () {
    return Auth::user();
    return response()->json(['message' => 'Protected route']);
});
Route::post('/token', [AuthController::class, 'generateJwt'])->name('token');
