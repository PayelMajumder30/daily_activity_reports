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

            <form method="POST" action="{{ route('asset-issue-register.store') }}" id="assetIssueForm">
                               
                @csrf

                {{-- ==========================================================
                    CUSTODIAN / USER DETAILS
                =========================================================== --}}

                <div class="row">

                    {{-- Custodian --}}
                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Custodian
                            <span class="text-danger">*</span>
                        </label>

                        <select name="custodian_id" id="custodian_id" class="form-select @error('custodian_id') is-invalid @enderror">
                            <option value="">
                                Select Custodian
                            </option>

                            @foreach($custodians as $custodian)

                                <option
                                    value="{{ $custodian->id }}"
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


                    {{-- Employee ID --}}
                    <div class="col-md-3 mb-3">
                        <label class="form-label">
                            Employee ID
                        </label>

                        <input type="text" id="custodian_emp_id" class="form-control" readonly>
                    </div>


                    {{-- Designation --}}
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Designation
                        </label>

                        <input type="text" id="custodian_designation" class="form-control" readonly>
                    </div>


                    {{-- Department --}}
                    <div class="col-md-3 mb-3">
                        <label class="form-label">
                            Department
                        </label>
                        <input type="text" id="custodian_department" class="form-control" readonly>
                    </div>


                    {{-- Section --}}
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Section
                        </label>
                        <input type="text" id="custodian_section" class="form-control" readonly>     

                    </div>

                    {{-- User Type --}}
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            User Type
                            <span class="text-danger">*</span>
                        </label>

                        <select name="user_type" id="user_type" class="form-select @error('user_type') is-invalid @enderror">
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


                    {{-- Operator --}}
                    <div class="col-md-3 mb-3" id="operatorField" style="display:none;">

                        <label class="form-label">
                            Operator Name
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text" name="operator_name" id="operator_name" value="{{ old('operator_name') }}" class="form-control" placeholder="Enter operator name">
                    </div>


                    {{-- Issue Date --}}
                    <div class="col-md-3 mb-3">

                        <label class="form-label">
                            Issue Date
                            <span class="text-danger">*</span>
                        </label>

                        <input type="date" name="issued_date" value="{{ old('issued_date', now()->format('Y-m-d')) }}"   
                            class="form-control @error('issued_date') is-invalid @enderror">

                        @error('issued_date')
                            <small class="text-danger">
                                {{ $message }}
                            </small>
                        @enderror

                    </div>

                </div>


                {{-- ==========================================================
                    SELECTED ASSETS
                =========================================================== --}}

                <div class="card border mt-3">

                    <div class="card-header d-flex justify-content-between align-items-center">

                        <h6 class="mb-0">
                            Selected Assets
                        </h6>

                        <button type="button" class="btn btn-primary btn-sm" id="addAssetBtn">
                            <i class="bi bi-plus-circle"></i>
                            Add Asset
                        </button>

                    </div>


                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered" id="selectedAssetsTable">
                                                               
                                <thead class="table-dark">
                                    <tr>
                                        <th width="60"> SL</th>                                                                                 
                                        <th>Tag No.</th>                                                                                 
                                        <th> Asset Type </th>                                                                                  
                                        <th> Asset Model </th>                                                                                    
                                        <th width="80"> Action</th>                                                                                   
                                    </tr>
                                </thead>


                                <tbody id="selectedAssetsBody">
                                    <tr id="noAssetRow">
                                        <td colspan="5" class="text-center text-muted">                                                                                       
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

                </div>


                {{-- ==========================================================
                    FORM BUTTONS
                =========================================================== --}}

                <div class="mt-4 d-flex justify-content-end">

                    <button type="submit" class="btn btn-primary" id="issueAssetBtn">
                        <i class="bi bi-check-circle"></i>
                        Issue Asset
                    </button>


                    <a href="{{ route('asset-issue-register.index') }}" class="btn btn-secondary ms-2">
                        Back
                    </a>

                </div>

            </form>


            {{-- ==============================================================
                HIDDEN ASSET OPTION SOURCE

                This is used by JavaScript to create every modal row.
            ============================================================== --}}

            <select id="assetOptionSource" style="display:none;">

                <option value="">
                    Select Asset Tag
                </option>

                @foreach($assets as $asset)
 
                    <option value="{{ $asset->id }}" data-type="{{ $asset->assetModel?->assetType?->name ?? '' }}"                                             
                        data-model="{{ $asset->assetModel?->model_name ?? '' }}">

                        {{ $asset->tag_no }}

                    </option>

                @endforeach

            </select>


            {{-- ==============================================================
                ADD ASSETS MODAL
            ============================================================== --}}

            <div class="modal fade" id="addAssetModal" tabindex="-1" aria-hidden="true">
                                                         
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">

                        {{-- Modal Header --}}
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-plus-circle"></i>
                                Add Assets
                            </h5>

                            <button type="button" class="btn-close"                                                             
                                data-bs-dismiss="modal">
                            </button>
                        </div>

                        {{-- Modal Body --}}
                        <div class="modal-body">
                            <div id="assetRows">
                                {{-- JavaScript will create first row here --}}
                            </div>
                        </div>


                        {{-- Modal Footer --}}
                        <div class="modal-footer">
                            <button type="button" class="btn btn-success" id="addMoreAssetRow">
                                <i class="bi bi-plus-circle"></i>
                                Add More
                            </button>

                            <button type="button" class="btn btn-secondary" id="doneAddingAssets">                                                                                             
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

    /* ==============================================================
       ASSET DATA
    ============================================================== */

    let assetData = @json($assetData);

    /* ==============================================================
       CUSTODIAN SELECT2
    ============================================================== */

    $('#custodian_id').select2({
        placeholder: 'Select Custodian',
        allowClear: true,
        width: '100%'
    });


    /* ==============================================================
       USER TYPE -> OPERATOR FIELD
    ============================================================== */

    function toggleOperatorField() {

        let userType = $('#user_type').val();
        if (userType === 'operator') {
            $('#operatorField').show();
            $('#operator_name').prop('required', true);
                
        } else {
            $('#operatorField').hide();
            $('#operator_name').prop('required', false).val('');                              
        }
    }

    $('#user_type').on(
        'change',
        toggleOperatorField
    );

    toggleOperatorField();

    /* ==============================================================
       CUSTODIAN DETAILS
    ============================================================== */

    $('#custodian_id').on('change', function () {
        let custodianId = $(this).val();
        /*
        |--------------------------------------------------------------
        | Clear fields
        |--------------------------------------------------------------
        */

        if (!custodianId) {
            $('#custodian_emp_id').val('');
            $('#custodian_designation').val('');
            $('#custodian_department').val('');
            $('#custodian_section').val('');
            return;
        }

        /*
        |--------------------------------------------------------------
        | URL
        |--------------------------------------------------------------
        */

        let url = "{{ route('asset-issue-register.custodian-details', ':id') }}".replace(':id', custodianId);
                      
        /*
        |--------------------------------------------------------------
        | AJAX
        |--------------------------------------------------------------
        */

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

                $('#custodian_emp_id').val(
                    response.custodian.emp_id || '-'
                );

                $('#custodian_designation').val(
                    response.custodian.designation ? response.custodian.designation.toLowerCase().replace(/\b\w/g, char => char.toUpperCase()) : '-'
                );

                $('#custodian_department').val(
                    response.custodian.department || '-'
                );

                $('#custodian_section').val(
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


    /* ==============================================================
       GET ASSET TYPE / MODEL
    ============================================================== */

    function getAssetDetails(selectElement) {
        let select = $(selectElement);
        let option = select.find('option:selected');           
        let assetType = option.attr('data-type') || '';           
        let assetModel = option.attr('data-model') || '';            
        let row = select.closest('.asset-row');           
        row.find('.modal-asset-type').val(assetType);           
        row.find('.modal-asset-model').val(assetModel);           
    }


    /* ==============================================================
       INITIALIZE SELECT2
    ============================================================== */

    function initializeAssetSelect(selectElement) {

        let select = $(selectElement);

        /*
        |--------------------------------------------------------------
        | Destroy old Select2 if already initialized
        |--------------------------------------------------------------
        */

        if (select.hasClass('select2-hidden-accessible'))
          {
            select.select2('destroy');
            }


        /*
        |--------------------------------------------------------------
        | Initialize
        |--------------------------------------------------------------
        */

        select.select2({
            placeholder: 'Select Asset Tag',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#addAssetModal')

        });

    }


    /* ==============================================================
       GET ASSETS ALREADY IN MAIN TABLE
    ============================================================== */

    function getMainSelectedAssetIds() {

        let ids = [];

        $('#selectedAssetsBody tr[data-asset-id]')
            .each(function () {

                ids.push(
                    String(
                        $(this).attr('data-asset-id')
                    )
                );

            });


        return ids;

    }


    /* ==============================================================
       GET ASSETS SELECTED IN MODAL
    ============================================================== */

    function getModalSelectedAssetIds(
        excludeSelect = null
    ) {

        let ids = [];


        $('#assetRows .modal-asset-tag')
            .each(function () {

                if (
                    excludeSelect &&
                    this === excludeSelect[0]
                ) {
                    return;
                }


                let value = $(this).val();

                if (value) {
                    ids.push(
                        String(value)
                    );
                }

            });

        return ids;

    }


    /* ==============================================================
       CREATE ASSET ROW
    ============================================================== */

    function createAssetRow() {

        let row = $(`
            <div class="row asset-row mb-3">
                <div class="col-md-5">
                    <label class="form-label">
                        Asset Tag
                        <span class="text-danger">
                            *
                        </span>

                    </label>

                    <select
                        class="form-select modal-asset-tag">

                        <option value="">
                            Select Asset Tag
                        </option>

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


                <div
                    class="col-md-1 d-flex align-items-end">

                    <button
                        type="button"
                        class="btn btn-danger remove-asset-row"
                        title="Remove">

                        <i class="bi bi-trash"></i>

                    </button>

                </div>

            </div>
        `);


        /*
        |--------------------------------------------------------------
        | Add all options from hidden source
        |--------------------------------------------------------------
        */

        let sourceOptions =
            $('#assetOptionSource')
                .html();


        row.find('.modal-asset-tag')
            .append(sourceOptions);


        /*
        |--------------------------------------------------------------
        | Add row to modal
        |--------------------------------------------------------------
        */

        $('#assetRows')
            .append(row);


        /*
        |--------------------------------------------------------------
        | Initialize Select2
        |--------------------------------------------------------------
        */

        initializeAssetSelect(
            row.find('.modal-asset-tag')
        );


        /*
        |--------------------------------------------------------------
        | Update available assets
        |--------------------------------------------------------------
        */

        refreshModalAssetOptions();

        return row;

    }


    /* ==============================================================
       REFRESH ASSET OPTIONS
    ============================================================== */

    function refreshModalAssetOptions() {

        let mainSelectedIds = getMainSelectedAssetIds();
            
        $('#assetRows .modal-asset-tag')
            .each(function () {

                let currentSelect = $(this);                   
                let currentValue = currentSelect.val();                   
                let modalSelectedIds =
                    getModalSelectedAssetIds(
                        currentSelect
                    );


                /*
                |------------------------------------------------------
                | Rebuild options
                |------------------------------------------------------
                */

                currentSelect.empty();


                currentSelect.append(`
                    <option value="">
                        Select Asset Tag
                    </option>
                `);


                /*
                |------------------------------------------------------
                | Add available assets
                |------------------------------------------------------
                */

                $('#assetOptionSource option')
                    .each(function () {

                        let option = $(this);                          

                        let assetId = option.val();                          

                        /*
                        |--------------------------------------------------
                        | Skip empty option
                        |--------------------------------------------------
                        */

                        if (!assetId) {
                            return;
                        }


                        /*
                        |--------------------------------------------------
                        | Do not show asset already in main table
                        |--------------------------------------------------
                        */

                        if (
                            mainSelectedIds.includes(
                                String(assetId)
                            )
                        ) {

                            return;

                        }


                        /*
                        |--------------------------------------------------
                        | Do not show asset selected in another modal row
                        |--------------------------------------------------
                        */

                        if (
                            modalSelectedIds.includes(
                                String(assetId)
                            )
                        ) {

                            return;

                        }


                        /*
                        |--------------------------------------------------
                        | Clone option
                        |--------------------------------------------------
                        */

                        currentSelect.append(
                            option.clone()
                        );

                    });


                /*
                |------------------------------------------------------
                | Restore current selection
                |------------------------------------------------------
                */

                if (
                    currentValue &&
                    currentSelect.find(
                        'option[value="' +
                        currentValue +
                        '"]'
                    ).length
                ) {

                    currentSelect
                        .val(currentValue);

                } else {

                    currentSelect
                        .val('');

                }


                /*
                |------------------------------------------------------
                | Refresh Select2
                |------------------------------------------------------
                */

                currentSelect.trigger('change.select2');


                /*
                |------------------------------------------------------
                | Update type/model
                |------------------------------------------------------
                */

                getAssetDetails(
                    currentSelect
                );

            });

    }


    /* ==============================================================
       RESET MODAL
    ============================================================== */

    function resetAssetModal() {

        /*
        |--------------------------------------------------------------
        | Destroy Select2
        |--------------------------------------------------------------
        */

        $('#assetRows .modal-asset-tag')
            .each(function () {

                if (
                    $(this)
                        .hasClass(
                            'select2-hidden-accessible'
                        )
                ) {

                    $(this).select2('destroy');

                }

            });


        /*
        |--------------------------------------------------------------
        | Clear rows
        |--------------------------------------------------------------
        */

        $('#assetRows').empty();


        /*
        |--------------------------------------------------------------
        | Create first row
        |--------------------------------------------------------------
        */

        createAssetRow();


        /*
        |--------------------------------------------------------------
        | Refresh options
        |--------------------------------------------------------------
        */

        refreshModalAssetOptions();

    }


    /* ==============================================================
       OPEN ADD ASSET MODAL
    ============================================================== */

    $('#addAssetBtn').on('click', function () {

        /*
        |--------------------------------------------------------------
        | Custodian required
        |--------------------------------------------------------------
        */

        if (!$('#custodian_id').val()) {

            Swal.fire({

                icon: 'warning',

                title: 'Select Custodian',

                text:
                    'Please select a custodian before adding assets.'

            });

            return;
        }


        /*
        |--------------------------------------------------------------
        | Reset modal
        |--------------------------------------------------------------
        */

        resetAssetModal();


        /*
        |--------------------------------------------------------------
        | Show modal
        |--------------------------------------------------------------
        */

        $('#addAssetModal').modal('show');

    });


    /* ==============================================================
       ASSET TAG CHANGE
    ============================================================== */

    $(document).on(
        'change',
        '.modal-asset-tag',
        function () {

            let currentSelect =
                $(this);


            /*
            |----------------------------------------------------------
            | Get selected value
            |----------------------------------------------------------
            */

            let selectedValue =
                currentSelect.val();


            /*
            |----------------------------------------------------------
            | Show type/model
            |----------------------------------------------------------
            */

            getAssetDetails(
                currentSelect
            );


            /*
            |----------------------------------------------------------
            | Empty selection
            |----------------------------------------------------------
            */

            if (!selectedValue) {

                refreshModalAssetOptions();

                return;

            }


            /*
            |----------------------------------------------------------
            | Check duplicate in modal
            |----------------------------------------------------------
            */

            let duplicate = false;


            $('.modal-asset-tag')
                .each(function () {

                    if (
                        this !== currentSelect[0] &&
                        String($(this).val()) ===
                        String(selectedValue)
                    ) {

                        duplicate = true;

                        return false;

                    }

                });


            /*
            |----------------------------------------------------------
            | Duplicate found
            |----------------------------------------------------------
            */

            if (duplicate) {

                Swal.fire({

                    icon: 'warning',

                    title: 'Asset Already Selected',

                    text:
                        'This asset tag has already been selected in another row.'

                });


                currentSelect
                    .val('')
                    .trigger('change');


                return;

            }


            /*
            |----------------------------------------------------------
            | Refresh all dropdowns
            |----------------------------------------------------------
            */

            refreshModalAssetOptions();

    });


    /* ==============================================================
       ADD MORE ASSET ROW
    ============================================================== */

    $('#addMoreAssetRow').on(
        'click',
        function () {

            let rows =
                $('#assetRows .asset-row');


            /*
            |----------------------------------------------------------
            | Check last row
            |----------------------------------------------------------
            */

            if (rows.length > 0) {

                let lastRow =
                    rows.last();


                let lastValue =
                    lastRow
                        .find('.modal-asset-tag')
                        .val();


                /*
                |------------------------------------------------------
                | Last row must have asset
                |------------------------------------------------------
                */

                if (!lastValue) {

                    Swal.fire({

                        icon: 'warning',

                        title: 'Asset Tag Required',

                        text:
                            'Please select an asset tag before adding another row.'

                    });

                    return;

                }

            }


            /*
            |----------------------------------------------------------
            | Create new row
            |----------------------------------------------------------
            */

            createAssetRow();


            /*
            |----------------------------------------------------------
            | Refresh options
            |----------------------------------------------------------
            */

            refreshModalAssetOptions();

        }
    );


    /* ==============================================================
       REMOVE MODAL ROW
    ============================================================== */

    $(document).on(
        'click',
        '.remove-asset-row',
        function () {

            let rows =
                $('#assetRows .asset-row');


            /*
            |----------------------------------------------------------
            | Keep minimum one row
            |----------------------------------------------------------
            */

            if (rows.length === 1) {

                let row = $(this).closest('.asset-row');                   

                row.find('.modal-asset-tag').val('').trigger('change');
                                       
                row.find('.modal-asset-type').val('');
                    
                row.find('.modal-asset-model')
                    .val('');


                return;

            }


            /*
            |----------------------------------------------------------
            | Destroy Select2
            |----------------------------------------------------------
            */

            let select =
                $(this).closest('.asset-row').find('.modal-asset-tag');
            if (
                select.hasClass(
                    'select2-hidden-accessible'
                )
            ) {
                select.select2('destroy');
            }

            /*
            |----------------------------------------------------------
            | Remove row
            |----------------------------------------------------------
            */

            $(this).closest('.asset-row').remove();
                
            /*
            |----------------------------------------------------------
            | Refresh available assets
            |----------------------------------------------------------
            */

            refreshModalAssetOptions();

        }
    );


    /* ==============================================================
       DONE ADDING ASSETS
    ============================================================== */

    $('#doneAddingAssets').on(
        'click',
        function () {

            let selectedAssets = [];

            let hasEmptyRow = false;

            let duplicateFound = false;


            /*
            |----------------------------------------------------------
            | Read modal rows
            |----------------------------------------------------------
            */

            $('#assetRows .asset-row')
                .each(function () {

                    let row =
                        $(this);


                    let select =
                        row.find(
                            '.modal-asset-tag'
                        );


                    let assetId =
                        select.val();


                    /*
                    |--------------------------------------------------
                    | Empty row
                    |--------------------------------------------------
                    */

                    if (!assetId) {

                        hasEmptyRow = true;

                        return false;

                    }


                    /*
                    |--------------------------------------------------
                    | Check duplicate
                    |--------------------------------------------------
                    */

                    let alreadyExists =
                        selectedAssets.some(
                            function (asset) {

                                return String(asset.id) ===
                                    String(assetId);

                            }
                        );


                    if (alreadyExists) {

                        duplicateFound = true;

                        return false;

                    }


                    /*
                    |--------------------------------------------------
                    | Selected option
                    |--------------------------------------------------
                    */

                    let option =
                        select.find(
                            'option:selected'
                        );


                    let tagNo =
                        option.text().trim();


                    let assetType =
                        option.attr(
                            'data-type'
                        ) || '-';


                    let assetModel =
                        option.attr(
                            'data-model'
                        ) || '-';


                    /*
                    |--------------------------------------------------
                    | Add to array
                    |--------------------------------------------------
                    */

                    selectedAssets.push({

                        id: assetId,

                        tag_no: tagNo,

                        asset_type: assetType,

                        asset_model: assetModel

                    });

                });


            /*
            |----------------------------------------------------------
            | Empty row validation
            |----------------------------------------------------------
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
            |----------------------------------------------------------
            | Duplicate validation
            |----------------------------------------------------------
            */

            if (duplicateFound) {

                Swal.fire({
                    icon: 'warning',
                    title: 'Duplicate Asset',
                    text:
                        'The same asset cannot be added more than once.'
                });

                return;

            }


            /*
            |----------------------------------------------------------
            | No asset validation
            |----------------------------------------------------------
            */

            if (
                selectedAssets.length === 0
            ) {

                Swal.fire({

                    icon: 'warning',
                    title: 'No Asset Selected',
                    text: 'Please select at least one asset.'                       

                });

                return;

            }


            /*
            |----------------------------------------------------------
            | Add assets to main table
            |----------------------------------------------------------
            */

            selectedAssets.forEach(
                function (selected) {

                    /*
                    |--------------------------------------------------
                    | Prevent duplicate in main table
                    |--------------------------------------------------
                    */

                    if (
                        $('#selectedAssetsBody tr[data-asset-id="' +
                        selected.id +
                        '"]').length
                    ) {

                        return;

                    }


                    /*
                    |--------------------------------------------------
                    | Remove empty row
                    |--------------------------------------------------
                    */

                    $('#noAssetRow').remove();


                    /*
                    |--------------------------------------------------
                    | Row number
                    |--------------------------------------------------
                    */

                    let rowNumber =
                        $('#selectedAssetsBody tr[data-asset-id]').length + 1;                           

                    /*
                    |--------------------------------------------------
                    | Create table row
                    |--------------------------------------------------
                    */

                    let html = `

                        <tr data-asset-id="${selected.id}">

                            <td>
                                ${rowNumber}
                            </td>

                            <td>
                                <strong>
                                    ${selected.tag_no}
                                </strong>

                                <input type="hidden" name="asset_inventory_ids[]" value="${selected.id}">                                                                                                       
                            </td>

                            <td>
                                ${selected.asset_type}
                            </td>

                            <td>
                                ${selected.asset_model}
                            </td>


                            <td>

                                <button type="button" class="btn btn-sm btn-danger remove-asset" title="Remove Asset">                                                                                                       
                                    <i class="bi bi-trash"></i>
                                </button>

                            </td>

                        </tr>

                    `;


                    /*
                    |--------------------------------------------------
                    | Append row
                    |--------------------------------------------------
                    */

                    $('#selectedAssetsBody').append(html);                      

                }
            );


            /*
            |----------------------------------------------------------
            | Close modal
            |----------------------------------------------------------
            */

            $('#addAssetModal').modal('hide');              

            /*
            |----------------------------------------------------------
            | Reset modal
            |----------------------------------------------------------
            */

            resetAssetModal();

        }
    );


    /* ==============================================================
       REMOVE ASSET FROM MAIN TABLE
    ============================================================== */

    $(document).on(
        'click',
        '.remove-asset',
        function () {

            let row = $(this).closest('tr');            

            /*
            |----------------------------------------------------------
            | Remove row
            |----------------------------------------------------------
            */

            row.remove();


            /*
            |----------------------------------------------------------
            | Re-number rows
            |----------------------------------------------------------
            */

            $('#selectedAssetsBody tr[data-asset-id]')
                .each(function (index) {

                    $(this)
                        .find('td:first')
                        .text(index + 1);

                });


            /*
            |----------------------------------------------------------
            | Show empty message
            |----------------------------------------------------------
            */

            if (
                $('#selectedAssetsBody tr[data-asset-id]')
                    .length === 0
            ) {

                $('#selectedAssetsBody')
                    .html(`

                        <tr id="noAssetRow">
 
                            <td colspan="5"  class="text-center text-muted">                                           
                                No assets selected.
                            </td>

                        </tr>

                    `);

            }

        }
    );


    /* ==============================================================
       FORM SUBMIT VALIDATION
    ============================================================== */

    $('#assetIssueForm').on('submit', function (e) {        

            /*
            |----------------------------------------------------------
            | Check asset
            |----------------------------------------------------------
            */

            let assets =
                $('input[name="asset_inventory_ids[]"]');

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
            |----------------------------------------------------------
            | Check custodian
            |----------------------------------------------------------
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
            |----------------------------------------------------------
            | Check user type
            |----------------------------------------------------------
            */

            if (!$('#user_type').val()) {

                e.preventDefault();

                Swal.fire({
                    icon: 'warning',
                    title: 'User Type Required',
                    text: 'Please select a user type.'                       
                });
                return false;
            }


            /*
            |----------------------------------------------------------
            | Check operator
            |----------------------------------------------------------
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
            |----------------------------------------------------------
            | Check issue date
            |----------------------------------------------------------
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

        }
    );

    /* ==============================================================
       INITIAL MODAL SETUP
    ============================================================== */

    resetAssetModal();

});

</script>

@endpush
@endsection