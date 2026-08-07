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
                            <label>PO Number</label>
                            <input type="text" class="form-control" name="po_number" id="po_number">
                            <small class="text-danger" id="po_number_error"></small>
                        </div>

                        <div class="col-md-2">
                            <label>Quantity</label>
                            <input type="number" class="form-control" name="quantity" id="quantity" min="1">
                            <small class="text-danger" id="quantity_error"></small>
                        </div>

                        <div class="col-md-3">
                            <label>Asset Type</label>
                            <select name="asset_type_id" id="asset_type_id" class="form-select">
                                <option value="">Select</option>
                                @foreach($assetTypes as $type)
                                <option value="{{ $type->id }}">
                                    {{ $type->name }}
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
                    </div>

                    <br>
                    <div class="row">
                        <div class="col-md-3">
                            <label>Location</label>
                            <select name="location_id" id="location_id" class="form-select">
                                <option value="">Select</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}">
                                        {{ ($location->name)}}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-danger" id="location_error"></small>
                        </div>

                        <div class="col-md-3">
                            <label>Installation Date</label>
                            <input type="date" name="installation_date" id="installation_date" class="form-control">
                            <small class="text-danger" id="installation_date_error"></small>
                        </div>

                        <div class="col-md-2">
                            <label>Warranty (Years)</label>
                            <input type="number" name="warranty_years" id="warranty_years" class="form-control">
                            <small class="text-danger" id="warranty_years_error"></small>
                        </div>

                        <div class="col-md-4 d-flex align-items-end">
                            <button type="button" class="btn btn-success" id="generateRows"> Generate Inventory Rows</button>
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

                url:"{{ route('asset-inventory.getModels',':id') }}"
                        .replace(':id',type),

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
        // Generate Preview Table
        // ==========================

        $('#generateRows').click(function(){

            clearErrors();

            let valid=true;

            function error(id,msg){

                $('#'+id+'_error').text(msg);

                valid=false;

            }

            if($('#po_number').val()=='')
                error('po_number','PO Number is required.');

            if($('#quantity').val()=='' || $('#quantity').val()<=0)
                error('quantity','Quantity is required.');

            if($('#asset_type_id').val()=='')
                error('asset_type','Asset Type is required.');

            if($('#asset_model_id').val()=='' ||
            $('#asset_model_id option:selected').text()=='No Model Available')
                error('asset_model','Asset Model is required.');

            if($('#location_id').val()=='')
                error('location','Location is required.');

            if($('#installation_date').val()=='')
                error('installation_date','Installation Date is required.');

            if($('#warranty_years').val()=='')
                error('warranty_years','Warranty Year is required.');

            if(!valid)
                return;

            //---------------------------------------
            // Warranty Calculation
            //---------------------------------------

            let install=new Date(
                $('#installation_date').val()
            );

            let warranty=new Date(install);

            warranty.setFullYear(

                warranty.getFullYear()
                +
                parseInt($('#warranty_years').val())
            );

            warranty.setDate(
                warranty.getDate()-1
            );

            let warrantyDate=
                warranty.toISOString().split('T')[0];

            //---------------------------------------
            // Values
            //---------------------------------------

            let qty=parseInt($('#quantity').val());

            let assetType=
                $('#asset_type_id option:selected').text();

            let assetModel=
                $('#asset_model_id option:selected').text();

            let po=
                $('#po_number').val();

            let installDate=
                $('#installation_date').val();

            let location=
                $('#location_id option:selected').text();



            //---------------------------------------
            // Preview Table
            //---------------------------------------

            let html='';

            html+=`
            <div class="card shadow mt-4">

                <div class="card-header d-flex justify-content-between">

                    <h5 class="mb-0">
                        Inventory Preview
                    </h5>

                </div>

                <div class="card-body">

                <form id="inventoryStoreForm">

                @csrf

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

                            <th>Location</th>

                        </tr>

                    </thead>

                    <tbody>
            `;


            for(let i=1;i<=qty;i++){

                html+=`

                <tr>

                    <td>${i}</td>

                    <td>

                        ${assetType}

                        <input type="hidden"
                            name="asset_type_id"
                            value="${$('#asset_type_id').val()}">

                    </td>

                    <td>

                        ${assetModel}

                        <input type="hidden"
                            name="asset_model_id"
                            value="${$('#asset_model_id').val()}">

                    </td>

                    <td>

                        ${po}

                        <input type="hidden"
                            name="po_number"
                            value="${po}">

                    </td>

                    <td>

                        ${installDate}

                        <input type="hidden" name="installation_date" value="${installDate}">
c
                    </td>

                    <td>

                        <span class="badge bg-secondary">
                            Auto Generate
                        </span>

                    </td>

                    <td>
                        <input type="text" class="form-control" name="serial_no[]" placeholder="Enter Serial No">     
                    </td>

                    <td>

                        ${warrantyDate}

                        <input type="hidden"
                            name="warranty_end"
                            value="${warrantyDate}">

                    </td>

                    <td>

                        ${location}

                        <input type="hidden"
                            name="location_id"
                            value="${ $('#location_id').val() }">

                    </td>

                </tr>

                `;

            }


            html+=`

                    </tbody>

                </table>

                <div class="text-end">

                    <button
                        type="submit"
                        class="btn btn-primary">

                        Final Submit

                    </button>

                </div>

                </form>

                </div>

            </div>
            `;


            $('#previewArea').html(html);

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