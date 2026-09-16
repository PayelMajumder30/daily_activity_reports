<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{DashboardController, UploadController, ComplaintController, UploaderController, UserConfigurationController, ConfigureController, EventLogController,
                            SettingController, AssetInventoryController, IssueRegController, CustodianController, AssetIssueRegisterController, AssetRetainedController};

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
            Route::get('/stations/{locationId}', [UserConfigurationController::class,'getStations'])->name('stations');
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

    // setting/Discipline(department)
    Route::prefix('setting/department')->name('discipline.')->group(function () {   
        Route::get('/', [SettingController::class,'discIndex'])->name('index');
        Route::post('/store', [SettingController::class,'discStore'])->name('store');
        Route::get('/edit/{id}', [SettingController::class,'discEdit'])->name('edit');
        Route::put('/update/{id}', [SettingController::class,'discUpdate'])->name('update');
        Route::post('/status/{id}', [SettingController::class,'discChangeStatus'])->name('changeStatus');

        // section
        Route::prefix('sections')->name('sections.')->group(function() {
            Route::get('/{id}', [SettingController::class, 'sectionIndex'])->name('index');
            Route::post('/store/{id}', [SettingController::class, 'sectionStore'])->name('store');
            Route::get('/edit/{id}', [SettingController::class, 'sectionEdit'])->name('edit');
            Route::put('/update/{id}', [SettingController::class, 'sectionUpdate'])->name('update');       
            Route::post('/status/{id}', [SettingController::class, 'sectionStatus'])->name('status');
        });
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

    // setting/location
    Route::prefix('setting/region')->name('location.')->group(function () {   
        Route::get('/', [SettingController::class,'locationIndex'])->name('index');
        Route::post('/store', [SettingController::class,'locationStore'])->name('store');
        Route::get('/edit/{id}', [SettingController::class,'locationEdit'])->name('edit');
        Route::put('/update/{id}', [SettingController::class,'locationUpdate'])->name('update');
        Route::post('/status/{id}', [SettingController::class,'locationChangeStatus'])->name('changeStatus');

        // airport/station
        Route::prefix('stations')->name('stations.')->group(function () {
            Route::get('/{id}', [SettingController::class, 'stationIndex'])->name('index');
            Route::post('/store/{id}', [SettingController::class, 'stationStore'])->name('store');
            Route::get('/edit/{id}', [SettingController::class, 'stationEdit'])->name('edit');
            Route::put('/update/{id}', [SettingController::class, 'stationUpdate'])->name('update');       
            Route::post('/status/{id}', [SettingController::class, 'stationStatus'])->name('status');
            Route::get('/by-location/{id}', [SettingController::class, 'stationsByLocation'])->name('byLocation');
        });
        
    });

    // Custodian
    Route::prefix('custodian')->name('custodian.')->group(function() {
        Route::get('/', [CustodianController::class, 'index'])->name('index');
        Route::get('/create', [CustodianController::class, 'create'])->name('create');
        Route::post('/store', [CustodianController::class, 'store'])->name('store');  
        Route::get('/edit/{id}', [CustodianController::class,'edit'])->name('edit');
        Route::put('/update/{id}', [CustodianController::class,'update'])->name('update');
        Route::get('/sections/{id}', [CustodianController::class, 'sections'])->name('sections');    
        Route::post('/status/{id}', [CustodianController::class,'changeStatus'])->name('changeStatus');        
    });

    // asset inventory
    Route::prefix('asset-inventory')->name('asset-inventory.')->group(function() {
        Route::get('/', [AssetInventoryController::class, 'index'])->name('index');
        Route::get('/create', [AssetInventoryController::class, 'create'])->name('create');
        Route::post('/store', [AssetInventoryController::class, 'store'])->name('store');
        Route::get('/generate-preview', [AssetInventoryController::class, 'generatePreview'])->name('generatePreview');
        Route::get('/generate-tags/{location}/{station}/{assetType}/{quantity}',[AssetInventoryController::class, 'generateTags'])->name('generateTags');
        Route::get('/get-models/{type}', [AssetInventoryController::class,'getModels'])->name('getModels');   
        Route::get('/export', [AssetInventoryController::class, 'export'])->name('export');    
        Route::get('/excel-template', [AssetInventoryController::class, 'downloadTemplate'])->name('downloadTemplate');
        Route::post('/import-excel', [AssetInventoryController::class, 'importExcel'])->name('importExcel');       
        Route::post('/outstation', [AssetInventoryController::class, 'outstation'])->name('outstation');       
        Route::get('/outstation/stations/{location}', [AssetInventoryController::class, 'getOutstationStations'])->name('outstation.stations');
        Route::get('/outstation/details/{id}', [AssetInventoryController::class, 'getOutstationDetails'])->name('outstation.details');  
        Route::get('/outstation/history/{id}', [AssetInventoryController::class, 'outstationHistory'])->name('outstation.history');   
        Route::get('/outstation/history/{id}/export', [AssetInventoryController::class, 'exportOutstationHistory'])->name('outstation.history.export');         
        Route::post('/scrap/{id}', [AssetInventoryController::class, 'scrap'])->name('scrap');           
    
    });

    // asset register
    Route::prefix('issue-register')->name('issue-register.')->group(function() {
        Route::get('/', [IssueRegController::class, 'index'])->name('index');
        Route::get('/create', [IssueRegController::class, 'create'])->name('create');
        Route::post('/store', [IssueRegController::class, 'store'])->name('store');
        Route::get('/sections/{id}', [IssueRegController::class, 'getSections'])->name('sections');
        Route::get('/employee/{emp_id}/assets', [IssueRegController::class, 'employeeAssets'])->name('employee-assets');
    });

    // asset issue register
    Route::prefix('asset-issue-register')->name('asset-issue-register.')->group(function() {
        Route::get('/', [AssetIssueRegisterController::class, 'index'])->name('index');
        Route::get('/create', [AssetIssueRegisterController::class, 'create'])->name('create');
        Route::post('/store', [AssetIssueRegisterController::class, 'store'])->name('store');
        Route::get('/edit/{id}', [AssetIssueRegisterController::class, 'edit'])->name('edit');
        Route::put('/update/{id}', [AssetIssueRegisterController::class, 'update'])->name('update');
        Route::post('/return/{id}', [AssetIssueRegisterController::class, 'returnAsset'])->name('return');
        Route::get('/custodian-details/{id}', [AssetIssueRegisterController::class, 'custodianDetails'])->name('custodian-details');
        Route::get('/custodian-asset-details/{id}', [AssetIssueRegisterController::class, 'custodianAssetDetails'])->name('custodian-asset-details');
        Route::get('/custodian-export/{id}', [AssetIssueRegisterController::class, 'custodianExport'])->name('custodian-export');

        // asset transfer
        Route::get('/transfer-details/{id}', [AssetIssueRegisterController::class, 'transferDetails'])->name('transfer-details');
        Route::post('/transfer', [AssetIssueRegisterController::class, 'transferAsset'] )->name('transfer');            
        Route::get('/export', [AssetIssueRegisterController::class, 'export'])->name('export');    
        Route::post('/{id}/retain', [AssetIssueRegisterController::class, 'retain'])->name('retain');   

    });

    // Asset retained
    Route::prefix('asset-retained')->name('asset-retained.')->group(function() {
        Route::get('/create', [AssetRetainedController::class, 'create'])->name('create');
        Route::post('/store', [AssetRetainedController::class, 'store'])->name('store');
        Route::get('/custodian-assets/{custodianId}', [AssetRetainedController::class, 'getCustodianAssets'])->name('custodian-assets');
        Route::get('/asset-details/{assetId}', [AssetRetainedController::class, 'getAssetDetailsByTag'])->name('asset-details');
    });

});

require __DIR__.'/auth.php';
    

        