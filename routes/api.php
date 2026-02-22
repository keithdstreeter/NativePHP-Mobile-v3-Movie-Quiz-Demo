<?php

use App\Http\Controllers\ContentController;
use App\Http\Controllers\LeaderboardController;
use Illuminate\Support\Facades\Route;

Route::post('/scores', [LeaderboardController::class, 'store']);
Route::get('/leaderboard', [LeaderboardController::class, 'index']);
Route::get('/leaderboard/{movie}', [LeaderboardController::class, 'show']);
Route::put('/devices/{deviceId}', [LeaderboardController::class, 'updateDevice']);

Route::get('/questions', [ContentController::class, 'index']);
