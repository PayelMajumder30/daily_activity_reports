@extends('layouts.app')

@section('title', 'Asset Inventory')

@section('content')

<div class="container-fluid py-4">
    <div class="row mb-4">

        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-box-seam"></i>
                Asset Inventory
            </h2>

            <p class="text-muted">
                Manage all inventory assets.
            </p>
        </div>

        <div class="col-md-4 text-end">
            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>
        </div>

    </div>

    {{-- ==========================
        Search Card
    ========================== --}}

    <div class="card shadow border-0 mb-4">

        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-search"></i>
                Search Asset Inventory
            </h5>

            <div class="d-flex gap-2">
                <a href="{{ route('asset-inventory.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i>
                    Add Inventory
                </a>

                <a href="{{ route('asset-inventory.export', request()->query()) }}"
                class="btn btn-success btn-sm">
                    <i class="bi bi-file-earmark-excel"></i>
                    Export to Excel
                </a>

                 {{-- Upload Excel --}}
                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#excelUploadModal">
                    <i class="bi bi-file-earmark-excel"></i>
                    Upload Excel
                </button>
            </div>
           
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('asset-inventory.index') }}">             
                <div class="row">
                    {{-- Tag No --}}
                    <div class="col-md-3">
                        <label>Tag No.</label>
                        <input type="text" name="tag_no" class="form-control" value="{{ request('tag_no') }}"placeholder="Search Tag">      
                    </div>

                    {{-- PO Number --}}
                    <div class="col-md-3">
                        <label>PO No.</label>
                        <input type="text" name="po_number" class="form-control" value="{{ request('po_number') }}" placeholder="Search PO">                               
                    </div>

                    {{-- Serial Number --}}
                    <div class="col-md-3">
                        <label>Serial No.</label>
                        <input type="text" name="serial_no" class="form-control" value="{{ request('serial_no') }}" placeholder="Search Serial">     
                    </div>

                    {{-- Asset Type --}}
                    <div class="col-md-3">
                        <label>Asset Type</label>

                        <select name="asset_type" class="form-select">                           
                            <option value="">
                                All Asset Types
                            </option>

                            @foreach($assetTypes as $type)

                                <option value="{{ $type->id }}"
                                    {{ request('asset_type') == $type->id ? 'selected' : '' }}>
                                    {{ ucwords($type->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Asset Status --}}
                    <div class="col-md-3">
                        <label>Asset status</label>
                        <select name="asset_status" class="form-select">                                                      
                            <option value="">
                                All Asset status
                            </option>
                            @foreach($assetStatuses as $status)
                                <option value="{{ $status }}" {{ request('asset_status') == $status ? 'selected' : '' }}>                                                              
                                    {{ ucwords($status)}}
                                </option>
                            @endforeach
                        </select>                               
                    </div>

                    {{-- Asset Model --}}
                    <div class="col-md-3">
                        <label>Asset Model</label>

                        <select name="asset_model" id="asset_model" class="form-select">                           
                            <option value="">
                                All Asset Models
                            </option>

                            @foreach($assetModels as $assetModel)
                                <option value="{{ $assetModel->id }}"
                                    {{ request('asset_model') == $assetModel->id ? 'selected' : '' }}>
                                    {{ ucwords($assetModel->model_name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Installation Date --}}
                    <div class="col-md-3">
                        <label>Installation Date</label>

                        <input type="text" name="installation_date" id="installation_date" class="form-control datepicker" value="{{ request('installation_date') }}" placeholder="DD-MM-YYYY">
                    </div>

                </div>

                <div class="mt-3">

                    <button type="submit" class="btn btn-primary">                        
                        <i class="bi bi-search"></i>
                        Search
                    </button>

                    <a href="{{ route('asset-inventory.index') }}" class="btn btn-secondary">                   
                        Reset
                    </a>
                </div>
            </form>
        </div>

    </div>

    {{-- ==========================
        Asset Inventory Table
    ========================== --}}

    <div class="card shadow border-0">

        <div class="card-header d-flex justify-content-between align-items-center">

            <h5 class="mb-0">
                Asset Inventory Details
            </h5>

        </div>

        <div class="card-body">

            <table class="table table-bordered table-hover" id="assetInventoryTable">                
                <thead class="table-dark">

                    <tr>
                        <th>SL</th>
                        <th>Asset Type</th>
                        <th>Asset Model</th>
                        <th>Asset Serial No.</th>
                        <th>Asset Tag</th>
                        <th>Region</th>
                        <th>Airport/Station</th>
                        <th>PO NO</th>
                        <th>Installation Date</th>
                        <th>Warranty (Yrs)</th>
                        <th>Warranty End Date</th>
                        <th>Asset Status</th>
                        <th>Action</th>
                    </tr>

                </thead>

                <tbody>

                    @foreach($inventories as $item)

                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ ucwords($item->assetType->name ?? 'N/A') }}</td>                                                          
                            <td>{{ $item->assetModel->model_name ?? 'N/A' }}</td>                                                         
                            <td>{{ $item->serial_no ?? 'N/A' }}</td>                                                          
                            <td>{{ $item->tag_no }}</td>
                            <td>{{ ucwords($item->location->name ?? 'N/A') }}</td>                                                         
                            <td>{{ ucwords($item->station->station_name ?? 'N/A') }}</td>  
                            <td>{{ $item->po_number }}</td>                                                                                                          
                            <td>
                                {{ $item->installation_date
                                    ? date('d-m-Y', strtotime($item->installation_date))
                                    : 'N/A'
                                }}
                            </td>

                            <td>
                                {{ $item->warranty_year ?? 'N/A' }}
                            </td>

                            <td>
                                {{ $item->warranty_end
                                    ? date('d-m-Y', strtotime($item->warranty_end))
                                    : 'N/A'
                                }}
                            </td>
                            <!-- <td>{{ $item->asset_status}}</td> -->
                             <td>

                                @if($item->asset_status === 'Damaged')

                                    <span class="badge bg-danger">
                                        Physically Damaged
                                    </span>

                                @elseif($item->asset_status === 'Outstation')

                                    <span class="badge bg-warning text-dark">
                                        Outstation
                                    </span>

                                @elseif($item->asset_status === 'Available')

                                    <span class="badge bg-success">
                                        Available
                                    </span>

                                @elseif($item->asset_status === 'Assigned')

                                    <span class="badge bg-primary">
                                        Assigned
                                    </span>

                                @elseif($item->asset_status === 'Retained')

                                    <span class="badge bg-secondary">
                                        Retained
                                    </span>

                                @else

                                    <span class="badge bg-dark">
                                        {{ $item->asset_status }}
                                    </span>

                                @endif

                            </td>
                            <td>

                            <button type="button" class="btn btn-sm btn-info view-outstation-history"                                   
                                    data-id="{{ $item->id }}" title="View Outstation History">                                   
                                <i class="bi bi-eye"></i>
                            </button>

                                {{-- Outstation --}}
                                @if($item->asset_status === 'Available')

                                <button type="button" class="btn btn-sm btn-primary outstation-asset" data-id="{{ $item->id }}" title="Outstation">                                                                    
                                                                     
                                    <i class="bi bi-arrow-left-right"></i>
                                </button>

                                @endif

                                {{-- Physically Damaged --}}
                                @if($item->asset_status !== 'Damaged')

                                    <button type="button" class="btn btn-sm btn-danger scrap-asset"                                                                         
                                        data-id="{{ $item->id }}" title="Physically Damaged">
                                                                               
                                        <i class="bi bi-tools text-warning"></i>

                                    </button>

                                @endif
                            </td>

                        </tr>

                    @endforeach

                </tbody>
            </table>

            {{-- =========================================================
                Upload Inventory Excel Modal
            ========================================================= --}}

            <div class="modal fade" id="excelUploadModal" tabindex="-1" aria-labelledby="excelUploadModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">

                    <div class="modal-content">

                        <div class="modal-header">

                            <h5 class="modal-title" id="excelUploadModalLabel">
                                <i class="bi bi-file-earmark-excel"></i>
                                Upload Asset Inventory Excel
                            </h5>
 
                            <button type="button" class="btn-close" data-bs-dismiss="modal">                                  
                                    
                            </button>

                        </div>

                        <div class="modal-body">

                            <div class="alert alert-info">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                                    <strong>Important Excel Import Guidelines:</strong>
                                </div>
                                <ul class="mb-2 ps-3 small">
                                    <li>Please use the official downloaded Excel template without modifying its structure.</li>
                                    <li><strong>Do not rename or delete</strong> the <code>Asset Inventory</code> or <code>Reference Data</code> sheet names.</li>
                                    <li><strong>Do not change, reorder, or remove</strong> any column headers in row 1.</li>
                                    <li>Use valid <strong>Asset Model ID</strong> and <strong>Airport Station ID</strong> values from the <em>Reference Data</em> sheet.</li>
                                    <li>Ensure dates follow the <code>DD-MM-YYYY</code> format or standard Excel date cell type.</li>
                                </ul>

                                <a href="{{ route('asset-inventory.downloadTemplate') }}" class="btn btn-sm btn-success mt-1">
                                    <i class="bi bi-download"></i> Download Excel Format
                                </a>
                            </div>


                            <form id="excelUploadForm" enctype="multipart/form-data">                               

                                @csrf

                                <div class="mb-3">

                                    <label class="form-label">
                                        Select Excel File
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input type="file" name="excel_file" id="excel_file" class="form-control" accept=".xlsx,.xls">                                                                                                                                                             

                                    <small class="text-muted">
                                        Allowed format: .xlsx, .xls
                                    </small>

                                </div>

                                <div id="excelImportErrors"
                                    class="alert alert-danger d-none">

                                </div>

                                <div class="text-end">

                                    <button type="submit" class="btn btn-primary" id="uploadExcelBtn">
                                                                                       
                                        <i class="bi bi-upload"></i>
                                        Bulk Upload
                                    </button>

                                </div>

                            </form>

                        </div>

                    </div>

                </div>
            </div>


            {{-- =========================================================
                Outstation modal
            ========================================================= --}}
            <div class="modal fade" id="outstationModal" tabindex="-1">

                <div class="modal-dialog modal-lg">

                    <div class="modal-content">

                        <div class="modal-header">

                            <h5 class="modal-title">
                                <i class="bi bi-arrow-left-right"></i>
                                Outstation Asset
                            </h5>

                            <button type="button" class="btn-close" data-bs-dismiss="modal">                      
                                                            
                            </button>

                        </div>


                        <form id="outstationForm">

                            @csrf

                            <input type="hidden" name="asset_inventory_id" id="outstation_asset_id">
                                                                                        
                            <div class="modal-body">

                                {{-- Asset Details --}}

                                <div class="card bg-light border-0 mb-3">

                                    <div class="card-body">

                                        <h6 class="mb-3">
                                            Current Asset Details
                                        </h6>

                                        <div class="row">

                                            <div class="col-md-4 mb-2">
                                                <strong>Tag No:</strong>
                                                <span id="outstation_tag"></span>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <strong>Asset Type:</strong>
                                                <span id="outstation_type"></span>
                                            </div>

                                            <div class="col-md-4 mb-2">
                                                <strong>Asset Model:</strong>
                                                <span id="outstation_model"></span>
                                            </div>

                                            <div class="col-md-6 mb-2">
                                                <strong>Current Region:</strong>
                                                <span id="outstation_current_location"></span>
                                            </div>

                                            <div class="col-md-6 mb-2">
                                                <strong>Current Station:</strong>
                                                <span id="outstation_current_station"></span>
                                            </div>

                                        </div>

                                    </div>

                                </div>


                                <div class="row">

                                    {{-- Destination Region --}}

                                    <div class="col-md-6 mb-3">

                                        <label class="form-label">
                                            Destination Region
                                            <span class="text-danger">*</span>
                                        </label>

                                        <select name="to_location_id" id="outstation_location" class="form-select">                                          

                                            <option value="">
                                                Select Destination Region
                                            </option>

                                            @foreach($locations as $location)

                                                <option value="{{ $location->id }}">
                                                    {{ ucwords($location->name) }}
                                                </option>

                                            @endforeach

                                        </select>

                                    </div>


                                    {{-- Destination Station --}}

                                    <div class="col-md-6 mb-3">

                                        <label class="form-label">
                                            Destination Station
                                            <span class="text-danger">*</span>
                                        </label>

                                        <select name="to_station_id" id="outstation_station" class="form-select" disabled>                                           
                                            <option value="">
                                                Select Destination Station
                                            </option>

                                        </select>

                                    </div>


                                    {{-- Date --}}

                                    <div class="col-md-6 mb-3">

                                        <label class="form-label">
                                            Outstation Date
                                            <span class="text-danger">*</span>
                                        </label>

                                        <input type="date" name="outstation_date" id="outstation_date" class="form-control" value="{{ now()->format('Y-m-d') }}">                                                

                                    </div>

                                </div>

                            </div>


                            <div class="modal-footer">

                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">             

                                    Cancel

                                </button>

                                <button type="submit" class="btn btn-primary"  id="outstationSubmitBtn">                                                                                                     
                                    <i class="bi bi-check-circle"></i>
                                    Move Outstation

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                Outstation History Modal 
             ========================================================= --}}
            <div class="modal fade" id="outstationHistoryModal" tabindex="-1"
                aria-labelledby="outstationHistoryModalLabel" aria-hidden="true">

                <div class="modal-dialog modal-xl modal-dialog-scrollable">

                    <div class="modal-content">

                        <div class="modal-header">

                            <h5 class="modal-title" id="outstationHistoryModalLabel">
                                <i class="bi bi-clock-history"></i>
                                Outstation History
                            </h5>

                            <button type="button" class="btn-close" data-bs-dismiss="modal">                                                                    
                            </button>

                        </div>

                        <div class="modal-body">

                            <!-- Asset Details -->
                            <div class="card mb-3">

                                <div class="card-header">
                                    <strong>Asset Details</strong>
                                </div>

                                <div class="card-body">

                                    <div class="row">

                                        <div class="col-md-3 mb-2">
                                            <strong>Asset Tag:</strong>
                                            <span id="history_tag">-</span>
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <strong>Serial No:</strong>
                                            <span id="history_serial">-</span>
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <strong>Asset Type:</strong>
                                            <span id="history_type">-</span>
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <strong>Asset Model:</strong>
                                            <span id="history_model">-</span>
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <strong>Current Region:</strong>
                                            <span id="history_current_location">-</span>
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <strong>Current Station:</strong>
                                            <span id="history_current_station">-</span>
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <strong>Asset Status:</strong>
                                            <span id="history_status">-</span>
                                        </div>

                                    </div>

                                </div>

                            </div>


                            <!-- History -->
                            <div class="card">

                                <div class="card-header d-flex justify-content-between align-items-center">

                                    <strong>
                                        <i class="bi bi-arrow-left-right"></i>
                                        Movement History
                                    </strong>

                                    <button type="button" class="btn btn-sm btn-success" id="exportOutstationHistoryBtn">                                                                                      
                                        <i class="bi bi-file-earmark-excel"></i>
                                        Export History
                                    </button>

                                </div>

                                <div class="card-body">

                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>SL</th>
                                                    <th>Outstation Date</th>
                                                    <th>From Region</th>
                                                    <th>From Station</th>
                                                    <th>To Region</th>
                                                    <th>To Station</th>
                                                </tr>
                                            </thead>

                                            <tbody id="outstationHistoryTableBody">

                                                <tr>
                                                    <td colspan="7"
                                                        class="text-center">
                                                        No history found.
                                                    </td>
                                                </tr>

                                            </tbody>

                                        </table>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
</div>


@push('scripts')

<script>

    $(document).ready(function () {

        let currentOutstationAssetId = null;

        $('#asset_model').select2({
            placeholder: 'All Asset Models',
            allowClear: true,
            width: '100%'
        });

        $('#assetInventoryTable').DataTable({
            columnDefs: [
                {
                    targets: 0,
                    orderable: false
                }
            ],
            order: [[0, 'asc']],
            pageLength: 10,
            searching: false,

            language: {
                emptyTable: "No Inventory Found"
            }
        });

        // Reset form, clear error alert, and restore button state on modal hide
        $('#excelUploadModal').on('hidden.bs.modal', function () {
            // 1. Reset the HTML form (clears selected file input)
            $('#excelUploadForm')[0].reset();

            // 2. Hide and clear the error alert box
            $('#excelImportErrors')
                .addClass('d-none')
                .html('');

            // 3. Reset the submit button text and state
            $('#uploadExcelBtn')
                .prop('disabled', false)
                .html('<i class="bi bi-upload"></i> Upload Excel');
        });

        $('#excelUploadForm').submit(function(e) {

            e.preventDefault();
            let form = this;
            let file = $('#excel_file')[0].files[0];
            if (!file) {
                Swal.fire({
                    icon: 'warning',
                    title: 'File Required',
                    text: 'Please select an Excel file.'
                });

                return;
            }

            let formData = new FormData(form);

            $('#excelImportErrors')
                .addClass('d-none')
                .html('');

            $('#uploadExcelBtn')
                .prop('disabled', true)
                .html(
                    '<span class="spinner-border spinner-border-sm"></span> Uploading...'
                );

            $.ajax({

                url: "{{ route('asset-inventory.importExcel') }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Import Successful',
                        text: res.message
                    }).then(function() {
                        location.reload();

                    });

                },

                error: function(xhr) {
                    let response = xhr.responseJSON;
                    let message = '';
                    if (response && response.errors) {
                        message =
                            '<strong>Please check the following:</strong><br><br>';

                        response.errors.forEach(function(error) {
                            message += '• ' + error + '<br>';

                        });

                        if (response.success_count !== undefined) {
                            message += '<br><strong>Successfully imported:</strong> ' + response.success_count;                                               
                        }

                        $('#excelImportErrors').removeClass('d-none').html(message);                                              

                    } else {

                        message =
                            response?.message ||
                            'Unable to import Excel file.';

                        Swal.fire({
                            icon: 'error',
                            title: 'Import Failed',
                            text: message
                        });

                    }

                },

                complete: function() {
                    $('#uploadExcelBtn').prop('disabled', false).html('<i class="bi bi-upload"></i> Upload Excel');                                                                             
                }

            });

        });


        /*
        |--------------------------------------------------------------------------
        | Open Outstation Modal
        |--------------------------------------------------------------------------
        */
        $(document).on('click', '.outstation-asset', function () {
            let assetId = $(this).data('id');

            // Reset modal first
            $('#outstationForm')[0].reset();

            $('#outstation_asset_id').val(assetId);

            $('#outstation_tag').text('-');
            $('#outstation_type').text('-');
            $('#outstation_model').text('-');
            $('#outstation_current_location').text('-');
            $('#outstation_current_station').text('-');

            $('#outstation_location').val('');

            $('#outstation_station')
                .html('<option value="">Select Destination Station</option>')
                .prop('disabled', true)
                .removeData('current-station');

            $('#outstation_date').val('{{ now()->format("Y-m-d") }}');

            /*
            |--------------------------------------------------------------------------
            | Fetch Asset Details
            |--------------------------------------------------------------------------
            */

            let url = "{{ route('asset-inventory.outstation.details', ':id') }}".replace(':id', assetId);
                
            $.ajax({
                url: url,
                type: 'GET',
                beforeSend: function () {

                    $('#outstation_tag').text('Loading...');
                    $('#outstation_type').text('Loading...');
                    $('#outstation_model').text('Loading...');
                    $('#outstation_current_location').text('Loading...');
                    $('#outstation_current_station').text('Loading...');

                },

                success: function (response) {

                    if (!response.success) {

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Unable to load asset details.'
                        });

                        return;
                    }

                    let asset = response.asset;

                    /*
                    |--------------------------------------------------------------------------
                    | Fill Current Asset Details
                    |--------------------------------------------------------------------------
                    */

                    $('#outstation_asset_id').val(asset.id);

                    $('#outstation_tag').text(asset.tag_no || '-');

                    $('#outstation_type').text(asset.asset_type || '-');

                    $('#outstation_model').text(asset.asset_model || '-');

                    $('#outstation_current_location')
                        .text(asset.location || '-');

                    $('#outstation_current_station')
                        .text(asset.station || '-');

                    /*
                    |--------------------------------------------------------------------------
                    | Store Current Station ID
                    |--------------------------------------------------------------------------
                    */

                    $('#outstation_station')
                        .data('current-station', asset.station_id || '');

                    /*
                    |--------------------------------------------------------------------------
                    | Show Modal
                    |--------------------------------------------------------------------------
                    */

                    bootstrap.Modal
                        .getOrCreateInstance(
                            document.getElementById('outstationModal')
                        ).show();                       

                },

                error: function (xhr) {

                    let response = xhr.responseJSON;

                    Swal.fire({
                        icon: 'error',
                        title: 'Unable to Load Asset',
                        text: response?.message ||
                            'Unable to fetch asset details.'
                    });

                }

            });

        });

       /*
        |--------------------------------------------------------------------------
        | Destination Region Changed
        |--------------------------------------------------------------------------
        */

        $('#outstation_location').on('change', function () {

            let locationId = $(this).val();

            let stationDropdown = $('#outstation_station');

            let currentStationId = stationDropdown.data('current-station');


            /*
            |--------------------------------------------------------------------------
            | Reset Station
            |--------------------------------------------------------------------------
            */

            stationDropdown
                .html('<option value="">Loading stations...</option>')
                .prop('disabled', true);


            if (!locationId) {

                stationDropdown
                    .html('<option value="">Select Destination Station</option>')
                    .prop('disabled', true);

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Get Stations By Region
            |--------------------------------------------------------------------------
            */

            let url = "{{ route('asset-inventory.outstation.stations', ':id') }}"
                .replace(':id', locationId);


            $.ajax({

                url: url,

                type: 'GET',

                success: function (response) {

                    stationDropdown.empty();

                    stationDropdown.append(
                        '<option value="">Select Destination Station</option>'
                    );


                    if (!response.success || !response.stations.length) {

                        stationDropdown.append(
                            '<option value="">No stations available</option>'
                        );

                        stationDropdown.prop('disabled', true);

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Add Stations
                    |--------------------------------------------------------------------------
                    */

                    $.each(response.stations, function (index, station) {

                        /*
                        |--------------------------------------------------------------------------
                        | Don't show current station
                        |--------------------------------------------------------------------------
                        */

                        if (
                            currentStationId &&
                            parseInt(station.id) === parseInt(currentStationId)
                        ) {
                            return;
                        }


                        stationDropdown.append(
                            $('<option>', {
                                value: station.id,
                                text: station.station_name
                            })
                        );

                    });


                    stationDropdown.prop('disabled', false);

                },

                error: function (xhr) {

                    stationDropdown
                        .html(
                            '<option value="">Unable to load stations</option>'
                        )
                        .prop('disabled', true);


                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Unable to load destination stations.'
                    });

                }

            });

        });


        /*
        |--------------------------------------------------------------------------
        | Submit Outstation
        |--------------------------------------------------------------------------
        */

        $('#outstationForm').submit(function (e) {

            e.preventDefault();

            let form = this;

            let button = $('#outstationSubmitBtn');


            button
                .prop('disabled', true)
                .html(
                    '<span class="spinner-border spinner-border-sm"></span> Moving...'
                );


            $.ajax({

                url: "{{ route('asset-inventory.outstation') }}",

                type: 'POST',

                data: $(form).serialize(),

                success: function (response) {

                    Swal.fire({
                        icon: 'success',
                        title: 'Outstation Successful',
                        text: response.message
                    }).then(function () {

                        window.location.href =
                            "{{ route('asset-inventory.index') }}";

                    });

                },

                error: function (xhr) {

                    let response = xhr.responseJSON;

                    Swal.fire({
                        icon: 'error',
                        title: 'Unable to Move Asset',
                        text:
                            response?.message ||
                            'Something went wrong.'
                    });

                },

                complete: function () {

                    button
                        .prop('disabled', false)
                        .html(
                            '<i class="bi bi-check-circle"></i> Move Outstation'
                        );

                }

            });

        });

         /*
        |--------------------------------------------------------------------------
        | show Outstation History
        |--------------------------------------------------------------------------
        */
        $(document).on('click', '.view-outstation-history', function () {

            let assetId = $(this).data('id');

            currentOutstationAssetId = assetId;

            // Reset modal
            $('#history_tag').text('Loading...');
            $('#history_serial').text('Loading...');
            $('#history_type').text('Loading...');
            $('#history_model').text('Loading...');
            $('#history_current_location').text('Loading...');
            $('#history_current_station').text('Loading...');
            $('#history_status').text('Loading...');

            $('#outstationHistoryTableBody').html(`
                <tr>
                    <td colspan="7" class="text-center">
                        <i class="bi bi-hourglass-split"></i>
                        Loading history...
                    </td>
                </tr>
            `);

            $('#exportOutstationHistoryBtn')
                .prop('disabled', true);

            // Show modal
            bootstrap.Modal
                .getOrCreateInstance(
                    document.getElementById('outstationHistoryModal')
                )
                .show();

            let url = "{{ route('asset-inventory.outstation.history', ':id') }}"
                .replace(':id', assetId);

            $.ajax({

                url: url,

                type: 'GET',

                success: function (response) {

                    if (!response.success) {

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Unable to load history.'
                        });

                        return;
                    }

                    let asset = response.asset;

                    // Asset details
                    $('#history_tag').text(asset.tag_no || '-');

                    $('#history_serial').text(asset.serial_no || '-');

                    $('#history_type').text(asset.asset_type || '-');

                    $('#history_model').text(asset.asset_model || '-');

                    $('#history_current_location').text(asset.location || '-');
                        
                    $('#history_current_station').text(asset.station || '-');                    

                    $('#history_status').text(asset.asset_status || '-');
                        

                    // History
                    let tbody = $('#outstationHistoryTableBody');

                    tbody.empty();

                    if (!response.history ||
                        response.history.length === 0) {

                        tbody.html(`
                            <tr>
                                <td colspan="7"
                                    class="text-center text-muted">
                                    No outstation history found.
                                </td>
                            </tr>
                        `);

                    } else {

                        $.each(response.history, function (index, item) {

                            tbody.append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${item.outstation_date || '-'}</td>
                                    <td>${item.from_location || '-'}</td>
                                    <td>${item.from_station || '-'}</td>
                                    <td>${item.to_location || '-'}</td>
                                    <td>${item.to_station || '-'}</td>
                                </tr>
                            `);

                        });
                    }

                    // Enable export
                    $('#exportOutstationHistoryBtn')
                        .prop('disabled', false);
                },

                error: function (xhr) {

                    let response = xhr.responseJSON;

                    $('#outstationHistoryTableBody').html(`
                        <tr>
                            <td colspan="7"
                                class="text-center text-danger">
                                Unable to load outstation history.
                            </td>
                        </tr>
                    `);

                    Swal.fire({
                        icon: 'error',
                        title: 'Unable to Load History',
                        text: response?.message ||
                            'Something went wrong.'
                    });
                }

            });

        });

        /*
        |--------------------------------------------------------------------------
        | Export Outstation History
        |--------------------------------------------------------------------------
        */
        $('#exportOutstationHistoryBtn').on('click', function () {

            if (!currentOutstationAssetId) {

                Swal.fire({
                    icon: 'warning',
                    title: 'Asset Not Selected',
                    text: 'Please select an asset first.'
                });

                return;
            }

            let url =
                "{{ route('asset-inventory.outstation.history.export', ':id') }}"
                .replace(':id', currentOutstationAssetId);

            window.location.href = url;
        });

        $(document).on('click', '.scrap-asset', function () {

            let assetId = $(this).data('id');

            Swal.fire({

                icon: 'warning',
                title: 'Mark Asset as Physically Damaged?',
                text: 'This asset will be marked as physically damaged.',
                showCancelButton: true,
                confirmButtonText: 'Yes, Mark Damaged',
                cancelButtonText: 'Cancel',
                reverseButtons: true

            }).then(function (result) {

                if (!result.isConfirmed) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Scrap Asset
                |--------------------------------------------------------------------------
                */

                $.ajax({

                    url: "{{ route('asset-inventory.scrap', ':id') }}".replace(':id', assetId),                     
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },

                    beforeSend: function () {

                        Swal.fire({

                            title: 'Processing...',
                            text: 'Updating asset status.',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: function () {
                                Swal.showLoading();
                            }

                        });

                    },

                    success: function (response) {

                        Swal.fire({
                            icon: 'success',
                            title: 'Asset Updated',
                            text: response.message

                        }).then(function () {

                            window.location.href =
                                "{{ route('asset-inventory.index') }}";
                        });

                    },

                    error: function (xhr) {
                        let response = xhr.responseJSON;
                        Swal.fire({
                            icon: 'error',
                            title: 'Unable to Update Asset',
                            text: response?.message || 'Something went wrong.'                          
                        });

                    }

                });

            });

        });
    });


</script>

@endpush
@endsection