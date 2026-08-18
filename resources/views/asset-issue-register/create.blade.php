@extends('layouts.app')

@section('title', 'Create Asset Issued Register')

@section('content')

<div class="container-fluid py-4">

    <div class="row mb-4">

        <div class="col-md-8">

            <h2 class="fw-bold">
                <i class="bi bi-box-arrow-right"></i>
                Issue Asset
            </h2>

            <p class="text-muted">
                Issue an available asset to a custodian
            </p>

        </div>

        <div class="col-md-4 text-end">

            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>

        </div>

    </div>


    <div class="card shadow border-0">

        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-person-workspace"></i>
                Issue Asset
            </h5>
        </div>

        <div class="card-body">

            <form method="POST"
                action="{{ route('asset-issue-register.store') }}"
                id="assetIssueForm">

                @csrf

                <div class="row">

                    {{-- =========================
                        Custodian
                    ========================== --}}

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Custodian
                            <span class="text-danger">*</span>
                        </label>

                        <select name="custodian_id"
                                id="custodian_id"
                                class="form-select @error('custodian_id') is-invalid @enderror">

                            <option value="">
                                Select Custodian
                            </option>

                            @foreach($custodians as $custodian)

                                <option value="{{ $custodian->id }}"
                                    {{ old('custodian_id') == $custodian->id ? 'selected' : '' }}>

                                    {{ ucwords($custodian->custodian_name) }}
                                    - {{ $custodian->emp_id }}

                                </option>

                            @endforeach

                        </select>

                        @error('custodian_id')
                            <small class="text-danger">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>


                    {{-- =========================
                        Employee ID
                    ========================== --}}

                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Employee ID
                        </label>

                        <input type="text"
                            id="custodian_emp_id"
                            class="form-control"
                            readonly>

                    </div>


                    {{-- =========================
                        Designation
                    ========================== --}}

                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Designation
                        </label>

                        <input type="text"
                            id="custodian_designation"
                            class="form-control"
                            readonly>

                    </div>


                    {{-- =========================
                        Department
                    ========================== --}}

                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Department
                        </label>

                        <input type="text"
                            id="custodian_department"
                            class="form-control"
                            readonly>

                    </div>


                    {{-- =========================
                        Section
                    ========================== --}}

                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Section
                        </label>

                        <input type="text"
                            id="custodian_section"
                            class="form-control"
                            readonly>

                    </div>


                    {{-- =========================
                        User Type
                    ========================== --}}

                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            User Type
                            <span class="text-danger">*</span>
                        </label>

                        <select name="user_type"
                                id="user_type"
                                class="form-select @error('user_type') is-invalid @enderror">

                            <option value="">
                                Select User Type
                            </option>

                            <option value="self"
                                {{ old('user_type') == 'self' ? 'selected' : '' }}>
                                Self
                            </option>

                            <option value="multiuser"
                                {{ old('user_type') == 'multiuser' ? 'selected' : '' }}>
                                Multiuser
                            </option>

                            <option value="operator"
                                {{ old('user_type') == 'operator' ? 'selected' : '' }}>
                                Operator
                            </option>

                        </select>

                        @error('user_type')
                            <small class="text-danger">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>


                    {{-- =========================
                        Operator
                    ========================== --}}

                    <div class="col-md-6 mb-3"
                        id="operatorField"
                        style="display:none;">

                        <label class="form-label">
                            Operator Name
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                            name="operator_name"
                            id="operator_name"
                            value="{{ old('operator_name') }}"
                            class="form-control"
                            placeholder="Enter operator name">

                    </div>


                    {{-- =========================
                        Issue Date
                    ========================== --}}

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Issue Date
                            <span class="text-danger">*</span>
                        </label>

                        <input type="date"
                            name="issued_date"
                            value="{{ old('issued_date', now()->format('Y-m-d')) }}"
                            class="form-control @error('issued_date') is-invalid @enderror">

                        @error('issued_date')
                            <small class="text-danger">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>

                </div>


                {{-- =========================
                    Selected Assets
                ========================== --}}

                <div class="card border mt-3">
                    <div class="card-header d-flex justify-content-between align-items-center">

                        <h6 class="mb-0">
                            Selected Assets
                        </h6>

                        <button type="button"
                                class="btn btn-primary btn-sm"
                                id="addAssetBtn">

                            <i class="bi bi-plus-circle"></i>
                            Add Asset

                        </button>

                    </div>

                    <div class="card-body">

                        <div class="table-responsive">

                            <table class="table table-bordered"
                                id="selectedAssetsTable">

                                <thead class="table-dark">

                                    <tr>
                                        <th width="60">SL</th>
                                        <th>Tag No.</th>
                                        <th>Asset Type</th>
                                        <th>Asset Model</th>
                                        <th width="80">Action</th>
                                    </tr>

                                </thead>

                                <tbody id="selectedAssetsBody">

                                    <tr id="noAssetRow">

                                        <td colspan="5"
                                            class="text-center text-muted">

                                            No assets selected.

                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                        @error('asset_inventory_ids')
                            <small class="text-danger">
                                {{ $message }}
                            </small>
                        @enderror
                    </div>
                    <div id="selectedAssetsContainer"></div>
                </div>


                <div class="mt-4">

                    <button type="submit" class="btn btn-primary" id="issueAssetBtn">
                        <i class="bi bi-check-circle"></i>
                        Issue Asset
                    </button>

                    <a href="{{ route('asset-issue-register.index') }}" class="btn btn-secondary ms-2">                
                        Back
                    </a>

                </div>

            </form>

            {{-- =========================
            Add Asset Modal
            ========================== --}}

            <div class="modal fade" id="addAssetModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">

                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-plus-circle"></i>
                                Add Assets
                            </h5>

                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="modal">
                            </button>
                        </div>

                        <div class="modal-body">

                            <div id="assetRows">

                                {{-- First Row --}}
                                <div class="row asset-row mb-3">

                                    {{-- Asset Tag --}}
                                    <div class="col-md-5">

                                        <label class="form-label">
                                            Asset Tag
                                            <span class="text-danger">*</span>
                                        </label>

                                        <select
                                            name="modal_asset_inventory_id[]"
                                            class="form-select modal-asset-tag">

                                            <option value="">
                                                Select Asset Tag
                                            </option>

                                            @foreach($assets as $asset)

                                                <option
                                                    value="{{ $asset->id }}"
                                                    data-type="{{ $asset->assetModel?->assetType?->name }}"
                                                    data-model="{{ $asset->assetModel?->model_name }}">

                                                    {{ $asset->tag_no }}

                                                </option>

                                            @endforeach

                                        </select>

                                    </div>


                                    {{-- Asset Type --}}
                                    <div class="col-md-3">

                                        <label class="form-label">
                                            Asset Type
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control modal-asset-type"
                                            readonly>

                                    </div>


                                    {{-- Asset Model --}}
                                    <div class="col-md-3">

                                        <label class="form-label">
                                            Asset Model
                                        </label>

                                        <input
                                            type="text"
                                            class="form-control modal-asset-model"
                                            readonly>

                                    </div>


                                    {{-- Remove --}}
                                    <div class="col-md-1 d-flex align-items-end">

                                        <button
                                            type="button"
                                            class="btn btn-danger remove-asset-row"
                                            style="display:none;">

                                            <i class="bi bi-trash"></i>

                                        </button>

                                    </div>

                                </div>

                            </div>

                        </div>


                        <div class="modal-footer">

                            <button
                                type="button"
                                class="btn btn-success"
                                id="addMoreAssetRow">

                                <i class="bi bi-plus-circle"></i>
                                Add More

                            </button>

                            <button
                                type="button"
                                class="btn btn-secondary"
                                data-bs-dismiss="modal">

                                Done

                            </button>

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

    /*
    |--------------------------------------------------------------------------
    | Asset Data
    |--------------------------------------------------------------------------
    */

    let asset = @json($assetData);

    /*
    |--------------------------------------------------------------------------
    | Select2 - Custodian
    |--------------------------------------------------------------------------
    */

    $('#custodian_id').select2({

        placeholder: 'Select Custodian',
        allowClear: true,
        width: '100%'

    });


    /*
    |--------------------------------------------------------------------------
    | Select2 - Asset Tag
    |--------------------------------------------------------------------------
    */

    $('#modal_asset_inventory_id').select2({
        placeholder: 'Select Asset Tag',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#addAssetModal')

    });

    /*
    |--------------------------------------------------------------------------
    | Custodian Details
    |--------------------------------------------------------------------------
    */

    $('#custodian_id').on('change', function () {
        let selected = $(this).find('option:selected');
        let custodianId = selected.val();

        /*
        |--------------------------------------------------------------------------
        | Clear Details
        |--------------------------------------------------------------------------
        */

        if (!custodianId) {

            $('#custodian_emp_id').val('');
            $('#custodian_designation').val('');
            $('#custodian_department').val('');
            $('#custodian_section').val('');
            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Fetch Custodian Details
        |--------------------------------------------------------------------------
        */

        let url = "{{ route('asset-issue-register.custodian-details', ':id') }}".replace(':id', custodianId);
            
        $.ajax({
            url: url,
            type: 'GET',

            success: function (response) {
                if (!response.status) {
                    $('#custodian_emp_id').val('');
                    $('#custodian_designation').val('');
                    $('#custodian_department').val('');
                    $('#custodian_section').val('');
                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | Employee ID
                |--------------------------------------------------------------------------
                */

                $('#custodian_emp_id')
                    .val(
                        response.custodian.emp_id || '-'
                    );


                /*
                |--------------------------------------------------------------------------
                | Designation
                |--------------------------------------------------------------------------
                */

                $('#custodian_designation')
                    .val(
                        response.custodian.designation || '-'
                    );

                /*
                |--------------------------------------------------------------------------
                | Department
                |--------------------------------------------------------------------------
                */

                $('#custodian_department')
                    .val(
                        response.custodian.department || '-'
                    );

                /*
                |--------------------------------------------------------------------------
                | Section
                |--------------------------------------------------------------------------
                */

                $('#custodian_section')
                    .val(
                        response.custodian.section || '-'
                    );
            },

            error: function () {

                $('#custodian_emp_id').val('');
                $('#custodian_designation').val('');
                $('#custodian_department').val('');
                $('#custodian_section').val('');

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Unable to load custodian details.'

                });

            }

        });

    });


    /*
    |--------------------------------------------------------------------------
    | User Type -> Operator Field
    |--------------------------------------------------------------------------
    */

    function toggleOperatorField() {

        let type = $('#user_type').val();
        if (type === 'operator') {
            $('#operatorField').show();
            $('#operator_name').prop('required', true);
                
        } else {

            $('#operatorField').hide();
            $('#operator_name')
                .prop('required', false)
                .val('');

        }

    }

    /*
    |--------------------------------------------------------------------------
    | User Type Change
    |--------------------------------------------------------------------------
    */

    $('#user_type').on(
        'change',
        toggleOperatorField
    );

    /*
    |--------------------------------------------------------------------------
    | Initial Operator State
    |--------------------------------------------------------------------------
    */

    toggleOperatorField();

    /*
    |--------------------------------------------------------------------------
    | Open Add Asset Modal
    |--------------------------------------------------------------------------
    */

    $('#addAssetBtn').on('click', function () {

        if (!$('#custodian_id').val()) {

            Swal.fire({
                icon: 'warning',
                title: 'Select Custodian',
                text: 'Please select a custodian before adding assets.'
            });

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Reset Modal
        |--------------------------------------------------------------------------
        */

        resetAssetModal();

        /*
        |--------------------------------------------------------------------------
        | Open Modal
        |--------------------------------------------------------------------------
        */

        $('#addAssetModal').modal('show');

    });

    
    /*
    |--------------------------------------------------------------------------
    | Asset Tag -> Asset Type / Model
    |--------------------------------------------------------------------------
    */

    $('#modal_asset_inventory_id').on('change', function () {

        let option = $(this).find('option:selected');          
        let type = option.data('type') || '';         
        let model = option.data('model') || '';
           
        /*
        |--------------------------------------------------------------------------
        | Show Asset Type
        |--------------------------------------------------------------------------
        */

        $('#modal_asset_type').val(type);
            
        /*
        |--------------------------------------------------------------------------
        | Show Asset Model
        |--------------------------------------------------------------------------
        */

        $('#modal_asset_model').val(model);
            
    });


    /*
    |--------------------------------------------------------------------------
    | Add Selected Asset
    |--------------------------------------------------------------------------
    */

    $('#addSelectedAsset').on('click', function () {
        let assetId = $('#modal_asset_inventory_id').val();
           
        /*
        |--------------------------------------------------------------------------
        | Validate Asset
        |--------------------------------------------------------------------------
        */

        if (!assetId) {
            Swal.fire({
                icon: 'warning',
                title: 'Asset Required',
                text: 'Please select an asset tag.'
            });
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Selected Option
        |--------------------------------------------------------------------------
        */

        let option = $('#modal_asset_inventory_id option:selected');           
        let tagNo = option.text().trim();          
        let assetType = option.data('type') || '-';           
        let assetModel = option.data('model') || '-';
            
        /*
        |--------------------------------------------------------------------------
        | Prevent Duplicate
        |--------------------------------------------------------------------------
        */

        if (
            $('#selectedAssetsBody tr[data-asset-id="' + assetId + '"]')
                .length
        ) {

            Swal.fire({
                icon: 'warning',
                title: 'Already Added',
                text: 'This asset has already been selected.'                   
            });

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Remove Empty Row
        |--------------------------------------------------------------------------
        */

        $('#noAssetRow').remove();
        /*
        |--------------------------------------------------------------------------
        | Row Number
        |--------------------------------------------------------------------------
        */

        let rowCount =
            $('#selectedAssetsBody tr').length + 1;


        /*
        |--------------------------------------------------------------------------
        | Create Row
        |--------------------------------------------------------------------------
        */

        let row = `
            <tr data-asset-id="${assetId}">
                <td>${rowCount}</td>
                                  
                <td>
                    <strong>${tagNo}</strong>                                     
                    <input type="hidden" name="asset_inventory_ids[]" value="${assetId}">
                </td>

                <td>${assetType}</td>
                                  
                <td> ${assetModel} </td>
                                  
                <td>
                    <button type="button" class="btn btn-sm btn-danger remove-asset" title="Remove Asset">                                                                    
                        <i class="bi bi-trash"></i>
                    </button>
                </td>

            </tr>

        `;

        /*
        |--------------------------------------------------------------------------
        | Add Row
        |--------------------------------------------------------------------------
        */

        $('#selectedAssetsBody').append(row);
            
        /*
        |--------------------------------------------------------------------------
        | Remove Selected Tag From Dropdown
        |--------------------------------------------------------------------------
        */

        option.remove();

        /*
        |--------------------------------------------------------------------------
        | Reset Modal
        |--------------------------------------------------------------------------
        */

        $('#modal_asset_inventory_id').val('').trigger('change');                     
        $('#modal_asset_type').val('');           
        $('#modal_asset_model').val('');          
        /*
        |--------------------------------------------------------------------------
        | Close Modal
        |--------------------------------------------------------------------------
        */
        $('#addAssetModal').modal('hide');           
    });

    /*
    |--------------------------------------------------------------------------
    | Initialize Asset Tag Select2
    |--------------------------------------------------------------------------
    */

    function initializeAssetSelect(element) {
        $(element).select2({
            placeholder: 'Select Asset Tag',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#addAssetModal')
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Initial Asset Select2
    |--------------------------------------------------------------------------
    */

    initializeAssetSelect($('.modal-asset-tag').first());


    /*
    |--------------------------------------------------------------------------
    | Add More Asset Row
    |--------------------------------------------------------------------------
    */

    $('#addModalAssetRow').on('click', function () {

        let newRow = `

            <div class="row asset-modal-row mb-3">

                {{-- Asset Tag --}}

                <div class="col-md-5">

                    <label class="form-label">
                        Asset Tag
                        <span class="text-danger">*</span>
                    </label>

                    <select class="form-select modal-asset-tag">

                        <option value="">
                            Select Asset Tag
                        </option>

                        @foreach($assets as $asset)

                            <option value="{{ $asset->id }}" data-type="{{ $asset->assetModel?->assetType?->name }}" data-model="{{ $asset->assetModel?->model_name }}">                                                                                   
                                {{ $asset->tag_no }}
                            </option>
                        @endforeach
                    </select>
                </div>


                {{-- Asset Type --}}

                <div class="col-md-3">

                    <label class="form-label">
                        Asset Type
                    </label>

                    <input type="text" class="form-control modal-asset-type" readonly>
                </div>


                {{-- Asset Model --}}

                <div class="col-md-3">

                    <label class="form-label">
                        Asset Model
                    </label>
                    <input type="text" class="form-control modal-asset-model" readonly>                                      

                </div>


                {{-- Remove --}}

                <div class="col-md-1 d-flex align-items-end">

                    <button type="button" class="btn btn-danger remove-modal-row" title="Remove">                       
                        <i class="bi bi-trash"></i>
                    </button>

                </div>

            </div>
        `;

        $('#assetModalRows').append(newRow);

        /*
        |--------------------------------------------------------------------------
        | Initialize Select2 For New Row
        |--------------------------------------------------------------------------
        */

        initializeAssetSelect(
            $('#assetModalRows .asset-modal-row:last .modal-asset-tag')
        );

    });

    /*
    |--------------------------------------------------------------------------
    | Asset Tag Change
    |--------------------------------------------------------------------------
    */

    $(document).on('change', '.modal-asset-tag', function () {       

        let select = $(this);
        let row = select.closest('.asset-modal-row');
        let option = select.find('option:selected');

        let assetType = option.data('type') || '';               
        let assetModel = option.data('model') || '';

        /*
        |--------------------------------------------------------------------------
        | Show Asset Type
        |--------------------------------------------------------------------------
        */

        row.find('.modal-asset-type').val(assetType);
            
        /*
        |--------------------------------------------------------------------------
        | Show Asset Model
        |--------------------------------------------------------------------------
        */
        row.find('.modal-asset-model').val(assetModel);
            
    });

    /*
    |--------------------------------------------------------------------------
    | Prevent Duplicate Asset Selection
    |--------------------------------------------------------------------------
    */

    $(document).on('change', '.modal-asset-tag',    
        function () {

            let currentSelect = $(this);
            let selectedValue = currentSelect.val();             

            if (!selectedValue) {
                return;
            }

            let duplicate = false;

            $('.modal-asset-tag').each(function () {
                if (
                    this !== currentSelect[0] &&
                    $(this).val() == selectedValue
                ) {

                    duplicate = true;
                    return false;
                }

            });


            if (duplicate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Asset Already Selected',
                    text:
                        'This asset tag has already been selected.'
                });


                currentSelect
                    .val('')
                    .trigger('change');

            }

        }
    );

    /*
    |--------------------------------------------------------------------------
    | Remove Asset From Selected List
    |--------------------------------------------------------------------------
    */

    $(document).on('click', '.remove-asset', function () {

        let row = $(this).closest('tr');              
        let assetId = row.data('asset-id');
            
        /*
        |--------------------------------------------------------------------------
        | Find Original Asset
        |--------------------------------------------------------------------------
        */

        let selectedAsset = asset.find(function (item) {            
                return item.id == assetId;
            });

        /*
        |--------------------------------------------------------------------------
        | Add Asset Back To Dropdown
        |--------------------------------------------------------------------------
        */

        if (selectedAsset) {
            /*
            |--------------------------------------------------------------------------
            | Prevent Duplicate Option
            |--------------------------------------------------------------------------
            */

            if (
                $('#modal_asset_inventory_id option[value="' + assetId + '"]')
                    .length === 0
            ) {

                let newOption =
                    new Option(
                        selectedAsset.tag_no,
                        selectedAsset.id,
                        false,
                        false

                    );

                /*
                |--------------------------------------------------------------------------
                | Add Asset Type
                |--------------------------------------------------------------------------
                */

                $(newOption).attr('data-type', selectedAsset.asset_type || '');
                /*
                |--------------------------------------------------------------------------
                | Add Asset Model
                |--------------------------------------------------------------------------
                */

                $(newOption).attr('data-model', selectedAsset.asset_model || '');
                /*
                |--------------------------------------------------------------------------
                | Append To Select2
                |--------------------------------------------------------------------------
                */

                $('#modal_asset_inventory_id').append(newOption).trigger('change');                                           

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Remove Row
        |--------------------------------------------------------------------------
        */

        row.remove();

        /*
        |--------------------------------------------------------------------------
        | Re-number Rows
        |--------------------------------------------------------------------------
        */

        $('#selectedAssetsBody tr').each(
            function (index) {
                $(this).find('td:first').text(index + 1);                                             
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Show Empty Message
        |--------------------------------------------------------------------------
        */

        if (
            $('#selectedAssetsBody tr').length === 0
        ) {

            $('#selectedAssetsBody').html(`
                <tr id="noAssetRow">
                    <td colspan="5" class="text-center text-muted">                                                   
                        No assets selected.
                    </td>
                </tr>

            `);

        }

    });

    /*
    |--------------------------------------------------------------------------
    | Remove Modal Row
    |--------------------------------------------------------------------------
    */

    $(document).on('click', '.remove-modal-row', function () {
        let rows = $('#assetModalRows .asset-modal-row');            
        /*
        |--------------------------------------------------------------------------
        | Keep At Least One Row
        |--------------------------------------------------------------------------
        */

        if (rows.length === 1) {

            let row = $(this).closest('.asset-modal-row');
            row.find('.modal-asset-tag').val('').trigger('change');                                     
            row.find('.modal-asset-type').val('');                  
            row.find('.modal-asset-model').val('');
                
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Destroy Select2
        |--------------------------------------------------------------------------
        */

        let select = $(this).closest('.asset-modal-row').find('.modal-asset-tag');                                                 
        if (select.hasClass('select2-hidden-accessible')) {
            select.select2('destroy');
        }

        /*
        |--------------------------------------------------------------------------
        | Remove Row
        |--------------------------------------------------------------------------
        */

        $(this).closest('.asset-modal-row').remove();                        

    });

    /*
    |--------------------------------------------------------------------------
    | Add Selected Assets
    |--------------------------------------------------------------------------
    */

    $('#addSelectedAssets').on('click', function () {
        let selectedAssets = [];
        let hasEmptyRow = false;

        /*
        |--------------------------------------------------------------------------
        | Read Every Modal Row
        |--------------------------------------------------------------------------
        */

        $('#assetModalRows .asset-modal-row').each(
            function () {
                let row = $(this);
                let assetId = row.find('.modal-asset-tag').val();                  
                let option = row.find('.modal-asset-tag option:selected');                   
                let tagNo = option.text().trim();                   
                let assetType = option.data('type') || '-';                
                let assetModel = option.data('model') || '-';                

                /*
                |--------------------------------------------------------------------------
                | Check Empty Tag
                |--------------------------------------------------------------------------
                */

                if (!assetId) {
                    hasEmptyRow = true;
                    return false;
                }

                /*
                |--------------------------------------------------------------------------
                | Add To Array
                |--------------------------------------------------------------------------
                */

                selectedAssets.push({
                    id: assetId,
                    tag_no: tagNo,
                    asset_type: assetType,
                    asset_model: assetModel
                });
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Empty Asset Validation
        |--------------------------------------------------------------------------
        */

        if (hasEmptyRow) {

            Swal.fire({
                icon: 'warning',
                title: 'Asset Tag Required',
                text:
                    'Please select an asset tag in every row.'
            });

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | No Asset
        |--------------------------------------------------------------------------
        */

        if (selectedAssets.length === 0) {

            Swal.fire({
                icon: 'warning',
                title: 'No Asset Selected',
                text:
                    'Please select at least one asset.'

            });

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Add Each Asset To Main Table
        |--------------------------------------------------------------------------
        */

        selectedAssets.forEach(function (selected) {


            /*
            |--------------------------------------------------------------------------
            | Check If Already In Main Table
            |--------------------------------------------------------------------------
            */

            if (
                $('#selectedAssetsBody tr[data-asset-id="' +
                selected.id +
                '"]').length
            ) {

                return;

            }


            /*
            |--------------------------------------------------------------------------
            | Remove Empty Row
            |--------------------------------------------------------------------------
            */

            $('#noAssetRow').remove();


            /*
            |--------------------------------------------------------------------------
            | Row Number
            |--------------------------------------------------------------------------
            */

            let rowNumber = $('#selectedAssetsBody tr').length + 1;              

            /*
            |--------------------------------------------------------------------------
            | Create Main Table Row
            |--------------------------------------------------------------------------
            */

            let html = `

                <tr data-asset-id="${selected.id}">

                    <td>${rowNumber}</td>                                           
                    <td>
                        <strong>
                            ${selected.tag_no}
                        </strong>

                        <input type="hidden" name="asset_inventory_ids[]" value="${selected.id}">
                    </td>
                    <td>${selected.asset_type}</td>                                  
                    <td>${selected.asset_model}</td>                                     
                    <td>
                        <button type="button" class="btn btn-sm btn-danger remove-asset" title="Remove">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>

            `;


            /*
            |--------------------------------------------------------------------------
            | Append To Main Table
            |--------------------------------------------------------------------------
            */

            $('#selectedAssetsBody')
                .append(html);

        });


        /*
        |--------------------------------------------------------------------------
        | Remove Selected Tags From Modal
        |--------------------------------------------------------------------------
        */

        selectedAssets.forEach(function (selected) {

            $('#modal_asset_inventory_id option[value="' +
                selected.id +
                '"]')
                .remove();

        });


        /*
        |--------------------------------------------------------------------------
        | Close Modal
        |--------------------------------------------------------------------------
        */

        $('#addAssetModal').modal('hide');


        /*
        |--------------------------------------------------------------------------
        | Reset Modal
        |--------------------------------------------------------------------------
        */

        resetAssetModal();

    });

    /*
    |--------------------------------------------------------------------------
    | Reset Asset Modal
    |--------------------------------------------------------------------------
    */

    function resetAssetModal() {

        /*
        |--------------------------------------------------------------------------
        | Destroy Existing Select2
        |--------------------------------------------------------------------------
        */

        $('#assetModalRows .modal-asset-tag').each(
            function () {

                if ($(this).hasClass('select2-hidden-accessible')) {

                    $(this).select2('destroy');

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | Remove All Rows
        |--------------------------------------------------------------------------
        */

        $('#assetModalRows').html(`

            <div class="row asset-modal-row mb-3">

                <div class="col-md-5">

                    <label class="form-label">
                        Asset Tag
                        <span class="text-danger">*</span>
                    </label>

                    <select class="form-select modal-asset-tag">

                        <option value="">
                            Select Asset Tag
                        </option>

                        @foreach($assets as $asset)

                            <option
                                value="{{ $asset->id }}"
                                data-type="{{ $asset->assetModel?->assetType?->name }}"
                                data-model="{{ $asset->assetModel?->model_name }}">

                                {{ $asset->tag_no }}

                            </option>

                        @endforeach

                    </select>

                </div>


                <div class="col-md-3">

                    <label class="form-label">
                        Asset Type
                    </label>

                    <input
                        type="text"
                        class="form-control modal-asset-type"
                        readonly>

                </div>


                <div class="col-md-3">

                    <label class="form-label">
                        Asset Model
                    </label>

                    <input
                        type="text"
                        class="form-control modal-asset-model"
                        readonly>

                </div>


                <div class="col-md-1 d-flex align-items-end">

                    <button
                        type="button"
                        class="btn btn-danger remove-modal-row"
                        title="Remove">

                        <i class="bi bi-trash"></i>

                    </button>

                </div>

            </div>

        `);


        /*
        |--------------------------------------------------------------------------
        | Initialize Select2 Again
        |--------------------------------------------------------------------------
        */

        initializeAssetSelect(
            $('#assetModalRows .modal-asset-tag').first()
        );

    }

    /*
    |--------------------------------------------------------------------------
    | Form Submit Validation
    |--------------------------------------------------------------------------
    */

    $('#assetIssueForm').on('submit', function (e) {
            
        /*
        |--------------------------------------------------------------------------
        | Check Asset
        |--------------------------------------------------------------------------
        */

        let assets = $('input[name="asset_inventory_ids[]"]');                
        if (assets.length === 0) {
            e.preventDefault();

            Swal.fire({
                icon: 'warning',
                title: 'No Asset Selected',
                text: 'Please add at least one asset before issuing.'                       

            });

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | Check Custodian
        |--------------------------------------------------------------------------
        */

        if (!$('#custodian_id').val()) {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Custodian Required',
                text: 'Please select a custodian.'                       
            });

            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Check User Type
        |--------------------------------------------------------------------------
        */

        if (!$('#user_type').val()) {

            e.preventDefault();


            Swal.fire({
                icon: 'warning',
                title: 'User Type Required',
                text:
                    'Please select a user type.'

            });

            return false;

        }


        /*
        |--------------------------------------------------------------------------
        | Check Operator
        |--------------------------------------------------------------------------
        */

        if (
            $('#user_type').val() === 'operator' &&
            !$('#operator_name').val().trim()
        ) {

            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Operator Required',
                text: 'Please enter the operator name.'                       
            });

            $('#operator_name').focus();
            return false;

        }


        /*
        |--------------------------------------------------------------------------
        | Check Issue Date
        |--------------------------------------------------------------------------
        */

        if (
            !$('input[name="issued_date"]').val()
        ) {
            e.preventDefault();
            Swal.fire({

                icon: 'warning',
                title: 'Issue Date Required',
                text: 'Please select the issue date.'                      
            });

            return false;

        }

    });

});

</script>
@endpush
@endsection