<?php

use App\Http\Controllers\H5PController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TempUserController;
    
// View all student progress
Route::get('/h5p/progress', [H5PController::class, 'showAllProgress'])
    ->name('h5p.progress');

// View specific student progress
Route::get('/h5p/progress/{student_id}', [H5PController::class, 'showStudentProgress'])
    ->name('h5p.student.progress');

Route::get('/', function () {
    if (!session()->has('temp_username')) { 
        return view('temp-user.index');
    }
    return view('h5p-module');
});

Route::get('/login', [TempUserController::class, 'index'])->name('home');
Route::post('/create-temp-user', [TempUserController::class, 'create'])->name('temp-user.create');
Route::post('/logout', [TempUserController::class, 'logout'])->name('temp-user.logout');

//Route::get('/xdebug', function () { xdebug_info(); });