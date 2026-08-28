@extends('layouts.app')

@section('title', 'Create Asset Inventory')

@section('content')

<div class="container-fluid py-4">

    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">
                <i class="bi bi-file-earmark-text"></i>
                Add Inventories
            </h2>
            <p class="text-muted">
                Create new inventories
            </p>
        </div>

        <div class="col-md-4 text-end">
            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>
        </div>
    </div>

    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5>Create Asset Inventory</h5>
        </div>
   
        <div class="card-body">
            <form id="previewForm">
                @csrf
                <div class="row">

                    <div class="col-md-3">
                        <label>Asset Type</label> <span class="text-danger">*</span>
                        <select name="asset_type_id" id="asset_type_id" class="form-select">
                            <option value="">Select</option>
                            @foreach($assetTypes as $type)
                            <option value="{{ $type->id }}">
                                {{ ucwords($type->name) }}
                            </option>
                            @endforeach
                        </select>
                        <small class="text-danger" id="asset_type_error"></small>
                    </div>

                    <div class="col-md-4">
                        <label>Asset Model</label>
                        <select name="asset_model_id" id="asset_model_id" class="form-select">
                            <option>Select Type First</option>
                        </select>
                        <small class="text-danger" id="asset_model_error"></small>
                    </div>
                    
                    <div class="col-md-3">
                        <label>PO Number</label> <span class="text-danger">*</span>
                        <input type="text" class="form-control" name="po_number" id="po_number">
                        <small class="text-danger" id="po_number_error"></small>
                    </div>

                    <div class="col-md-2">
                        <label>Quantity</label> <span class="text-danger">*</span>
                        <input type="number" class="form-control" name="quantity" id="quantity" min="1">
                        <small class="text-danger" id="quantity_error"></small>
                    </div>

                </div>

                <br>
                <div class="row">
                   {{-- Region --}}
                    <div class="col-md-3">
                        <label>Region</label>
                        <span class="text-danger">*</span>

                        <select name="location_id" id="location_id" class="form-select">
                            <option value="">Select Region</option>

                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">
                                    {{ ucwords($location->name) }}
                                </option>
                            @endforeach
                        </select>

                        <small class="text-danger" id="location_error"></small>
                    </div>

                    {{-- Station --}}
                    <div class="col-md-3">
                        <label>Station</label>
                        <span class="text-danger">*</span>

                        <select name="station_id" id="station_id" class="form-select" disabled>                                                                                          
                            <option value="">Select Region First</option>

                        </select>

                        <small class="text-danger" id="station_error"></small>
                    </div>

                    <br>
                    <div class="col-md-3">
                        <label>Installation Date</label> <span class="text-danger">*</span>
                        <input type="date" name="installation_date" id="installation_date" class="form-control">
                        <small class="text-danger" id="installation_date_error"></small>
                    </div>

                    <div class="col-md-2">
                        <label>Warranty (Years)</label> <span class="text-danger">*</span>
                        <input type="number" name="warranty_years" id="warranty_years" class="form-control">
                        <small class="text-danger" id="warranty_years_error"></small>
                    </div>

                    <div class="col-12 mt-4">
                        <div class="d-flex justify-content-end gap-2">
                            <button type="button" class="btn btn-success" id="generateRows"> Generate Inventory Rows</button>
                            <a href="{{ route('asset-inventory.index') }}" class="btn btn-secondary">                                  
                                Back
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>


    {{-- Preview table will appear here --}}

    <div id="previewArea" class="mt-4"></div>

    </div>

</div>

@push('scripts')
<script>
    $(document).ready(function () {

        // ==========================
        // Asset Type -> Asset Model
        // ==========================

        $('#asset_type_id').change(function () {
            let type = $(this).val();
            $('#asset_model_id').html('<option>Loading...</option>');
            if(type == ''){

                $('#asset_model_id').html(
                    '<option value="">Select Type First</option>'
                );

                return;
            }

            $.ajax({
                url:"{{ route('asset-inventory.getModels',':id') }}".replace(':id',type),                       
                type:"GET",
                success:function(res){
                    let option='';
                    if(res.length==0){
                        option =
                        '<option value="">No Model Available</option>';

                    }else{

                        option =
                        '<option value="">Select Model</option>';

                        $.each(res,function(index,item){
                            option +=
                            `<option value="${item.id}"
                                    data-short="${item.short_name ?? ''}">
                                    ${item.model_name}
                            </option>`;

                        });
                    }

                    $('#asset_model_id').html(option);

                }

            });

        });

        // ==========================
        // Region -> Station
        // ==========================

        $('#location_id').change(function () {

            let locationId = $(this).val();
            let stationSelect = $('#station_id');

            stationSelect
                .empty()
                .append('<option value="">Loading stations...</option>')
                .prop('disabled', true);

            if (locationId === '') {

                stationSelect
                    .empty()
                    .append('<option value="">Select Region First</option>')
                    .prop('disabled', true);

                return;
            }

            let url = "{{ route('location.stations.byLocation', ':id') }}"
                .replace(':id', locationId);

            $.ajax({

                url: url,
                type: "GET",

                success: function (response) {

                    stationSelect.empty();

                    if (response.length > 0) {

                        stationSelect.append(
                            '<option value="">Select Station</option>'
                        );

                        $.each(response, function (index, station) {

                            let stationName = station.station_name;

                            if (station.short_name) {
                                stationName += ' (' + station.short_name + ')';
                            }

                            stationSelect.append(
                                $('<option>', {
                                    value: station.id,
                                    text: stationName
                                })
                            );

                        });

                        stationSelect.prop('disabled', false);

                    } else {

                        stationSelect
                            .append(
                                '<option value="">No Station Available</option>'
                            )
                            .prop('disabled', true);
                    }

                },

                error: function () {

                    stationSelect
                        .empty()
                        .append(
                            '<option value="">Unable to load stations</option>'
                        )
                        .prop('disabled', true);
                }

            });

        });
        // ==========================
        // Generate Preview Table
        // ==========================
       
        $(document).ready(function () {

            // ==========================
            // Asset Type -> Asset Model
            // ==========================

            $('#asset_type_id').change(function () {

                let type = $(this).val();

                $('#asset_model_id').html('<option value="">Loading...</option>');

                if (type == '') {

                    $('#asset_model_id').html(
                        '<option value="">Select Type First</option>'
                    );

                    return;
                }

                $.ajax({

                    url: "{{ route('asset-inventory.getModels', ':id') }}"
                        .replace(':id', type),

                    type: "GET",

                    success: function (res) {

                        let option = '';

                        if (res.length == 0) {

                            option =
                                '<option value="">No Model Available</option>';

                        } else {

                            option =
                                '<option value="">Select Model</option>';

                            $.each(res, function (index, item) {

                                option += `
                                    <option value="${item.id}"
                                        data-short="${item.short_name ?? ''}">
                                        ${item.model_name}
                                    </option>
                                `;

                            });

                        }

                        $('#asset_model_id').html(option);

                    },

                    error: function () {

                        $('#asset_model_id').html(
                            '<option value="">Unable to load models</option>'
                        );

                    }

                });

            });


            $('#generateRows').click(function () {

                clearErrors();

                let valid = true;

                function error(id, msg) {

                    $('#' + id + '_error').text(msg);

                    valid = false;
                }


                // ==========================
                // Validation
                // ==========================

                if ($('#po_number').val().trim() == '') {

                    error(
                        'po_number',
                        'PO Number is required.'
                    );
                }


                if (
                    $('#quantity').val() == '' ||
                    parseInt($('#quantity').val()) <= 0
                ) {

                    error(
                        'quantity',
                        'Quantity is required.'
                    );
                }


                if ($('#asset_type_id').val() == '') {

                    error(
                        'asset_type',
                        'Asset Type is required.'
                    );
                }


                // if (
                //     $('#asset_model_id').val() == '' ||
                //     $('#asset_model_id option:selected')
                //         .text()
                //         .trim() == 'No Model Available'
                // ) {

                //     error(
                //         'asset_model',
                //         'Asset Model is required.'
                //     );
                // }


                if ($('#location_id').val() == '') {

                    error(
                        'location',
                        'Location is required.'
                    );
                }
                if ($('#station_id').val() == '') {
                    error(
                        'station',
                        'Station is required.'
                    );
                }


                if ($('#installation_date').val() == '') {

                    error(
                        'installation_date',
                        'Installation Date is required.'
                    );
                }


                if (
                    $('#warranty_years').val() == '' ||
                    parseInt($('#warranty_years').val()) < 0
                ) {

                    error(
                        'warranty_years',
                        'Warranty Year is required.'
                    );
                }

                if (!valid) {
                    return;
                }

                // ==========================
                // Get Values
                // ==========================

                let qty = parseInt(
                    $('#quantity').val()
                );

                let locationId = $('#location_id').val();  
                let stationId = $('#station_id').val();               
                let assetTypeId = $('#asset_type_id').val();                    

                // ==========================
                // Generate Asset Tags
                // ==========================

                $.ajax({

                    url: "{{ route('asset-inventory.generateTags', [
                        'location' => ':location',
                        'station' => ':station',
                        'assetType' => ':assetType',
                        'quantity' => ':quantity'
                    ]) }}"
                    .replace(':location', locationId)
                    .replace(':station', stationId)
                    .replace(':assetType', assetTypeId)
                    .replace(':quantity', qty),

                    type: "GET",

                    beforeSend: function () {

                        $('#generateRows')
                            .prop('disabled', true)
                            .html(
                                '<span class="spinner-border spinner-border-sm"></span> Generating...'
                            );
                    },

                    success: function (res) {

                        if (!res.success) {

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: res.message ||
                                    'Unable to generate asset tags.'
                            });

                            return;
                        }

                        // Generate preview table
                        generatePreviewTable(res.tags);
                    },

                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Unable to generate asset tags.'
                        });

                        console.log(xhr.responseText);
                    },

                    complete: function () {

                        $('#generateRows')
                            .prop('disabled', false)
                            .html(
                                'Generate Inventory Rows'
                            );
                    }

                });

            });


            // ==========================
            // Generate Preview Table
            // ==========================

            function generatePreviewTable(tags) {

                let qty = parseInt(
                    $('#quantity').val()
                );

                let assetTypeId = $('#asset_type_id').val();                   
                let assetModelId = $('#asset_model_id').val();                    
                let locationId = $('#location_id').val();     
                let stationId = $('#station_id').val();               
                let assetType =
                    $('#asset_type_id option:selected').text().trim();
                                                
                let assetModel =
                    $('#asset_model_id option:selected').text().trim();
                                               
                let location =
                    $('#location_id option:selected').text().trim();

                let station =
                    $('#station_id option:selected').text().trim();
                                               
                let po = $('#po_number').val();
                    
                let installationDateDb = $('#installation_date').val();
                    
                let warrantyYears = parseInt($('#warranty_years').val());
                    
                // ==========================
                // Installation Date Display
                // YYYY-MM-DD -> DD-MM-YYYY
                // ==========================

                let installParts = installationDateDb.split('-');
                    
                let installationDate =  `${installParts[2]}-${installParts[1]}-${installParts[0]}`;
                   
                // ==========================
                // Warranty End Date
                // ==========================

                let install =
                    new Date(
                        installationDateDb + 'T00:00:00'
                    );

                let warranty = new Date(install);
                    
                warranty.setFullYear(
                    warranty.getFullYear() + warrantyYears
                );

                warranty.setDate(
                    warranty.getDate() - 1
                );

                // Display format: DD-MM-YYYY

                let warrantyDay = String(warranty.getDate()).padStart(2, '0');                                           
                let warrantyMonth = String(warranty.getMonth() + 1).padStart(2, '0');                                           
                let warrantyYear = warranty.getFullYear();                   
                let warrantyDate = `${warrantyDay}-${warrantyMonth}-${warrantyYear}`;
                let warrantyEndDb = `${warrantyYear}-${warrantyMonth}-${warrantyDay}`;
                    
                // ==========================
                // Preview Table
                // ==========================

                let html = `

                <div class="card shadow mt-4">

                    <div class="card-header">

                        <h5 class="mb-0">
                            Inventory Preview
                        </h5>

                    </div>

                    <div class="card-body">

                        <form id="inventoryStoreForm">

                            @csrf
                            <input type="hidden" name="asset_type_id" value="${assetTypeId}">                                                              
                            <input type="hidden" name="asset_model_id" value="${assetModelId}">                                                               
                            <input type="hidden" name="po_number" value="${po}">                                                             
                            <input type="hidden" name="location_id" value="${locationId}">                              
                            <input type="hidden" name="station_id" value="${stationId}">                              
                            <input type="hidden" name="installation_date" value="${installationDateDb}">
                            <input type="hidden" name="warranty_year" value="${warrantyYears}">                                                             
                            <input type="hidden" name="warranty_end" value="${warrantyEndDb}">                                                            
                            <table class="table table-bordered table-striped">

                                <thead class="table-dark">
                                    <tr>
                                        <th>SL</th>
                                        <th>Asset Type</th>
                                        <th>Asset Model</th>
                                        <th>PO Number</th>
                                        <th>Installation Date</th>
                                        <th>Tag No</th>
                                        <th width="180">
                                            Serial No
                                        </th>

                                        <th>Warranty End</th>
                                        <th>Region</th>
                                        <th>Station</th>

                                    </tr>

                                </thead>

                                <tbody>
                `;


                // ==========================
                // Create Rows
                // ==========================

                for (let i = 0; i < qty; i++) {

                    html += `
                        <tr>
                            <td>${i + 1}</td>                                                            
                            <td>${assetType}</td>                                                           
                            <td>${assetModel}</td>                                                           
                            <td>${po}</td>                                                            
                            <td>${installationDate}</td>                                                            
                            <td>
                                <span class="badge bg-success">
                                    ${tags[i]}
                                </span>

                                <input type="hidden" name="tag_no[]" value="${tags[i]}">
                            </td>
                            <td>
                                <input type="text" class="form-control serial-no" name="serial_no[]" placeholder="Enter Serial No" required>
                            </td>
                            <td>${warrantyDate}</td>                                                           
                            <td>${location}</td>   
                            <td>${station}</td>                                                         
                        </tr>

                    `;

                }

                html += `

                                </tbody>

                            </table>

                            <div class="text-end">

                                <button
                                    type="submit"
                                    class="btn btn-primary">

                                    <i class="bi bi-check-circle"></i>
                                    Final Submit

                                </button>

                            </div>

                        </form>

                    </div>

                </div>

                `;


                $('#previewArea').html(html);

                $(document).on('submit', '#inventoryStoreForm', function (e) {

                    let isValid = true;
                    let firstEmptyInput = null;

                    $('.serial-no').each(function () {
                        let serialNo = $(this).val().trim();
                        if (serialNo === '') {
                            isValid = false;
                            $(this).addClass('is-invalid');
                            if (!firstEmptyInput) {
                                firstEmptyInput = this;
                            }

                        } else {

                            $(this).removeClass('is-invalid');

                        }

                    });

                    if (!isValid) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Serial Number Required',
                            text: 'Please fill in the serial number for all assets before submitting.',
                            confirmButtonText: 'OK'
                        });

                        firstEmptyInput.focus();

                        return false;
                    }

                });

            }

            // ========================================
            // Excel Column Paste → Serial No. Inputs
            // ========================================

            $(document).on('paste', 'input.serial-no', function(e) {
                e.preventDefault();
                let pastedText = (e.originalEvent || e).clipboardData.getData('text');

                // Split Excel copied column into separate values
                let serialNumbers = pastedText.split(/\r?\n/).map(value => value.trim()).filter(value => value !== '');

                // Get all serial number input boxes
                let inputs = $('input.serial-no');

                //Find the input where user pasted
                let startIndex =  inputs.index(this);

                // Put each serial number into the following inputs
                serialNumbers.forEach(function(serial, index){
                    let targetIndex = startIndex + index;
                    if(targetIndex < inputs.length) {
                        $(inputs[targetIndex]).val(serial);
                        // Remove invalid style if previously added
                        $(inputs[targetIndex]).removeClass('is-invalid');
                    }
                });
            });

            // ==========================
            // Final Submit
            // ==========================

            $(document).on('submit','#inventoryStoreForm',function (e) {
                    e.preventDefault();
                    let form = this;
                    let valid = true;


                    // Check Serial Numbers
                    // $(form).find('.serial-no')
                        
                    //     .each(function () {

                    //         if ($(this).val().trim() == '') {

                    //             $(this).addClass('is-invalid');

                    //             valid = false;

                    //         } else {

                    //             $(this).removeClass('is-invalid');

                    //         }

                    //     });

                    $(form).find('.serial-no').each(function () {                       
                        let value = $(this).val().trim();
                        if (value === '') {
                            $(this).addClass('is-invalid');

                            valid = false;

                        } else {

                            $(this).removeClass('is-invalid');
                        }

                    });

                    // ==========================
                    // Stop if invalid
                    // ==========================

                    if (!valid) {

                        Swal.fire({
                            icon: 'warning',
                            title: 'Required',
                            text: 'Please enter Serial Number for all inventories.'
                        });

                        return;
                    }

                    $.ajax({

                        url: "{{ route('asset-inventory.store') }}",

                        type: "POST",

                        data: $(form).serialize(),

                        beforeSend: function () {

                            $(form)
                                .find('button[type="submit"]')
                                .prop('disabled', true)
                                .html(
                                    '<span class="spinner-border spinner-border-sm"></span> Saving...'
                                );
                        },

                        success: function (res) {

                            if (res.success) {

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: res.message 
                                }).then(function () {

                                    window.location.href =
                                        "{{ route('asset-inventory.index') }}";

                                });

                            }

                        },

                        error: function (xhr) {
                            console.log(xhr.responseText);
                            let message =
                                'Unable to save asset inventory.';
                            if (
                                xhr.responseJSON &&
                                xhr.responseJSON.message
                            ) {
                                message = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: message
                            });
                        },

                        complete: function () {

                            $(form)
                                .find('button[type="submit"]')
                                .prop('disabled', false)
                                .html(
                                    '<i class="bi bi-check-circle"></i> Final Submit'
                                );

                        }

                    });

                }
            );


            // ==========================
            // Clear Errors
            // ==========================

            function clearErrors() {

                $('.text-danger').text('');

                $('.form-control, .form-select')
                    .removeClass('is-invalid');

            }

        });

        //=====================
        // Clear Errors
        //=====================

        function clearErrors(){
            $('.text-danger').text('');
        }
    });
</script>
@endpush

@endsection