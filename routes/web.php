<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{DashboardController, UploadController, ComplaintController, UploaderController, UserConfigurationController, ConfigureController, EventLogController,
                            SettingController};

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return redirect()->route('login');
});
Route::middleware(['auth', 'prevent-back-history', 'user.status'])->group(function() {

    // For management
    Route::middleware('role:0')->group(function(){
        Route::get('/dashboard', [DashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard/pie_chart', [DashboardController::class, 'pieChartData'])->name('dashboard.pieChart');
        Route::get('/dashboard/bar_chart', [DashboardController::class, 'barpieChartData'])->name('dashboard.barChart');
        Route::get('/dashboard/status_details', [DashboardController::class, 'statusDetails'])->name('dashboard.statusDetails');
        Route::get('/total_complaints', [ComplaintController::class, 'index'])->name('complaints.index');
        Route::get('/complaints/search', [ComplaintController::class, 'search'])->name('complaints.search');
        Route::post('/upload', [UploadController::class, 'store'])->name('upload.store');   
        
        //User configuration
        Route::prefix('configure/user')->name('user-configuration.')->group(function () {
            Route::get('/', [UserConfigurationController::class,'index'])->name('index');
            Route::get('/create', [UserConfigurationController::class,'create'])->name('create');
            Route::post('/store', [UserConfigurationController::class,'store'])->name('store');
            Route::get('/edit/{id}', [UserConfigurationController::class,'edit'])->name('edit');
            Route::put('/update/{id}', [UserConfigurationController::class,'update'])->name('update');
            Route::delete('/delete/{id}', [UserConfigurationController::class,'destroy'])->name('destroy');
            Route::post('/status/{id}', [UserConfigurationController::class,'changeStatus'])->name('changeStatus');
        });

        // Activity Configuration
        Route::prefix('configure/activity')->name('activity-configuration.')->group(function () {   
            Route::get('/', [ConfigureController::class,'index'])->name('index');
            Route::post('/store', [ConfigureController::class,'store'])->name('store');
            Route::get('/edit/{id}', [ConfigureController::class,'edit'])->name('edit');
            Route::put('/update/{id}', [ConfigureController::class,'update'])->name('update');
            Route::delete('/delete/{id}', [ConfigureController::class,'destroy'])->name('destroy');
            Route::post('/status/{id}', [ConfigureController::class,'changeStatus'])->name('changeStatus');
        });

        // Status Configuration
        Route::prefix('configure/status')->name('status-configuration.')->group(function () {   
            Route::get('/', [ConfigureController::class,'statusIndex'])->name('index');
            Route::post('/store', [ConfigureController::class,'statusStore'])->name('store');
            Route::get('/edit/{id}', [ConfigureController::class,'statusEdit'])->name('edit');
            Route::put('/update/{id}', [ConfigureController::class,'statusUpdate'])->name('update');
            Route::delete('/delete/{id}', [ConfigureController::class,'statusDestroy'])->name('destroy');
            Route::post('/status/{id}', [ConfigureController::class,'statusChange'])->name('statusChange');
        });

        // for audit trail
        Route::get('/audit-trail', [EventLogController::class, 'index'])->name('audit.trail');
        
    });

    //For Uploader and management both

    Route::prefix('upload_complaint')->name('uploader.')->group(function(){
        Route::get('/', [UploaderController::class,'index'])->name('index');
        Route::post('/preview', [UploaderController::class,'uploadPreview'])->name('preview');
        Route::post('/update/{id}',[UploaderController::class,'updateTemp'])->name('update');
        Route::post('/save',[UploaderController::class,'savePermanent'])->name('save');
        Route::delete('/delete/{upload_id}',[UploaderController::class,'deleteUpload'])->name('delete');
        Route::get('/download_template',[UploaderController::class,'downloadTemplate'])->name('downloadTemplate');
    });

    // Asset Inventory
        // setting/Designation
    Route::prefix('setting/designation')->name('designation.')->group(function () {   
        Route::get('/', [SettingController::class,'desIndex'])->name('index');
        Route::post('/store', [SettingController::class,'desStore'])->name('store');
        Route::get('/edit/{id}', [SettingController::class,'desEdit'])->name('edit');
        Route::put('/update/{id}', [SettingController::class,'desUpdate'])->name('update');
        Route::post('/status/{id}', [SettingController::class,'desChangeStatus'])->name('changeStatus');
    });

        // setting/Discipline
    Route::prefix('setting/discipline')->name('discipline.')->group(function () {   
        Route::get('/', [SettingController::class,'discIndex'])->name('index');
        Route::post('/store', [SettingController::class,'discStore'])->name('store');
        Route::get('/edit/{id}', [SettingController::class,'discEdit'])->name('edit');
        Route::put('/update/{id}', [SettingController::class,'discUpdate'])->name('update');
        Route::post('/status/{id}', [SettingController::class,'discChangeStatus'])->name('changeStatus');
    });

    // setting/Asset Type
    Route::prefix('setting/asset_type')->name('asset-type.')->group(function () {   
        Route::get('/', [SettingController::class,'assetIndex'])->name('index');
        Route::post('/store', [SettingController::class,'assetStore'])->name('store');
        Route::get('/edit/{id}', [SettingController::class,'assetEdit'])->name('edit');
        Route::put('/update/{id}', [SettingController::class,'assetUpdate'])->name('update');
        Route::post('/status/{id}', [SettingController::class,'assetChangeStatus'])->name('changeStatus');
    });

    // setting/asset model
    Route::prefix('setting/asset_model')->name('asset-model.')->group(function () {   
        Route::get('/', [SettingController::class,'assetModelIndex'])->name('index');
        Route::post('/store', [SettingController::class,'assetModelStore'])->name('store');
        Route::get('/edit/{id}', [SettingController::class,'assetModelEdit'])->name('edit');
        Route::put('/update/{id}', [SettingController::class,'assetModelUpdate'])->name('update');
        Route::post('/status/{id}', [SettingController::class,'assetModelChangeStatus'])->name('changeStatus');
    });

       // setting/Asset Tag no
    Route::prefix('setting/asset_tag')->name('asset-tag.')->group(function () {   
        Route::get('/', [SettingController::class,'tagIndex'])->name('index');
        Route::post('/store', [SettingController::class,'tagStore'])->name('store');
        Route::get('/edit/{id}', [SettingController::class,'tagEdit'])->name('edit');
        Route::put('/update/{id}', [SettingController::class,'tagUpdate'])->name('update');
        Route::post('/status/{id}', [SettingController::class,'tagChangeStatus'])->name('changeStatus');
    });
});

require __DIR__.'/auth.php';
    

        