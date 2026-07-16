<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{DashboardController, UploadController, ComplaintController, UploaderController};

// Route::get('/', function () {
//     return view('welcome');
// });
// Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/', [DashboardController::class, 'dashboard'])->name('dashboard');
Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');

Route::get('/complaints/search', [ComplaintController::class, 'search'])->name('complaints.search');


Route::get('/uploader', [UploaderController::class,'index'])->name('uploader.index');
        
// Route::post('/uploader/upload', [UploaderController::class,'upload'])->name('uploader.upload');
Route::post('/uploader/upload-preview', [UploaderController::class,'uploadPreview'])->name('uploader.preview');
Route::post('/uploader/update-temp/{id}',[UploaderController::class,'updateTemp'])->name('uploader.update');
Route::post('/uploader/save',[UploaderController::class,'savePermanent'])->name('uploader.save');
        