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

Route::prefix('uploader')->name('uploader.')->group(function(){
    Route::get('/', [UploaderController::class,'index'])->name('index');
    Route::post('/preview', [UploaderController::class,'uploadPreview'])->name('preview');
    Route::post('/update/{id}',[UploaderController::class,'updateTemp'])->name('update');
    Route::post('/save',[UploaderController::class,'savePermanent'])->name('save');
});

        