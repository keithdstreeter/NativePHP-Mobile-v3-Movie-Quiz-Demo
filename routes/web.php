<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/', 'home-page')->name('home');
Route::livewire('/movies', 'movie-index')->name('movies');
Route::livewire('/movies/{slug}', 'movie-show')->name('movies.show');
Route::livewire('/quiz/{session}', 'quiz-runner')->name('quiz.play');
Route::livewire('/quiz/{session}/summary', 'quiz-summary')->name('quiz.summary');
Route::livewire('/progress', 'progress-dashboard')->name('progress');
Route::livewire('/settings', 'settings-page')->name('settings');
Route::livewire('/leaderboard', 'leaderboard')->name('leaderboard');
