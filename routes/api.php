<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HeroController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

Route::post('/heroes', [HeroController::class, 'store'])->middleware('auth:api');

Route::get('/heroes', [HeroController::class, 'index'])->middleware('auth:api');

// Route::middleware('auth:api')->group(function () {
//     Route::get('/user', function (Request $request) {
//         return $request->user();
//     });

//     Route::post('/logout', [AuthController::class, 'logout']);
// });