@extends('layouts.app')

@section('title', 'Incident Issue Register')

@section('content')

<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="row mb-4">

        <div class="col-md-8">

            <h2 class="fw-bold">
                <i class="bi bi-person-workspace"></i>
                Add Custodian
            </h2>

            <p class="text-muted">
                Create a new custodian
            </p>

        </div>

        <div class="col-md-4 text-end">
            <h6 class="text-secondary">
                {{ now()->format('d M Y') }}
            </h6>
        </div>

    </div>


    {{-- Form Card --}}
    <div class="card shadow border-0">

        <div class="card-header">

            <h5 class="mb-0">
                <i class="bi bi-person-plus"></i>
                Add Incident Issue
            </h5>

        </div>
      
        <div class="card-body">

            <form id="incidentForm"
                action="{{ route('incident_issue_register.store') }}"
                method="POST">

                @csrf

                {{-- Asset Search --}}
                <div class="row">

                    <div class="col-md-8">
                        <label class="form-label">
                            Search Device
                        </label>

                        <div class="input-group">

                            <input type="text"
                                id="asset_search"
                                class="form-control"
                                placeholder="Search Tag No, Serial No or Custodian Name">

                            <button type="button"
                                    class="btn btn-primary"
                                    id="searchAssetBtn">
                                Search
                            </button>

                        </div>

                        <small class="text-muted">
                            Search by Tag No, Serial Number or Custodian Name.
                        </small>
                    </div>

                </div>

                {{-- Search Results --}}
                <div class="row mt-4">

                    <div class="col-md-12">

                        <div id="assetSearchResult"
                            style="display:none;">

                            <h6>Search Result</h6>

                            <div class="table-responsive">

                                <table class="table table-bordered table-hover">

                                    <thead>
                                        <tr>
                                            <th>Sl No</th>
                                            <th>Tag No</th>
                                            <th>Serial No</th>
                                            <th>Asset Model</th>
                                            <th>Asset Type</th>
                                            <th>Custodian</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>

                                    <tbody id="assetSearchBody">
                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>

                {{-- Selected Asset Preview --}}
                <div class="row mt-4"
                    id="assetPreviewSection"
                    style="display:none;">

                    <div class="col-md-12">

                        <div class="card border">

                            <div class="card-header">
                                <strong>Selected Asset Preview</strong>
                            </div>

                            <div class="card-body">

                                <input type="hidden" name="asset_issue_register_id" id="asset_issue_register_id">
                                                                      
                                <div class="row">

                                    <div class="col-md-4 mb-3">
                                        <strong>Tag No:</strong>
                                        <div id="preview_tag_no">-</div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <strong>Serial No:</strong>
                                        <div id="preview_serial_no">-</div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <strong>Asset Model:</strong>
                                        <div id="preview_asset_model">-</div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <strong>Asset Type:</strong>
                                        <div id="preview_asset_type">-</div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <strong>Custodian:</strong>
                                        <div id="preview_custodian">-</div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <strong>Location:</strong>
                                        <div id="preview_location">-</div>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <strong>Station:</strong>
                                        <div id="preview_station">-</div>
                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                {{-- Incident Details --}}
                <div id="incidentDetailsSection"
                    style="display:none;">

                    <hr class="my-4">

                    <div class="row">

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Select Engineer
                                <span class="text-danger">*</span>
                            </label>

                            <select name="support_user_id" id="support_user_id" class="form-select">                                   

                                <option value="">
                                    Select Engineer
                                </option>

                                @foreach($engineers as $engineer)

                                    <option value="{{ $engineer->id }}">
                                        {{ $engineer->name }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="col-md-6 mb-3">

                            <label class="form-label">
                                Issue Category
                                <span class="text-danger">*</span>
                            </label>

                            <select name="category_id" id="category_id" class="form-select">                                   

                                <option value="">
                                    Select Issue Category
                                </option>

                                @foreach($categories as $category)

                                    <option value="{{ $category->id }}">
                                        {{ $category->title }}
                                    </option>

                                @endforeach

                            </select>

                        </div>

                        <div class="col-md-12 mb-3">

                            <label class="form-label">
                                Issue Details
                                <span class="text-danger">*</span>
                            </label>

                            <textarea name="remarks" id="remarks" class="form-control" rows="5"></textarea>                                  

                        </div>

                    </div>

                    <div class="text-end">

                        <button type="submit" class="btn btn-primary">                              
                            Generate Incident
                        </button>

                    </div>

                </div>

            </form>

        </div


    </div>


    @push('scripts')

    <script>

        $(document).ready(function () {

            /*
            |--------------------------------------------------------------------------
            | Search Asset
            |--------------------------------------------------------------------------
            */

            $('#searchAssetBtn').on('click', function () {

                let search = $('#asset_search').val().trim();

                if (search === '') {

                    Swal.fire({
                        icon: 'warning',
                        title: 'Search Required',
                        text: 'Please enter Tag No, Serial No or Custodian Name.',
                        confirmButtonText: 'OK'
                    });

                    return;
                }

                $.ajax({

                    url: "{{ route('incident_issue_register.searchAsset') }}",

                    type: "GET",

                    data: {
                        search: search
                    },

                    beforeSend: function () {

                        $('#searchAssetBtn')
                            .prop('disabled', true)
                            .text('Searching...');

                    },

                    success: function (response) {

                        $('#assetSearchBody').empty();

                        $('#assetPreviewSection').hide();

                        $('#incidentDetailsSection').hide();

                        $('#asset_issue_register_id').val('');

                        if (!response.status || response.data.length === 0) {

                            $('#assetSearchResult').show();

                            $('#assetSearchBody').html(`
                                <tr>
                                    <td colspan="7"
                                        class="text-center text-muted">
                                        No issued asset found.
                                    </td>
                                </tr>
                            `);

                            return;
                        }

                        $.each(response.data, function (index, asset) {

                            $('#assetSearchBody').append(`

                                <tr>

                                    <td>${index + 1}</td>

                                    <td>${asset.tag_no}</td>

                                    <td>${asset.serial_no}</td>

                                    <td>${asset.asset_model}</td>

                                    <td>${asset.asset_type}</td>

                                    <td>${asset.custodian_name}</td>

                                    <td>

                                        <button type="button" class="btn btn-sm btn-primary selectAssetBtn" data-id="${asset.id}">                                                                                             
                                            Select
                                        </button>

                                    </td>

                                </tr>

                            `);

                        });

                        $('#assetSearchResult').show();

                    },

                    error: function () {

                        Swal.fire({
                            icon: 'error',
                            title: 'Search Failed',
                            text: 'Unable to search asset.',
                            confirmButtonText: 'OK'
                        });

                    },

                    complete: function () {

                        $('#searchAssetBtn')
                            .prop('disabled', false)
                            .text('Search');

                    }

                });

            });


            /*
            |--------------------------------------------------------------------------
            | Select Asset
            |--------------------------------------------------------------------------
            */

            $(document).on('click', '.selectAssetBtn', function () {

                let id = $(this).data('id');

                $.ajax({

                    url: "{{ route('incident_issue_register.assetDetails', ':id') }}".replace(':id', id),

                    type: "GET",

                    success: function (response) {

                        if (!response.status) {

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message,
                                confirmButtonText: 'OK'
                            });

                            return;
                        }

                        let data = response.data;

                        $('#asset_issue_register_id')
                            .val(data.id);

                        $('#preview_tag_no')
                            .text(data.tag_no);

                        $('#preview_serial_no')
                            .text(data.serial_no);

                        $('#preview_asset_model')
                            .text(data.asset_model);

                        $('#preview_asset_type')
                            .text(data.asset_type);

                        $('#preview_custodian')
                            .text(data.custodian_name);

                        $('#preview_location')
                            .text(data.location);

                        $('#preview_station')
                            .text(data.station);

                        $('#assetPreviewSection').show();

                        $('#incidentDetailsSection').show();

                        $('html, body').animate({
                            scrollTop: $('#assetPreviewSection').offset().top - 100
                        }, 500);

                    },

                    error: function () {

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Unable to fetch asset details.',
                            confirmButtonText: 'OK'
                        });

                    }

                });

            });


            /*
            |--------------------------------------------------------------------------
            | Incident Form Validation
            |--------------------------------------------------------------------------
            */

            $('#incidentForm').on('submit', function (e) {

                if (!$('#asset_issue_register_id').val()) {

                    e.preventDefault();

                    Swal.fire({
                        icon: 'warning',
                        title: 'Asset Required',
                        text: 'Please select an asset.',
                        confirmButtonText: 'OK'
                    });

                    return;
                }

                if (!$('#support_user_id').val()) {

                    e.preventDefault();

                    Swal.fire({
                        icon: 'warning',
                        title: 'Engineer Required',
                        text: 'Please select an engineer.',
                        confirmButtonText: 'OK'
                    });

                    return;
                }

                if (!$('#category_id').val()) {

                    e.preventDefault();

                    Swal.fire({
                        icon: 'warning',
                        title: 'Category Required',
                        text: 'Please select an issue category.',
                        confirmButtonText: 'OK'
                    });

                    return;
                }

            });

        });

    </script>

    @endpush
    @endsection