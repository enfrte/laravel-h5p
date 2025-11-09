<?php

use App\Http\Controllers\H5PController;
use Illuminate\Support\Facades\Route;

// View all student progress
Route::get('/h5p/progress', [H5PController::class, 'showAllProgress'])
    ->name('h5p.progress');

// View specific student progress
Route::get('/h5p/progress/{student_id}', [H5PController::class, 'showStudentProgress'])
    ->name('h5p.student.progress');

Route::get('/', function () {
    return view('h5p-module');
});
