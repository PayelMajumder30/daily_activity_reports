<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{DashboardController, UploadController, ComplaintController};

// Route::get('/', function () {
//     return view('welcome');
// });
// Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');

Route::get('/complaints/search', [ComplaintController::class, 'search'])->name('complaints.search');