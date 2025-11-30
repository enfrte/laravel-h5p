<?php

// routes/api.php
use App\Http\Controllers\H5PController;
use Illuminate\Support\Facades\Route;

Route::post('/h5p/interaction', [H5PController::class, 'recordInteraction']);
Route::post('/h5p/questionAnswerCollection', [H5PController::class, 'recordQuestionAnswerCollection']);

// Get all progress as JSON
Route::get('/h5p/progress', [H5PController::class, 'getProgressJson']);
