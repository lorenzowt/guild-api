<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HeroController;
use App\Http\Controllers\QuestController;
use App\Http\Controllers\MissionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');

Route::post('/heroes', [HeroController::class, 'store'])->middleware('auth:api');

Route::get('/heroes', [HeroController::class, 'index'])->middleware('auth:api');

Route::get('/heroes/{hero}', [HeroController::class, 'show'])->middleware('auth:api');

Route::patch('/heroes/{hero}', [HeroController::class, 'update'])->middleware('auth:api');

Route::delete('/heroes/{hero}', [HeroController::class, 'destroy'])->middleware('auth:api');

Route::post('/heroes/{hero}/heal', [HeroController::class, 'heal'])->middleware('auth:api');

Route::get('/quests', [QuestController::class, 'index'])->middleware('auth:api');

Route::get('/quests/{quest}', [QuestController::class, 'show'])->middleware('auth:api');

Route::post('/missions', [MissionController::class, 'store'])->middleware('auth:api');

Route::get('/missions/{mission}', [MissionController::class, 'show'])->middleware('auth:api');

Route::get('/missions', [MissionController::class, 'index'])->middleware('auth:api');
// Route::middleware('auth:api')->group(function () {
//     Route::get('/user', function (Request $request) {
//         return $request->user();
//     });

//     Route::post('/logout', [AuthController::class, 'logout']);
// });