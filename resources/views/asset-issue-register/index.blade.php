@extends('layouts.app')
@section('title', 'Asset Issued Register')
@section('content')

<div class="container-fluid py-4">


    {{-- Header --}}

    <div class="row mb-4">

        <div class="col-md-8">

            <h2 class="fw-bold">
                <i class="bi bi-box-arrow-right"></i>
                Asset Issue Register
            </h2>

            <p class="text-muted">
                Manage issued assets and issue history
            </p>

        </div>

    </div>


    {{-- Search Card --}}

    <div class="card shadow border-0 mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-search"></i>
                Search Issued Assets
            </h5>

            <a href="{{ route('asset-issue-register.create') }}" class="btn btn-primary btn-sm">               
                <i class="bi bi-plus-circle"></i>
                Issue Asset
            </a>

        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('asset-issue-register.index') }}">
                <div class="row">
                    {{-- Custodian --}}
                    <div class="col-md-3">
                        <label class="form-label">
                            Custodian Name
                        </label>
                        <input type="text" name="custodian_name" value="{{ request('custodian_name') }}" class="form-control" placeholder="Search custodian">
                    </div>


                    {{-- Employee ID --}}

                    <div class="col-md-2">

                        <label class="form-label">
                            Employee ID
                        </label>

                        <input type="text" name="emp_id" value="{{ request('emp_id') }}" class="form-control" placeholder="Employee ID">                   
                    </div>


                    {{-- Tag --}}

                    <div class="col-md-2">

                        <label class="form-label">
                            Asset Tag
                        </label>

                        <input type="text" name="tag_no" value="{{ request('tag_no') }}" class="form-control" placeholder="Tag No">
                    </div>


                    {{-- Issue Status --}}

                    <div class="col-md-2">

                        <label class="form-label">
                            Status
                        </label>

                        <select name="issue_status" class="form-select">                                                      

                            <option value="">
                                All
                            </option>

                            @foreach($issueStatuses as $status)
                                <option value="Issued" {{ request('issue_status') == $status ? 'selected' : '' }}>                                                              
                                    {{ ucwords($status)}}
                                </option>
                            @endforeach

                        </select>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">

                        <button type="submit" class="btn btn-primary">                                                     
                            <i class="bi bi-search"></i>
                            Search

                        </button>

                        <a href="{{ route('asset-issue-register.index') }}" class="btn btn-secondary ms-2">                                                      
                            Reset
                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- Table --}}

    <div class="card shadow border-0">
        <div class="card-header">
            <h5 class="mb-0">
                Asset Issue Details
            </h5>

        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="assetIssueTable">
                                     
                    <thead class="table-dark">

                        <tr>
                            <th>SL</th>
                            <th>Tag No.</th>
                            <th>Asset Type</th>
                            <th>Asset Model</th>
                            <th>Custodian</th>
                            <th>Employee ID</th>
                            <th>Department</th>
                            <th>User Type</th>
                            <th>Issued Date</th>
                            <th>Returned Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>

                    </thead>


                    <tbody>
                        @foreach($issueRegisters as $issue)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                                                   
                                <td>
                                    <strong>
                                        {{ $issue->assetInventory->tag_no ?? 'N/A' }}
                                    </strong>
                                </td>

                                <td> {{ ucwords($issue->assetInventory->assetModel->assetType->name ?? 'N/A') }} </td>
                                
                                <td> {{ $issue->assetInventory->assetModel->model_name ?? 'N/A' }}</td>
                                    
                                <td>{{ ucwords($issue->custodian->custodian_name ?? 'N/A') }}</td>

                                <td>{{ $issue->custodian->emp_id ?? 'N/A' }} </td>

                                <td>{{ ucwords($issue->custodian->discipline->name ?? 'N/A') }}</td>
                                    
                                <td>{{ ucfirst($issue->user_type) }}</td>
                                                                  
                                <td>{{ $issue->issued_date? $issue->issued_date->format('d-m-Y'): 'N/A' }}</td>

                                <td>{{ $issue->returned_date? $issue->returned_date->format('d-m-Y'): '-' }} </td>

                                <td>
                                    @if($issue->issue_status === 'Issued')
                                        <span class="badge bg-success">
                                            Issued
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            Returned
                                        </span>
                                    @endif

                                </td>

                                <td>
                                    <!-- <a href="{{ route('asset-issue-register.edit',encryptId($issue->id)) }}" class="btn btn-sm btn-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a> -->
                                    {{-- custodian details --}}
                                    <button type="button" class="btn btn-sm btn-info view-issue-details"                                                                         
                                        data-id="{{ encryptId($issue->custodian_id) }}" title="View Custodian & Asset Details">                                       
                                        <i class="bi bi-eye"></i>
                                    </button>

                                    {{-- Return Asset --}}
                                    @if($issue->issue_status === 'Issued')
                                        <button type="button" class="btn btn-sm btn-success return-asset" data-id="{{ encryptId($issue->id) }}" title="Return Asset">                                                                                                                                
                                            <i class="bi bi-arrow-return-left"></i>
                                        </button>

                                    {{-- Transfer Asset --}}
                                    <button type="button" class="btn btn-sm btn-warning transfer-asset" data-id="{{ encryptId($issue->id)}}" 
                                    data-custodian-id="{{ encryptId($issue->custodian_id) }}" title="Transfer Asset">
                                        <i class="bi bi-arrow-left-right"></i>
                                    </button>
                                    @endif

                                   
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- ==========================================================
                    Custodian Details Modal
                ========================================================== --}}

                <div class="modal fade" id="issueDetailsModal" tabindex="-1" aria-labelledby="issueDetailsModalLabel" aria-hidden="true">       

                    <div class="modal-dialog modal-xl modal-dialog-scrollable">
                        <div class="modal-content">
                            {{-- Modal Header --}}
                            <div class="modal-header">

                                <h5 class="modal-title" id="issueDetailsModalLabel">
                                    <i class="bi bi-person-badge"></i>
                                    Custodian & Issued Asset Details
                                </h5>

                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">                                                                                                               
                                </button>

                            </div>


                            {{-- Modal Body --}}
                            <div class="modal-body" id="issueDetailsContent">

                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">                                       
                                    </div>

                                    <p class="mt-2 text-muted">
                                        Loading asset details...
                                    </p>

                                </div>

                            </div>


                            {{-- Modal Footer --}}
                            <div class="modal-footer">

                                <button type="button" class="btn btn-success" id="downloadCustodianExcel">                                                                         
                                    <i class="bi bi-file-earmark-excel"></i>
                                    Download as Excel
                                </button>

                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">                                                                          
                                    <i class="bi bi-x-circle"></i>
                                    Close
                                </button>
                            </div>

                        </div>

                    </div>

                </div>

               {{-- ==========================================================
                    Transfer Asset Modal
                ========================================================== --}}

                <div
                    class="modal fade"
                    id="transferAssetModal"
                    tabindex="-1"
                    aria-labelledby="transferAssetModalLabel"
                    aria-hidden="true">

                    <div class="modal-dialog modal-lg modal-dialog-centered">

                        <div class="modal-content">

                            {{-- Modal Header --}}
                            <div class="modal-header">

                                <h5 class="modal-title" id="transferAssetModalLabel">

                                    <i class="bi bi-arrow-left-right"></i>

                                    Transfer Asset

                                </h5>

                                <button
                                    type="button"
                                    class="btn-close"
                                    data-bs-dismiss="modal"
                                    aria-label="Close">
                                </button>

                            </div>


                            {{-- Modal Body --}}
                            <div class="modal-body">

                                <form id="transferAssetForm">

                                    @csrf

                                    <input
                                        type="hidden"
                                        id="transfer_issue_id"
                                        name="issue_id">


                                    {{-- Current Custodian --}}
                                    <div class="card border mb-3">

                                        <div class="card-header">

                                            <strong>
                                                Current Custodian
                                            </strong>

                                        </div>

                                        <div class="card-body">

                                            <div class="row">

                                                <div class="col-md-6 mb-3">

                                                    <label class="form-label">
                                                        Custodian Name
                                                    </label>

                                                    <input
                                                        type="text"
                                                        id="current_custodian_name"
                                                        class="form-control"
                                                        readonly>

                                                </div>


                                                <div class="col-md-3 mb-3">

                                                    <label class="form-label">
                                                        Employee ID
                                                    </label>

                                                    <input
                                                        type="text"
                                                        id="current_custodian_emp_id"
                                                        class="form-control"
                                                        readonly>

                                                </div>


                                                <div class="col-md-3 mb-3">

                                                    <label class="form-label">
                                                        Asset Tag
                                                    </label>

                                                    <input
                                                        type="text"
                                                        id="transfer_asset_tag"
                                                        class="form-control"
                                                        readonly>

                                                </div>

                                            </div>

                                        </div>

                                    </div>


                                    {{-- New Custodian --}}
                                    <div class="card border">

                                        <div class="card-header">

                                            <strong>
                                                Transfer To
                                            </strong>

                                        </div>

                                        <div class="card-body">

                                            <div class="row">

                                                {{-- Custodian --}}
                                                <div class="col-md-6 mb-3">

                                                    <label class="form-label">

                                                        New Custodian

                                                        <span class="text-danger">
                                                            *
                                                        </span>

                                                    </label>

                                                    <select
                                                        name="to_custodian_id"
                                                        id="transfer_to_custodian_id"
                                                        class="form-select">

                                                        <option value="">
                                                            Select Custodian
                                                        </option>

                                                        @foreach($custodians as $custodian)

                                                            <option
                                                                value="{{ $custodian->id }}">

                                                                {{ ucwords($custodian->custodian_name) }}
                                                                -
                                                                {{ $custodian->emp_id }}

                                                            </option>

                                                        @endforeach

                                                    </select>

                                                </div>


                                                {{-- Employee ID --}}
                                                <div class="col-md-3 mb-3">

                                                    <label class="form-label">
                                                        Employee ID
                                                    </label>

                                                    <input
                                                        type="text"
                                                        id="transfer_emp_id"
                                                        class="form-control"
                                                        readonly>

                                                </div>


                                                {{-- Designation --}}
                                                <div class="col-md-3 mb-3">

                                                    <label class="form-label">
                                                        Designation
                                                    </label>

                                                    <input
                                                        type="text"
                                                        id="transfer_designation"
                                                        class="form-control"
                                                        readonly>

                                                </div>


                                                {{-- Department --}}
                                                <div class="col-md-6 mb-3">

                                                    <label class="form-label">
                                                        Department
                                                    </label>

                                                    <input
                                                        type="text"
                                                        id="transfer_department"
                                                        class="form-control"
                                                        readonly>

                                                </div>


                                                {{-- Section --}}
                                                <div class="col-md-6 mb-3">

                                                    <label class="form-label">
                                                        Section
                                                    </label>

                                                    <input
                                                        type="text"
                                                        id="transfer_section"
                                                        class="form-control"
                                                        readonly>

                                                </div>


                                                {{-- Transfer Date --}}
                                                <div class="col-md-6 mb-3">

                                                    <label class="form-label">

                                                        Transfer Date

                                                        <span class="text-danger">
                                                            *
                                                        </span>

                                                    </label>

                                                    <input type="date" name="transfer_date" id="transfer_date" class="form-control" value="{{ date('Y-m-d') }}">
                                                </div>


                                                {{-- Remarks --}}
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">
                                                        Remarks
                                                    </label>

                                                    <textarea name="remarks" id="transfer_remarks" class="form-control" rows="2" placeholder="Enter transfer remarks"></textarea>
                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </form>

                            </div>


                            {{-- Modal Footer --}}
                            <div class="modal-footer">

                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">                                                                                             
                                    <i class="bi bi-x-circle"></i>
                                    Cancel
                                </button>


                                <button type="button" class="btn btn-warning" id="confirmTransferBtn">
                                    <i class="bi bi-arrow-left-right"></i>
                                    Transfer Asset
                                </button>

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
</div>


@push('scripts')
@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: @json(session('success')),
        timer: 2000,
        showConfirmButton: false
    });
</script>
@endif
<script>

    $(document).ready(function () {

    $('#assetIssueTable').DataTable({
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
                emptyTable: "No Issue register Found"
            }
        });

    });

    // asset return

    $(document).on('click', '.return-asset', function () {

        function ucwords(str) {
            return str
                    .toLowerCase()
                    .replace(/\b\w/g, function (char) {
                        return char.toUpperCase();
                    });
        }
        
        let button = $(this);
        let id = button.data('id');

        Swal.fire({
            title: 'Return Asset?',
            text: 'This asset will become available for another custodian.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, Return',
            cancelButtonText: 'Cancel'

        }).then((result) => {

            if (!result.isConfirmed) {
                return;
            }

            $.ajax({

                url: "{{ route('asset-issue-register.return', ':id') }}".replace(':id', id),                      
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}"
                },

                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Returned',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false

                    }).then(() => {
                        location.reload();
                    });
                },


                error: function (xhr) {
                    let message = xhr.responseJSON?.message ?? 'Unable to return asset.';                      
                        
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: message
                    });

                }
            });

        });

    });

    /*
    |--------------------------------------------------------------------------
    | View Custodian & Asset Details
    |--------------------------------------------------------------------------
    */

    $(document).on('click', '.view-issue-details', function () {

        let button = $(this);
        let id = button.data('id');
        $('#downloadCustodianExcel').data('id', id);
        /*
        |--------------------------------------------------------------------------
        | Get Modal
        |--------------------------------------------------------------------------
        */

        let modalElement = document.getElementById('issueDetailsModal');

        if (!modalElement) {

            console.error('issueDetailsModal not found.');

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Details modal was not found on the page.'
            });

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Bootstrap 5 Modal
        |--------------------------------------------------------------------------
        */

        let modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        modal.show();

        /*
        |--------------------------------------------------------------------------
        | Show Loading
        |--------------------------------------------------------------------------
        */

        $('#issueDetailsContent').html(`

            <div class="text-center py-5">
                <div class="spinner-border text-primary"
                    role="status">
                </div>

                <p class="mt-2 text-muted">
                    Loading asset details...
                </p>

            </div>

        `);


        /*
        |--------------------------------------------------------------------------
        | AJAX URL
        |--------------------------------------------------------------------------
        */

        let url =
            "{{ route('asset-issue-register.custodian-asset-details', ':id') }}"
            .replace(':id', id);


        console.log('Custodian Details URL:', url);


        /*
        |--------------------------------------------------------------------------
        | AJAX Request
        |--------------------------------------------------------------------------
        */

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',

            /*
            |--------------------------------------------------------------------------
            | Success
            |--------------------------------------------------------------------------
            */

            success: function (response) {

                console.log('Custodian Details Response:', response);


                if (!response.status) {

                    $('#issueDetailsContent').html(`

                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            ${response.message ?? 'Unable to load details.'}
                        </div>

                    `);

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | Custodian Details
                |--------------------------------------------------------------------------
                */

                let custodian = response.custodian;
                let assets = response.assets ?? [];

                /*
                |--------------------------------------------------------------------------
                | Create Asset Rows
                |--------------------------------------------------------------------------
                */

                let assetRows = '';

                if (assets.length > 0) {
                    $.each(assets, function (index, asset) {
                        assetRows += `
                            <tr>
                                <td>
                                    ${index + 1}
                                </td>

                                <td>
                                    <strong>
                                        ${asset.tag_no ?? '-'}
                                    </strong>
                                </td>

                                <td>
                                    ${asset.asset_type ?? '-'}
                                </td>

                                <td>
                                    ${asset.asset_model ?? '-'}
                                </td>

                                <td>
                                    ${asset.manufacturer ?? '-'}
                                </td>

                                <td>
                                    ${asset.serial_no ?? '-'}
                                </td>

                                <td>
                                    ${asset.location ?? '-'}
                                </td>

                                <td>
                                    ${asset.issued_date ?? '-'}
                                </td>

                                <td>
                                    <span class="badge bg-success">
                                        ${asset.issue_status ?? '-'}
                                    </span>
                                </td>

                            </tr>

                        `;

                    });

                } else {

                    assetRows = `
                        <tr>

                            <td colspan="9" class="text-center text-muted py-4">                               
                                <i class="bi bi-info-circle"></i>
                                No currently issued assets found.

                            </td>

                        </tr>

                    `;

                }


                /*
                |--------------------------------------------------------------------------
                | Modal HTML
                |--------------------------------------------------------------------------
                */

                let html = `

                    {{-- =====================================================
                        Custodian Information
                    ====================================================== --}}

                    <div class="card border mb-4">

                        <div class="card-header bg-light">

                            <h6 class="mb-0">

                                <i class="bi bi-person"></i>

                                Custodian Details

                            </h6>

                        </div>


                        <div class="card-body">

                            <div class="row">


                                {{-- Custodian Name --}}

                                <div class="col-md-4 mb-3">

                                    <label class="text-muted small">
                                        Custodian Name
                                    </label>

                                    <div class="fw-semibold">
                                        ${custodian.custodian_name ?? '-'}
                                    </div>

                                </div>


                                {{-- Employee ID --}}

                                <div class="col-md-4 mb-3">

                                    <label class="text-muted small">
                                        Employee ID
                                    </label>

                                    <div class="fw-semibold">
                                        ${custodian.emp_id ?? '-'}
                                    </div>

                                </div>


                                {{-- Email --}}

                                <div class="col-md-4 mb-3">

                                    <label class="text-muted small">
                                        Email
                                    </label>

                                    <div>
                                        ${custodian.email ?? '-'}
                                    </div>

                                </div>


                                {{-- Designation --}}

                                <div class="col-md-4 mb-3">

                                    <label class="text-muted small">
                                        Designation
                                    </label>

                                    <div>
                                        ${custodian.designation ? custodian.designation.toLowerCase()
                                            .replace(/\b\w/g, char => char.toUpperCase()) : '-'}
                                    </div>

                                </div>


                                {{-- Department --}}

                                <div class="col-md-4 mb-3">

                                    <label class="text-muted small">
                                        Department
                                    </label>

                                    <div>
                                        ${custodian.department ?? '-'}
                                    </div>

                                </div>


                                {{-- Section --}}

                                <div class="col-md-4 mb-3">

                                    <label class="text-muted small">
                                        Section
                                    </label>

                                    <div>
                                        ${custodian.section ? custodian.section : 'N/A'}
                                    </div>

                                </div>


                                {{-- Location --}}

                                <div class="col-md-3 mb-3">

                                    <label class="text-muted small">
                                        Location
                                    </label>

                                    <div>
                                        ${custodian.location ?? 'N/A'}
                                    </div>

                                </div>


                                {{-- Status --}}

                            </div>

                        </div>

                    </div>


                    {{-- =====================================================
                        Issue Summary
                    ====================================================== --}}

                    <div class="card border mb-4">
                        <div class="card-header bg-light">

                            <h6 class="mb-0">

                                <i class="bi bi-info-circle"></i>

                                Issue Summary

                            </h6>

                        </div>


                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-3">

                                    <label class="text-muted small">
                                        Total Assets
                                    </label>

                                    <div>
                                        <span class="badge bg-primary fs-6">
                                            ${response.issue.total_assets ?? 0}
                                        </span>
                                    </div>

                                </div>


                                <div class="col-md-3">
                                    <label class="text-muted small">
                                        User Type
                                    </label>

                                    <div>
                                        ${response.issue.user_type ?? '-'}
                                    </div>
                                </div>

                                <div class="col-md-3">

                                    <label class="text-muted small">
                                        Operator Name
                                    </label>

                                    <div>
                                        ${response.issue.operator_name ?? '-'}
                                    </div>

                                </div>


                                <div class="col-md-3">

                                    <label class="text-muted small">
                                        Issued Date
                                    </label>

                                    <div>
                                        ${response.issue.issued_date ?? '-'}
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- =====================================================
                        Issued Assets
                    ====================================================== --}}

                    <div class="card border">

                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                <i class="bi bi-pc-display"></i>
                                Currently Issued Assets
                            </h6>

                            <span class="badge bg-primary">
                                ${assets.length} Asset(s)
                            </span>

                        </div>


                        <div class="card-body">

                            <div class="table-responsive">

                                <table class="table table-bordered table-hover align-middle">
                                                                                      
                                    <thead class="table-dark">

                                        <tr>
                                            <th>SL</th>
                                            <th>Tag No.</th>
                                            <th>Asset Type</th>
                                            <th>Model</th>
                                            <th>Manufacturer</th>
                                            <th>Serial No.</th>
                                            <th>Location</th>
                                            <th>Issue Date</th>
                                            <th>Status</th>
                                        </tr>

                                    </thead>


                                    <tbody>

                                        ${assetRows}

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                `;


                /*
                |--------------------------------------------------------------------------
                | Put HTML Into Modal
                |--------------------------------------------------------------------------
                */

                $('#issueDetailsContent').html(html);

            },


            /*
            |--------------------------------------------------------------------------
            | AJAX Error
            |--------------------------------------------------------------------------
            */

            error: function (xhr) {

                console.error(
                    'Custodian Details Error:',
                    xhr.responseJSON ?? xhr.responseText
                );


                let message =
                    xhr.responseJSON?.message ??
                    'Unable to load custodian details.';


                $('#issueDetailsContent').html(`

                    <div class="alert alert-danger">

                        <i class="bi bi-exclamation-triangle"></i>

                        ${message}

                    </div>

                `);

            }

        });

    });

    /*
    |--------------------------------------------------------------------------
    | Download Custodian Details as Excel
    |--------------------------------------------------------------------------
    */

    $(document).on('click', '#downloadCustodianExcel', function () {

        let id = $(this).data('id');

        if (!id) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Custodian information is not available.'
            });
            return;
        }

        let url = "{{ route('asset-issue-register.custodian-export', ':id') }}".replace(':id', id);
            
        window.location.href = url;

    });

    $('#transfer_to_custodian').select2({
        placeholder: 'Select Custodian',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#transferAssetModal')
    });

    /* ==============================================================
    OPEN TRANSFER MODAL
    ============================================================== */

    $(document).on('click', '.transfer-asset', function () {

        let issueId = $(this).data('id');

        /*
        |--------------------------------------------------------------------------
        | Current Custodian
        |--------------------------------------------------------------------------
        |
        | This is the custodian FROM whom the asset is being transferred.
        |
        */

        let currentCustodianId =
            String($(this).data('custodian-id'));


        if (!issueId) {

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Invalid issue selected.'
            });

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Store Issue ID
        |--------------------------------------------------------------------------
        */

        $('#transfer_issue_id').val(issueId);


        /*
        |--------------------------------------------------------------------------
        | Reset Fields
        |--------------------------------------------------------------------------
        */

        $('#transfer_asset_tag').val('');
        $('#transfer_asset_type').val('');
        $('#transfer_asset_model').val('');

        $('#transfer_from_custodian').val('');
        $('#transfer_from_emp_id').val('');

        $('#transfer_to_custodian')
            .val('')
            .trigger('change');

        $('#transfer_to_emp_id').val('');
        $('#transfer_to_designation').val('');
        $('#transfer_to_department').val('');
        $('#transfer_to_section').val('');
        $('#transfer_to_location').val('');

        $('#transfer_remarks').val('');

        $('#transfer_date').val(
            new Date().toISOString().split('T')[0]
        );


        /*
        |--------------------------------------------------------------------------
        | Populate New Custodian Dropdown
        |--------------------------------------------------------------------------
        |
        | Remove the CURRENT custodian from the destination list.
        |
        */

        let transferSelect = $('#transfer_to_custodian');
            
        transferSelect.empty();

        transferSelect.append(`
            <option value="">
                Select New Custodian
            </option>
        `);


        /*
        |--------------------------------------------------------------------------
        | Add all custodians except current custodian
        |--------------------------------------------------------------------------
        */

        $('#custodianOptionSource option').each(function () {
            let option = $(this);

            // Skip empty option
            if (!option.val()) {
                return;
            }
            /*
            | Do not show current custodian
            */

            if (
                String(option.val()) ===
                currentCustodianId
            ) {
                return;
            }

            transferSelect.append(
                option.clone()
            );

        });


        /*
        |--------------------------------------------------------------------------
        | Reset Select2
        |--------------------------------------------------------------------------
        */

        transferSelect
            .val('')
            .trigger('change');


        /*
        |--------------------------------------------------------------------------
        | Show Modal
        |--------------------------------------------------------------------------
        */

        let modalElement =
            document.getElementById('transferAssetModal');

        let modal =
            bootstrap.Modal.getOrCreateInstance(
                modalElement
            );

        modal.show();


        /*
        |--------------------------------------------------------------------------
        | Get Transfer Details
        |--------------------------------------------------------------------------
        */

        let url =
            "{{ route('asset-issue-register.transfer-details', ':id') }}"
                .replace(':id', issueId);


        $.ajax({

            url: url,

            type: 'GET',

            dataType: 'json',

            success: function (response) {

                if (!response.status) {

                    Swal.fire({

                        icon: 'error',

                        title: 'Error',

                        text:
                            response.message ||
                            'Unable to load asset details.'

                    });

                    return;
                }


                let issue =
                    response.issue;


                /*
                |--------------------------------------------------------------
                | Current Asset
                |--------------------------------------------------------------
                */

                $('#transfer_asset_tag')
                    .val(issue.asset_tag);

                $('#transfer_asset_type')
                    .val(issue.asset_type);

                $('#transfer_asset_model')
                    .val(issue.asset_model);


                /*
                |--------------------------------------------------------------
                | Current Custodian
                |--------------------------------------------------------------
                */

                $('#transfer_from_custodian')
                    .val(issue.custodian_name);

                $('#transfer_from_emp_id')
                    .val(issue.emp_id);

            },


            error: function (xhr) {

                Swal.fire({

                    icon: 'error',

                    title: 'Error',

                    text:
                        xhr.responseJSON?.message ||
                        'Unable to load transfer details.'

                });

            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | Initialize Transfer Custodian Select2
    |--------------------------------------------------------------------------
    */

    $('#transfer_to_custodian_id').select2({
        placeholder: 'Select New Custodian',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#transferAssetModal')
    });


    /*
    |--------------------------------------------------------------------------
    | Open Transfer Modal
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'click',
        '.transfer-asset',
        function () {
            let issueId = $(this).data('id');


            /*
            |--------------------------------------------------------------------------
            | Reset modal
            |--------------------------------------------------------------------------
            */

            $('#transferAssetForm')[0].reset();
            $('#transfer_issue_id').val(issueId);
            $('#transfer_to_custodian_id').val('').trigger('change');               
            $('#current_custodian_name').val('');
            $('#current_custodian_emp_id').val('');
            $('#transfer_asset_tag').val('');
            $('#transfer_emp_id').val('');
            $('#transfer_designation').val('');
            $('#transfer_department').val('');
            $('#transfer_section').val('');

            /*
            |--------------------------------------------------------------------------
            | Default transfer date
            |--------------------------------------------------------------------------
            */

            $('#transfer_date').val(
                new Date().toISOString().split('T')[0]
            );

            /*
            |--------------------------------------------------------------------------
            | Show loading
            |--------------------------------------------------------------------------
            */

            Swal.fire({
                title: 'Loading',
                text: 'Loading transfer details...',
                allowOutsideClick: false,
                didOpen: function () {
                    Swal.showLoading();
                }

            });


            /*
            |--------------------------------------------------------------------------
            | Get current issue details
            |--------------------------------------------------------------------------
            */

            let url = "{{ route('asset-issue-register.transfer-details', ':id') }}".replace(':id', issueId);
                        
            $.ajax({
                url: url,
                type: 'GET',

                success: function (response) {
                    Swal.close();

                    if (!response.status) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text:
                                response.message ||
                                'Unable to load transfer details.'
                        });

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Current custodian
                    |--------------------------------------------------------------------------
                    */

                    $('#current_custodian_name').val(
                        response.issue.custodian_name || '-'
                    );


                    $('#current_custodian_emp_id').val(
                        response.issue.emp_id || '-'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Asset tag
                    |--------------------------------------------------------------------------
                    */

                    $('#transfer_asset_tag').val(
                        response.issue.asset_tag || '-'
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Store issue ID
                    |--------------------------------------------------------------------------
                    */

                    $('#transfer_issue_id').val(
                        response.issue.issue_id
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Open modal
                    |--------------------------------------------------------------------------
                    */

                    $('#transferAssetModal').modal('show');

                },


                error: function (xhr) {

                    Swal.close();


                    Swal.fire({

                        icon: 'error',

                        title: 'Error',

                        text:
                            xhr.responseJSON?.message ||
                            'Unable to load transfer details.'

                    });

                }

            });

        }
    );

    /* ==============================================================
    NEW CUSTODIAN CHANGE
    ============================================================== */

    $('#transfer_to_custodian_id').on(
        'change',
        function () {

            let custodianId = $(this).val();


            /*
            |--------------------------------------------------------------------------
            | Clear details
            |--------------------------------------------------------------------------
            */

            $('#transfer_emp_id').val('');

            $('#transfer_designation').val('');

            $('#transfer_department').val('');

            $('#transfer_section').val('');


            if (!custodianId) {

                return;

            }


            /*
            |--------------------------------------------------------------------------
            | Prevent transferring to same custodian
            |--------------------------------------------------------------------------
            */

            let currentEmpId =
                $('#current_custodian_emp_id').val();


            let selectedText =
                $('#transfer_to_custodian_id option:selected')
                .text();


            /*
            |--------------------------------------------------------------------------
            | Get custodian details
            |--------------------------------------------------------------------------
            */

            let url =
                "{{ route('asset-issue-register.custodian-details', ':id') }}"
                .replace(':id', custodianId);


            $.ajax({

                url: url,

                type: 'GET',


                success: function (response) {

                    if (!response.status) {

                        Swal.fire({

                            icon: 'error',

                            title: 'Error',

                            text:
                                response.message ||
                                'Unable to load custodian details.'

                        });

                        return;
                    }


                    let custodian =
                        response.custodian;


                    /*
                    |--------------------------------------------------------------------------
                    | Check same employee
                    |--------------------------------------------------------------------------
                    */

                    if (
                        String(custodian.emp_id) ===
                        String(currentEmpId)
                    ) {

                        Swal.fire({

                            icon: 'warning',

                            title: 'Invalid Custodian',

                            text:
                                'You cannot transfer the asset to the current custodian.'

                        });


                        $('#transfer_to_custodian_id')
                            .val('')
                            .trigger('change');

                        return;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Display details
                    |--------------------------------------------------------------------------
                    */

                    $('#transfer_emp_id').val(
                        custodian.emp_id || '-'
                    );


                    $('#transfer_designation').val(
                        custodian.designation || '-'
                    );


                    $('#transfer_department').val(
                        custodian.department || '-'
                    );


                    $('#transfer_section').val(
                        custodian.section || '-'
                    );

                },


                error: function () {

                    Swal.fire({

                        icon: 'error',

                        title: 'Error',

                        text:
                            'Unable to load custodian details.'

                    });

                }

            });

        }
    );

    /* ==============================================================
    CONFIRM TRANSFER
    ============================================================== */

    $('#confirmTransferBtn').on(
        'click',
        function () {

            let issueId =
                $('#transfer_issue_id').val();

            let toCustodianId =
                $('#transfer_to_custodian_id').val();

            let transferDate =
                $('#transfer_date').val();

            let remarks =
                $('#transfer_remarks').val();


            /*
            |--------------------------------------------------------------------------
            | Validation
            |--------------------------------------------------------------------------
            */

            if (!toCustodianId) {

                Swal.fire({

                    icon: 'warning',

                    title: 'Custodian Required',

                    text:
                        'Please select the new custodian.'

                });

                return;

            }


            if (!transferDate) {

                Swal.fire({

                    icon: 'warning',

                    title: 'Transfer Date Required',

                    text:
                        'Please select the transfer date.'

                });

                return;

            }


            /*
            |--------------------------------------------------------------------------
            | Confirmation
            |--------------------------------------------------------------------------
            */

            Swal.fire({

                title: 'Transfer Asset?',

                text:
                    'This asset will be returned from the current custodian and issued to the new custodian.',

                icon: 'question',

                showCancelButton: true,

                confirmButtonText:
                    'Yes, Transfer',

                cancelButtonText:
                    'Cancel'

            }).then(function (result) {

                if (!result.isConfirmed) {

                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | Disable button
                |--------------------------------------------------------------------------
                */

                $('#confirmTransferBtn')
                    .prop('disabled', true);


                /*
                |--------------------------------------------------------------------------
                | AJAX
                |--------------------------------------------------------------------------
                */

                $.ajax({

                    url:
                        "{{ route('asset-issue-register.transfer') }}",

                    type: 'POST',

                    data: {

                        _token:
                            "{{ csrf_token() }}",

                        issue_id:
                            issueId,

                        to_custodian_id:
                            toCustodianId,

                        transfer_date:
                            transferDate,

                        remarks:
                            remarks

                    },


                    success: function (response) {

                        $('#confirmTransferBtn')
                            .prop('disabled', false);


                        $('#transferAssetModal')
                            .modal('hide');


                        Swal.fire({

                            icon: 'success',

                            title: 'Transferred',

                            text:
                                response.message ||
                                'Asset transferred successfully.',

                            timer: 2000,

                            showConfirmButton: false

                        }).then(function () {

                            location.reload();

                        });

                    },


                    error: function (xhr) {

                        $('#confirmTransferBtn')
                            .prop('disabled', false);


                        let message =
                            'Unable to transfer asset.';


                        if (
                            xhr.responseJSON &&
                            xhr.responseJSON.message
                        ) {

                            message =
                                xhr.responseJSON.message;

                        }


                        Swal.fire({

                            icon: 'error',

                            title: 'Transfer Failed',

                            text: message

                        });

                    }

                });

            });

        }
    );
</script>

@endpush
@endsection