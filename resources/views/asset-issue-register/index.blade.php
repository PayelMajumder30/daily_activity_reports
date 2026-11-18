@extends('layouts.app')
@section('title', 'Asset Issued Register')
@section('content')

<div class="container-fluid py-4">


    {{-- Header --}}

    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-person-check"></i>
                Asset Issue Register
            </h2>

            <p class="text-muted">
                Manage issued assets and issue history
            </p>
        </div>

        <div class="col-md-4 text-end">
            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>
        </div>
    </div>


    {{-- Search Card --}}

    <div class="card shadow border-0 mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="bi bi-search"></i>
                Search Issued Assets
            </h5>

            <div class="d-flex gap-3">

                {{-- Issue Asset --}}
                <a href="{{ route('asset-issue-register.create') }}" class="btn btn-primary btn-sm">               
                    <i class="bi bi-plus-circle"></i>
                    Issue Asset
                </a>

                {{-- Retained Asset --}}
                <!-- <a href="{{ route('asset-retained.create') }}" class="btn btn-warning btn-sm">               
                    <i class="bi bi-plus-circle"></i>
                     Asset Retention
                </a> -->

                {{-- Export Excel --}}
                <a href="{{ route('asset-issue-register.export', request()->query()) }}" class="btn btn-success btn-sm">              
                    <i class="bi bi-file-earmark-excel"></i>
                    Export to Excel
                </a>
            </div>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('asset-issue-register.index') }}">
                <div class="row">
                    {{-- Custodian --}}
                    <div class="col-md-4">
                        <label class="form-label">
                            Custodian Name
                        </label>
                        <input type="text" name="custodian_name" value="{{ request('custodian_name') }}" class="form-control" placeholder="Search custodian">
                    </div>


                    {{-- Employee ID --}}

                    <div class="col-md-4">

                        <label class="form-label">
                            Employee ID
                        </label>

                        <input type="text" name="emp_id" value="{{ request('emp_id') }}" class="form-control" placeholder="Employee ID">                   
                    </div>


                    {{-- Tag --}}

                    <div class="col-md-4">
                        <label class="form-label">
                            Asset Tag
                        </label>
                        <input type="text" name="tag_no" value="{{ request('tag_no') }}" class="form-control" placeholder="Tag No">
                    </div>

                    {{-- Asset Type --}}
                    <div class="col-md-4">
                        <label class="form-label">Asset Type</label>
                        <select name="asset_type" class="form-select">         
                            <option value="">
                                All Asset Types
                            </option>
                            @foreach($assetTypes as $type)
                                <option value="{{ encryptId($type->id) }}" 
                                    {{ (request('asset_type') == encryptId($type->id) || (isset($assetTypeId) && $assetTypeId == $type->id)) ? 'selected' : '' }}>         
                                    {{ ucwords($type->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Asset Model --}}
                    <div class="col-md-4">
                        <label class="form-label">Asset Model</label>
                        <select name="asset_model" id="asset_model" class="form-select">         
                            <option value="">
                                All Asset Models
                            </option>
                            @foreach($assetModels as $assetModel)
                                <option value="{{ encryptId($assetModel->id) }}"
                                    {{ (request('asset_model') == encryptId($assetModel->id) || (isset($assetModelId) && $assetModelId == $assetModel->id)) ? 'selected' : '' }}>
                                    {{ ucwords($assetModel->model_name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Issue Status --}}

                    <div class="col-md-4">
                        <label class="form-label">
                            Status
                        </label>

                        <select name="issue_status" class="form-select">                                                      
                            <option value="">
                                All
                            </option>
                            @foreach($issueStatuses as $status)
                                <option value="{{ $status }}" {{ request('issue_status') == $status ? 'selected' : '' }}>                                                              
                                    {{ ucwords($status)}}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-3">
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
                            <th>Asset Department</th>
                            <th>Asset Section</th>
                            <th>Custodian</th>
                            <th>Employee ID</th>
                            <th>Custodian Department</th>
                            <th>User Type</th>
                            <th>Issued Date</th>
                            <th>Transfer Date</th>
                            <th>Returned Date</th>
                            <th>Retained Date</th>
                            <!-- <th>Asset History</th> -->
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
                                <td> {{ $issue->discipline->name ?? 'N/A' }}</td>                                    
                                <td> {{ $issue->deptSection->section_name ?? 'N/A' }}</td>                                    
                                <td>{{ ucwords($issue->custodian->custodian_name ?? 'N/A') }}</td>
                                <td>{{ $issue->custodian->emp_id ?? 'N/A' }} </td>
                                <td>{{ ucwords($issue->custodian->discipline->name ?? 'N/A') }}</td>                                 
                                <td>{{ ucfirst($issue->user_type) }}</td>                                                                 
                                <td>{{ $issue->issued_date? $issue->issued_date->format('d-m-Y'): 'N/A' }}</td>
                                <td>{{ $issue->transfer_date ? $issue->transfer_date->format('d-m-Y'): 'N/A' }}</td>
                                <td>{{ $issue->returned_date? $issue->returned_date->format('d-m-Y'): 'N/A' }} </td>
                                <td>{{ $issue->retained_date? $issue->retained_date->format('d-m-Y'): 'N/A' }} </td>
  
                                <!-- <td></td> -->
                               <td>
                                    @if($issue->issue_status === 'Issued')
                                        <span class="badge bg-success">
                                            Issued
                                        </span>
                                    @elseif($issue->issue_status === 'Transferred')
                                        <span class="badge bg-warning text-dark">
                                            Transferred
                                        </span>
                                    @elseif($issue->issue_status === 'Returned')
                                        <span class="badge bg-secondary">
                                            Returned
                                        </span>
                                    @elseif($issue->issue_status === 'Retained')
                                        <span class="badge bg-info text-dark">
                                            Retained
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark">
                                            {{ ucwords($issue->issue_status) }}
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

                                        {{-- Retention Asset --}}
                                        <button type="button" class="btn btn-sm btn-primary retain-asset"                                                                                     
                                            data-id="{{ encryptId($issue->id) }}" data-custodian="{{ ucwords($issue->custodian->custodian_name ?? '') }}"                        
                                            data-tag="{{ $issue->assetInventory->tag_no ?? '' }}" title="Retain Asset">                                                                 
                                            <i class="bi bi-person-workspace"></i>
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

                <div class="modal fade" id="transferAssetModal" tabindex="-1" aria-labelledby="transferAssetModalLabel" aria-hidden="true">
                     
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">

                            {{-- Modal Header --}}
                            <div class="modal-header">

                                <h5 class="modal-title" id="transferAssetModalLabel">
                                    <i class="bi bi-arrow-left-right"></i>
                                    Transfer Asset
                                </h5>

                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">    
                                </button>

                            </div>


                            {{-- Modal Body --}}
                            <div class="modal-body">
                                <form id="transferAssetForm">
                                    @csrf
                                    <input type="hidden" id="transfer_issue_id" name="issue_id">

                                    {{-- Current Custodian Card --}}
                                    <div class="card border mb-3">
                                        <div class="card-header">
                                            <strong>Current Custodian</strong>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Custodian Name</label>
                                                    <input type="text" id="current_custodian_name" class="form-control" readonly>                                                                                                                                                                                                                           
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Employee ID</label>
                                                    <input type="text" id="current_custodian_emp_id" class="form-control" readonly>
                                                </div>
                                                <div class="col-md-4 mb-3">
                                                    <label class="form-label">Asset Tag</label>
                                                    <input type="text" id="transfer_asset_tag" class="form-control" readonly>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Asset Department</label>
                                                    <input type="text" id="current_custodian_department" class="form-control" readonly>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Asset Section</label>
                                                    <input type="text" id="current_custodian_section" class="form-control" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Transfer To Card --}}
                                    <div class="card border">
                                        <div class="card-header">
                                            <strong>Transfer To</strong>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                {{-- New Custodian --}}
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">New Custodian <span class="text-danger">*</span></label>
                                                    <select name="to_custodian_id" id="transfer_to_custodian_id" class="form-select">
                                                        <option value="">Select Custodian</option>
                                                        @foreach($custodians as $custodian)
                                                            <option value="{{ $custodian->id }}">
                                                                {{ ucwords($custodian->custodian_name) }} - {{ $custodian->emp_id }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                {{-- Employee ID --}}
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Employee ID</label>
                                                    <input type="text" id="transfer_emp_id" class="form-control" readonly>
                                                </div>

                                                {{-- Designation --}}
                                                <div class="col-md-3 mb-3">
                                                    <label class="form-label">Designation</label>
                                                    <input type="text" id="transfer_designation" class="form-control" readonly>
                                                </div>

                                                {{-- Department Dropdown (Manual/Selectable) --}}
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Asset Department <span class="text-danger">*</span></label>
                                                    <select name="discipline_id" id="transfer_discipline_id" class="form-select">
                                                        <option value="">Select Department</option>
                                                    </select>
                                                </div>

                                                {{-- Section Dropdown (Dynamic) --}}
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Asset Section</label>
                                                    <select name="dept_section_id" id="transfer_dept_section_id" class="form-select">
                                                        <option value="">Select Section</option>
                                                    </select>                                                                                                                                                                                                                      
                                                </div>

                                                {{-- Transfer Date --}}
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Transfer Date <span class="text-danger">*</span></label>
                                                    <input type="date" name="transfer_date" id="transfer_date" class="form-control" value="{{ date('Y-m-d') }}">
                                                </div>

                                                {{-- Remarks --}}
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">Remarks</label>
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
        $('#asset_model').select2({
            placeholder: 'All Asset Models',
            allowClear: true,
            width: '100%'
        });

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

        // Initialize Transfer Custodian Select2
        $('#transfer_to_custodian_id').select2({
            placeholder: 'Select New Custodian',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#transferAssetModal')
        });
    });

    // Asset Return
    $(document).on('click', '.return-asset', function () {
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

    // View Custodian & Asset Details
    $(document).on('click', '.view-issue-details', function () {
        let button = $(this);
        let id = button.data('id');
        $('#downloadCustodianExcel').data('id', id);

        let modalElement = document.getElementById('issueDetailsModal');
        if (!modalElement) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Details modal was not found on the page.'
            });
            return;
        }

        let modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        modal.show();

        $('#issueDetailsContent').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Loading asset details...</p>
            </div>
        `);

        let url = "{{ route('asset-issue-register.custodian-asset-details', ':id') }}".replace(':id', id);

        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (!response.status) {
                    $('#issueDetailsContent').html(`
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            ${response.message ?? 'Unable to load details.'}
                        </div>
                    `);
                    return;
                }

                let custodian = response.custodian;
                let assets = response.assets ?? [];
                let assetRows = '';

                if (assets.length > 0) {
                    $.each(assets, function (index, asset) {
                        assetRows += `
                            <tr>
                                <td>${index + 1}</td>
                                <td><strong>${asset.tag_no ?? '-'}</strong></td>
                                <td>${asset.asset_type ?? '-'}</td>                                                                 
                                <td>${asset.asset_model ?? '-'}</td>                                                                 
                                <td>${asset.manufacturer ?? '-'}</td>                                                                  
                                <td>${asset.serial_no ?? '-'}</td>                                                                  
                                <td>${asset.location ?? '-'}</td>                                                              
                                <td>${asset.station ?? '-'}</td>   
                                <td>${asset.department_name ?? '-'}</td>  
                                <td>${asset.section_name  ?? '-'}</td>   
                                <td>${asset.issued_date ?? '-'}</td>                                                              
                                <td><span class="badge bg-success">${asset.issue_status ?? '-'}</span></td>
                            </tr>
                        `;
                    });
                } else {
                    assetRows = `
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">                               
                                <i class="bi bi-info-circle"></i> No currently issued assets found.
                            </td>
                        </tr>
                    `;
                }

                let html = `
                    <div class="card border mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-person"></i> Custodian Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Custodian Name</label>
                                    <div class="fw-semibold">${custodian.custodian_name ?? '-'}</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Employee ID</label>
                                    <div class="fw-semibold">${custodian.emp_id ?? '-'}</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Email</label>
                                    <div>${custodian.email ?? '-'}</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Designation</label>
                                    <div>${custodian.designation ?? '-'}</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Department</label>
                                    <div>${custodian.department ?? '-'}</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="text-muted small">Section</label>
                                    <div>${custodian.section ?? 'N/A'}</div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small">Region</label>
                                    <div>${custodian.location ?? 'N/A'}</div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="text-muted small">Airport / Station</label>
                                    <div>${custodian.station ?? 'N/A'}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0"><i class="bi bi-info-circle"></i> Issue Summary</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="text-muted small">Total Assets</label>
                                    <div><span class="badge bg-primary fs-6">${response.issue.total_assets ?? 0}</span></div>
                                </div>
                                <div class="col-md-3">
                                    <label class="text-muted small">User Type</label>
                                    <div>${response.issue.user_type ?? '-'}</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="text-muted small">Operator Name</label>
                                    <div>${response.issue.operator_name ?? '-'}</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="text-muted small">Issued Date</label>
                                    <div>${response.issue.issued_date ?? '-'}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="mb-0"><i class="bi bi-pc-display"></i> Currently Issued Assets</h6>
                            <span class="badge bg-primary">${assets.length} Asset(s)</span>
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
                                            <th>Region</th>
                                            <th>Airport/Station</th>
                                            <th>Asset Department</th>
                                            <th>Asset Section</th>
                                            <th>Issue Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>${assetRows}</tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `;
                $('#issueDetailsContent').html(html);
            },
            error: function (xhr) {
                let message = xhr.responseJSON?.message ?? 'Unable to load custodian details.';                                      
                $('#issueDetailsContent').html(`
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> ${message}
                    </div>
                `);
            }
        });
    });

    // Download Custodian Details as Excel
    $(document).on('click', '#downloadCustodianExcel', function () {
        let id = $(this).data('id');
        if (!id) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Custodian information is not available.' });
            return;
        }
        let url = "{{ route('asset-issue-register.custodian-export', ':id') }}".replace(':id', id);
        window.location.href = url;
    });

    /* ==============================================================
    OPEN TRANSFER MODAL
    ============================================================== */
    $(document).on('click', '.transfer-asset', function () {
        let issueId = $(this).data('id');

        if (!issueId) {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Invalid issue selected.' });
            return;
        }

        $('#transferAssetForm')[0].reset();
        $('#transfer_issue_id').val(issueId);
        $('#transfer_to_custodian_id').val('').trigger('change');               
        $('#current_custodian_name').val('');
        $('#current_custodian_emp_id').val('');
        $('#transfer_asset_tag').val('');
        $('#transfer_emp_id').val('');
        $('#transfer_designation').val('');

        // Reset and disable section dropdown by default until department is selected
        $('#transfer_discipline_id').empty().append('<option value="">Select Department</option>');
        $('#transfer_dept_section_id').empty().append('<option value="">Select Section</option>').prop('disabled', true);

        $('#transfer_date').val(new Date().toISOString().split('T')[0]);

        Swal.fire({
            title: 'Loading',
            text: 'Loading transfer details...',
            allowOutsideClick: false,
            didOpen: function () {
                Swal.showLoading();
            }
        });

        let url = "{{ route('asset-issue-register.transfer-details', ':id') }}".replace(':id', issueId);
                    
        $.ajax({
            url: url,
            type: 'GET',
            success: function (response) {
                Swal.close();
                if (!response.status) {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Unable to load transfer details.' });
                    return;
                }

                let issue = response.issue;
                
                $('#current_custodian_name').val(issue.custodian_name || '-');
                $('#current_custodian_emp_id').val(issue.emp_id || '-');
                $('#transfer_asset_tag').val(issue.asset_tag || '-');
                $('#current_custodian_department').val(issue.department || '-');
                $('#current_custodian_section').val(issue.section || '-');
                $('#transfer_issue_id').val(issue.issue_id);

                // Populate Transfer To Department dropdown
                let disciplineSelect = $('#transfer_discipline_id');
                disciplineSelect.empty().append('<option value="">Select Department</option>');
                if (response.disciplines) {
                    $.each(response.disciplines, function (i, disc) {
                        disciplineSelect.append(`<option value="${disc.id}">${disc.name}</option>`);
                    });
                }

                $('#transferAssetModal').modal('show');
            },
            error: function (xhr) {
                Swal.close();
                Swal.fire({ icon: 'error', title: 'Error', text: xhr.responseJSON?.message || 'Unable to load transfer details.' });
            }
        });
    });

    /* ==============================================================
    NEW CUSTODIAN CHANGE
    ============================================================== */
    $('#transfer_to_custodian_id').on('change', function () {
        let custodianId = $(this).val();
        
        $('#transfer_emp_id').val('');
        $('#transfer_designation').val('');
        $('#transfer_discipline_id').val('').trigger('change');

        // Disable section field when custodian changes/clears
        $('#transfer_dept_section_id').empty().append('<option value="">Select Section</option>').prop('disabled', true);

        if (!custodianId) return;

        let currentEmpId = $('#current_custodian_emp_id').val();
        let url = "{{ route('asset-issue-register.custodian-details', ':id') }}".replace(':id', custodianId);

        $.ajax({
            url: url,
            type: 'GET',
            success: function (response) {
                if (!response.status) return;
                let custodian = response.custodian;

                if (String(custodian.emp_id) === String(currentEmpId)) {
                    Swal.fire({ icon: 'warning', title: 'Invalid Custodian', text: 'You cannot transfer the asset to the current custodian.' });
                    $('#transfer_to_custodian_id').val('').trigger('change');
                    return;
                }

                $('#transfer_emp_id').val(custodian.emp_id || '-');
                $('#transfer_designation').val(custodian.designation || '-');

                if (custodian.discipline_id) {
                    $('#transfer_discipline_id').val(custodian.discipline_id).trigger('change');
                    setTimeout(() => {
                        if(custodian.section_id) {
                            $('#transfer_dept_section_id').val(custodian.section_id);
                        }
                    }, 200);
                }
            }
        });
    });

    // Dynamic Section Fetching on Department Change
    $('#transfer_discipline_id').on('change', function () {
        let disciplineId = $(this).val();
        let sectionSelect = $('#transfer_dept_section_id');

        sectionSelect.empty().append('<option value="">Select Section</option>');

        // Reset and disable section dropdown if no department selected
        if (!disciplineId) {
            sectionSelect.empty().append('<option value="">Select Section</>').prop('disabled', true);
            return;
        }

        let url = "{{ route('asset-issue-register.department-sections', ':id') }}".replace(':id', disciplineId);
        $.get(url, function (response) {
            sectionSelect.empty();
            
            if (response.status && response.sections && response.sections.length > 0) {
                // Sections available: enable dropdown and populate list
                sectionSelect.append('<option value="">Select Section</option>');
                $.each(response.sections, function (index, section) {
                    sectionSelect.append(`<option value="${section.id}">${section.section_name}</option>`);
                });
                sectionSelect.prop('disabled', false);
            } else {
                // No sections available: show message and keep disabled
                sectionSelect.append('<option value="">No section available</option>');
                sectionSelect.prop('disabled', true);
            }
        }).fail(function () {
            sectionSelect.append('<option value="">No section available</option>');
            sectionSelect.prop('disabled', true);
        });
    });

    /* ==============================================================
    CONFIRM TRANSFER
    ============================================================== */
    $('#confirmTransferBtn').on('click', function () {
        let issueId = $('#transfer_issue_id').val();
        let toCustodianId = $('#transfer_to_custodian_id').val();
        let disciplineId = $('#transfer_discipline_id').val();
        let deptSectionId = $('#transfer_dept_section_id').val();
        let transferDate = $('#transfer_date').val();
        let remarks = $('#transfer_remarks').val();

        if (!toCustodianId) {
            Swal.fire({ icon: 'warning', title: 'Custodian Required', text: 'Please select the new custodian.' });
            return;
        }
        if (!disciplineId) {
            Swal.fire({ icon: 'warning', title: 'Department Required', text: 'Please select the department.' });
            return;
        }
        if (!transferDate) {
            Swal.fire({ icon: 'warning', title: 'Transfer Date Required', text: 'Please select the transfer date.' });
            return;
        }

        $.ajax({
            url: "{{ route('asset-issue-register.transfer') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                issue_id: issueId,
                to_custodian_id: toCustodianId,
                discipline_id: disciplineId,
                dept_section_id: deptSectionId,
                transfer_date: transferDate,
                remarks: remarks
            },
            success: function (response) {
                $('#transferAssetModal').modal('hide');
                Swal.fire({ icon: 'success', title: 'Transferred', text: response.message, timer: 2000, showConfirmButton: false })
                    .then(() => location.reload());
            },
            error: function (xhr) {
                Swal.fire({ icon: 'error', title: 'Transfer Failed', text: xhr.responseJSON?.message || 'Unable to transfer asset.' });
            }
        });
    });

    /* ==============================================================
    RETAIN ASSET
    ============================================================== */
    $(document).on('click', '.retain-asset', function () {
        let button = $(this);
        let id = button.data('id');
        let custodianName = button.data('custodian');
        let assetTag = button.data('tag');
        let today = new Date().toISOString().split('T')[0];

        Swal.fire({
            title: 'Retain Asset?',
            html: `
                <div class="text-start">
                    <p>Are you sure you want to retain this asset?</p>
                    <div class="card border-0 bg-light mb-3">
                        <div class="card-body">
                            <div class="mb-2"><strong>Asset Tag:</strong> <span class="text-primary">${assetTag || '-'}</span></div>
                            <div><strong>Retained To Custodian:</strong> <span class="text-success">${custodianName || '-'}</span></div>
                        </div>
                    </div>
                    <div>
                        <label class="form-label fw-bold">Retention Date</label>
                        <input type="date" id="swal_retained_date" class="form-control" value="${today}">
                    </div>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Retain Asset',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                let retainedDate = document.getElementById('swal_retained_date').value;
                if (!retainedDate) {
                    Swal.showValidationMessage('Please select the retention date.');
                    return false;
                }
                return { retained_date: retainedDate };
            }
        }).then((result) => {
            if (!result.isConfirmed) return;

            let retainedDate = result.value.retained_date;

            $.ajax({
                url: "{{ route('asset-issue-register.retain', ':id') }}".replace(':id', id),
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    retained_date: retainedDate
                },
                success: function (response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Asset Retained',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false,
                        allowOutsideClick: false
                    }).then(() => {
                        window.location.href = "{{ route('asset-issue-register.index') }}";                          
                    });
                },
                error: function (xhr) {
                    let message = xhr.responseJSON?.message || 'Unable to retain asset.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Retention Failed',
                        text: message
                    });
                }
            });
        });
    });
</script>

@endpush
@endsection