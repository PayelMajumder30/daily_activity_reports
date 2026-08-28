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
                    Export Excel
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

                    {{-- Location --}}
                    <div class="col-md-3">
                        <label>Asset Model</label>

                        <select name="asset_model" class="form-select">                           
                            <option value="">
                                All Asset Models
                            </option>

                            @foreach($assetModels as $assetModel)
                                <option value="{{ $assetModel->id }}"
                                    {{ request('assetModel') == $assetModel->id ? 'selected' : '' }}>
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
                            <td>{{ $item->asset_status}}</td>

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

                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="modal">
                            </button>

                        </div>

                        <div class="modal-body">

                            <div class="alert alert-info">

                                <strong>Excel Format:</strong>

                                Please use the prescribed Excel format.

                                <br>

                                <a href="{{ route('asset-inventory.downloadTemplate') }}"
                                class="btn btn-sm btn-success mt-2">

                                    <i class="bi bi-download"></i>
                                    Download Excel Format

                                </a>

                            </div>


                            <form id="excelUploadForm"
                                enctype="multipart/form-data">

                                @csrf

                                <div class="mb-3">

                                    <label class="form-label">
                                        Select Excel File
                                        <span class="text-danger">*</span>
                                    </label>

                                    <input type="file"
                                        name="excel_file"
                                        id="excel_file"
                                        class="form-control"
                                        accept=".xlsx,.xls">

                                    <small class="text-muted">
                                        Allowed format: .xlsx, .xls
                                    </small>

                                </div>

                                <div id="excelImportErrors"
                                    class="alert alert-danger d-none">

                                </div>

                                <div class="text-end">

                                    <button type="submit"
                                            class="btn btn-primary"
                                            id="uploadExcelBtn">

                                        <i class="bi bi-upload"></i>
                                        Upload Excel

                                    </button>

                                </div>

                            </form>

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

                        message +=
                            '<br><strong>Successfully imported:</strong> '
                            + response.success_count;

                    }

                    $('#excelImportErrors')
                        .removeClass('d-none')
                        .html(message);

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

                $('#uploadExcelBtn')
                    .prop('disabled', false)
                    .html(
                        '<i class="bi bi-upload"></i> Upload Excel'
                    );

            }

        });

    });

});

</script>

@endpush
@endsection