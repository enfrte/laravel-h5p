<?php

// routes/api.php
use App\Http\Controllers\H5PController;
use Illuminate\Support\Facades\Route;

// Endpoint for frontend to broadcast interactions
Route::post('/h5p/interaction', [H5PController::class, 'recordInteraction']);

// Get all progress as JSON
Route::get('/h5p/progress', [H5PController::class, 'getProgressJson']);

