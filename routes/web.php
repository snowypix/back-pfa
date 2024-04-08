<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

Route::get('/test', [TestController::class, 'test'])->name('test');
// Route::get('/{id}', function ($id) {
//     return $id;
// })->name('index');
