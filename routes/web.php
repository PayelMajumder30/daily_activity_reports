<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{DashboardController, UploadController, ComplaintController, UploaderController};

// Route::get('/', function () {
//     return view('welcome');
// });
// Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/', function () {
    return redirect()->route('login');
});
Route::middleware('auth')->group(function() {

    // For management
    Route::middleware('role:0')->group(function(){
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/total_complaints', [ComplaintController::class, 'index'])->name('complaints.index');
        Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');

        Route::get('/complaints/search', [ComplaintController::class, 'search'])->name('complaints.search');

        Route::prefix('upload_complaint')->name('uploader.')->group(function(){
            Route::get('/', [UploaderController::class,'index'])->name('index');
            Route::post('/preview', [UploaderController::class,'uploadPreview'])->name('preview');
            Route::post('/update/{id}',[UploaderController::class,'updateTemp'])->name('update');
            Route::post('/save',[UploaderController::class,'savePermanent'])->name('save');
        });
    });

    //For Uploader
    Route::middleware('role:1')->group(function() {
        Route::prefix('upload_complaint')->name('uploader.')->group(function(){
            Route::get('/', [UploaderController::class,'index'])->name('index');
            Route::post('/preview', [UploaderController::class,'uploadPreview'])->name('preview');
            Route::post('/update/{id}',[UploaderController::class,'updateTemp'])->name('update');
            Route::post('/save',[UploaderController::class,'savePermanent'])->name('save');
        });

    });
    // Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
    // Route::get('/total_complaints', [ComplaintController::class, 'index'])->name('complaints.index');
    // Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');

    // Route::get('/complaints/search', [ComplaintController::class, 'search'])->name('complaints.search');

    // Route::prefix('upload_complaint')->name('uploader.')->group(function(){
    //     Route::get('/', [UploaderController::class,'index'])->name('index');
    //     Route::post('/preview', [UploaderController::class,'uploadPreview'])->name('preview');
    //     Route::post('/update/{id}',[UploaderController::class,'updateTemp'])->name('update');
    //     Route::post('/save',[UploaderController::class,'savePermanent'])->name('save');
    // });
});

require __DIR__.'/auth.php';
    

        